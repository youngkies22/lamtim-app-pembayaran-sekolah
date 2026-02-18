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
            // Run backup synchronously so user gets immediate feedback
            BackupJob::dispatchSync();

            // Find the latest backup file to return its name
            $appName = config('backup.backup.name');
            $files = Storage::disk($this->disk)->allFiles($appName);
            $latestFile = null;
            $latestTime = 0;

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $time = Storage::disk($this->disk)->lastModified($file);
                    if ($time > $latestTime) {
                        $latestTime = $time;
                        $latestFile = basename($file);
                    }
                }
            }

            return [
                'success' => true,
                'message' => 'Backup berhasil dibuat.',
                'filename' => $latestFile ?? 'backup.zip'
            ];
        } catch (Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat backup: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Run Backup Synchronously (For Testing or Specific cases)
     * Or just helper to dispatch job
     */
    public function runBackup()
    {
        BackupJob::dispatchSync();
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
     * Create a backup using pure Laravel (pg_dump / mysqldump).
     */
    public function createLaravelBackup()
    {
        try {
            $connection = config('database.default');
            $dbConfig = config("database.connections.{$connection}");
            $dbName = $dbConfig['database'];
            $timestamp = date('Y-m-d-H-i-s');
            $filename = "laravel-backup-{$dbName}-{$timestamp}.sql";

            // Ensure backup directory exists
            $backupDir = storage_path('app/private/backups/laravel');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $filePath = $backupDir . '/' . $filename;

            if ($connection === 'pgsql') {
                $host = $dbConfig['host'] ?? '127.0.0.1';
                $port = $dbConfig['port'] ?? '5432';
                $username = $dbConfig['username'] ?? 'postgres';
                $password = $dbConfig['password'] ?? '';

                // Set PGPASSWORD environment variable for pg_dump
                putenv("PGPASSWORD={$password}");

                $command = sprintf(
                    'pg_dump -h %s -p %s -U %s -F p -b -v -f %s %s 2>&1',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($filePath),
                    escapeshellarg($dbName)
                );
            } elseif ($connection === 'mysql') {
                $host = $dbConfig['host'] ?? '127.0.0.1';
                $port = $dbConfig['port'] ?? '3306';
                $username = $dbConfig['username'] ?? 'root';
                $password = $dbConfig['password'] ?? '';

                $command = sprintf(
                    'mysqldump -h %s -P %s -u %s %s %s > %s 2>&1',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    $password ? '-p' . escapeshellarg($password) : '',
                    escapeshellarg($dbName),
                    escapeshellarg($filePath)
                );
            } else {
                throw new Exception("Unsupported database connection: {$connection}");
            }

            Log::info("LaravelBackup: Running command for {$connection}...");

            exec($command, $output, $exitCode);

            // Clear password from environment
            if ($connection === 'pgsql') {
                putenv("PGPASSWORD");
            }

            if ($exitCode !== 0) {
                $outputStr = implode("\n", $output);
                Log::error("LaravelBackup: Command failed. Exit code: {$exitCode}. Output: {$outputStr}");
                // Clean up failed file
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                throw new Exception("Database dump failed (exit code {$exitCode}): {$outputStr}");
            }

            // Verify file exists and has content
            if (!file_exists($filePath) || filesize($filePath) < 100) {
                if (file_exists($filePath)) unlink($filePath);
                throw new Exception("Backup file is empty or was not created.");
            }

            $size = $this->formatSize(filesize($filePath));
            Log::info("LaravelBackup: Success! File: {$filename} ({$size})");

            return [
                'success' => true,
                'message' => "Backup Laravel berhasil dibuat ({$size}).",
                'filename' => $filename,
            ];
        } catch (Exception $e) {
            Log::error('LaravelBackup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal membuat backup Laravel: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get list of Laravel native backups.
     */
    public function listLaravelBackups()
    {
        $backupDir = storage_path('app/private/backups/laravel');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
            return [];
        }

        $files = glob($backupDir . '/*.sql');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'path' => $file,
                'size' => $this->formatSize(filesize($file)),
                'created_at' => Carbon::createFromTimestamp(filemtime($file))->format('Y-m-d H:i:s'),
                'timestamp' => filemtime($file),
                'type' => 'laravel',
            ];
        }

        usort($backups, fn($a, $b) => $b['timestamp'] - $a['timestamp']);

        return $backups;
    }

    /**
     * Get Laravel backup file path for download.
     */
    public function getLaravelBackupPath($filename)
    {
        $path = storage_path('app/private/backups/laravel/' . basename($filename));
        return file_exists($path) ? $path : null;
    }

    /**
     * Delete a Laravel backup file.
     */
    public function deleteLaravelBackup($filename)
    {
        $path = storage_path('app/private/backups/laravel/' . basename($filename));
        if (file_exists($path)) {
            unlink($path);
            return true;
        }
        return false;
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
