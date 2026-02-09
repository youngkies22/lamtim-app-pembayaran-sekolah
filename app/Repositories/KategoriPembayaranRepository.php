<?php

namespace App\Repositories;

use App\Models\LamtimKategoriPembayaran;
use App\Repositories\Interfaces\KategoriPembayaranRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class KategoriPembayaranRepository implements KategoriPembayaranRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = LamtimKategoriPembayaran::query()
            ->where('isActive', 1);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LamtimKategoriPembayaran::query()
            ->where('isActive', 1);

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama')->paginate($perPage);
    }

    public function find(string $id): ?LamtimKategoriPembayaran
    {
        return LamtimKategoriPembayaran::where('id', $id)
            ->where('isActive', 1)
            ->first();
    }

    public function findByKode(string $kode): ?LamtimKategoriPembayaran
    {
        return LamtimKategoriPembayaran::where('kode', $kode)
            ->where('isActive', 1)
            ->first();
    }

    public function create(array $data): LamtimKategoriPembayaran
    {
        return LamtimKategoriPembayaran::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $kategori = LamtimKategoriPembayaran::find($id);
        if (!$kategori) {
            return false;
        }

        return $kategori->update($data);
    }

    public function delete(string $id): bool
    {
        $kategori = LamtimKategoriPembayaran::find($id);
        if (!$kategori) {
            return false;
        }

        $kategori->softDelete(auth()->id());
        return true;
    }
}
