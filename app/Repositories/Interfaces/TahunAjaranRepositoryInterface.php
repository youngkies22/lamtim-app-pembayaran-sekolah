<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimTahunAjaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TahunAjaranRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimTahunAjaran;
    
    public function findByKode(string $kode): ?LamtimTahunAjaran;
    
    public function create(array $data): LamtimTahunAjaran;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
    
    public function getActive(): ?LamtimTahunAjaran;
}
