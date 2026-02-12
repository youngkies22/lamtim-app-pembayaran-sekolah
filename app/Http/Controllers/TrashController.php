<?php

namespace App\Http\Controllers;

use App\Services\TrashService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function __construct(
        protected TrashService $service
    ) {}

    /**
     * List soft-deleted items by type.
     */
    public function index(Request $request)
    {
        try {
            $type = $request->get('type', 'tagihan');
            $items = $this->service->getTrashItems($type);

            return ResponseHelper::success($items);
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }

    /**
     * Restore a soft-deleted item.
     */
    public function restore(Request $request, $id)
    {
        try {
            $this->service->restore($id, $request->get('type'));

            return ResponseHelper::success(null, 'Data berhasil dikembalikan');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Permanently delete a soft-deleted item.
     */
    public function forceDelete(Request $request, $id)
    {
        try {
            $this->service->forceDelete($id, $request->get('type'));

            return ResponseHelper::success(null, 'Data berhasil dihapus permanen');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Empty all trash of a given type.
     */
    public function emptyTrash(Request $request)
    {
        try {
            $this->service->emptyTrash($request->get('type'));

            return ResponseHelper::success(null, 'Semua sampah berhasil dibersihkan');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
