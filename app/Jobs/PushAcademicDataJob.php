<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PushAcademicDataJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected \Illuminate\Database\Eloquent\Model $model
    ) {}

    /**
     * Execute the job.
     */
    public function handle(\App\Services\Interfaces\AcademicIntegrationServiceInterface $service): void
    {
        if ($this->model instanceof \App\Models\LamtimTagihan) {
            $service->pushTagihan($this->model);
        } elseif ($this->model instanceof \App\Models\LamtimPembayaran) {
            $service->pushPembayaran($this->model);
        }
    }
}
