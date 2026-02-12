<?php

namespace App\Repositories;

use App\Models\LamtimSiswa;
use App\Repositories\Interfaces\SiswaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SiswaRepository implements SiswaRepositoryInterface
{
    protected $model;

    public function __construct(LamtimSiswa $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery()
            ->with(['agama', 'currentRombel.rombel.jurusan', 'currentRombel.rombel.kelas']); // Eager load

        // Apply filters
        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['idKelas'])) {
            $query->whereHas('rombels.rombel', function($q) use ($filters) {
                $q->where('idKelas', $filters['idKelas']);
            });
        }

        if (isset($filters['idJurusan'])) {
            $query->whereHas('rombels.rombel', function($q) use ($filters) {
                $q->where('idJurusan', $filters['idJurusan']);
            });
        }

        if (isset($filters['idRombel'])) {
            $query->whereHas('rombels', function($q) use ($filters) {
                $q->where('idRombel', $filters['idRombel'])->latest('created_at');
            });
        }

        if (isset($filters['tahunAngkatan'])) {
            $query->where('tahunAngkatan', $filters['tahunAngkatan']);
        }

        if (isset($filters['jsk'])) {
            $query->where('jsk', $filters['jsk']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama', 'asc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['agama', 'currentRombel.rombel.jurusan', 'currentRombel.rombel.kelas']); // Eager load

        // Apply same filters as all()
        if (isset($filters['isActive']) && $filters['isActive'] !== null && $filters['isActive'] !== '') {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['idKelas']) && $filters['idKelas']) {
            $query->whereHas('rombels', function($q) use ($filters) {
                $q->where('idKelas', $filters['idKelas'])->latest('created_at');
            });
        }

        if (isset($filters['idJurusan']) && $filters['idJurusan']) {
            $query->whereHas('rombels.rombel', function($q) use ($filters) {
                $q->where('idJurusan', $filters['idJurusan']);
            });
        }

        if (isset($filters['idRombel']) && $filters['idRombel']) {
            $query->whereHas('rombels', function($q) use ($filters) {
                $q->where('idRombel', $filters['idRombel'])->latest('created_at');
            });
        }

        if (isset($filters['tahunAngkatan']) && $filters['tahunAngkatan']) {
            $query->where('tahunAngkatan', $filters['tahunAngkatan']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $filters['sort'] ?? 'nama';
        $sortDirection = $filters['direction'] ?? 'asc';
        
        // Validate sort column to prevent SQL injection
        $allowedSorts = ['nama', 'nis', 'nisn', 'username', 'created_at', 'updated_at'];
        if (!in_array($sortColumn, $allowedSorts)) {
            $sortColumn = 'nama';
        }
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'asc';

        return $query->orderBy($sortColumn, $sortDirection)->paginate($perPage);
    }

    public function find(string $id): ?LamtimSiswa
    {
        return $this->model->with(['agama', 'profile', 'rombels', 'currentRombel.rombel.jurusan', 'currentRombel.rombel.kelas'])->find($id);
    }

    public function findByUsername(string $username): ?LamtimSiswa
    {
        return $this->model->where('username', $username)->first();
    }

    public function findByNis(string $nis): ?LamtimSiswa
    {
        return $this->model->where('nis', $nis)->first();
    }

    public function findByNisn(string $nisn): ?LamtimSiswa
    {
        return $this->model->where('nisn', $nisn)->first();
    }

    public function create(array $data): LamtimSiswa
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
        $model->softDelete();
        return true;
    }

    public function restore(string $id): bool
    {
        $model = $this->model->where('id', $id)->where('isActive', 0)->first();
        if (!$model) {
            return false;
        }
        $model->restore();
        return true;
    }

    public function getActive(): Collection
    {
        return $this->model->active()->with(['agama', 'currentRombel'])->get();
    }

    public function getByKelas(string $idKelas): Collection
    {
        return $this->model->whereHas('rombels.rombel', function($q) use ($idKelas) {
            $q->where('idKelas', $idKelas);
        })->active()->with(['agama', 'currentRombel.rombel.kelas'])->get();
    }

    public function getByJurusan(string $idJurusan): Collection
    {
        return $this->model->whereHas('rombels.rombel', function($q) use ($idJurusan) {
            $q->where('idJurusan', $idJurusan);
        })->active()->with(['agama', 'currentRombel'])->get();
    }

    public function getByRombel(string $idRombel): Collection
    {
        return $this->model->whereHas('rombels', function($q) use ($idRombel) {
            $q->where('idRombel', $idRombel)->latest('created_at');
        })->active()->with(['agama', 'currentRombel'])->get();
    }

    public function getByTahunAngkatan(string $tahunAngkatan): Collection
    {
        return $this->model->where('tahunAngkatan', $tahunAngkatan)
            ->active()
            ->with(['agama', 'currentRombel'])
            ->get();
    }
}
