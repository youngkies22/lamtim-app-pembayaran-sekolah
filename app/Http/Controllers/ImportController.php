<?php

namespace App\Http\Controllers;

use App\Services\ImportService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    protected $service;

    public function __construct(ImportService $service)
    {
        $this->service = $service;
    }

    /**
     * Upload and process import file
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|string|in:siswa,jurusan,kelas,rombel',
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
            ]);

            $file = $request->file('file');
            $type = $request->input('type');

            $importLog = $this->service->uploadFile($type, $file);

            return ResponseHelper::success([
                'import_log_id' => $importLog->id,
                'status' => $importLog->status,
            ], 'File berhasil diupload dan sedang diproses');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHelper::validationError($e->errors(), 'Validasi gagal');
        } catch (\Exception $e) {
            Log::error('Import upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get import progress
     */
    public function progress(string $id): JsonResponse
    {
        try {
            $progress = $this->service->getProgress($id);

            if ($progress['status'] === 'not_found') {
                return ResponseHelper::notFound('Import log tidak ditemukan');
            }

            return ResponseHelper::success($progress);
        } catch (\Exception $e) {
            Log::error('Get import progress failed', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Download template
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|string|in:siswa,jurusan,kelas,rombel',
            ]);

            $type = $request->input('type');
            $filePath = $this->service->downloadTemplate($type);

            if (!file_exists($filePath)) {
                return ResponseHelper::error('Template tidak ditemukan', 404);
            }

            $filename = 'template_' . $type . '.xlsx';

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Download template failed', [
                'type' => $request->input('type'),
                'error' => $e->getMessage(),
            ]);

            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Download error file
     */
    public function downloadErrorFile(string $id)
    {
        try {
            $filePath = $this->service->downloadErrorFile($id);

            if (!$filePath || !file_exists($filePath)) {
                return ResponseHelper::notFound('File error tidak ditemukan');
            }

            $importLog = $this->service->getImportLog($id);
            $filename = 'error_' . $importLog->filename;

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Exception $e) {
            Log::error('Download error file failed', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get import logs list
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['type', 'status', 'search']);
            $perPage = $request->get('per_page', 15);

            $importLogs = $this->service->getImportLogs($filters, $perPage);

            return ResponseHelper::success($importLogs);
        } catch (\Exception $e) {
            Log::error('Get import logs failed', [
                'error' => $e->getMessage(),
            ]);

            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
