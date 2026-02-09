<?php

namespace App\Repositories;

use App\Models\LamtimMasterPembayaran;
use App\Repositories\Interfaces\MasterPembayaranRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MasterPembayaranRepository implements MasterPembayaranRepositoryInterface
{
    protected $model;

    public function __construct(LamtimMasterPembayaran $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        } else {
            // Default: show only active if not specified
            $query->where('isActive', 1);
        }

        if (isset($filters['jenisPembayaran'])) {
            $query->where('jenisPembayaran', $filters['jenisPembayaran']);
        }

        if (isset($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        if (isset($filters['idKelas'])) {
            $query->where('idKelas', $filters['idKelas']);
        }

        if (isset($filters['idJurusan'])) {
            $query->where('idJurusan', $filters['idJurusan']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['jenisPembayaran'])) {
            $query->where('jenisPembayaran', $filters['jenisPembayaran']);
        }

        if (isset($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
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

    public function find(string $id): ?LamtimMasterPembayaran
    {
        return $this->model->find($id);
    }

    public function findByKode(string $kode): ?LamtimMasterPembayaran
    {
        return $this->model->where('kode', $kode)->first();
    }

    public function create(array $data): LamtimMasterPembayaran
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
        return $this->model->active()->get();
    }

    public function getByJenis(string $jenis): Collection
    {
        return $this->model->jenis($jenis)->active()->get();
    }

    public function getByKategori(string $kategori): Collection
    {
        return $this->model->where('kategori', $kategori)->active()->get();
    }
}
