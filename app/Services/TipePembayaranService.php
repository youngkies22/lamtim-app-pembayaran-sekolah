<?php

namespace App\Services;

use App\Repositories\Interfaces\TipePembayaranRepositoryInterface;
use App\Models\LamtimTipePembayaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TipePembayaranService
{
    protected $repository;

    public function __construct(TipePembayaranRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimTipePembayaran
    {
        return $this->repository->find($id);
    }

    public function findByKode(string $kode): ?LamtimTipePembayaran
    {
        return $this->repository->findByKode($kode);
    }

    public function create(array $data): LamtimTipePembayaran
    {
        try {
            DB::beginTransaction();

            // Validate kode uniqueness
            if ($this->repository->findByKode($data['kode'])) {
                throw new \Exception('Kode tipe pembayaran sudah digunakan');
            }

            $data['createdBy'] = auth()->id();
            $tipe = $this->repository->create($data);

            DB::commit();

            Log::info('Tipe Pembayaran created', ['id' => $tipe->id, 'kode' => $tipe->kode]);

            return $tipe;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating tipe pembayaran', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimTipePembayaran
    {
        try {
            DB::beginTransaction();

            $tipe = $this->repository->find($id);
            if (!$tipe) {
                throw new \Exception('Tipe pembayaran tidak ditemukan');
            }

            // Validate kode uniqueness if changed
            if (isset($data['kode']) && $data['kode'] !== $tipe->kode) {
                if ($this->repository->findByKode($data['kode'])) {
                    throw new \Exception('Kode tipe pembayaran sudah digunakan');
                }
            }

            $data['updatedBy'] = auth()->id();
            $this->repository->update($id, $data);

            DB::commit();

            Log::info('Tipe Pembayaran updated', ['id' => $id]);

            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating tipe pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $tipe = $this->repository->find($id);
            if (!$tipe) {
                throw new \Exception('Tipe pembayaran tidak ditemukan');
            }

            $this->repository->delete($id);

            DB::commit();

            Log::info('Tipe Pembayaran deleted', ['id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting tipe pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
