<?php

namespace App\Services;

use App\Jobs\BackupJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class BackupService
{
    protected $disk = 'backups'; // Using the dedicated disk defined in config/filesystems.php

    /**
     * Get list of backups
     */
    public function listBackups()
    {
        // Ensure backup directory exists
        if (!Storage::disk($this->disk)->exists('')) {
            Storage::disk($this->disk)->makeDirectory('');
        }

        // Spatie stores backups in a folder named after the application name (from config/backup.php)
        // Check config backup.name
        $appName = config('backup.backup.name');
        
        // Files are usually stored in directly or inside the app name folder depending on config.
        // Default spatie config uses `name` as a subfolder? 
        // Let's check where spatie puts files. Usually it is inside the disk root if 'name' is just a label.
        // Actually Spatie Backup puts them in a folder named after the application name within the disk.
        
        $files = Storage::disk($this->disk)->allFiles($appName);
        
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                $backups[] = [
                    'filename' => basename($file),
                    'path' => $file,
                    'size' => $this->formatSize(Storage::disk($this->disk)->size($file)),
                    'created_at' => Carbon::createFromTimestamp(Storage::disk($this->disk)->lastModified($file))->format('Y-m-d H:i:s'),
                    'timestamp' => Storage::disk($this->disk)->lastModified($file) // for sorting
                ];
            }
        }

        // Sort by newest first
        usort($backups, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return $backups;
    }

    /**
     * Create a new backup (Trigger Job)
     */
    public function createBackup()
    {
        try {
            // Dispatch the job to run in background
            BackupJob::dispatch();
            
            return [
                'success' => true,
                'message' => 'Backup process started in background. It may take a few minutes.',
                'filename' => 'processing...'
            ];
        } catch (Exception $e) {
            Log::error('Backup dispatch failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to start backup: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Run Backup Synchronously (For Testing or Specific cases)
     * Or just helper to dispatch job
     */
    public function runBackup()
    {
        BackupJob::dispatch();
    }

    /**
     * Delete a backup
     */
    public function deleteBackup($filename)
    {
        // We need to find the full path. Since we know the filename, we can search or construct path.
        $appName = config('backup.backup.name');
        $path = $appName . '/' . $filename;
        
        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
            return true;
        }
        return false;
    }

    /**
     * Get backup path for download
     */
    public function getBackupPath($filename)
    {
        $appName = config('backup.backup.name');
        $path = $appName . '/' . $filename;

        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->path($path);
        }
        return null;
    }

    /**
     * Format bytes to readable size
     */
    protected function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
