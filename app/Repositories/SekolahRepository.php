<?php

namespace App\Repositories;

use App\Models\LamtimSekolah;
use App\Repositories\Interfaces\SekolahRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SekolahRepository implements SekolahRepositoryInterface
{
    protected $model;

    public function __construct(LamtimSekolah $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('npsn', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama', 'asc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('npsn', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama', 'asc')->paginate($perPage);
    }

    public function find(string $id): ?LamtimSekolah
    {
        return $this->model->with(['jurusans', 'rombels'])->find($id);
    }

    public function findByNpsn(string $npsn): ?LamtimSekolah
    {
        return $this->model->where('npsn', $npsn)->first();
    }

    public function create(array $data): LamtimSekolah
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $model = $this->find($id);
        if (!$model) {
            return false;
        }
        return $model->update($data);
    }

    public function delete(string $id): bool
    {
        $model = $this->find($id);
        if (!$model) {
            return false;
        }
        return $model->delete();
    }
}
