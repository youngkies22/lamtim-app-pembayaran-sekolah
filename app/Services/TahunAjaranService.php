<?php

namespace App\Services;

use App\Models\LamtimTahunAjaran;
use App\Repositories\Interfaces\TahunAjaranRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TahunAjaranService
{
    protected $repository;

    public function __construct(TahunAjaranRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimTahunAjaran
    {
        return $this->repository->find($id);
    }

    public function findByKode(string $kode): ?LamtimTahunAjaran
    {
        return $this->repository->findByKode($kode);
    }

    public function create(array $data): LamtimTahunAjaran
    {
        try {
            DB::beginTransaction();

            // Validate kode uniqueness
            if ($this->repository->findByKode($data['kode'])) {
                throw new \Exception('Kode tahun ajaran sudah digunakan');
            }

            $tahunAjaran = $this->repository->create($data);

            DB::commit();

            return $tahunAjaran;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating tahun ajaran', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimTahunAjaran
    {
        try {
            DB::beginTransaction();

            $tahunAjaran = $this->repository->find($id);
            if (!$tahunAjaran) {
                throw new \Exception('Tahun ajaran tidak ditemukan');
            }

            // Validate kode uniqueness if changed
            if (isset($data['kode']) && $data['kode'] !== $tahunAjaran->kode) {
                if ($this->repository->findByKode($data['kode'])) {
                    throw new \Exception('Kode tahun ajaran sudah digunakan');
                }
            }

            $this->repository->update($id, $data);

            DB::commit();

            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating tahun ajaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $tahunAjaran = $this->repository->find($id);
            if (!$tahunAjaran) {
                throw new \Exception('Tahun ajaran tidak ditemukan');
            }

            $this->repository->delete($id);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting tahun ajaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getActive(): ?LamtimTahunAjaran
    {
        return $this->repository->getActive();
    }

    public function setActive(string $id): bool
    {
        try {
            DB::beginTransaction();

            // Set all to inactive
            LamtimTahunAjaran::where('isActive', 1)->update(['isActive' => 0]);

            // Set selected to active
            $tahunAjaran = $this->repository->find($id);
            if (!$tahunAjaran) {
                throw new \Exception('Tahun ajaran tidak ditemukan');
            }

            $tahunAjaran->isActive = 1;
            $tahunAjaran->save();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error setting tahun ajaran active', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
