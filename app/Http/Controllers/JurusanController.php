<?php

namespace App\Http\Controllers;

use App\Services\JurusanService;
use App\Helpers\ResponseHelper;
use App\Models\LamtimJurusan;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class JurusanController extends Controller
{
    protected $service;

    public function __construct(JurusanService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['idSekolah', 'search']);
            
            // Check if force refresh is requested (via _t parameter)
            $forceRefresh = $request->has('_t');
            
            // Create cache key based on filters
            $cacheKey = 'jurusan_list_' . md5(json_encode($filters));
            
            // If force refresh, skip cache
            if ($forceRefresh) {
                $jurusan = $this->service->getAll($filters);
            } else {
                // Cache for 5 minutes (reduced from 30 minutes for better data freshness)
                $jurusan = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($filters) {
                    return $this->service->getAll($filters);
                });
            }
            
            return ResponseHelper::success($jurusan);
        }

        if ($request->ajax()) {
            return $this->datatable();
        }
        return view('jurusan.index');
    }

    public function datatable(Request $request)
    {
        // Build cache key from request params
        $cacheKey = 'jurusan_datatable_' . md5(json_encode($request->all()));
        
        // Try to get from cache first
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return response()->json($cached);
        }

        // Hanya select kolom yang diperlukan
        $query = LamtimJurusan::query()
            ->select('lamtim_jurusans.id', 'lamtim_jurusans.kode', 'lamtim_jurusans.nama', 'lamtim_jurusans.idSekolah')
            ->with(['sekolah:id,kode,nama']);

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
            ->addColumn('sekolah_kode', function($row) {
                return $row->sekolah->kode ?? '-';
            })
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

    public function create()
    {
        return view('jurusan.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'idSekolah' => 'required|uuid|exists:lamtim_sekolahs,id',
                'kode' => 'required|string|unique:lamtim_jurusans,kode',
                'nama' => 'required|string|max:255',
            ]);

            $jurusan = $this->service->create($validated);
            
            // Clear all jurusan cache patterns
            $this->clearJurusanCache();
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($jurusan, 'Jurusan berhasil dibuat');
            }

            return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil dibuat');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(string $id)
    {
        $jurusan = $this->service->find($id);
        if (!$jurusan) {
            if (request()->expectsJson()) {
                return ResponseHelper::notFound('Jurusan tidak ditemukan');
            }
            abort(404);
        }
        if (request()->expectsJson()) {
            return ResponseHelper::success($jurusan);
        }
        return view('jurusan.show', compact('jurusan'));
    }

    public function edit(string $id)
    {
        $jurusan = $this->service->find($id);
        if (!$jurusan) abort(404);
        return view('jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'idSekolah' => 'sometimes|uuid|exists:lamtim_sekolahs,id',
                'kode' => 'sometimes|string|unique:lamtim_jurusans,kode,' . $id,
                'nama' => 'sometimes|string|max:255',
            ]);

            $jurusan = $this->service->update($id, $validated);
            
            // Clear all jurusan cache patterns
            $this->clearJurusanCache();
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($jurusan, 'Jurusan berhasil diupdate');
            }

            return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil diupdate');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            
            // Clear all jurusan cache patterns
            $this->clearJurusanCache();
            
            if (request()->expectsJson()) {
                return ResponseHelper::success(null, 'Jurusan berhasil dihapus');
            }
            return redirect()->route('jurusan.index')->with('success', 'Jurusan berhasil dihapus');
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
        $filters = $request->only(['idSekolah']);
        $cacheKey = 'jurusan_select_' . md5(json_encode($filters));
        
        // Try to get from cache first
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return ResponseHelper::success($cached);
        }

        $query = LamtimJurusan::query()
            ->select('id', 'kode', 'nama');
        
        if (!empty($filters['idSekolah'])) {
            $query->where('idSekolah', $filters['idSekolah']);
        }
        
        $jurusan = $query->orderBy('kode')->get()->toArray();
        
        // Cache for 5 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $jurusan, 300);
        
        return ResponseHelper::success($jurusan);
    }

    /**
     * Clear all jurusan cache patterns
     */
    protected function clearJurusanCache(): void
    {
        // Clear cache by trying to forget common filter combinations
        $commonFilters = [
            [],
            ['search' => ''],
            ['search' => null],
            ['idSekolah' => null],
            ['idSekolah' => ''],
        ];

        foreach ($commonFilters as $filters) {
            $cacheKey = 'jurusan_list_' . md5(json_encode($filters));
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }
        
        // Also clear cache without filters (empty array)
        \Illuminate\Support\Facades\Cache::forget('jurusan_list_' . md5('[]'));
        
        // Try to clear with empty string search
        $emptySearchKey = 'jurusan_list_' . md5(json_encode(['search' => '']));
        \Illuminate\Support\Facades\Cache::forget($emptySearchKey);
        
        // Try to clear with null search
        $nullSearchKey = 'jurusan_list_' . md5(json_encode(['search' => null]));
        \Illuminate\Support\Facades\Cache::forget($nullSearchKey);
    }
}
