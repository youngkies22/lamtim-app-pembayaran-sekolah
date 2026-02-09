<?php

namespace App\Services;

use App\Repositories\Interfaces\RombelRepositoryInterface;
use App\Models\LamtimRombel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RombelService
{
    protected $repository;

    public function __construct(RombelRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimRombel
    {
        return $this->repository->find($id);
    }

    public function findByKode(string $kode): ?LamtimRombel
    {
        return $this->repository->findByKode($kode);
    }

    public function create(array $data): LamtimRombel
    {
        try {
            DB::beginTransaction();

            // Validate kode uniqueness
            if ($this->repository->findByKode($data['kode'])) {
                throw new \Exception('Kode rombel sudah digunakan');
            }

            $rombel = $this->repository->create($data);

            DB::commit();

            Log::info('Rombel created', ['id' => $rombel->id, 'kode' => $rombel->kode]);

            return $rombel;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating rombel', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimRombel
    {
        try {
            DB::beginTransaction();

            $rombel = $this->repository->find($id);
            if (!$rombel) {
                throw new \Exception('Rombel tidak ditemukan');
            }

            // Validate kode uniqueness if changed
            if (isset($data['kode']) && $data['kode'] !== $rombel->kode) {
                if ($this->repository->findByKode($data['kode'])) {
                    throw new \Exception('Kode rombel sudah digunakan');
                }
            }

            $this->repository->update($id, $data);

            DB::commit();

            Log::info('Rombel updated', ['id' => $id]);

            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating rombel', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $rombel = $this->repository->find($id);
            if (!$rombel) {
                throw new \Exception('Rombel tidak ditemukan');
            }

            $this->repository->delete($id);

            DB::commit();

            Log::info('Rombel deleted', ['id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting rombel', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function getByJurusan(string $idJurusan): Collection
    {
        return $this->repository->getByJurusan($idJurusan);
    }

    public function getByKelas(string $idKelas): Collection
    {
        return $this->repository->getByKelas($idKelas);
    }

    public function getBySekolah(string $idSekolah): Collection
    {
        return $this->repository->getBySekolah($idSekolah);
    }
}
