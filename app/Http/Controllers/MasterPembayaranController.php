<?php

namespace App\Http\Controllers;

use App\Models\LamtimMasterPembayaran;

use App\Http\Requests\StoreMasterPembayaranRequest;
use App\Http\Requests\UpdateMasterPembayaranRequest;
use App\Http\Resources\MasterPembayaranResource;
use App\Services\MasterPembayaranService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MasterPembayaranController extends Controller
{
    protected $service;

    public function __construct(MasterPembayaranService $service)
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
            $filters = $request->only(['search', 'jenisPembayaran', 'kategori', 'idKelas', 'idJurusan', 'isActive']);
            
            // Remove empty filters
            $filters = array_filter($filters, function($value) {
                return $value !== '' && $value !== null;
            });
            
            // Use getAll for better compatibility with frontend
            $masters = $this->service->getAll($filters);
            return ResponseHelper::success($masters);
        }

        // For DataTables (web)
        if ($request->ajax()) {
            return $this->datatable();
        }

        return view('master-pembayaran.index');
    }

    /**
     * Get options for select dropdown (only id, kode, nama, nominal)
     */
    public function select(Request $request)
    {
        $filters = $request->only(['isActive']);
        $cacheKey = 'master_pembayaran_select_' . md5(json_encode($filters));
        
        // Try to get from cache first
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return ResponseHelper::success($cached);
        }

        $query = LamtimMasterPembayaran::query()
            ->select('id', 'kode', 'nama', 'nominal');
        
        if (isset($filters['isActive'])) {
            $query->where('isActive', $filters['isActive']);
        } else {
            $query->where('isActive', 1);
        }
        
        $masters = $query->orderBy('kode')->get()->toArray();
        
        // Cache for 5 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $masters, 300);
        
        return ResponseHelper::success($masters);
    }

    /**
     * DataTables server-side processing
     */
    public function datatable()
    {
        $query = LamtimMasterPembayaran::query()
            ->select('lamtim_master_pembayarans.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nominal_formatted', function($row) {
                return \App\Helpers\FormatHelper::currency($row->nominal);
            })
            ->addColumn('isCicilan_label', function($row) {
                return $row->isCicilan ? 'Ya' : 'Tidak';
            })
            ->addColumn('isActive_badge', function($row) {
                return $row->isActive 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Tidak Aktif</span>';
            })
            ->addColumn('action', function($row) {
                return '<div class="flex gap-1">' .
                    '<button data-action="edit" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Edit</button>' .
                    '<button data-action="delete" data-id="' . $row->id . '" data-nama="' . htmlspecialchars($row->nama) . '" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Hapus</button>' .
                    '</div>';
            })
            ->rawColumns(['isActive_badge', 'action'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMasterPembayaranRequest $request)
    {
        try {
            $master = $this->service->create($request->validated());
            
            return ResponseHelper::success(
                new MasterPembayaranResource($master),
                'Master pembayaran berhasil dibuat'
            );
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $master = $this->service->find($id);
        
        if (!$master) {
            return ResponseHelper::notFound('Master pembayaran tidak ditemukan');
        }

        return ResponseHelper::success(new MasterPembayaranResource($master));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMasterPembayaranRequest $request, string $id)
    {
        try {
            $master = $this->service->update($id, $request->validated());
            
            return ResponseHelper::success(
                new MasterPembayaranResource($master),
                'Master pembayaran berhasil diupdate'
            );
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
            $this->service->delete($id);
            
            return ResponseHelper::success(null, 'Master pembayaran berhasil dihapus');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
