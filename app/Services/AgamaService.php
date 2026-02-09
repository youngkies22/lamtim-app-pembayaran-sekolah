<?php

namespace App\Services;

use App\Repositories\Interfaces\AgamaRepositoryInterface;
use App\Models\LamtimAgama;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgamaService
{
    protected $repository;

    public function __construct(AgamaRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimAgama
    {
        return $this->repository->find($id);
    }

    public function findByKode(string $kode): ?LamtimAgama
    {
        return $this->repository->findByKode($kode);
    }

    public function create(array $data): LamtimAgama
    {
        try {
            DB::beginTransaction();

            // Validate kode uniqueness
            if ($this->repository->findByKode($data['kode'])) {
                throw new \Exception('Kode agama sudah digunakan');
            }

            $agama = $this->repository->create($data);

            DB::commit();

            Log::info('Agama created', ['id' => $agama->id, 'kode' => $agama->kode]);

            return $agama;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating agama', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimAgama
    {
        try {
            DB::beginTransaction();

            $agama = $this->repository->find($id);
            if (!$agama) {
                throw new \Exception('Agama tidak ditemukan');
            }

            // Validate kode uniqueness if changed
            if (isset($data['kode']) && $data['kode'] !== $agama->kode) {
                if ($this->repository->findByKode($data['kode'])) {
                    throw new \Exception('Kode agama sudah digunakan');
                }
            }

            $this->repository->update($id, $data);

            DB::commit();

            Log::info('Agama updated', ['id' => $id]);

            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating agama', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $agama = $this->repository->find($id);
            if (!$agama) {
                throw new \Exception('Agama tidak ditemukan');
            }

            $this->repository->delete($id);

            DB::commit();

            Log::info('Agama deleted', ['id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting agama', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
