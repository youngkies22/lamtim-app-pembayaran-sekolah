<?php

namespace App\Repositories;

use App\Models\LamtimImportLog;
use App\Repositories\Interfaces\ImportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ImportRepository implements ImportRepositoryInterface
{
    protected $model;

    public function __construct(LamtimImportLog $model)
    {
        $this->model = $model;
    }

    /**
     * Create new import log
     */
    public function create(array $data): LamtimImportLog
    {
        return $this->model->create($data);
    }

    /**
     * Find import log by ID
     */
    public function find(string $id): ?LamtimImportLog
    {
        return $this->model->find($id);
    }

    /**
     * Update import log
     */
    public function update(string $id, array $data): bool
    {
        $model = $this->find($id);
        if (!$model) {
            return false;
        }
        return $model->update($data);
    }

    /**
     * Get all import logs
     */
    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get paginated import logs
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get import logs by type
     */
    public function getByType(string $type, array $filters = []): Collection
    {
        $filters['type'] = $type;
        return $this->all($filters);
    }

    /**
     * Get latest import log by type
     */
    public function getLatestByType(string $type): ?LamtimImportLog
    {
        return $this->model->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
