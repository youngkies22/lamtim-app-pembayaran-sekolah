<?php

namespace App\Services;

use App\Models\LamtimTagihan;
use App\Models\LamtimPembayaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TrashService
{
    private const VALID_TYPES = ['tagihan', 'pembayaran'];

    /**
     * Get soft-deleted items by type.
     */
    public function getTrashItems(string $type): Collection
    {
        $this->validateType($type);

        if ($type === 'tagihan') {
            return $this->getDeletedTagihan();
        }

        return $this->getDeletedPembayaran();
    }

    /**
     * Restore a soft-deleted item.
     */
    public function restore(string $id, string $type): void
    {
        $this->validateType($type);

        try {
            DB::beginTransaction();

            $item = $this->findDeletedItem($id, $type);
            $item->restore();

            DB::commit();


        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error restoring trash item", ['type' => $type, 'id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Permanently delete a soft-deleted item.
     */
    public function forceDelete(string $id, string $type): void
    {
        $this->validateType($type);

        try {
            DB::beginTransaction();

            $item = $this->findDeletedItem($id, $type);
            $this->deleteWithRelations($item, $type);

            DB::commit();


        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error force deleting trash item", ['type' => $type, 'id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Empty all trash of a given type.
     */
    public function emptyTrash(string $type): void
    {
        $this->validateType($type);

        try {
            DB::beginTransaction();

            $model = $this->getModelClass($type);
            $items = $model::where('isActive', 0)->get();

            foreach ($items as $item) {
                $this->deleteWithRelations($item, $type);
            }

            DB::commit();


        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error emptying trash", ['type' => $type, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate the trash type parameter.
     */
    private function validateType(string $type): void
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new \InvalidArgumentException('Invalid type. Must be: ' . implode(', ', self::VALID_TYPES));
        }
    }

    /**
     * Find a deleted item or throw.
     */
    private function findDeletedItem(string $id, string $type)
    {
        $model = $this->getModelClass($type);
        $item = $model::where('id', $id)->where('isActive', 0)->first();

        if (!$item) {
            throw new \Exception('Item not found or already restored');
        }

        return $item;
    }

    /**
     * Delete item with its related records.
     */
    private function deleteWithRelations($item, string $type): void
    {
        if ($type === 'tagihan') {
            $item->invoices()->delete();
        } elseif ($type === 'pembayaran' && $item->invoice) {
            $item->invoice->delete();
        }

        $item->delete();
    }

    /**
     * Get the model class for a given type.
     */
    private function getModelClass(string $type): string
    {
        return match ($type) {
            'tagihan' => LamtimTagihan::class,
            'pembayaran' => LamtimPembayaran::class,
        };
    }

    private function getDeletedTagihan(): Collection
    {
        return LamtimTagihan::where('isActive', 0)
            ->with(['siswa', 'masterPembayaran'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'type' => 'tagihan',
                'identifier' => $item->kodeTagihan,
                'description' => ($item->siswa->nama ?? 'Siswa dihapus') . ' - ' . ($item->masterPembayaran->nama ?? 'Master dihapus'),
                'amount' => $item->nominalTagihan,
                'deleted_at' => $item->updated_at,
            ]);
    }

    private function getDeletedPembayaran(): Collection
    {
        return LamtimPembayaran::where('isActive', 0)
            ->with(['siswa', 'masterPembayaran'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'type' => 'pembayaran',
                'identifier' => $item->kodePembayaran ?? $item->id,
                'description' => ($item->siswa->nama ?? 'Siswa dihapus') . ' - ' . ($item->masterPembayaran->nama ?? 'Master dihapus'),
                'amount' => $item->nominalBayar,
                'deleted_at' => $item->updated_at,
            ]);
    }
}
