<?php

namespace App\Repositories\Interfaces;

interface JobRepositoryInterface
{
    /**
     * Get all failed jobs.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFailedJobs();

    /**
     * Find a failed job by ID.
     *
     * @param int|string $id
     * @return object|null
     */
    public function findFailedJob($id);

    /**
     * Delete a failed job by ID.
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteFailedJob($id);

    /**
     * Delete all failed jobs.
     *
     * @return bool
     */
    public function deleteAllFailedJobs();
}
