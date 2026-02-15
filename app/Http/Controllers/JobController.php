<?php

namespace App\Http\Controllers;

use App\Services\JobService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class JobController extends Controller
{
    protected JobService $service;

    public function __construct(JobService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of failed jobs.
     */
    public function failedJobs()
    {
        try {
            $jobs = $this->service->getFailedJobs();
            return ResponseHelper::success($jobs, 'Failed jobs retrieved');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Retry a failed job.
     */
    public function retry(string $id)
    {
        try {
            $success = $this->service->retryJob($id);
            if ($success) {
                return ResponseHelper::success(null, 'Job has been queued for retry');
            }
            return ResponseHelper::error('Failed to retry job', 500);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Retry all failed jobs.
     */
    public function retryAll()
    {
        try {
            $success = $this->service->retryAllJobs();
            if ($success) {
                return ResponseHelper::success(null, 'All failed jobs have been queued for retry');
            }
            return ResponseHelper::error('Failed to retry all jobs', 500);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove a failed job.
     */
    public function destroy(string $id)
    {
        try {
            $success = $this->service->deleteJob($id);
            if ($success) {
                return ResponseHelper::success(null, 'Failed job deleted');
            }
            return ResponseHelper::error('Failed to delete job', 500);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove all failed jobs.
     */
    public function flush()
    {
        try {
            $success = $this->service->flushJobs();
            if ($success) {
                return ResponseHelper::success(null, 'All failed jobs cleared');
            }
            return ResponseHelper::error('Failed to clear jobs', 500);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
