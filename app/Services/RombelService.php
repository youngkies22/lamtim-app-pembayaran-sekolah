<?php

namespace App\Services;

use App\Repositories\Interfaces\RombelRepositoryInterface;
use App\Models\LamtimRombel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
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

    /**
     * Get cached rombel list for API.
     */
    public function getCachedList(array $filters = []): Collection
    {
        $version = $this->getCacheVersion();
        $cacheKey = "rombel_list_v{$version}_" . md5(json_encode($filters));

        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            return $this->getAll($filters);
        });
    }

    /**
     * Get select options (id, kode, nama) with caching.
     */
    public function getSelectOptions(array $filters = [], bool $bustCache = false): array
    {
        $version = $this->getCacheVersion();
        $cacheKey = "rombel_select_v{$version}_" . md5(json_encode($filters));

        if (!$bustCache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
        }

        $query = LamtimRombel::query()->select('id', 'kode', 'nama');

        if (!empty($filters['idSekolah'])) {
            $query->where('idSekolah', $filters['idSekolah']);
        }
        if (!empty($filters['idJurusan'])) {
            $query->where('idJurusan', $filters['idJurusan']);
        }
        if (!empty($filters['idKelas'])) {
            $query->where('idKelas', $filters['idKelas']);
        }

        $result = $query->orderBy('kode')->get()->toArray();

        Cache::put($cacheKey, $result, 3600);

        return $result;
    }

    /**
     * Build query for DataTables.
     */
    public function buildDatatableQuery(?string $searchTerm = null)
    {
        $query = LamtimRombel::query()
            ->select('lamtim_rombels.id', 'lamtim_rombels.kode', 'lamtim_rombels.nama', 'lamtim_rombels.idSekolah', 'lamtim_rombels.idJurusan', 'lamtim_rombels.idKelas', 'lamtim_rombels.isActive')
            ->with([
                'sekolah:id,kode,nama',
                'jurusan:id,kode,nama',
                'kelas:id,kode,nama',
            ]);

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('kode', 'like', "%{$searchTerm}%")
                    ->orWhere('nama', 'like', "%{$searchTerm}%");
            });
        }

        return $query;
    }

    /**
     * Invalidate all rombel-related caches.
     */
    public function invalidateCache(): void
    {
        $version = Cache::get('rombel_cache_version', 1);
        Cache::put('rombel_cache_version', $version + 1);
    }

    /**
     * Get current cache version.
     */
    public function getCacheVersion(): int
    {
        return (int) Cache::get('rombel_cache_version', 1);
    }
}
