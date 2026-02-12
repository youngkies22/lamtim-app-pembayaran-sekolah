<?php

namespace App\Services;

use App\Repositories\Interfaces\JenisPembayaranRepositoryInterface;
use App\Models\LamtimJenisPembayaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JenisPembayaranService
{
    protected $repository;

    public function __construct(JenisPembayaranRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimJenisPembayaran
    {
        return $this->repository->find($id);
    }

    public function findByKode(string $kode): ?LamtimJenisPembayaran
    {
        return $this->repository->findByKode($kode);
    }

    public function create(array $data): LamtimJenisPembayaran
    {
        try {
            DB::beginTransaction();

            // Validate kode uniqueness
            if ($this->repository->findByKode($data['kode'])) {
                throw new \Exception('Kode jenis pembayaran sudah digunakan');
            }

            $data['createdBy'] = auth()->id();
            $jenis = $this->repository->create($data);

            DB::commit();



            return $jenis;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating jenis pembayaran', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimJenisPembayaran
    {
        try {
            DB::beginTransaction();

            $jenis = $this->repository->find($id);
            if (!$jenis) {
                throw new \Exception('Jenis pembayaran tidak ditemukan');
            }

            // Validate kode uniqueness if changed
            if (isset($data['kode']) && $data['kode'] !== $jenis->kode) {
                if ($this->repository->findByKode($data['kode'])) {
                    throw new \Exception('Kode jenis pembayaran sudah digunakan');
                }
            }

            $data['updatedBy'] = auth()->id();
            $this->repository->update($id, $data);

            DB::commit();



            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating jenis pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $jenis = $this->repository->find($id);
            if (!$jenis) {
                throw new \Exception('Jenis pembayaran tidak ditemukan');
            }

            $this->repository->delete($id);

            DB::commit();



            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting jenis pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
