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
    protected $rombelService;

    public function __construct(
        SiswaRepositoryInterface $repository,
        SiswaRombelService $rombelService
    ) {
        $this->repository = $repository;
        $this->rombelService = $rombelService;
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
            Log::error('Error creating student', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimSiswa
    {
        try {
            DB::beginTransaction();

            $siswa = $this->repository->find($id);
            if (!$siswa) {
                throw new \Exception('Student not found');
            }

            // Validate username uniqueness if changed
            if (isset($data['username']) && $data['username'] !== $siswa->username) {
                if ($this->repository->findByUsername($data['username'])) {
                    throw new \Exception('Username already in use');
                }
            }

            // Validate NIS uniqueness if changed
            if (isset($data['nis']) && $data['nis'] !== $siswa->nis) {
                if ($this->repository->findByNis($data['nis'])) {
                    throw new \Exception('NIS already in use');
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
            Log::error('Error updating student', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $siswa = $this->repository->find($id);
            if (!$siswa) {
                throw new \Exception('Student not found');
            }

            $this->repository->delete($id);

            DB::commit();



            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting student', ['id' => $id, 'error' => $e->getMessage()]);
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
            Log::error('Error restoring student', ['id' => $id, 'error' => $e->getMessage()]);
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

    /**
     * Mark selected students as alumni (isActive = 0).
     * Validates that each student is currently in a final-level kelas.
     */
    public function markAsAlumni(array $siswaIds): array
    {
        try {
            DB::beginTransaction();

            $processed = 0;
            $errors = [];
            $rombelIdsToDeactivate = [];

            foreach ($siswaIds as $id) {
                $siswa = LamtimSiswa::with(['currentRombel.rombel.kelas'])->find($id);

                if (!$siswa) {
                    $errors[] = "Siswa {$id} tidak ditemukan";
                    continue;
                }

                if ($siswa->isAlumni) {
                    $errors[] = "Siswa {$siswa->nama} sudah berstatus Alumni";
                    continue;
                }

                // Check if current rombel is final kelas
                $currentRombel = $siswa->currentRombel->rombel ?? null;
                $kelasKode = $currentRombel->kelas->kode ?? null;

                if (!$kelasKode || !$this->rombelService->isFinalKelas($kelasKode)) {
                    $errors[] = "Siswa {$siswa->nama} berada di kelas {$kelasKode} (Bukan kelas terakhir). Hanya siswa kelas terakhir yang bisa dijadikan alumni.";
                    continue;
                }

                $siswa->update([
                    'isAlumni' => 1,
                    'updatedBy' => auth()->id(),
                ]);

                // Collect rombel ID to deactivate later
                if ($currentRombel) {
                    $rombelIdsToDeactivate[] = $currentRombel->id;
                }

                $processed++;
            }

            // Deactivate processed rombels
            if (!empty($rombelIdsToDeactivate)) {
                $uniqueRombelIds = array_unique($rombelIdsToDeactivate);
                \App\Models\LamtimRombel::whereIn('id', $uniqueRombelIds)->update(['isActive' => 0]);
            }

            if ($processed === 0 && !empty($errors)) {
                throw new \Exception(implode('; ', $errors));
            }

            DB::commit();

            return [
                'processed_count' => $processed,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking students as alumni', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
