<?php

namespace App\Http\Controllers;

use App\Services\SiswaRombelService;
use App\Helpers\ResponseHelper;
use App\Models\LamtimSiswaRombel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SiswaRombelController extends Controller
{
    protected $service;

    public function __construct(SiswaRombelService $service)
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
            $filters = $request->only(['idSiswa', 'idRombel']);
            $mappings = $this->service->getAll($filters);
            return ResponseHelper::success($mappings);
        }

        if ($request->ajax()) {
            return $this->datatable($request);
        }

        return view('siswa-rombel.index');
    }

    /**
     * DataTables server-side processing
     */
    public function datatable(Request $request)
    {
        $query = LamtimSiswaRombel::with(['siswa', 'rombel.sekolah', 'rombel.jurusan', 'rombel.kelas']);

        // Apply filters
        if ($request->has('idSiswa') && $request->input('idSiswa')) {
            $query->where('idSiswa', $request->input('idSiswa'));
        }

        if ($request->has('idRombel') && $request->input('idRombel')) {
            $query->where('idRombel', $request->input('idRombel'));
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('siswa_nama', function($row) {
                return $row->siswa ? $row->siswa->nama : '-';
            })
            ->addColumn('siswa_nis', function($row) {
                return $row->siswa ? ($row->siswa->nis ?? '-') : '-';
            })
            ->addColumn('rombel_nama', function($row) {
                return $row->rombel ? $row->rombel->nama : '-';
            })
            ->addColumn('sekolah_nama', function($row) {
                return $row->rombel?->sekolah?->nama ?? '-';
            })
            ->addColumn('jurusan_nama', function($row) {
                return $row->rombel?->jurusan?->nama ?? '-';
            })
            ->addColumn('kelas_kode', function($row) {
                return $row->rombel?->kelas?->kode ?? '-';
            })
            ->addColumn('action', function($row) {
                return '<div class="flex gap-1">' .
                    '<button data-action="edit" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Edit</button>' .
                    '<button data-action="delete" data-id="' . $row->id . '" data-siswa="' . htmlspecialchars($row->siswa?->nama ?? '') . '" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Hapus</button>' .
                    '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get students without rombel mapping (with pagination support)
     */
    public function getUnmappedStudents(Request $request)
    {
        try {
            $filters = $request->only(['search', 'page', 'per_page']);
            $students = $this->service->getUnmappedStudents($filters);
            
            // If paginated, return paginated response
            if ($students instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                return ResponseHelper::success([
                    'data' => $students->items(),
                    'current_page' => $students->currentPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                    'last_page' => $students->lastPage(),
                ]);
            }
            
            // If collection, return as array
            return ResponseHelper::success($students->toArray());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching unmapped students: ' . $e->getMessage(), ['exception' => $e]);
            return ResponseHelper::error('Gagal mengambil data siswa: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
                    $validated = $request->validate([
                        'idSiswa' => 'required|uuid|exists:lamtim_siswas,id',
                        'idRombel' => 'required|uuid|exists:lamtim_rombels,id',
                    ]);

            $mapping = $this->service->create($validated);

            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::success($mapping, 'Mapping siswa rombel berhasil dibuat');
            }

            return redirect()->route('siswa-rombel.index')
                ->with('success', 'Mapping siswa rombel berhasil dibuat');
        } catch (\Exception $e) {
            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $mapping = $this->service->find($id);

        if (!$mapping) {
            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::notFound('Mapping tidak ditemukan');
            }
            abort(404);
        }

        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            return ResponseHelper::success($mapping);
        }

        return view('siswa-rombel.show', compact('mapping'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
                    $validated = $request->validate([
                        'idRombel' => 'sometimes|uuid|exists:lamtim_rombels,id',
                    ]);

            $mapping = $this->service->update($id, $validated);

            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::success($mapping, 'Mapping siswa rombel berhasil diupdate');
            }

            return redirect()->route('siswa-rombel.index')
                ->with('success', 'Mapping siswa rombel berhasil diupdate');
        } catch (\Exception $e) {
            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Batch create mappings
     */
    public function batchStore(Request $request)
    {
        try {
                    $validated = $request->validate([
                        'siswa_ids' => 'required|array|min:1',
                        'siswa_ids.*' => 'required|uuid|exists:lamtim_siswas,id',
                        'idRombel' => 'required|uuid|exists:lamtim_rombels,id',
                    ]);

            $results = $this->service->batchCreate($validated);

            return ResponseHelper::success($results, 'Mapping berhasil dibuat untuk ' . count($validated['siswa_ids']) . ' siswa');
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal membuat mapping: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $this->service->delete($id);

            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::success(null, 'Mapping siswa rombel berhasil dihapus');
            }

            return redirect()->route('siswa-rombel.index')
                ->with('success', 'Mapping siswa rombel berhasil dihapus');
        } catch (\Exception $e) {
            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Promote students to the next kelas level (Kenaikan Kelas)
     */
    public function promote(Request $request)
    {
        try {
            $validated = $request->validate([
                'fromRombelId' => 'required|uuid|exists:lamtim_rombels,id',
                'siswa_ids' => 'required|array|min:1',
                'siswa_ids.*' => 'required|uuid|exists:lamtim_siswas,id',
            ]);

            $result = $this->service->promoteStudents(
                $validated['fromRombelId'],
                $validated['siswa_ids']
            );

            return ResponseHelper::success($result, 'Kenaikan kelas berhasil diproses');
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal memproses kenaikan kelas: ' . $e->getMessage(), 500);
        }
    }
}
