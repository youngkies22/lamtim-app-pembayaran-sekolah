<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimKategoriPembayaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface KategoriPembayaranRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimKategoriPembayaran;
    
    public function findByKode(string $kode): ?LamtimKategoriPembayaran;
    
    public function create(array $data): LamtimKategoriPembayaran;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
}
