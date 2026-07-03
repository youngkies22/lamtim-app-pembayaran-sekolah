<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}

    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        try {
            return ResponseHelper::success($this->service->getStats());
        } catch (\Exception $e) {
            Log::error('Dashboard stats failed', ['error' => $e->getMessage()]);
            return ResponseHelper::error('Gagal memuat statistik dashboard', 500);
        }
    }

    /**
     * Get recent payments for dashboard
     */
    public function recentPayments(Request $request)
    {
        try {
            $limit = min((int) $request->get('limit', 5), 50);

            return ResponseHelper::success($this->service->getRecentPayments($limit));
        } catch (\Exception $e) {
            Log::error('Dashboard recent payments failed', ['error' => $e->getMessage()]);
            return ResponseHelper::error('Gagal memuat data pembayaran terbaru', 500);
        }
    }
}
