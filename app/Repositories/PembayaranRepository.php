<?php

namespace App\Repositories;

use App\Models\LamtimPembayaran;
use App\Repositories\Interfaces\PembayaranRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PembayaranRepository implements PembayaranRepositoryInterface
{
    protected $model;

    public function __construct(LamtimPembayaran $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery()
            ->with(['siswa', 'invoice', 'tagihan', 'masterPembayaran']); // Eager load to prevent N+1

        // Apply filters
        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['idSiswa'])) {
            $query->where('idSiswa', $filters['idSiswa']);
        }

        if (isset($filters['idInvoice'])) {
            $query->where('idInvoice', $filters['idInvoice']);
        }

        if (isset($filters['idTagihan'])) {
            $query->where('idTagihan', $filters['idTagihan']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['isVerified'])) {
            $query->where('isVerified', $filters['isVerified']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('tanggalBayar', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('tanggalBayar', '<=', $filters['end_date']);
        }

        if (isset($filters['jenisPembayaran'])) {
            $query->whereHas('masterPembayaran', function($q) use ($filters) {
                $q->where('jenisPembayaran', $filters['jenisPembayaran']);
            });
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kodePembayaran', 'like', "%{$search}%")
                  ->orWhere('noReferensi', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function($sq) use ($search) {
                      $sq->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('tanggalBayar', 'desc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['siswa', 'invoice', 'tagihan', 'masterPembayaran']); // Eager load to prevent N+1

        // Apply filters (same as all method)
        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        }

        if (isset($filters['idSiswa'])) {
            $query->where('idSiswa', $filters['idSiswa']);
        }

        if (isset($filters['idInvoice'])) {
            $query->where('idInvoice', $filters['idInvoice']);
        }

        if (isset($filters['idTagihan'])) {
            $query->where('idTagihan', $filters['idTagihan']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['isVerified'])) {
            $query->where('isVerified', $filters['isVerified']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('tanggalBayar', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('tanggalBayar', '<=', $filters['end_date']);
        }

        if (isset($filters['jenisPembayaran'])) {
            $query->whereHas('masterPembayaran', function($q) use ($filters) {
                $q->where('jenisPembayaran', $filters['jenisPembayaran']);
            });
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kodePembayaran', 'like', "%{$search}%")
                  ->orWhere('noReferensi', 'like', "%{$search}%")
                  ->orWhereHas('siswa', function($sq) use ($search) {
                      $sq->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('tanggalBayar', 'desc')->paginate($perPage);
    }

    public function find(string $id): ?LamtimPembayaran
    {
        return $this->model->with(['siswa', 'invoice', 'tagihan', 'masterPembayaran'])->find($id);
    }

    public function create(array $data): LamtimPembayaran
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

    public function getBySiswa(string $idSiswa): Collection
    {
        return $this->model->where('idSiswa', $idSiswa)
            ->where('isActive', 1)
            ->with(['invoice', 'tagihan', 'masterPembayaran'])
            ->orderBy('tanggalBayar', 'desc')
            ->get();
    }

    public function getByInvoice(string $idInvoice): Collection
    {
        return $this->model->where('idInvoice', $idInvoice)
            ->where('isActive', 1)
            ->orderBy('tanggalBayar', 'desc')
            ->get();
    }

    public function getByTagihan(string $idTagihan): Collection
    {
        return $this->model->where('idTagihan', $idTagihan)
            ->where('isActive', 1)
            ->with(['invoice'])
            ->orderBy('tanggalBayar', 'desc')
            ->get();
    }

    public function getVerified(): Collection
    {
        return $this->model->verified()
            ->where('isActive', 1)
            ->with(['siswa', 'invoice', 'tagihan'])
            ->get();
    }

    public function getUnverified(): Collection
    {
        return $this->model->where('isVerified', 0)
            ->where('isActive', 1)
            ->with(['siswa', 'invoice', 'tagihan'])
            ->get();
    }
}
