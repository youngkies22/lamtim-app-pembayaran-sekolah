<?php

namespace App\Jobs;

use App\Models\LamtimImportLog;
use App\Imports\SiswaImport;
use App\Imports\JurusanImport;
use App\Imports\KelasImport;
use App\Imports\RombelImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importLog;

    /**
     * Create a new job instance.
     */
    public function __construct(LamtimImportLog $importLog)
    {
        $this->importLog = $importLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Mark as processing
            $this->importLog->markAsProcessing();

            // Get file path
            $filePath = storage_path('app/' . $this->importLog->file_path);
            
            if (!file_exists($filePath)) {
                throw new \Exception('File tidak ditemukan: ' . $filePath);
            }

            // Count total rows
            $totalRows = $this->countRows($filePath);
            $this->importLog->update(['total_rows' => $totalRows]);

            // Process import based on type
            $importClass = $this->getImportClass($this->importLog->type);
            $import = new $importClass($this->importLog->id);

            // Import with progress tracking
            $result = Excel::import($import, $filePath);

            // Get failures from import
            $failures = $import->failures();
            $errors = $import->getErrors();

            // Combine all errors
            $allErrors = [];
            foreach ($failures as $failure) {
                $allErrors[] = [
                    'row' => $failure->row(),
                    'errors' => $failure->errors(),
                ];
            }
            $allErrors = array_merge($allErrors, $errors);

            // Calculate success and failed rows
            $successRows = $totalRows - count($allErrors);
            $failedRows = count($allErrors);

            // Generate error file if there are errors
            $errorFilePath = null;
            if (count($allErrors) > 0) {
                $errorFilePath = $this->generateErrorFile($allErrors, $filePath);
            }

            // Update import log
            $this->importLog->markAsCompleted($successRows, $failedRows);
            $this->importLog->update([
                'errors' => $allErrors,
                'error_file_path' => $errorFilePath,
            ]);

            Log::info('Import completed', [
                'import_log_id' => $this->importLog->id,
                'type' => $this->importLog->type,
                'success' => $successRows,
                'failed' => $failedRows,
            ]);

        } catch (\Exception $e) {
            Log::error('Import failed', [
                'import_log_id' => $this->importLog->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->importLog->markAsFailed($e->getMessage());
        }
    }

    /**
     * Get import class based on type
     */
    protected function getImportClass(string $type): string
    {
        return match($type) {
            'siswa' => SiswaImport::class,
            'jurusan' => JurusanImport::class,
            'kelas' => KelasImport::class,
            'rombel' => RombelImport::class,
            default => throw new \Exception('Unknown import type: ' . $type),
        };
    }

    /**
     * Count total rows in Excel file
     */
    protected function countRows(string $filePath): int
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            
            // Subtract 1 for header row
            return max(0, $highestRow - 1);
        } catch (\Exception $e) {
            Log::warning('Failed to count rows, using default', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Generate error Excel file
     */
    protected function generateErrorFile(array $errors, string $originalFilePath): ?string
    {
        try {
            // Create error file path
            $errorFileName = 'import_errors_' . $this->importLog->id . '_' . time() . '.xlsx';
            $errorFilePath = 'imports/errors/' . $errorFileName;

            // Load original file
            $spreadsheet = IOFactory::load($originalFilePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Add error column
            $highestColumn = $worksheet->getHighestColumn();
            $errorColumn = ++$highestColumn; // Next column after last

            // Set header
            $worksheet->setCellValue($errorColumn . '1', 'Error');

            // Add errors to rows
            foreach ($errors as $error) {
                $row = $error['row'];
                $errorMessage = implode('; ', $error['errors']);
                $worksheet->setCellValue($errorColumn . $row, $errorMessage);
            }

            // Save error file
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save(storage_path('app/' . $errorFilePath));

            return $errorFilePath;
        } catch (\Exception $e) {
            Log::error('Failed to generate error file', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        $this->importLog->markAsFailed($exception->getMessage());
        
        Log::error('Import job failed', [
            'import_log_id' => $this->importLog->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
