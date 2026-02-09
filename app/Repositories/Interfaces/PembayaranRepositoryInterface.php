<?php

namespace App\Repositories\Interfaces;

use App\Models\LamtimPembayaran;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PembayaranRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(string $id): ?LamtimPembayaran;
    
    public function create(array $data): LamtimPembayaran;
    
    public function update(string $id, array $data): bool;
    
    public function delete(string $id): bool;
    
    public function restore(string $id): bool;
    
    public function getBySiswa(string $idSiswa): Collection;
    
    public function getByInvoice(string $idInvoice): Collection;
    
    public function getByTagihan(string $idTagihan): Collection;
    
    public function getVerified(): Collection;
    
    public function getUnverified(): Collection;
}
