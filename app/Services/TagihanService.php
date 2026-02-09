<?php

namespace App\Services;

use App\Models\LamtimSiswa;

use App\Repositories\Interfaces\TagihanRepositoryInterface;
use App\Repositories\Interfaces\MasterPembayaranRepositoryInterface;
use App\Models\LamtimTagihan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagihanService
{
    protected $repository;
    protected $masterPembayaranRepository;

    public function __construct(
        TagihanRepositoryInterface $repository,
        MasterPembayaranRepositoryInterface $masterPembayaranRepository
    ) {
        $this->repository = $repository;
        $this->masterPembayaranRepository = $masterPembayaranRepository;
    }

    public function getAll(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function find(string $id): ?LamtimTagihan
    {
        return $this->repository->find($id);
    }

    public function findBySiswa(string $idSiswa, string $idMasterPembayaran): ?LamtimTagihan
    {
        return $this->repository->findBySiswa($idSiswa, $idMasterPembayaran);
    }

    /**
     * Generate tagihan untuk siswa dari master pembayaran
     */
    public function generateTagihanUntukSiswa(
        string $idSiswa,
        string $idMasterPembayaran,
        ?string $idTahunAjaran = null,
        ?string $idRombel = null,
        ?string $bulan = null
    ): LamtimTagihan {
        try {
            DB::beginTransaction();

            // Cek apakah tagihan sudah ada
            $existing = $this->repository->findBySiswa($idSiswa, $idMasterPembayaran);
            if ($existing) {
                return $existing;
            }

            // Get master pembayaran
            $master = $this->masterPembayaranRepository->find($idMasterPembayaran);
            if (!$master) {
                throw new \Exception('Master pembayaran tidak ditemukan');
            }

            // Get siswa
            $siswa = LamtimSiswa::find($idSiswa);
            if (!$siswa) {
                throw new \Exception('Siswa tidak ditemukan');
            }

            // Get tahun ajaran jika tidak ada
            $tahunAjaranModel = null;
            if ($idTahunAjaran) {
                $tahunAjaranModel = \App\Models\LamtimTahunAjaran::find($idTahunAjaran);
            }
            
            // Jika tidak ada tahun ajaran, ambil yang aktif
            if (!$tahunAjaranModel) {
                $tahunAjaranModel = \App\Models\LamtimTahunAjaran::active()->first();
            }

            // Generate tagihan menggunakan method dari model
            $tagihan = $master->generateTagihanUntukSiswa($siswa, $tahunAjaranModel, $idRombel, $bulan);

            DB::commit();

            return $tagihan;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating tagihan', [
                'idSiswa' => $idSiswa,
                'idMasterPembayaran' => $idMasterPembayaran,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate tagihan untuk banyak siswa (batch)
     */
    public function generateTagihanBatch(
        string $idMasterPembayaran,
        array $siswaIds = [],
        ?string $idKelas = null,
        ?string $idJurusan = null
    ): array {
        try {
            DB::beginTransaction();

            $master = $this->masterPembayaranRepository->find($idMasterPembayaran);
            if (!$master) {
                throw new \Exception('Master pembayaran tidak ditemukan');
            }

            // Get tahun ajaran aktif sebagai fallback
            $tahunAjaranAktif = \App\Models\LamtimTahunAjaran::active()->first();
            $idTahunAjaran = $master->idTahunAjaran ?? ($tahunAjaranAktif?->id);

            // Get siswa list
            $query = LamtimSiswa::active();
            
            if (!empty($siswaIds)) {
                $query->whereIn('id', $siswaIds);
            }

            if ($idKelas) {
                $query->whereHas('rombels.rombel', function($q) use ($idKelas) {
                    $q->where('idKelas', $idKelas);
                });
            }

            if ($idJurusan) {
                $query->whereHas('rombels.rombel', function($q) use ($idJurusan) {
                    $q->where('idJurusan', $idJurusan);
                });
            }

            $siswaList = $query->get();

            $generated = [];
            $skipped = [];

            foreach ($siswaList as $siswa) {
                // Cek apakah tagihan sudah ada
                $existing = $this->repository->findBySiswa($siswa->id, $idMasterPembayaran);
                if ($existing) {
                    $skipped[] = $siswa->id;
                    continue;
                }

                // Generate tagihan dengan tahun ajaran
                $tagihan = $master->generateTagihanUntukSiswa($siswa, $tahunAjaranAktif);
                $generated[] = $tagihan->id;
            }

            DB::commit();

            return [
                'generated' => $generated,
                'skipped' => $skipped,
                'total' => count($generated) + count($skipped),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating tagihan batch', [
                'idMasterPembayaran' => $idMasterPembayaran,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function update(string $id, array $data): LamtimTagihan
    {
        try {
            DB::beginTransaction();

            $this->repository->update($id, $data);

            DB::commit();

            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating tagihan', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $tagihan = $this->repository->find($id);
            if (!$tagihan) {
                throw new \Exception('Tagihan tidak ditemukan');
            }

            // Check if tagihan has invoices
            if ($tagihan->invoices()->where('isActive', 1)->exists()) {
                throw new \Exception('Tagihan masih memiliki invoice aktif');
            }

            $this->repository->delete($id);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting tagihan', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getBelumLunas(array $filters = []): Collection
    {
        return $this->repository->getBelumLunas($filters);
    }

    public function getLunas(array $filters = []): Collection
    {
        return $this->repository->getLunas($filters);
    }

    public function getBySiswa(string $idSiswa): Collection
    {
        return $this->repository->getBySiswa($idSiswa);
    }

    public function getByMasterPembayaran(string $idMasterPembayaran): Collection
    {
        return $this->repository->getByMasterPembayaran($idMasterPembayaran);
    }
}
