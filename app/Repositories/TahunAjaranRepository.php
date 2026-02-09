<?php

namespace App\Repositories;

use App\Models\LamtimTahunAjaran;
use App\Repositories\Interfaces\TahunAjaranRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TahunAjaranRepository implements TahunAjaranRepositoryInterface
{
    protected $model;

    public function __construct(LamtimTahunAjaran $model)
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

        // Sort by kode DESC (tahun lebih besar di atas)
        return $query->orderBy('kode', 'desc')->get();
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

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(string $id): ?LamtimTahunAjaran
    {
        return $this->model->find($id);
    }

    public function findByKode(string $kode): ?LamtimTahunAjaran
    {
        return $this->model->where('kode', $kode)->first();
    }

    public function create(array $data): LamtimTahunAjaran
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

    public function getActive(): ?LamtimTahunAjaran
    {
        return $this->model->active()->first();
    }
}
