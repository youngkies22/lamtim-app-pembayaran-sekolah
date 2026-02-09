<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimTipePembayaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TipePembayaranRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimTipePembayaran;
    
    public function findByKode(string $kode): ?LamtimTipePembayaran;
    
    public function create(array $data): LamtimTipePembayaran;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
}
