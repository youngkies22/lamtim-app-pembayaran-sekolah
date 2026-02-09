<?php

namespace App\Repositories;

use App\Models\LamtimTipePembayaran;
use App\Repositories\Interfaces\TipePembayaranRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TipePembayaranRepository implements TipePembayaranRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        $query = LamtimTipePembayaran::query()
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
        $query = LamtimTipePembayaran::query()
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

    public function find(string $id): ?LamtimTipePembayaran
    {
        return LamtimTipePembayaran::where('id', $id)
            ->where('isActive', 1)
            ->first();
    }

    public function findByKode(string $kode): ?LamtimTipePembayaran
    {
        return LamtimTipePembayaran::where('kode', $kode)
            ->where('isActive', 1)
            ->first();
    }

    public function create(array $data): LamtimTipePembayaran
    {
        return LamtimTipePembayaran::create($data);
    }

    public function update(string $id, array $data): bool
    {
        $tipe = LamtimTipePembayaran::find($id);
        if (!$tipe) {
            return false;
        }

        return $tipe->update($data);
    }

    public function delete(string $id): bool
    {
        $tipe = LamtimTipePembayaran::find($id);
        if (!$tipe) {
            return false;
        }

        $tipe->softDelete(auth()->id());
        return true;
    }
}
