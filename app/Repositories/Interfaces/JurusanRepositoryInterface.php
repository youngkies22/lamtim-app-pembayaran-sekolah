<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimJurusan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface JurusanRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimJurusan;
    
    public function findByKode(string $kode): ?LamtimJurusan;
    
    public function create(array $data): LamtimJurusan;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
    
    public function getBySekolah(string $idSekolah): Collection;
}
