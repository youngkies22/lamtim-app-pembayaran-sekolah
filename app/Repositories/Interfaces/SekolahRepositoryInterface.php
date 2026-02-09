<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimSekolah;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SekolahRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimSekolah;
    
    public function findByNpsn(string $npsn): ?LamtimSekolah;
    
    public function create(array $data): LamtimSekolah;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
}
