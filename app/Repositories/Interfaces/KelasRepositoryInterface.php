<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimKelas;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface KelasRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimKelas;
    
    public function findByKode(string $kode): ?LamtimKelas;
    
    public function create(array $data): LamtimKelas;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
}
