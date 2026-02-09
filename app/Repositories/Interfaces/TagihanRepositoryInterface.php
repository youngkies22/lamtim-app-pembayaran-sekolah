<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimTagihan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface TagihanRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimTagihan;
    
    public function findBySiswa(string $idSiswa, string $idMasterPembayaran): ?LamtimTagihan;
    
    public function create(array $data): LamtimTagihan;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
    
    public function restore(string $id): bool;
    
    public function getBelumLunas(array $filters = []): Collection;
    
    public function getLunas(array $filters = []): Collection;
    
    public function getBySiswa(string $idSiswa): Collection;
    
    public function getByMasterPembayaran(string $idMasterPembayaran): Collection;
}
