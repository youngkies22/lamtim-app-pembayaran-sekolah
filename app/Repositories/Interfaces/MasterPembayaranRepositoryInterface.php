<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimMasterPembayaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface MasterPembayaranRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimMasterPembayaran;
    
    public function findByKode(string $kode): ?LamtimMasterPembayaran;
    
    public function create(array $data): LamtimMasterPembayaran;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
    
    public function restore(string $id): bool;
    
    public function getActive(): Collection;
    
    public function getByJenis(string $jenis): Collection;
    
    public function getByKategori(string $kategori): Collection;
}
