<?php

namespace App\Services;

use App\Models\LamtimInvoice;
use App\Models\LamtimSekolah;
use App\Models\LamtimTahunAjaran;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    /**
     * Get cached invoice statistics.
     */
    public function getStats(): array
    {
        $stats = Cache::rememberForever('invoice_stats', function () {
            return LamtimInvoice::where('isActive', 1)
                ->selectRaw('
                    SUM(CASE WHEN "status" = 1 THEN "nominalInvoice" ELSE 0 END) as total,
                    COUNT(CASE WHEN "status" = 1 THEN 1 END) as lunas,
                    COUNT(CASE WHEN "status" = 0 THEN 1 END) as pending,
                    COUNT(CASE WHEN "status" = 2 THEN 1 END) as cancelled
                ')
                ->first()
                ->toArray();
        });

        return [
            'total' => (int) ($stats['total'] ?? 0),
            'lunas' => (int) ($stats['lunas'] ?? 0),
            'pending' => (int) ($stats['pending'] ?? 0),
            'cancelled' => (int) ($stats['cancelled'] ?? 0),
        ];
    }

    /**
     * Get paginated invoices with filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LamtimInvoice::query()
            ->with(['siswa.currentRombel.rombel.kelas', 'tagihan', 'masterPembayaran'])
            ->where('isActive', 1);

        if (isset($filters['idSiswa'])) {
            $query->where('idSiswa', $filters['idSiswa']);
        }
        if (isset($filters['idTagihan'])) {
            $query->where('idTagihan', $filters['idTagihan']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['startDate'])) {
            $query->whereDate('tanggalInvoice', '>=', $filters['startDate']);
        }
        if (isset($filters['endDate'])) {
            $query->whereDate('tanggalInvoice', '<=', $filters['endDate']);
        }

        return $query->orderBy('tanggalInvoice', 'desc')->paginate($perPage);
    }

    /**
     * Find invoice by ID with relations.
     */
    public function find(string $id): ?LamtimInvoice
    {
        return LamtimInvoice::with(['siswa', 'tagihan', 'masterPembayaran', 'pembayarans'])->find($id);
    }

    /**
     * Build filtered query for DataTables.
     */
    public function buildDatatableQuery(array $filters = [])
    {
        $query = LamtimInvoice::query()
            ->with(['siswa.currentRombel.rombel.kelas', 'tagihan', 'masterPembayaran'])
            ->select('lamtim_invoices.*')
            ->where('isActive', 1)
            ->orderBy('tanggalInvoice', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['startDate'])) {
            $query->whereDate('tanggalInvoice', '>=', $filters['startDate']);
        }
        if (!empty($filters['endDate'])) {
            $query->whereDate('tanggalInvoice', '<=', $filters['endDate']);
        }

        return $query;
    }

    /**
     * Get export context (sekolah name, tahun ajaran).
     */
    public function getExportContext(): array
    {
        $sekolah = LamtimSekolah::first();
        $tahunAjaran = LamtimTahunAjaran::active()->first();

        return [
            'sekolahNama' => $sekolah->nama ?? 'Sekolah',
            'tahunAjaran' => $tahunAjaran->tahun ?? '',
        ];
    }
}
