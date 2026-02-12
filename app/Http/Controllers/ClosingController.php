<?php

namespace App\Http\Controllers;

use App\Services\ClosingService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class ClosingController extends Controller
{
    public function __construct(
        protected ClosingService $service
    ) {}

    /**
     * Display a listing of the resource.
     * Supports 'daily' and 'monthly' types.
     */
    public function index(Request $request)
    {
        $type = $request->query('type', 'daily');
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        if ($type === 'daily') {
            $data = $this->service->getDailyClosings($month, $year);
            return response()->json([
                'data' => $data,
                'meta' => [
                    'list_type' => 'daily',
                    'month' => $month,
                    'year' => $year
                ]
            ]);
        } else {
            $data = $this->service->getMonthlyClosings($year);
            return response()->json([
                'data' => $data,
                'meta' => [
                    'list_type' => 'monthly',
                    'year' => $year
                ]
            ]);
        }
    }

    /**
     * Close a specific date (Daily Closing).
     */
    public function storeDaily(Request $request)
    {
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
        ]);

        try {
            $closing = $this->service->storeDaily($request->date);
            return response()->json(['message' => 'Daily closing successful.', 'data' => $closing]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Close a specific month (Monthly Closing).
     * Admin only.
     */
    public function storeMonthly(Request $request)
    {
        // Enforce Admin only (assuming role 1 is admin)
        if ($request->user()->role != 1) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
        ]);

        try {
            $closing = $this->service->storeMonthly($request->month, $request->year);
            return response()->json(['message' => 'Monthly closing successful.', 'data' => $closing]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Reopen a closing period.
     * Admin only.
     */
    public function reopen(Request $request)
    {
        // Enforce Admin only
        if ($request->user()->role != 1) {
            return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
        }

        $request->validate([
            'id' => 'required|exists:closings,id',
        ]);

        try {
            $closing = $this->service->reopen($request->id);
            return response()->json(['message' => 'Period reopened successfully.', 'data' => $closing]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Check closing status for a specific date (Helper for frontend/middleware check).
     */
    public function checkStatus(Request $request)
    {
        $date = $request->query('date', now()->format('Y-m-d'));
        
        $status = $this->service->checkStatus($date);

        return response()->json($status);
    }
}

