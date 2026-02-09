<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimJenisPembayaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface JenisPembayaranRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimJenisPembayaran;
    
    public function findByKode(string $kode): ?LamtimJenisPembayaran;
    
    public function create(array $data): LamtimJenisPembayaran;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
}
