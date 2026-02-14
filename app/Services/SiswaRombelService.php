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
            ->where('isAlumni', 0)
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

    /**
     * Universal kelas progression categories
     */
    private const KELAS_CATEGORIES = [
        'SD' => ['1', '2', '3', '4', '5', '6'],
        'SMP' => ['7', '8', '9'],
        'SMA_ARABIC' => ['10', '11', '12'],
        'SMA_ROMAN' => ['X', 'XI', 'XII'],
    ];

    /**
     * Get the next kelas kode in the progression.
     */
    private function getNextKelasKode(string $currentKode): ?string
    {
        foreach (self::KELAS_CATEGORIES as $category => $order) {
            $index = array_search($currentKode, $order);
            if ($index !== false && $index < count($order) - 1) {
                return $order[$index + 1];
            }
        }
        return null; // Already at final kelas or unknown
    }

    /**
     * Check if a kelas kode is the final level.
     */
    public function isFinalKelas(string $kelasKode): bool
    {
        foreach (self::KELAS_CATEGORIES as $category => $order) {
            if ($kelasKode === end($order)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Promote students to the next kelas level.
     * Finds destination rombel with same nama + sekolah + jurusan but next kelas.
     * Updates existing LamtimSiswaRombel.idRombel.
     */
    public function promoteStudents(string $fromRombelId, array $siswaIds): array
    {
        try {
            DB::beginTransaction();

            // Load source rombel with kelas
            $fromRombel = LamtimRombel::with('kelas')->find($fromRombelId);
            if (!$fromRombel) {
                throw new \Exception('Rombel asal tidak ditemukan');
            }

            $currentKelasKode = $fromRombel->kelas->kode ?? null;
            if (!$currentKelasKode) {
                throw new \Exception('Kelas pada rombel asal tidak valid');
            }

            // Determine next kelas
            $nextKelasKode = $this->getNextKelasKode($currentKelasKode);
            if (!$nextKelasKode) {
                throw new \Exception("Kelas {$currentKelasKode} sudah merupakan kelas terakhir. Gunakan fitur Alumni.");
            }

            // Find next kelas record
            $nextKelas = \App\Models\LamtimKelas::where('kode', $nextKelasKode)->first();
            if (!$nextKelas) {
                throw new \Exception("Kelas {$nextKelasKode} tidak ditemukan di database");
            }

            // Find destination rombel: same nama + sekolah + jurusan, next kelas
            $toRombel = LamtimRombel::where('nama', $fromRombel->nama)
                ->where('idSekolah', $fromRombel->idSekolah)
                ->where('idJurusan', $fromRombel->idJurusan)
                ->where('idKelas', $nextKelas->id)
                ->first();

            if (!$toRombel) {
                throw new \Exception(
                    "Rombel tujuan ({$fromRombel->nama} kelas {$nextKelasKode}) tidak ditemukan. " .
                    "Pastikan rombel dengan nama, sekolah, dan jurusan yang sama sudah ada di kelas {$nextKelasKode}."
                );
            }

            $promoted = 0;
            $errors = [];

            foreach ($siswaIds as $idSiswa) {
                try {
                    // Find existing mapping in source rombel
                    $mapping = LamtimSiswaRombel::where('idSiswa', $idSiswa)
                        ->where('idRombel', $fromRombelId)
                        ->first();

                    if (!$mapping) {
                        $errors[] = "Siswa {$idSiswa} tidak terdaftar di rombel asal";
                        continue;
                    }

                    // Update to destination rombel
                    $mapping->update(['idRombel' => $toRombel->id]);
                    $promoted++;
                } catch (\Exception $e) {
                    $errors[] = "Gagal memproses siswa {$idSiswa}: " . $e->getMessage();
                }
            }

            if ($promoted === 0 && !empty($errors)) {
                throw new \Exception(implode('; ', $errors));
            }

            DB::commit();

            return [
                'promoted_count' => $promoted,
                'errors' => $errors,
                'from_kelas' => $currentKelasKode,
                'to_kelas' => $nextKelasKode,
                'rombel_nama' => $fromRombel->nama,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error promoting students', [
                'error' => $e->getMessage(),
                'from' => $fromRombelId,
            ]);
            throw $e;
        }
    }
}
