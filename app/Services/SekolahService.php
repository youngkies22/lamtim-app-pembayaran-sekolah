<?php

namespace App\Services;

use App\Repositories\Interfaces\SekolahRepositoryInterface;
use App\Models\LamtimSekolah;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SekolahService
{
    protected $repository;

    public function __construct(SekolahRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimSekolah
    {
        return $this->repository->find($id);
    }

    public function findByNpsn(string $npsn): ?LamtimSekolah
    {
        return $this->repository->findByNpsn($npsn);
    }

    public function create(array $data): LamtimSekolah
    {
        try {
            DB::beginTransaction();

            // Validate NPSN uniqueness if provided
            if (isset($data['npsn']) && $data['npsn']) {
                if ($this->repository->findByNpsn($data['npsn'])) {
                    throw new \Exception('NPSN sudah digunakan');
                }
            }

            $sekolah = $this->repository->create($data);

            DB::commit();

            Log::info('Sekolah created', ['id' => $sekolah->id, 'nama' => $sekolah->nama]);

            return $sekolah;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sekolah', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimSekolah
    {
        try {
            DB::beginTransaction();

            $sekolah = $this->repository->find($id);
            if (!$sekolah) {
                throw new \Exception('Sekolah tidak ditemukan');
            }

            // Validate NPSN uniqueness if changed
            if (isset($data['npsn']) && $data['npsn'] !== $sekolah->npsn) {
                if ($this->repository->findByNpsn($data['npsn'])) {
                    throw new \Exception('NPSN sudah digunakan');
                }
            }

            $this->repository->update($id, $data);

            DB::commit();

            Log::info('Sekolah updated', ['id' => $id]);

            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sekolah', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $sekolah = $this->repository->find($id);
            if (!$sekolah) {
                throw new \Exception('Sekolah tidak ditemukan');
            }

            $this->repository->delete($id);

            DB::commit();

            Log::info('Sekolah deleted', ['id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting sekolah', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
