<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\ResetDataRequest;
use App\Services\ResetDataService;
use Illuminate\Support\Facades\Log;

class ResetDataController extends Controller
{
    public function __construct(
        protected ResetDataService $service
    ) {}

    /**
     * Daftar kategori data yang bisa direset beserta jumlah barisnya saat ini.
     */
    public function index()
    {
        return ResponseHelper::success($this->service->getCategories());
    }

    /**
     * Hapus permanen kategori data yang dipilih.
     */
    public function reset(ResetDataRequest $request)
    {
        try {
            $results = $this->service->reset($request->validated()['categories']);

            return ResponseHelper::success($results, 'Data berhasil direset');
        } catch (\Exception $e) {
            Log::error('ResetData: gagal mereset data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
