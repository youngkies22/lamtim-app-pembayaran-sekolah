<?php

namespace App\Services;

use App\Repositories\Interfaces\KategoriPembayaranRepositoryInterface;
use App\Models\LamtimKategoriPembayaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KategoriPembayaranService
{
    protected $repository;

    public function __construct(KategoriPembayaranRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function find(string $id): ?LamtimKategoriPembayaran
    {
        return $this->repository->find($id);
    }

    public function findByKode(string $kode): ?LamtimKategoriPembayaran
    {
        return $this->repository->findByKode($kode);
    }

    public function create(array $data): LamtimKategoriPembayaran
    {
        try {
            DB::beginTransaction();

            // Validate kode uniqueness
            if ($this->repository->findByKode($data['kode'])) {
                throw new \Exception('Kode kategori pembayaran sudah digunakan');
            }

            $data['createdBy'] = auth()->id();
            $kategori = $this->repository->create($data);

            DB::commit();

            Log::info('Kategori Pembayaran created', ['id' => $kategori->id, 'kode' => $kategori->kode]);

            return $kategori;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating kategori pembayaran', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimKategoriPembayaran
    {
        try {
            DB::beginTransaction();

            $kategori = $this->repository->find($id);
            if (!$kategori) {
                throw new \Exception('Kategori pembayaran tidak ditemukan');
            }

            // Validate kode uniqueness if changed
            if (isset($data['kode']) && $data['kode'] !== $kategori->kode) {
                if ($this->repository->findByKode($data['kode'])) {
                    throw new \Exception('Kode kategori pembayaran sudah digunakan');
                }
            }

            $data['updatedBy'] = auth()->id();
            $this->repository->update($id, $data);

            DB::commit();

            Log::info('Kategori Pembayaran updated', ['id' => $id]);

            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating kategori pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $kategori = $this->repository->find($id);
            if (!$kategori) {
                throw new \Exception('Kategori pembayaran tidak ditemukan');
            }

            $this->repository->delete($id);

            DB::commit();

            Log::info('Kategori Pembayaran deleted', ['id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting kategori pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
