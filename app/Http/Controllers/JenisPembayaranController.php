<?php

namespace App\Http\Controllers;

use App\Services\JenisPembayaranService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class JenisPembayaranController extends Controller
{
    protected $service;

    public function __construct(JenisPembayaranService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search']);
            $jenis = $this->service->getAll($filters);
            return ResponseHelper::success($jenis);
        }

        return response()->json(['message' => 'API endpoint only'], 400);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|max:50',
                'nama' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'urutan' => 'nullable|integer',
            ]);

            $jenis = $this->service->create($validated);
            
            return ResponseHelper::success($jenis, 'Jenis pembayaran berhasil dibuat');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        $jenis = $this->service->find($id);
        
        if (!$jenis) {
            return ResponseHelper::notFound('Jenis pembayaran tidak ditemukan');
        }

        return ResponseHelper::success($jenis);
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'kode' => 'sometimes|string|max:50',
                'nama' => 'sometimes|string|max:255',
                'deskripsi' => 'nullable|string',
                'urutan' => 'nullable|integer',
            ]);

            $jenis = $this->service->update($id, $validated);
            
            return ResponseHelper::success($jenis, 'Jenis pembayaran berhasil diupdate');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            
            return ResponseHelper::success(null, 'Jenis pembayaran berhasil dihapus');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
