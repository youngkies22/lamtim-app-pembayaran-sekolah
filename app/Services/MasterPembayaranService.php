<?php

namespace App\Services;

use App\Repositories\Interfaces\MasterPembayaranRepositoryInterface;
use App\Models\LamtimMasterPembayaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MasterPembayaranService
{
    protected $repository;

    public function __construct(MasterPembayaranRepositoryInterface $repository)
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

    public function find(string $id): ?LamtimMasterPembayaran
    {
        return $this->repository->find($id);
    }

    public function findByKode(string $kode): ?LamtimMasterPembayaran
    {
        return $this->repository->findByKode($kode);
    }

    public function create(array $data): LamtimMasterPembayaran
    {
        try {
            DB::beginTransaction();

            // Generate kode otomatis jika tidak ada
            if (empty($data['kode'])) {
                $data['kode'] = $this->generateKode($data['jenisPembayaran'] ?? '', $data['kategori'] ?? '');
            }

            // Validate kode uniqueness
            if ($this->repository->findByKode($data['kode'])) {
                // Jika kode sudah ada, generate ulang sampai unik
                $attempts = 0;
                do {
                    $data['kode'] = $this->generateKode($data['jenisPembayaran'] ?? '', $data['kategori'] ?? '');
                    $attempts++;
                    if ($attempts > 10) {
                        throw new \Exception('Gagal membuat kode unik setelah beberapa percobaan');
                    }
                } while ($this->repository->findByKode($data['kode']));
            }

            // Set createdBy
            $data['createdBy'] = auth()->id();

            $master = $this->repository->create($data);

            DB::commit();



            return $master;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating master pembayaran', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate kode otomatis berdasarkan jenis pembayaran
     * Format: {JENIS}-{TIMESTAMP}
     * Contoh: SPP-20260208143025
     * Timestamp sudah cukup unik (YmdHis format)
     */
    private function generateKode(string $jenisPembayaran, string $kategori): string
    {
        // Ambil kode dari jenis pembayaran jika ada
        $jenisKode = '';
        if (!empty($jenisPembayaran)) {
            $jenisModel = \App\Models\LamtimJenisPembayaran::where('kode', $jenisPembayaran)->first();
            if ($jenisModel && !empty($jenisModel->kode)) {
                $jenisKode = strtoupper($jenisModel->kode);
            } else {
                $jenisKode = strtoupper(substr($jenisPembayaran, 0, 3));
            }
        } else {
            $jenisKode = 'MP'; // Master Pembayaran default
        }

        // Generate timestamp (format: YmdHis - 14 digit)
        // Format ini sudah cukup unik karena menggunakan detik
        $timestamp = now()->format('YmdHis');

        // Format: {JENIS}-{TIMESTAMP}
        // Contoh: SPP-20260208143025
        return "{$jenisKode}-{$timestamp}";
    }

    public function update(string $id, array $data): LamtimMasterPembayaran
    {
        try {
            DB::beginTransaction();

            $master = $this->repository->find($id);
            if (!$master) {
                throw new \Exception('Master pembayaran tidak ditemukan');
            }

            // Validate kode uniqueness if changed
            if (isset($data['kode']) && $data['kode'] !== $master->kode) {
                if ($this->repository->findByKode($data['kode'])) {
                    throw new \Exception('Kode master pembayaran sudah digunakan');
                }
            }

            // Set updatedBy
            $data['updatedBy'] = auth()->id();

            $this->repository->update($id, $data);

            DB::commit();



            return $this->repository->find($id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating master pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            DB::beginTransaction();

            $master = $this->repository->find($id);
            if (!$master) {
                throw new \Exception('Master pembayaran tidak ditemukan');
            }

            // Check if master has active tagihans
            if ($master->tagihans()->where('isActive', 1)->exists()) {
                throw new \Exception('Master pembayaran masih memiliki tagihan aktif');
            }

            $this->repository->delete($id);

            DB::commit();



            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting master pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
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
            Log::error('Error restoring master pembayaran', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    public function getByJenis(string $jenis): Collection
    {
        return $this->repository->getByJenis($jenis);
    }

    public function getByKategori(string $kategori): Collection
    {
        return $this->repository->getByKategori($kategori);
    }
}
