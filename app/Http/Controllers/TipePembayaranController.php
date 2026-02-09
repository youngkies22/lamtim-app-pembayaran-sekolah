<?php

namespace App\Http\Controllers;

use App\Services\TipePembayaranService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class TipePembayaranController extends Controller
{
    protected $service;

    public function __construct(TipePembayaranService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search']);
            $tipe = $this->service->getAll($filters);
            return ResponseHelper::success($tipe);
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

            $tipe = $this->service->create($validated);
            
            return ResponseHelper::success($tipe, 'Tipe pembayaran berhasil dibuat');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function show(string $id)
    {
        $tipe = $this->service->find($id);
        
        if (!$tipe) {
            return ResponseHelper::notFound('Tipe pembayaran tidak ditemukan');
        }

        return ResponseHelper::success($tipe);
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

            $tipe = $this->service->update($id, $validated);
            
            return ResponseHelper::success($tipe, 'Tipe pembayaran berhasil diupdate');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            
            return ResponseHelper::success(null, 'Tipe pembayaran berhasil dihapus');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
