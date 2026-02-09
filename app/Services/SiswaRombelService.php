<?php

namespace App\Services;

use App\Models\LamtimSiswaRombel;
use App\Models\LamtimRombel;
use App\Models\LamtimSiswa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SiswaRombelService
{
    public function getAll(array $filters = []): Collection
    {
        $query = LamtimSiswaRombel::with(['siswa', 'rombel.sekolah', 'rombel.jurusan', 'rombel.kelas']);

        if (isset($filters['idSiswa'])) {
            $query->where('idSiswa', $filters['idSiswa']);
        }

        if (isset($filters['idRombel'])) {
            $query->where('idRombel', $filters['idRombel']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function find(string $id): ?LamtimSiswaRombel
    {
        return LamtimSiswaRombel::with(['siswa', 'rombel.sekolah', 'rombel.jurusan', 'rombel.kelas'])->find($id);
    }

    public function create(array $data): LamtimSiswaRombel
    {
        try {
            DB::beginTransaction();

            // Validate rombel exists
            $rombel = LamtimRombel::find($data['idRombel']);
            if (!$rombel) {
                throw new \Exception('Rombel tidak ditemukan');
            }

            // Create mapping (idKelas diambil dari rombel, tidak disimpan di mapping)
            $mappingData = [
                'idSiswa' => $data['idSiswa'],
                'idRombel' => $data['idRombel'],
            ];

            $mapping = LamtimSiswaRombel::create($mappingData);

            DB::commit();

            return $mapping->load(['siswa', 'rombel.sekolah', 'rombel.jurusan', 'rombel.kelas']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating siswa rombel mapping', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimSiswaRombel
    {
        try {
            DB::beginTransaction();

            $mapping = LamtimSiswaRombel::find($id);
            if (!$mapping) {
                throw new \Exception('Mapping tidak ditemukan');
            }

            // If rombel is changed, validate it exists
            if (isset($data['idRombel']) && $data['idRombel'] !== $mapping->idRombel) {
                $rombel = LamtimRombel::find($data['idRombel']);
                if (!$rombel) {
                    throw new \Exception('Rombel tidak ditemukan');
                }
            }

            // Update only allowed fields (idRombel only, idKelas is in rombel)
            $updateData = array_intersect_key($data, array_flip(['idRombel']));
            $mapping->update($updateData);

            DB::commit();

            return $mapping->load(['siswa', 'rombel.sekolah', 'rombel.jurusan', 'rombel.kelas']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating siswa rombel mapping', [
                'id' => $id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $mapping = LamtimSiswaRombel::find($id);
            if (!$mapping) {
                throw new \Exception('Mapping tidak ditemukan');
            }

            $mapping->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting siswa rombel mapping', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get unmapped students with pagination support
     */
    public function getUnmappedStudents(array $filters = [])
    {
        $query = LamtimSiswa::query()
            ->whereIn('isActive', [1, 2]) // Aktif atau Off (bukan yang dihapus)
            ->whereDoesntHave('rombels');

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $query->orderBy('nama');

        // Always use pagination (default per_page = 20)
        $perPage = $filters['per_page'] ?? 20;
        $page = $filters['page'] ?? 1;
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Batch create mappings for multiple students
     */
    public function batchCreate(array $data): array
    {
        try {
            DB::beginTransaction();

            // Validate rombel exists
            $rombel = LamtimRombel::find($data['idRombel']);
            if (!$rombel) {
                throw new \Exception('Rombel tidak ditemukan');
            }

            $created = [];
            $errors = [];

            foreach ($data['siswa_ids'] as $idSiswa) {
                try {
                    // Check if student is already mapped to this rombel
                    $existing = LamtimSiswaRombel::where('idSiswa', $idSiswa)
                        ->where('idRombel', $data['idRombel'])
                        ->first();

                    if ($existing) {
                        continue; // Skip if already mapped to this rombel
                    }

                    $mappingData = [
                        'idSiswa' => $idSiswa,
                        'idRombel' => $data['idRombel'],
                    ];

                    $mapping = LamtimSiswaRombel::create($mappingData);
                    $created[] = $mapping->load(['siswa', 'rombel.sekolah', 'rombel.jurusan', 'rombel.kelas']);
                } catch (\Exception $e) {
                    $errors[] = "Gagal mapping siswa {$idSiswa}: " . $e->getMessage();
                }
            }

            if (empty($created) && !empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            DB::commit();

            return [
                'created' => $created,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error batch creating siswa rombel mapping', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }
}
