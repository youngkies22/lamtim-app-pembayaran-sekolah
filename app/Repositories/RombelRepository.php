<?php

namespace App\Repositories;

use App\Models\LamtimRombel;
use App\Repositories\Interfaces\RombelRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RombelRepository implements RombelRepositoryInterface
{
    protected $model;

    public function __construct(LamtimRombel $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery()
            ->with(['sekolah', 'jurusan', 'kelas']); // Eager load

        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['idJurusan'])) {
            $query->where('idJurusan', $filters['idJurusan']);
        }

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
            ->with(['sekolah', 'jurusan', 'kelas']); // Eager load

        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['idJurusan'])) {
            $query->where('idJurusan', $filters['idJurusan']);
        }

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

    public function find(string $id): ?LamtimRombel
    {
        return $this->model->with(['sekolah', 'jurusan', 'kelas'])->find($id);
    }

    public function findByKode(string $kode): ?LamtimRombel
    {
        return $this->model->where('kode', $kode)->first();
    }

    public function create(array $data): LamtimRombel
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

    public function getActive(): Collection
    {
        return $this->model->active()->with(['sekolah', 'jurusan', 'kelas'])->get();
    }

    public function getByJurusan(string $idJurusan): Collection
    {
        return $this->model->where('idJurusan', $idJurusan)
            ->where('isActive', 1)
            ->with(['sekolah', 'jurusan', 'kelas'])
            ->get();
    }

    public function getByKelas(string $idKelas): Collection
    {
        // Note: idKelas sekarang ada langsung di rombel
        return $this->model->where('idKelas', $idKelas)
            ->where('isActive', 1)
            ->with(['sekolah', 'jurusan', 'kelas'])
            ->get();
    }

    public function getBySekolah(string $idSekolah): Collection
    {
        return $this->model->where('idSekolah', $idSekolah)
            ->where('isActive', 1)
            ->with(['sekolah', 'jurusan', 'kelas'])
            ->get();
    }
}
