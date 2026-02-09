<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimSiswa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SiswaRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimSiswa;
    
    public function findByUsername(string $username): ?LamtimSiswa;
    
    public function findByNis(string $nis): ?LamtimSiswa;
    
    public function findByNisn(string $nisn): ?LamtimSiswa;
    
    public function create(array $data): LamtimSiswa;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
    
    public function restore(string $id): bool;
    
    public function getActive(): Collection;
    
    public function getByKelas(string $idKelas): Collection;
    
    public function getByJurusan(string $idJurusan): Collection;
    
    public function getByRombel(string $idRombel): Collection;
    
    public function getByTahunAngkatan(string $tahunAngkatan): Collection;
}
