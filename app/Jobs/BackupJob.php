<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class BackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1200; // 20 minutes

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $appName = config('backup.backup.name');
        
        Log::info("BackupJob: Initializing backup process for {$appName}...");
        Log::info("BackupJob: Original DB Connection: " . config('database.default'));

        // 1. FORCE CONFIGURATION OVERRIDES
        // ==============================================================================
        
        // Force Database Connection to MySQL
        config(['database.default' => 'mysql']);
        
        // Force Backup Source to ONLY use 'mysql' connection
        config(['backup.backup.source.databases' => ['mysql']]);
        
        // Force Storage Configuration
        // Ensure 'backups' disk exists and points to storage/app/private/backups
        config(['filesystems.disks.backups' => [
            'driver' => 'local',
            'root' => storage_path('app/private/backups'), // Laravel 11/12 standard
            'throw' => false,
        ]]);
        
        config(['backup.backup.destination.disks' => ['backups']]);
        
        // Verify Config
        Log::info("BackupJob: Forced DB Connection: " . config('database.default'));
        Log::info("BackupJob: Target Databases: " . implode(', ', config('backup.backup.source.databases')));
        Log::info("BackupJob: Backup Root Path: " . config('filesystems.disks.backups.root'));

        try {
            // 2. RUN BACKUP
            // ==============================================================================
            
            // We disable signals to prevent interruption issues in some environments
            $exitCode = Artisan::call('backup:run', [
                '--only-db' => true,
                '--disable-notifications' => true,
            ]);

            $output = Artisan::output();
            
            Log::info("BackupJob: Artisan Output: " . $output);

            if ($exitCode !== 0) {
                // Check if it's the specific pg_dump error and provide a helpful hint
                if (str_contains($output, 'pg_dump') || str_contains($output, 'PostgreSql')) {
                    throw new Exception("Backup failed. It seems the system is still trying to use PostgreSQL. Please check your .env file and ensure DB_CONNECTION=mysql. \nFull Output: {$output}");
                }
                throw new Exception("Backup command failed with exit code {$exitCode}. Output: {$output}");
            }

            // 3. VALIDATE BACKUP
            // ==============================================================================
            $disk = Storage::disk('backups');
            $files = $disk->allFiles($appName);
            
            // Get the latest file
            $latestFile = null;
            $latestTime = 0;

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $time = $disk->lastModified($file);
                    if ($time > $latestTime) {
                        $latestTime = $time;
                        $latestFile = $file;
                    }
                }
            }

            if (!$latestFile) {
                throw new Exception("Backup command ran successfully but no zip file was found in " . $disk->path($appName));
            }

            // Check size
            $size = $disk->size($latestFile);
            if ($size < 1024) { 
                 throw new Exception("Backup file is empty or too small ({$size} bytes). Path: " . $disk->path($latestFile));
            }

            Log::info("BackupJob: Success! Backup stored at: " . $disk->path($latestFile) . " (Size: " . round($size / 1024, 2) . "KB)");

        } catch (Exception $e) {
            Log::error('BackupJob: Failed. ' . $e->getMessage());
            throw $e;
        }
    }
}
