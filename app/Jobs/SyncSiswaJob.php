<?php

namespace App\Jobs;

use App\Services\ExternalSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     * Student sync can be heavy, so we give it a generous timeout if the queue driver supports it.
     */
    public $timeout = 600;

    /**
     * @param array<int, string>|null $kelasIds Local Kelas UUIDs to restrict the sync to.
     *                                          Null/empty means sync all kelas.
     */
    public function __construct(
        protected ?array $kelasIds = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ExternalSyncService $syncService): void
    {
        Log::info('SyncSiswaJob started.', ['kelas_ids' => $this->kelasIds]);

        try {
            $syncService->syncSiswaBackground($this->kelasIds);
            Log::info('SyncSiswaJob completed successfully.');
        } catch (\Exception $e) {
            Log::error('SyncSiswaJob failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            // Ensure progress is marked as failed in cache if service didn't handle it
            $syncService->updateProgress('siswa', 0, 0, 'failed');
            
            throw $e;
        }
    }
}
