<?php

namespace App\Services;

use App\Models\LamtimSemester;
use App\Repositories\Interfaces\SemesterRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SemesterService
{
    protected $repository;

    public function __construct(SemesterRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimSemester
    {
        return $this->repository->find($id);
    }

    public function findByKode(string $kode): ?LamtimSemester
    {
        return $this->repository->findByKode($kode);
    }

    public function create(array $data): LamtimSemester
    {
        try {
            DB::beginTransaction();

            // Validate kode uniqueness
            if ($this->repository->findByKode($data['kode'])) {
                throw new \Exception('Kode semester sudah digunakan');
            }

            $semester = $this->repository->create($data);

            DB::commit();

            return $semester;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating semester', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimSemester
    {
        try {
            DB::beginTransaction();

            $semester = $this->repository->find($id);
            if (!$semester) {
                throw new \Exception('Semester tidak ditemukan');
            }

            // Validate kode uniqueness if changed
            if (isset($data['kode']) && $data['kode'] !== $semester->kode) {
                if ($this->repository->findByKode($data['kode'])) {
                    throw new \Exception('Kode semester sudah digunakan');
                }
            }

            $this->repository->update($id, $data);

            DB::commit();

            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating semester', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $semester = $this->repository->find($id);
            if (!$semester) {
                throw new \Exception('Semester tidak ditemukan');
            }

            $this->repository->delete($id);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting semester', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getActive(): ?LamtimSemester
    {
        return $this->repository->getActive();
    }

    public function setActive(string $id): bool
    {
        try {
            DB::beginTransaction();

            // Set all to inactive
            LamtimSemester::where('isActive', 1)->update(['isActive' => 0]);

            // Set selected to active
            $semester = $this->repository->find($id);
            if (!$semester) {
                throw new \Exception('Semester tidak ditemukan');
            }

            $semester->isActive = 1;
            $semester->save();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting active semester', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
