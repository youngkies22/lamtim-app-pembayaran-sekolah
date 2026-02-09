<?php

namespace App\Http\Controllers;

use App\Services\KategoriPembayaranService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class KategoriPembayaranController extends Controller
{
    protected $service;

    public function __construct(KategoriPembayaranService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search']);
            $kategori = $this->service->getAll($filters);
            return ResponseHelper::success($kategori);
        }

        return response()->json(['message' => 'API endpoint only'], 400);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|max:50',
                'nama' => 'required|string|max:255',
                'keterangan' => 'nullable|string',

            ]);

            $kategori = $this->service->create($validated);
            
            return ResponseHelper::success($kategori, 'Kategori pembayaran berhasil dibuat');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        $kategori = $this->service->find($id);
        
        if (!$kategori) {
            return ResponseHelper::notFound('Kategori pembayaran tidak ditemukan');
        }

        return ResponseHelper::success($kategori);
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'kode' => 'sometimes|string|max:50',
                'nama' => 'sometimes|string|max:255',
                'keterangan' => 'nullable|string',

            ]);

            $kategori = $this->service->update($id, $validated);
            
            return ResponseHelper::success($kategori, 'Kategori pembayaran berhasil diupdate');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            
            return ResponseHelper::success(null, 'Kategori pembayaran berhasil dihapus');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
