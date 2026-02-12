<?php

namespace App\Services;

use App\Repositories\Interfaces\SiswaRepositoryInterface;
use App\Models\LamtimSiswa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class SiswaService
{
    protected $repository;

    public function __construct(SiswaRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimSiswa
    {
        return $this->repository->find($id);
    }

    public function findByUsername(string $username): ?LamtimSiswa
    {
        return $this->repository->findByUsername($username);
    }

    public function findByNis(string $nis): ?LamtimSiswa
    {
        return $this->repository->findByNis($nis);
    }

    public function create(array $data): LamtimSiswa
    {
        try {
            DB::beginTransaction();

            // Validate username uniqueness
            if ($this->repository->findByUsername($data['username'])) {
                throw new \Exception('Username sudah digunakan');
            }

            // Validate NIS uniqueness if provided
            if (isset($data['nis']) && $data['nis']) {
                if ($this->repository->findByNis($data['nis'])) {
                    throw new \Exception('NIS sudah digunakan');
                }
            }

            // Hash password
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Set createdBy
            $data['createdBy'] = auth()->id();

            $siswa = $this->repository->create($data);

            DB::commit();



            return $siswa;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating siswa', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimSiswa
    {
        try {
            DB::beginTransaction();

            $siswa = $this->repository->find($id);
            if (!$siswa) {
                throw new \Exception('Siswa tidak ditemukan');
            }

            // Validate username uniqueness if changed
            if (isset($data['username']) && $data['username'] !== $siswa->username) {
                if ($this->repository->findByUsername($data['username'])) {
                    throw new \Exception('Username sudah digunakan');
                }
            }

            // Validate NIS uniqueness if changed
            if (isset($data['nis']) && $data['nis'] !== $siswa->nis) {
                if ($this->repository->findByNis($data['nis'])) {
                    throw new \Exception('NIS sudah digunakan');
                }
            }

            // Hash password if provided
            if (isset($data['password']) && $data['password']) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']); // Don't update password if not provided
            }

            // Set updatedBy
            $data['updatedBy'] = auth()->id();

            $this->repository->update($id, $data);

            DB::commit();



            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating siswa', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $siswa = $this->repository->find($id);
            if (!$siswa) {
                throw new \Exception('Siswa tidak ditemukan');
            }

            $this->repository->delete($id);

            DB::commit();



            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting siswa', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function restore(string $id): bool
    {
        try {
            DB::beginTransaction();

            $result = $this->repository->restore($id);

            DB::commit();



            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring siswa', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function getByKelas(string $idKelas): Collection
    {
        return $this->repository->getByKelas($idKelas);
    }

    public function getByJurusan(string $idJurusan): Collection
    {
        return $this->repository->getByJurusan($idJurusan);
    }

    public function getByRombel(string $idRombel): Collection
    {
        return $this->repository->getByRombel($idRombel);
    }

    public function getByTahunAngkatan(string $tahunAngkatan): Collection
    {
        return $this->repository->getByTahunAngkatan($tahunAngkatan);
    }
}
