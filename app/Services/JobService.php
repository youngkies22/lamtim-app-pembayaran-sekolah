<?php

namespace App\Services;

use App\Repositories\Interfaces\JobRepositoryInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class JobService
{
    protected JobRepositoryInterface $repository;

    public function __construct(JobRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get failed jobs with parsed payload.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFailedJobs()
    {
        return $this->repository->getFailedJobs()->map(function ($job) {
            $payload = json_decode($job->payload, true);
            $job->display_name = $payload['displayName'] ?? ($payload['data']['commandName'] ?? 'Unknown');
            return $job;
        });
    }

    /**
     * Retry a failed job.
     *
     * @param int|string $id
     * @return bool
     */
    public function retryJob($id): bool
    {
        try {
            $exitCode = Artisan::call('queue:retry', ['id' => [(string) $id]]);
            return $exitCode === 0;
        } catch (\Exception $e) {
            Log::error('Failed to retry job', ['id' => $id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Retry all failed jobs.
     *
     * @return bool
     */
    public function retryAllJobs(): bool
    {
        try {
            $exitCode = Artisan::call('queue:retry', ['id' => ['all']]);
            return $exitCode === 0;
        } catch (\Exception $e) {
            Log::error('Failed to retry all jobs', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete a failed job.
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteJob($id): bool
    {
        return $this->repository->deleteFailedJob($id);
    }

    /**
     * Flush all failed jobs.
     *
     * @return bool
     */
    public function flushJobs(): bool
    {
        return $this->repository->deleteAllFailedJobs();
    }
}
