<?php

namespace App\Repositories;

use App\Repositories\Interfaces\JobRepositoryInterface;
use Illuminate\Support\Facades\DB;

class JobRepository implements JobRepositoryInterface
{
    protected string $table = 'failed_jobs';

    /**
     * @inheritDoc
     */
    public function getFailedJobs()
    {
        return DB::table($this->table)
            ->orderBy('failed_at', 'desc')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function findFailedJob($id)
    {
        return DB::table($this->table)
            ->where('id', $id)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function deleteFailedJob($id)
    {
        return DB::table($this->table)
            ->where('id', $id)
            ->delete() > 0;
    }

    /**
     * @inheritDoc
     */
    public function deleteAllFailedJobs()
    {
        return DB::table($this->table)->delete() >= 0;
    }
}
