<?php

namespace App\Services;

use App\Models\Closing;
use App\Models\LamtimPembayaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClosingService
{
    protected $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }
    
    /**
     * Get daily closings for a specific month and year.
     */
    public function getDailyClosings(int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $startDate->daysInMonth;
        
        // Get existing closings for this month
        $closings = Closing::daily()
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->keyBy(function ($item) {
                return $item->date->format('Y-m-d');
            });

        $results = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
            $closing = $closings->get($date);

            if ($closing && $closing->status === 'closed') {
                $status = 'closed';
                $income = $closing->total_income;
                $closedAt = $closing->closed_at;
                $user = $closing->user;
                $id = $closing->id;
            } else {
                $status = 'open';
                // Calculate live income if open
                $income = LamtimPembayaran::whereDate('tanggalBayar', $date)
                    ->where('status', 1) // Valid payments only
                    ->sum('nominalBayar');
                $closedAt = null;
                $user = null;
                $id = $closing ? $closing->id : null;
            }

            $results[] = [
                'date' => $date,
                'status' => $status,
                'total_income' => (float) $income,
                'closed_at' => $closedAt,
                'user' => $user,
                'id' => $id,
                'is_today' => $date === now()->format('Y-m-d'),
            ];
        }

        return $results;
    }

    /**
     * Get monthly closings for a specific year.
     */
    public function getMonthlyClosings(int $year): array
    {
        // Get existing monthly closings for this year
        $closings = Closing::monthly()
            ->where('year', $year)
            ->get()
            ->keyBy('month');

        $results = [];
        for ($m = 1; $m <= 12; $m++) {
            $closing = $closings->get($m);

            if ($closing && $closing->status === 'closed') {
                $status = 'closed';
                $income = $closing->total_income;
                $closedAt = $closing->closed_at;
                $user = $closing->user;
                $id = $closing->id;
            } else {
                $status = 'open';
                // Calculate live income if open
                $income = LamtimPembayaran::whereMonth('tanggalBayar', $m)
                    ->whereYear('tanggalBayar', $year)
                    ->where('status', 1)
                    ->sum('nominalBayar');
                $closedAt = null;
                $user = null;
                $id = $closing ? $closing->id : null;
            }

            $results[] = [
                'month' => $m,
                'month_name' => Carbon::create()->month($m)->translatedFormat('F'),
                'year' => $year,
                'status' => $status,
                'total_income' => (float) $income,
                'closed_at' => $closedAt,
                'user' => $user,
                'id' => $id,
                'is_current_month' => $m === now()->month && $year == now()->year,
            ];
        }

        return $results;
    }

    /**
     * Close a specific date (Daily Closing).
     */
    public function storeDaily(string $date)
    {
        return DB::transaction(function () use ($date) {
            // Check if already closed
            $existing = Closing::daily()
                ->where('date', $date)
                ->first();

            if ($existing && $existing->status === 'closed') {
                throw new \Exception('Date is already closed.');
            }

            // Calculate total income
            $totalIncome = LamtimPembayaran::whereDate('tanggalBayar', $date)
                ->where('status', 1)
                ->sum('nominalBayar');

            $closing = Closing::updateOrCreate(
                ['type' => 'daily', 'date' => $date],
                [
                    'status' => 'closed',
                    'closed_at' => now(),
                    'user_id' => Auth::id(),
                    'total_income' => $totalIncome,
                ]
            );
            
            // Trigger Backup
            // Implement automatic database backup during closing process
            // If dispatch fails, rollback the transaction.
            $backupResult = $this->backupService->createBackup();
            
            if (!$backupResult['success']) {
                // Throw exception to trigger rollback
                throw new \Exception('Closing failed: Unable to initiate backup process. ' . $backupResult['message']);
            }

            return $closing;
        });
    }

    /**
     * Close a specific month (Monthly Closing).
     */
    public function storeMonthly(int $month, int $year)
    {
        return DB::transaction(function () use ($month, $year) {
            // Check if already closed
            $existing = Closing::monthly()
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existing && $existing->status === 'closed') {
                throw new \Exception('Month is already closed.');
            }
            
            // Prevent closing future months
            $requestedDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            if ($requestedDate->isFuture()) {
                throw new \Exception('Cannot close future months.');
            }

            // Check for open daily closings record (prevent closing if a day is explicitly open)
            $openDailyClosings = Closing::daily()
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 'open')
                ->exists();

            if ($openDailyClosings) {
                throw new \Exception('Cannot close month. There are re-opened days in this month. Please close them first.');
            }

            // Calculate total income for the month
            $totalIncome = LamtimPembayaran::whereMonth('tanggalBayar', $month)
                ->whereYear('tanggalBayar', $year)
                ->where('status', 1)
                ->sum('nominalBayar');

            $closing = Closing::updateOrCreate(
                ['type' => 'monthly', 'month' => $month, 'year' => $year],
                [
                    'status' => 'closed',
                    'closed_at' => now(),
                    'user_id' => Auth::id(),
                    'total_income' => $totalIncome,
                ]
            );

            // Trigger Backup
            // Implement automatic database backup during closing process
            // If dispatch fails, rollback the transaction.
            $backupResult = $this->backupService->createBackup();

            if (!$backupResult['success']) {
                // Throw exception to trigger rollback
                throw new \Exception('Closing failed: Unable to initiate backup process. ' . $backupResult['message']);
            }

            return $closing;
        });
    }

    /**
     * Reopen a closing period.
     */
    public function reopen(string $id)
    {
        $closing = Closing::findOrFail($id);

        // If it's a daily closing, check if the month is closed
        if ($closing->type === 'daily') {
            $monthClosed = Closing::isMonthlyClosed($closing->date->month, $closing->date->year);
            if ($monthClosed) {
                throw new \Exception('Cannot reopen day. The month is closed. Reopen the month first.');
            }
        }

        $closing->update([
            'status' => 'open',
            'closed_at' => null,
            'user_id' => null,
        ]);

        return $closing;
    }

    /**
     * Check status of a date.
     */
    public function checkStatus(string $date): array
    {
        $isClosed = Closing::isDateClosed($date);

        return [
            'date' => $date,
            'is_closed' => $isClosed,
            'message' => $isClosed ? 'Date is closed.' : 'Date is open.'
        ];
    }
}
