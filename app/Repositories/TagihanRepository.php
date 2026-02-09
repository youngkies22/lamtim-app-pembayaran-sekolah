<?php

namespace App\Repositories;

use App\Models\LamtimTagihan;
use App\Repositories\Interfaces\TagihanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TagihanRepository implements TagihanRepositoryInterface
{
    protected $model;

    public function __construct(LamtimTagihan $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery()
            ->with(['siswa', 'masterPembayaran']); // Eager load to prevent N+1

        // Apply filters
        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['idSiswa'])) {
            $query->where('idSiswa', $filters['idSiswa']);
        }

        if (isset($filters['idMasterPembayaran'])) {
            $query->where('idMasterPembayaran', $filters['idMasterPembayaran']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if (isset($filters['start_date'])) {
            $query->where('tanggalTagihan', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('tanggalTagihan', '<=', $filters['end_date']);
        }

        // Filter by jenis pembayaran (dari master pembayaran)
        if (isset($filters['jenisPembayaran'])) {
            $query->whereHas('masterPembayaran', function($q) use ($filters) {
                $q->where('jenisPembayaran', $filters['jenisPembayaran']);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['siswa', 'masterPembayaran']); // Eager load to prevent N+1

        // Apply filters
        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['idSiswa'])) {
            $query->where('idSiswa', $filters['idSiswa']);
        }

        if (isset($filters['idMasterPembayaran'])) {
            $query->where('idMasterPembayaran', $filters['idMasterPembayaran']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('siswa', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if (isset($filters['start_date'])) {
            $query->where('tanggalTagihan', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('tanggalTagihan', '<=', $filters['end_date']);
        }

        // Filter by jenis pembayaran (dari master pembayaran)
        if (isset($filters['jenisPembayaran'])) {
            $query->whereHas('masterPembayaran', function($q) use ($filters) {
                $q->where('jenisPembayaran', $filters['jenisPembayaran']);
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(string $id): ?LamtimTagihan
    {
        return $this->model->with(['siswa', 'masterPembayaran', 'invoices'])->find($id);
    }

    public function findBySiswa(string $idSiswa, string $idMasterPembayaran): ?LamtimTagihan
    {
        return $this->model->where('idSiswa', $idSiswa)
            ->where('idMasterPembayaran', $idMasterPembayaran)
            ->where('isActive', 1)
            ->first();
    }

    public function create(array $data): LamtimTagihan
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

    public function getBelumLunas(array $filters = []): Collection
    {
        $query = $this->model->belumLunas()
            ->with(['siswa', 'masterPembayaran']);

        if (isset($filters['idMasterPembayaran'])) {
            $query->where('idMasterPembayaran', $filters['idMasterPembayaran']);
        }

        return $query->get();
    }

    public function getLunas(array $filters = []): Collection
    {
        $query = $this->model->lunas()
            ->with(['siswa', 'masterPembayaran']);

        if (isset($filters['idMasterPembayaran'])) {
            $query->where('idMasterPembayaran', $filters['idMasterPembayaran']);
        }

        return $query->get();
    }

    public function getBySiswa(string $idSiswa): Collection
    {
        return $this->model->where('idSiswa', $idSiswa)
            ->where('isActive', 1)
            ->with(['masterPembayaran'])
            ->get();
    }

    public function getByMasterPembayaran(string $idMasterPembayaran): Collection
    {
        return $this->model->where('idMasterPembayaran', $idMasterPembayaran)
            ->where('isActive', 1)
            ->with(['siswa'])
            ->get();
    }
}
