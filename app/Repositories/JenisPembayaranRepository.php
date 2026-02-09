<?php

namespace App\Repositories;

use App\Models\LamtimJenisPembayaran;
use App\Repositories\Interfaces\JenisPembayaranRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class JenisPembayaranRepository implements JenisPembayaranRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = LamtimJenisPembayaran::query()
            ->where('isActive', 1);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('urutan')->orderBy('nama')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LamtimJenisPembayaran::query()
            ->where('isActive', 1);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('urutan')->orderBy('nama')->paginate($perPage);
    }

    public function find(string $id): ?LamtimJenisPembayaran
    {
        return LamtimJenisPembayaran::where('id', $id)
            ->where('isActive', 1)
            ->first();
    }

    public function findByKode(string $kode): ?LamtimJenisPembayaran
    {
        return LamtimJenisPembayaran::where('kode', $kode)
            ->where('isActive', 1)
            ->first();
    }

    public function create(array $data): LamtimJenisPembayaran
    {
        return LamtimJenisPembayaran::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $jenis = LamtimJenisPembayaran::find($id);
        if (!$jenis) {
            return false;
        }

        return $jenis->update($data);
    }

    public function delete(string $id): bool
    {
        $jenis = LamtimJenisPembayaran::find($id);
        if (!$jenis) {
            return false;
        }

        $jenis->softDelete(auth()->id());
        return true;
    }
}
