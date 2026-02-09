<?php

namespace App\Http\Controllers;

use App\Models\LamtimTagihan;

use App\Services\TagihanService;
use App\Http\Resources\TagihanResource;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TagihanController extends Controller
{
    protected $service;

    public function __construct(TagihanService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search', 'idSiswa', 'idMasterPembayaran', 'jenisPembayaran', 'status', 'isActive', 'start_date', 'end_date']);
            $tagihans = $this->service->getPaginated($filters, $request->get('per_page', 15));
            return ResponseHelper::success(TagihanResource::collection($tagihans));
        }

        // For DataTables (web)
        if ($request->ajax()) {
            return $this->datatable($request);
        }

        return view('tagihan.index');
    }

    /**
     * DataTables server-side processing
     */
    public function datatable(Request $request)
    {
        $query = LamtimTagihan::query()
            ->with(['siswa', 'masterPembayaran'])
            ->select('lamtim_tagihans.*')
            ->orderBy('tanggalTagihan', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('idMasterPembayaran')) {
            $query->where('idMasterPembayaran', $request->idMasterPembayaran);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('siswa_nama', function($row) {
                return $row->siswa->nama ?? '-';
            })
            ->addColumn('master_nama', function($row) {
                return $row->masterPembayaran->nama ?? '-';
            })
            ->addColumn('tanggal_formatted', function($row) {
                return \App\Helpers\FormatHelper::date($row->tanggalTagihan);
            })
            ->addColumn('nominal_formatted', function($row) {
                return \App\Helpers\FormatHelper::currency($row->nominalTagihan);
            })
            ->addColumn('terbayar_formatted', function($row) {
                return \App\Helpers\FormatHelper::currency($row->totalSudahBayar ?? 0);
            })
            ->addColumn('sisa_formatted', function($row) {
                return \App\Helpers\FormatHelper::currency($row->totalSisa ?? 0);
            })
            ->addColumn('status_badge', function($row) {
                return \App\Helpers\FormatHelper::statusBadge($row->status, 'tagihan');
            })
            ->addColumn('action', function($row) {
                $buttons = '<button data-action="detail" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Detail</button>';
                
                if ($row->status === 0) {
                    $buttons .= '<button data-action="delete" data-id="' . $row->id . '" data-kode="' . htmlspecialchars($row->kodeTagihan) . '" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Hapus</button>';
                }
                
                return '<div class="flex gap-1">' . $buttons . '</div>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $tagihan = $this->service->find($id);
        
        if (!$tagihan) {
            return ResponseHelper::notFound('Tagihan tidak ditemukan');
        }

        return ResponseHelper::success(new TagihanResource($tagihan));
    }

    /**
     * Generate tagihan batch
     */
    public function generateBatch(Request $request)
    {
        try {
            $validated = $request->validate([
                'idMasterPembayaran' => 'required|uuid|exists:lamtim_master_pembayarans,id',
                'siswaIds' => 'nullable|array',
                'idKelas' => 'nullable|uuid|exists:lamtim_kelas,id',
                'idJurusan' => 'nullable|uuid|exists:lamtim_jurusans,id',
            ]);

            $result = $this->service->generateTagihanBatch(
                $validated['idMasterPembayaran'],
                $validated['siswaIds'] ?? [],
                $validated['idKelas'] ?? null,
                $validated['idJurusan'] ?? null
            );

            return ResponseHelper::success($result, 'Tagihan berhasil di-generate');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get tagihan by siswa (untuk billing)
     */
    public function getBySiswa(Request $request, string $idSiswa)
    {
        try {
            // Get tagihan yang belum lunas penuh (status 0 atau 3) dan masih ada sisa untuk siswa tertentu
            $query = LamtimTagihan::query()
                ->where('idSiswa', $idSiswa)
                ->whereIn('status', [0, 3]) // Belum lunas (0) atau Sebagian (3)
                ->where('totalSisa', '>', 0) // Masih ada sisa yang harus dibayar
                ->where('isActive', 1)
                ->with(['masterPembayaran:id,kode,nama,jenisPembayaran,kategori'])
                ->orderBy('tanggalTagihan', 'desc');

            $tagihans = $query->get();

            return ResponseHelper::success(TagihanResource::collection($tagihans));
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $tagihan = $this->service->find($id);
            
            if (!$tagihan) {
                return ResponseHelper::notFound('Tagihan tidak ditemukan');
            }
            
            // Only allow delete if status is 0 (belum bayar)
            if ($tagihan->status != 0) {
                return ResponseHelper::error('Tagihan yang sudah dibayar tidak dapat dihapus', 422);
            }
            
            $this->service->delete($id);
            
            return ResponseHelper::success(null, 'Tagihan berhasil dihapus');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
