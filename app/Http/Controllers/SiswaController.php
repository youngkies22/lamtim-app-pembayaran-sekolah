<?php

namespace App\Http\Controllers;

use App\Services\SiswaService;
use App\Http\Requests\StoreSiswaRequest;
use App\Http\Requests\UpdateSiswaRequest;
use App\Helpers\ResponseHelper;
use App\Models\LamtimSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;

class SiswaController extends Controller
{
    protected $service;

    public function __construct(SiswaService $service)
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
            $filters = $request->only(['search', 'idKelas', 'idJurusan', 'idRombel', 'tahunAngkatan', 'jsk', 'isActive', 'sort', 'direction']);
            $siswa = $this->service->getPaginated($filters, $request->get('per_page', 15));
            return ResponseHelper::success($siswa);
        }

        if ($request->ajax()) {
            return $this->datatable($request);
        }

        return view('siswa.index');
    }

    /**
     * Get options for select dropdown (id, nis, nama, rombel)
     * Supports filters: idSekolah, mode (aktif|alumni)
     * - aktif (default): returns isActive=1 students
     * - alumni: returns isActive=0 students who still have unpaid tagihan
     */
    public function select(Request $request)
    {
        $filters = $request->only(['isActive', 'idSekolah', 'mode']);
        $cacheKey = 'siswa_select_' . md5(json_encode($filters));

        // Try to get from cache first
        $cached = Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return ResponseHelper::success($cached);
        }

        $mode = $filters['mode'] ?? 'aktif';

        $query = LamtimSiswa::query()
            ->select('lamtim_siswas.id', 'lamtim_siswas.nis', 'lamtim_siswas.nama', 'lamtim_siswas.nisn')
            ->with(['currentRombel:id,idSiswa,idRombel', 'currentRombel.rombel:id,nama,idKelas,idSekolah', 'currentRombel.rombel.kelas:id,kode']);

        // Filter by mode
    if ($mode === 'alumni') {
        // Alumni: graduated students (isAlumni=1) with unpaid tagihan
        $query->where('lamtim_siswas.isAlumni', 1)
            ->whereHas('tagihans', function ($q) {
                $q->where('totalSisa', '>', 0);
            });
    } else {
        // Default: active students (isActive=1 and NOT alumni)
        $query->where('lamtim_siswas.isActive', 1)
              ->where('lamtim_siswas.isAlumni', 0);
    }

        // Filter by sekolah (via rombel relationship)
        if (!empty($filters['idSekolah'])) {
            $query->whereHas('currentRombel.rombel', function ($q) use ($filters) {
                $q->where('idSekolah', $filters['idSekolah']);
            });
        }

        $siswa = $query->orderBy('lamtim_siswas.nis')->get();

        // Format response dengan rombel
        $formatted = $siswa->map(function($item) {
            $rombelInfo = '-';
            if ($item->currentRombel && $item->currentRombel->rombel) {
                $rombel = $item->currentRombel->rombel;
                $kelasKode = $rombel->kelas->kode ?? '';
                $rombelInfo = trim(($kelasKode ? "$kelasKode " : "") . ($rombel->nama ?? ''));
            }

            return [
                'id' => $item->id,
                'nis' => $item->nis,
                'nisn' => $item->nisn,
                'nama' => $item->nama,
                'rombel' => $rombelInfo,
            ];
        })->toArray();

        // Cache for 5 minutes
       Cache::put($cacheKey, $formatted, 300);

        return ResponseHelper::success($formatted);
    }

    /**
     * DataTables server-side processing
     */
    public function datatable(Request $request)
    {
        // Build cache key from request params
        $cacheKey = 'siswa_datatable_' . md5(json_encode($request->all()));

        // Try to get from cache first
        $cached = Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return response()->json($cached);
        }

        // Hanya select kolom yang diperlukan
        $query = LamtimSiswa::query()
            ->select('lamtim_siswas.id', 'lamtim_siswas.nama', 'lamtim_siswas.username', 'lamtim_siswas.nis', 'lamtim_siswas.nisn', 'lamtim_siswas.jsk', 'lamtim_siswas.isActive', 'lamtim_siswas.isAlumni', 'lamtim_siswas.tahunAngkatan')
            ->with([
                'currentRombel:id,idSiswa,idRombel',
                'currentRombel.rombel:id,kode,nama,idJurusan,idKelas',
                'currentRombel.rombel.jurusan:id,kode,nama',
                'currentRombel.rombel.kelas:id,kode'
            ]);


        // Apply filters
        // Handle search - can be string or array from DataTables
        if ($request->has('search')) {
            $search = $request->input('search');
            // If search is array (from DataTables), get the value
            if (is_array($search)) {
                $search = $search['value'] ?? $search[0] ?? '';
            }
            // Convert to string, trim, and ensure it's not empty
            $searchTerm = trim((string) $search);
            // Only apply if search is not empty
            if (!empty($searchTerm)) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('nama', 'like', "%{$searchTerm}%")
                      ->orWhere('username', 'like', "%{$searchTerm}%")
                      ->orWhere('nis', 'like', "%{$searchTerm}%")
                      ->orWhere('nisn', 'like', "%{$searchTerm}%");
                });
            }
        }

        // Handle idKelas filter
        if ($request->has('idKelas')) {
            $idKelas = $request->input('idKelas');
            if (is_array($idKelas)) {
                $idKelas = $idKelas['value'] ?? $idKelas[0] ?? null;
            }
            if (!empty($idKelas)) {
                $query->whereHas('currentRombel.rombel', function($q) use ($idKelas) {
                    $q->where('idKelas', $idKelas);
                });
            }
        }

        // Handle idJurusan filter
        if ($request->has('idJurusan')) {
            $idJurusan = $request->input('idJurusan');
            if (is_array($idJurusan)) {
                $idJurusan = $idJurusan['value'] ?? $idJurusan[0] ?? null;
            }
            if (!empty($idJurusan)) {
                $query->whereHas('currentRombel.rombel', function($q) use ($idJurusan) {
                    $q->where('idJurusan', $idJurusan);
                });
            }
        }

        // Handle idRombel filter
        if ($request->has('idRombel')) {
            $idRombel = $request->input('idRombel');
            if (is_array($idRombel)) {
                $idRombel = $idRombel['value'] ?? $idRombel[0] ?? null;
            }
            if (!empty($idRombel)) {
                $query->whereHas('currentRombel', function($q) use ($idRombel) {
                    $q->where('id', $idRombel);
                });
            }
        }

        // Handle isActive filter
        if ($request->has('isActive') && $request->input('isActive') !== null && $request->input('isActive') !== '') {
            $isActive = $request->input('isActive');
            if (is_array($isActive)) {
                $isActive = $isActive['value'] ?? $isActive[0] ?? null;
            }
            if ($isActive !== null && $isActive !== '') {
                $query->where('isActive', $isActive);
            }
        }

        // Handle isAlumni filter
        if ($request->has('isAlumni') && $request->input('isAlumni') !== null && $request->input('isAlumni') !== '') {
            $query->where('isAlumni', $request->input('isAlumni'));
        } else {
            // Default: hide alumni in main list
            $query->where('isAlumni', 0);
        }

        // Handle tahunAngkatan filter
        if ($request->has('tahunAngkatan') && !empty($request->input('tahunAngkatan'))) {
            $tahun = $request->input('tahunAngkatan');
            $query->where('tahunAngkatan', 'like', "%{$tahun}%");
        }

        $result = DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('jsk_label', function($row) {
                return $row->jsk == 1 ? 'Laki-laki' : 'Perempuan';
            })
            ->addColumn('isActive_badge', function ($row) {
                if ($row->isAlumni) {
                    $status = (int)$row->isActive === 1 ? 'AKTIF' : 'OFF';
                    return \App\Helpers\FormatHelper::statusBadge($status, 'alumni');
                }
                return \App\Helpers\FormatHelper::statusBadge((int)$row->isActive, 'siswa');
            })
            ->addColumn('rombel_info', function($row) {
                if ($row->currentRombel && $row->currentRombel->rombel) {
                    $rombel = $row->currentRombel->rombel;
                    $kelasKode = $rombel->kelas->kode ?? '';
                    return trim(($kelasKode ? "$kelasKode " : "") . ($rombel->nama ?? ''));
                }
                return '-';
            })
            ->addColumn('jurusan_info', function($row) {
                if ($row->currentRombel && $row->currentRombel->rombel && $row->currentRombel->rombel->jurusan) {
                    return $row->currentRombel->rombel->jurusan->nama ?? '-';
                }
                return '-';
            })
            ->addColumn('action', function($row) {
                return '<div class="flex gap-1">' .
                    '<button data-action="edit" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Edit</button>' .
                    '<button data-action="delete" data-id="' . $row->id . '" data-nama="' . htmlspecialchars($row->nama) . '" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Hapus</button>' .
                    '</div>';
            })
            ->rawColumns(['isActive_badge', 'action'])
            ->make(true);

        // Get data and filter only needed columns
        $data = $result->getData(true);
        if (isset($data->data) && is_array($data->data)) {
            $data->data = array_map(function($item) {
                $itemArray = is_object($item) ? json_decode(json_encode($item), true) : $item;
                return array_intersect_key($itemArray, array_flip([
                    'id', 'nama', 'username', 'nis', 'nisn', 'jsk_label',
                    'isActive_badge', 'rombel_info', 'jurusan_info', 'tahunAngkatan', 'isAlumni',
                    'action', 'DT_RowIndex'
                ]));
            }, $data->data);
        }

        // Cache the filtered result for 5 minutes
        Cache::put($cacheKey, $data, 300);

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('siswa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiswaRequest $request)
    {
        $this->authorize('create', LamtimSiswa::class);

        try {
            $siswa = $this->service->create($request->validated());

            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::success($siswa, 'Siswa berhasil dibuat');
            }

            return redirect()->route('siswa.index')
                ->with('success', 'Siswa berhasil dibuat');
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
        $siswa = $this->service->find($id);

        if (!$siswa) {
            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::notFound('Siswa tidak ditemukan');
            }
            abort(404);
        }

        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            return ResponseHelper::success($siswa);
        }

        return view('siswa.show', compact('siswa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $siswa = $this->service->find($id);
        if (!$siswa) abort(404);
        
        $this->authorize('update', $siswa);

        return view('siswa.edit', compact('siswa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiswaRequest $request, string $id)
    {
        $siswa = $this->service->find($id);
        if (!$siswa) {
             if ($request->expectsJson()) return ResponseHelper::notFound('Siswa tidak ditemukan');
             return abort(404);
        }

        $this->authorize('update', $siswa);

        try {
            $siswa = $this->service->update($id, $request->validated());

            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::success($siswa, 'Siswa berhasil diupdate');
            }

            return redirect()->route('siswa.index')
                ->with('success', 'Siswa berhasil diupdate');
        } catch (\Exception $e) {
            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $siswa = $this->service->find($id);
        if (!$siswa) {
             if (request()->expectsJson()) return ResponseHelper::notFound('Siswa tidak ditemukan');
             return back()->withErrors(['error' => 'Siswa tidak ditemukan']);
        }

        $this->authorize('delete', $siswa);

        try {
            $this->service->delete($id);

            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::success(null, 'Siswa berhasil dihapus');
            }

            return redirect()->route('siswa.index')
                ->with('success', 'Siswa berhasil dihapus');
        } catch (\Exception $e) {
            // API always returns JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Mark selected students as alumni (batch set isActive = 0)
     */
    public function markAsAlumni(Request $request)
    {
        try {
            $validated = $request->validate([
                'siswa_ids' => 'required|array|min:1',
                'siswa_ids.*' => 'required|uuid|exists:lamtim_siswas,id',
            ]);

            $result = $this->service->markAsAlumni($validated['siswa_ids']);

            // Clear siswa select cache
            Cache::forget('siswa_select_' . md5(json_encode([])));

            return ResponseHelper::success($result, 'Siswa berhasil dijadikan alumni');
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal menjadikan alumni: ' . $e->getMessage(), 500);
        }
    }
}
