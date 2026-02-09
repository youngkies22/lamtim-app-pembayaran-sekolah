<?php

namespace App\Repositories;

use App\Models\LamtimSemester;
use App\Repositories\Interfaces\SemesterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SemesterRepository implements SemesterRepositoryInterface
{
    protected $model;

    public function __construct(LamtimSemester $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('kode', 'asc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('kode', 'asc')->paginate($perPage);
    }

    public function find(string $id): ?LamtimSemester
    {
        return $this->model->find($id);
    }

    public function findByKode(string $kode): ?LamtimSemester
    {
        return $this->model->where('kode', $kode)->first();
    }

    public function create(array $data): LamtimSemester
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
        $model->isActive = 0;
        $model->save();
        return true;
    }

    public function getActive(): ?LamtimSemester
    {
        return $this->model->active()->first();
    }
}
