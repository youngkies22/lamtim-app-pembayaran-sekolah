<?php

namespace App\Services;

use App\Repositories\Interfaces\KelasRepositoryInterface;
use App\Models\LamtimKelas;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KelasService
{
    protected $repository;

    public function __construct(KelasRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimKelas
    {
        return $this->repository->find($id);
    }

    public function findByKode(string $kode): ?LamtimKelas
    {
        return $this->repository->findByKode($kode);
    }

    public function create(array $data): LamtimKelas
    {
        try {
            DB::beginTransaction();

            // Validate kode uniqueness
            if ($this->repository->findByKode($data['kode'])) {
                throw new \Exception('Kode kelas sudah digunakan');
            }

            $kelas = $this->repository->create($data);

            DB::commit();



            return $kelas;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating kelas', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimKelas
    {
        try {
            DB::beginTransaction();

            $kelas = $this->repository->find($id);
            if (!$kelas) {
                throw new \Exception('Kelas tidak ditemukan');
            }

            // Validate kode uniqueness if changed
            if (isset($data['kode']) && $data['kode'] !== $kelas->kode) {
                if ($this->repository->findByKode($data['kode'])) {
                    throw new \Exception('Kode kelas sudah digunakan');
                }
            }

            $this->repository->update($id, $data);

            DB::commit();



            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating kelas', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $kelas = $this->repository->find($id);
            if (!$kelas) {
                throw new \Exception('Kelas tidak ditemukan');
            }

            // Check if kelas has students
            // You can add this check if needed

            $this->repository->delete($id);

            DB::commit();



            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting kelas', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
