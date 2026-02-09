<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimRombel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RombelRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimRombel;
    
    public function findByKode(string $kode): ?LamtimRombel;
    
    public function create(array $data): LamtimRombel;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
    
    public function getActive(): Collection;
    
    public function getByJurusan(string $idJurusan): Collection;
    
    public function getByKelas(string $idKelas): Collection;
    
    public function getBySekolah(string $idSekolah): Collection;
}
