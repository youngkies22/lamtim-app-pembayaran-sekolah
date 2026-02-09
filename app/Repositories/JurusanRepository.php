<?php

namespace App\Repositories;

use App\Models\LamtimJurusan;
use App\Repositories\Interfaces\JurusanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JurusanRepository implements JurusanRepositoryInterface
{
    protected $model;

    public function __construct(LamtimJurusan $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery()
            ->with(['sekolah']); // Eager load

        if (isset($filters['idSekolah'])) {
            $query->where('idSekolah', $filters['idSekolah']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama', 'asc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['sekolah']); // Eager load

        if (isset($filters['idSekolah'])) {
            $query->where('idSekolah', $filters['idSekolah']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama', 'asc')->paginate($perPage);
    }

    public function find(string $id): ?LamtimJurusan
    {
        return $this->model->with(['sekolah', 'rombels'])->find($id);
    }

    public function findByKode(string $kode): ?LamtimJurusan
    {
        return $this->model->where('kode', $kode)->first();
    }

    public function create(array $data): LamtimJurusan
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

    public function getBySekolah(string $idSekolah): Collection
    {
        return $this->model->where('idSekolah', $idSekolah)
            ->with(['sekolah'])
            ->get();
    }
}
