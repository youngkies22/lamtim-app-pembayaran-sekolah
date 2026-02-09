<?php

namespace App\Http\Controllers;

use App\Services\RombelService;
use App\Helpers\ResponseHelper;
use App\Models\LamtimRombel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RombelController extends Controller
{
    protected $service;

    public function __construct(RombelService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['idSekolah', 'idJurusan', 'idKelas', 'search']);
            
            // Create cache key based on filters
            $cacheKey = 'rombel_list_' . md5(json_encode($filters));
            
            // Cache for 30 minutes
            $rombel = \Illuminate\Support\Facades\Cache::remember($cacheKey, 1800, function () use ($filters) {
                return $this->service->getAll($filters);
            });
            
            return ResponseHelper::success($rombel);
        }

        if ($request->ajax()) {
            return $this->datatable();
        }
        return view('rombel.index');
    }

    public function datatable(Request $request)
    {
        // Build cache key from request params
        $cacheKey = 'rombel_datatable_' . md5(json_encode($request->all()));
        
        // Try to get from cache first
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return response()->json($cached);
        }

        // Hanya select kolom yang diperlukan
        $query = LamtimRombel::query()
            ->select('lamtim_rombels.id', 'lamtim_rombels.kode', 'lamtim_rombels.nama', 'lamtim_rombels.idSekolah', 'lamtim_rombels.idJurusan', 'lamtim_rombels.idKelas', 'lamtim_rombels.isActive')
            ->with([
                'sekolah:id,kode,nama',
                'jurusan:id,kode,nama',
                'kelas:id,kode,nama'
            ]);

        // Handle search - can be string or array from DataTables
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
                return $row->sekolah?->kode ?? '-';
            })
            ->addColumn('jurusan_kode', function($row) {
                return $row->jurusan?->kode ?? '-';
            })
            ->addColumn('kelas_kode', function($row) {
                return $row->kelas?->kode ?? '-';
            })
            ->addColumn('isActive_badge', function($row) {
                return $row->isActive == 1 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Tidak Aktif</span>';
            })
            ->addColumn('action', function($row) {
                return '<div class="flex gap-1">' .
                    '<button data-action="edit" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Edit</button>' .
                    '<button data-action="delete" data-id="' . $row->id . '" data-nama="' . htmlspecialchars($row->nama) . '" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Hapus</button>' .
                    '</div>';
            })
            ->rawColumns(['isActive_badge', 'action'])
            ->make(true);

        // Cache the result for 5 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $result->getData(true), 300);

        return $result;
    }

    public function create()
    {
        return view('rombel.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'idSekolah' => 'required|uuid|exists:lamtim_sekolahs,id',
                'idJurusan' => 'required|uuid|exists:lamtim_jurusans,id',
                'idKelas' => 'nullable|uuid|exists:lamtim_kelas,id',
                'kode' => 'required|string|unique:lamtim_rombels,kode',
                'nama' => 'required|string|max:255',
            ]);

            $rombel = $this->service->create($validated);
            
            // Clear all rombel cache patterns
            $this->clearRombelCache();
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($rombel, 'Rombel berhasil dibuat');
            }

            return redirect()->route('rombel.index')->with('success', 'Rombel berhasil dibuat');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(string $id)
    {
        $rombel = $this->service->find($id);
        if (!$rombel) {
            if (request()->expectsJson()) {
                return ResponseHelper::notFound('Rombel tidak ditemukan');
            }
            abort(404);
        }
        if (request()->expectsJson()) {
            return ResponseHelper::success($rombel);
        }
        return view('rombel.show', compact('rombel'));
    }

    public function edit(string $id)
    {
        $rombel = $this->service->find($id);
        if (!$rombel) abort(404);
        return view('rombel.edit', compact('rombel'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'idSekolah' => 'sometimes|uuid|exists:lamtim_sekolahs,id',
                'idJurusan' => 'sometimes|uuid|exists:lamtim_jurusans,id',
                'idKelas' => 'nullable|uuid|exists:lamtim_kelas,id',
                'kode' => 'sometimes|string|unique:lamtim_rombels,kode,' . $id,
                'nama' => 'sometimes|string|max:255',
            ]);

            $rombel = $this->service->update($id, $validated);
            
            // Clear all rombel cache patterns
            $this->clearRombelCache();
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($rombel, 'Rombel berhasil diupdate');
            }

            return redirect()->route('rombel.index')->with('success', 'Rombel berhasil diupdate');
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
            
            // Clear all rombel cache patterns
            $this->clearRombelCache();
            
            if (request()->expectsJson()) {
                return ResponseHelper::success(null, 'Rombel berhasil dihapus');
            }
            return redirect()->route('rombel.index')->with('success', 'Rombel berhasil dihapus');
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
        $filters = $request->only(['idSekolah', 'idJurusan', 'idKelas']);
        $cacheKey = 'rombel_select_' . md5(json_encode($filters));
        
        // Try to get from cache first
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return ResponseHelper::success($cached);
        }

        $query = LamtimRombel::query()
            ->select('id', 'kode', 'nama');
        
        if (!empty($filters['idSekolah'])) {
            $query->where('idSekolah', $filters['idSekolah']);
        }
        if (!empty($filters['idJurusan'])) {
            $query->where('idJurusan', $filters['idJurusan']);
        }
        if (!empty($filters['idKelas'])) {
            $query->where('idKelas', $filters['idKelas']);
        }
        
        $rombel = $query->orderBy('kode')->get()->toArray();
        
        // Cache for 5 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $rombel, 300);
        
        return ResponseHelper::success($rombel);
    }

    /**
     * Clear all rombel cache patterns
     */
    protected function clearRombelCache(): void
    {
        // Clear cache by trying to forget common filter combinations
        $commonFilters = [
            [],
            ['search' => ''],
            ['search' => null],
            ['idSekolah' => null],
            ['idJurusan' => null],
        ];

        foreach ($commonFilters as $filters) {
            $cacheKey = 'rombel_list_' . md5(json_encode($filters));
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }
        
        // Also clear cache without filters (empty array)
        \Illuminate\Support\Facades\Cache::forget('rombel_list_' . md5('[]'));
    }
}
