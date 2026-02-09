<?php

namespace App\Services;

use App\Repositories\Interfaces\JurusanRepositoryInterface;
use App\Models\LamtimJurusan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JurusanService
{
    protected $repository;

    public function __construct(JurusanRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimJurusan
    {
        return $this->repository->find($id);
    }

    public function findByKode(string $kode): ?LamtimJurusan
    {
        return $this->repository->findByKode($kode);
    }

    public function create(array $data): LamtimJurusan
    {
        try {
            DB::beginTransaction();

            // Validate kode uniqueness
            if ($this->repository->findByKode($data['kode'])) {
                throw new \Exception('Kode jurusan sudah digunakan');
            }

            $jurusan = $this->repository->create($data);

            DB::commit();

            Log::info('Jurusan created', ['id' => $jurusan->id, 'kode' => $jurusan->kode]);

            return $jurusan;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating jurusan', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimJurusan
    {
        try {
            DB::beginTransaction();

            $jurusan = $this->repository->find($id);
            if (!$jurusan) {
                throw new \Exception('Jurusan tidak ditemukan');
            }

            // Validate kode uniqueness if changed
            if (isset($data['kode']) && $data['kode'] !== $jurusan->kode) {
                if ($this->repository->findByKode($data['kode'])) {
                    throw new \Exception('Kode jurusan sudah digunakan');
                }
            }

            $this->repository->update($id, $data);

            DB::commit();

            Log::info('Jurusan updated', ['id' => $id]);

            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating jurusan', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $jurusan = $this->repository->find($id);
            if (!$jurusan) {
                throw new \Exception('Jurusan tidak ditemukan');
            }

            $this->repository->delete($id);

            DB::commit();

            Log::info('Jurusan deleted', ['id' => $id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting jurusan', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getBySekolah(string $idSekolah): Collection
    {
        return $this->repository->getBySekolah($idSekolah);
    }
}
