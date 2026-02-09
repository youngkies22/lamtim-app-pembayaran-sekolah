<?php

namespace App\Services;

use App\Repositories\Interfaces\ImportRepositoryInterface;
use App\Models\LamtimImportLog;
use App\Jobs\ProcessImportJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportService
{
    protected $repository;

    public function __construct(ImportRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Upload file and create import log
     */
    public function uploadFile(string $type, UploadedFile $file): LamtimImportLog
    {
        try {
            DB::beginTransaction();

            // Validate file
            $this->validateFile($file);

            // Generate filename
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $storedFilename = Str::uuid() . '.' . $extension;
            $filePath = 'imports/' . $type . '/' . $storedFilename;

            // Store file
            $storedPath = $file->storeAs('imports/' . $type, $storedFilename);

            // Create import log
            $importLog = $this->repository->create([
                'type' => $type,
                'filename' => $filename,
                'file_path' => $storedPath,
                'status' => 'pending',
                'createdBy' => auth()->id(),
            ]);

            // Dispatch job
            ProcessImportJob::dispatch($importLog);

            DB::commit();

            Log::info('Import file uploaded', [
                'import_log_id' => $importLog->id,
                'type' => $type,
                'filename' => $filename,
            ]);

            return $importLog;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload import file', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        $allowedExtensions = ['xlsx', 'xls', 'csv'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception('Format file tidak didukung. Gunakan file Excel (.xlsx, .xls) atau CSV (.csv)');
        }

        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSize) {
            throw new \Exception('Ukuran file terlalu besar. Maksimal 10MB');
        }
    }

    /**
     * Get import log by ID
     */
    public function getImportLog(string $id): ?LamtimImportLog
    {
        return $this->repository->find($id);
    }

    /**
     * Get import progress
     */
    public function getProgress(string $id): array
    {
        $importLog = $this->repository->find($id);
        
        if (!$importLog) {
            return [
                'status' => 'not_found',
                'progress' => 0,
            ];
        }

        return [
            'id' => $importLog->id,
            'type' => $importLog->type,
            'filename' => $importLog->filename,
            'status' => $importLog->status,
            'progress' => $importLog->progress,
            'total_rows' => $importLog->total_rows,
            'processed_rows' => $importLog->processed_rows,
            'success_rows' => $importLog->success_rows,
            'failed_rows' => $importLog->failed_rows,
            'error_message' => $importLog->error_message,
            'error_file_path' => $importLog->error_file_path,
            'errors' => $importLog->errors,
            'started_at' => $importLog->started_at?->toIso8601String(),
            'completed_at' => $importLog->completed_at?->toIso8601String(),
        ];
    }

    /**
     * Download error file
     */
    public function downloadErrorFile(string $id): ?string
    {
        $importLog = $this->repository->find($id);
        
        if (!$importLog || !$importLog->error_file_path) {
            return null;
        }

        $filePath = storage_path('app/' . $importLog->error_file_path);
        
        if (!file_exists($filePath)) {
            return null;
        }

        return $filePath;
    }

    /**
     * Download template
     */
    public function downloadTemplate(string $type): string
    {
        $templatePath = $this->generateTemplate($type);
        return $templatePath;
    }

    /**
     * Generate template Excel file
     */
    protected function generateTemplate(string $type): string
    {
        $headers = $this->getTemplateHeaders($type);
        
        // Create temporary file
        $filename = 'template_' . $type . '_' . time() . '.xlsx';
        $filePath = storage_path('app/templates/' . $filename);
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Create spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        // Save file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * Get template headers based on type
     */
    protected function getTemplateHeaders(string $type): array
    {
        return match($type) {
            'siswa' => [
                'username',
                'nama',
                'password',
                'nis',
                'nisn',
                'jenis_kelamin',
                'agama',
                'wa_siswa',
                'wa_ortu',
                'wa_wali',
                'tahun_angkatan',
            ],
            'jurusan' => [
                'kode',
                'nama',
                'sekolah',
            ],
            'kelas' => [
                'kode',
                'nama',
            ],
            'rombel' => [
                'kode',
                'nama',
                'sekolah',
                'jurusan',
                'is_active',
            ],
            default => throw new \Exception('Unknown import type: ' . $type),
        };
    }

    /**
     * Get import logs
     */
    public function getImportLogs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }
}
