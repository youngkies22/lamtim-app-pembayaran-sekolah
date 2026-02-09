<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimImportLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ImportRepositoryInterface
{
    /**
     * Create new import log
     */
    public function create(array $data): LamtimImportLog;

    /**
     * Find import log by ID
     */
    public function find(string $id): ?LamtimImportLog;

    /**
     * Update import log
     */
    public function update(string $id, array $data): bool;

    /**
     * Get all import logs
     */
    public function all(array $filters = []): Collection;

    /**
     * Get paginated import logs
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get import logs by type
     */
    public function getByType(string $type, array $filters = []): Collection;

    /**
     * Get latest import log by type
     */
    public function getLatestByType(string $type): ?LamtimImportLog;
}
