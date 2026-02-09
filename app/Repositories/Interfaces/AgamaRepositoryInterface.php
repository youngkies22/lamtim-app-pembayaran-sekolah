<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimAgama;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface AgamaRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimAgama;
    
    public function findByKode(string $kode): ?LamtimAgama;
    
    public function create(array $data): LamtimAgama;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
}
