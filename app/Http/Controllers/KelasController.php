<?php

namespace App\Http\Controllers;

use App\Services\KelasService;
use App\Helpers\ResponseHelper;
use App\Models\LamtimKelas;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KelasController extends Controller
{
    protected $service;

    public function __construct(KelasService $service)
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
            $filters = $request->only(['search']);
            
            // Check if force refresh is requested (via _t parameter)
            $forceRefresh = $request->has('_t');
            
            // Create cache key based on filters
            $cacheKey = 'kelas_list_' . md5(json_encode($filters));
            
            // If force refresh, skip cache
            if ($forceRefresh) {
                $kelas = $this->service->getAll($filters);
            } else {
                // Cache for 5 minutes (reduced from 30 minutes for better data freshness)
                $kelas = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($filters) {
                    return $this->service->getAll($filters);
                });
            }
            
            return ResponseHelper::success($kelas);
        }

        if ($request->ajax()) {
            return $this->datatable();
        }

        return view('kelas.index');
    }

    /**
     * DataTables server-side processing
     */
    public function datatable(Request $request)
    {
        // Build cache key from request params
        $cacheKey = 'kelas_datatable_' . md5(json_encode($request->all()));
        
        // Try to get from cache first
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return response()->json($cached);
        }

        // Hanya select kolom yang diperlukan
        $query = LamtimKelas::query()
            ->select('lamtim_kelas.id', 'lamtim_kelas.kode', 'lamtim_kelas.nama');

        // Handle search
        if ($request->has('search')) {
            $search = $request->input('search');
            if (is_array($search)) {
                $search = $search['value'] ?? $search[0] ?? '';
            }
            $searchTerm = trim((string) $search);
            if (!empty($searchTerm)) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('kode', 'like', "%{$searchTerm}%")
                      ->orWhere('nama', 'like', "%{$searchTerm}%");
                });
            }
        }

        $result = DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row) {
                return '<div class="flex gap-1">' .
                    '<button data-action="edit" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Edit</button>' .
                    '<button data-action="delete" data-id="' . $row->id . '" data-nama="' . htmlspecialchars($row->nama) . '" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Hapus</button>' .
                    '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);

        // Cache the result for 5 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $result->getData(true), 300);

        return $result;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kelas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|unique:lamtim_kelas,kode',
                'nama' => 'required|string|max:255',
            ]);

            $kelas = $this->service->create($validated);
            
            // Clear all kelas cache patterns
            $this->clearKelasCache();
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($kelas, 'Kelas berhasil dibuat');
            }

            return redirect()->route('kelas.index')
                ->with('success', 'Kelas berhasil dibuat');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $kelas = $this->service->find($id);
        
        if (!$kelas) {
            if (request()->expectsJson()) {
                return ResponseHelper::notFound('Kelas tidak ditemukan');
            }
            abort(404);
        }

        if (request()->expectsJson()) {
            return ResponseHelper::success($kelas);
        }

        return view('kelas.show', compact('kelas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kelas = $this->service->find($id);
        
        if (!$kelas) {
            abort(404);
        }

        return view('kelas.edit', compact('kelas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'kode' => 'sometimes|string|unique:lamtim_kelas,kode,' . $id,
                'nama' => 'sometimes|string|max:255',
            ]);

            $kelas = $this->service->update($id, $validated);
            
            // Clear all kelas cache patterns
            $this->clearKelasCache();
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($kelas, 'Kelas berhasil diupdate');
            }

            return redirect()->route('kelas.index')
                ->with('success', 'Kelas berhasil diupdate');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            
            // Clear all kelas cache patterns
            $this->clearKelasCache();
            
            if (request()->expectsJson()) {
                return ResponseHelper::success(null, 'Kelas berhasil dihapus');
            }

            return redirect()->route('kelas.index')
                ->with('success', 'Kelas berhasil dihapus');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get options for select dropdown (only id, kode, nama)
     */
    public function select(Request $request)
    {
        $cacheKey = 'kelas_select';
        
        // Try to get from cache first
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return ResponseHelper::success($cached);
        }

        $kelas = LamtimKelas::query()
            ->select('id', 'kode', 'nama')
            ->orderBy('kode')
            ->get()
            ->toArray();
        
        // Cache for 5 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $kelas, 300);
        
        return ResponseHelper::success($kelas);
    }

    /**
     * Clear all kelas cache patterns
     */
    protected function clearKelasCache(): void
    {
        // Clear cache by trying to forget common filter combinations
        $commonFilters = [
            [],
            ['search' => ''],
            ['search' => null],
        ];

        foreach ($commonFilters as $filters) {
            $cacheKey = 'kelas_list_' . md5(json_encode($filters));
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }
        
        // Also clear cache without filters (empty array)
        \Illuminate\Support\Facades\Cache::forget('kelas_list_' . md5('[]'));
        
        // Try to clear with empty string search
        $emptySearchKey = 'kelas_list_' . md5(json_encode(['search' => '']));
        \Illuminate\Support\Facades\Cache::forget($emptySearchKey);
        
        // Try to clear with null search
        $nullSearchKey = 'kelas_list_' . md5(json_encode(['search' => null]));
        \Illuminate\Support\Facades\Cache::forget($nullSearchKey);
    }
}
