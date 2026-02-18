<?php

namespace App\Jobs;

use App\Services\ExternalSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncExternalDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 600;

    protected ?string $entity;

    /**
     * Create a new job instance.
     */
    public function __construct(?string $entity = null)
    {
        $this->entity = $entity;
    }

    /**
     * Execute the job.
     */
    public function handle(ExternalSyncService $syncService): void
    {
        try {
            // Set execution time to unlimited for background job
            set_time_limit(0);

            // The service now handles its own progress updates internally via Cache
            $syncService->sync($this->entity);


        } catch (\Exception $e) {
            Log::error("SyncExternalDataJob failed", [
                'entity' => $this->entity ?? 'all',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Re-throw to let Laravel handle the failure (e.g. log to failed_jobs)
            throw $e;
        }
    }
}
