<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\ExternalApiClient;
use App\Services\ExternalSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExternalSyncController extends Controller
{
    public function __construct(
        protected ExternalSyncService $syncService,
        protected ExternalApiClient $apiClient,
    ) {}

    /**
     * Run synchronization for NON-SISWA entities (synchronous).
     * For siswa, use the chunked endpoints below.
     */
    public function run(Request $request): JsonResponse
    {
        $request->validate([
            'entity' => 'required|string|in:' . implode(',', array_diff(config('external_api.sync_order', []), ['siswa'])),
        ]);

        $entity = $request->input('entity');

        try {
            set_time_limit(120);
            $result = $this->syncService->syncSingleEntity($entity);

            return ResponseHelper::success([
                'entity' => $entity,
                'result' => $result,
            ], 'Sinkronisasi ' . $entity . ' selesai');
        } catch (\Exception $e) {
            Log::error("Sync failed for {$entity}", ['error' => $e->getMessage()]);
            return ResponseHelper::error('Gagal sinkronisasi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Download siswa data from API to JSON file. Returns total count.
     */
    public function downloadSiswa(): JsonResponse
    {
        try {
            set_time_limit(120);
            $result = $this->syncService->downloadSiswaToFile();

            return ResponseHelper::success($result, 'Data siswa berhasil didownload');
        } catch (\Exception $e) {
            Log::error("Siswa download failed", ['error' => $e->getMessage()]);
            return ResponseHelper::error('Gagal download data siswa: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Process a chunk of siswa from the downloaded JSON file.
     */
    public function processSiswaChunk(Request $request): JsonResponse
    {
        $request->validate([
            'offset' => 'required|integer|min:0',
            'limit'  => 'required|integer|min:1|max:100',
        ]);

        try {
            set_time_limit(120);
            $result = $this->syncService->processSiswaChunk(
                $request->input('offset'),
                $request->input('limit')
            );

            return ResponseHelper::success($result, 'Chunk berhasil diproses');
        } catch (\Exception $e) {
            Log::error("Siswa chunk processing failed", [
                'offset' => $request->input('offset'),
                'error' => $e->getMessage(),
            ]);
            return ResponseHelper::error('Gagal proses chunk: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cleanup temporary siswa JSON file.
     */
    public function cleanupSiswa(): JsonResponse
    {
        try {
            $this->syncService->cleanupSiswaFile();
            $this->syncService->saveLastSyncTime();
            return ResponseHelper::success(null, 'File temporary berhasil dihapus');
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal cleanup: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Dispatch background sync for siswa.
     */
    public function dispatchSiswaSync(): JsonResponse
    {
        try {
            // This now delegates the check to the service if called from service, 
            // but here we can keep the controller check for faster response or move it to service.
            // Let's call the service method that will eventually handle the job dispatch if we want it there.
            // Actually, currently syncSiswaBackground IS the method that dispatches the job in some implementations, 
            // but in this version SyncSiswaJob calls syncSiswaBackground.
            
            // If the controller dispatches directly:
            if (!\App\Services\SettingService::isJobEnabled('job_sync_siswa_enabled')) {
                return ResponseHelper::error('Sync Siswa Job tidak aktif. Aktifkan di Pengaturan.', 400);
            }

            \App\Jobs\SyncSiswaJob::dispatch();

            return ResponseHelper::success(null, 'Sinkronisasi siswa dimulai di latar belakang');
        } catch (\Exception $e) {
            Log::error("Failed to dispatch SyncSiswaJob", ['error' => $e->getMessage()]);
            return ResponseHelper::error('Gagal memulai sinkronisasi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get real-time progress of current sync.
     */
    public function getSyncProgress(): JsonResponse
    {
        try {
            $progress = $this->syncService->getProgress();
            return ResponseHelper::success($progress);
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal mengambil progres: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get last sync status and entity statistics.
     */
    public function status(): JsonResponse
    {
        try {
            return ResponseHelper::success([
                'last_sync' => $this->syncService->getLastSyncStatus(),
                'stats' => $this->syncService->getSyncStats(),
                'entities' => config('external_api.sync_order', []),
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal mengambil status sync: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Test connection to external API.
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->apiClient->testConnection();

            if ($result['success']) {
                return ResponseHelper::success($result, $result['message']);
            }

            return ResponseHelper::error($result['message'], 502);
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal menguji koneksi: ' . $e->getMessage(), 500);
        }
    }
}
