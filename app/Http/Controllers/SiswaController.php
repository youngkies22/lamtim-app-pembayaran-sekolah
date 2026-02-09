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
     */
    public function select(Request $request)
    {
        $filters = $request->only(['isActive']);
        $cacheKey = 'siswa_select_' . md5(json_encode($filters));

        // Try to get from cache first
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return ResponseHelper::success($cached);
        }

        $query = LamtimSiswa::query()
            ->select('lamtim_siswas.id', 'lamtim_siswas.nis', 'lamtim_siswas.nama')
            ->with(['currentRombel:id,idSiswa,idRombel', 'currentRombel.rombel:id,nama']);

        if (isset($filters['isActive'])) {
            $query->where('lamtim_siswas.isActive', $filters['isActive']);
        } else {
            $query->where('lamtim_siswas.isActive', 1);
        }

        $siswa = $query->orderBy('lamtim_siswas.nis')->get();

        // Format response dengan rombel
        $formatted = $siswa->map(function($item) {
            return [
                'id' => $item->id,
                'nis' => $item->nis,
                'nama' => $item->nama,
                'rombel' => $item->currentRombel?->rombel?->nama ?? '-',
            ];
        })->toArray();

        // Cache for 5 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $formatted, 300);

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
            ->select('lamtim_siswas.id', 'lamtim_siswas.nama', 'lamtim_siswas.nis', 'lamtim_siswas.nisn', 'lamtim_siswas.jsk', 'lamtim_siswas.isActive')
            ->with([
                'currentRombel:id,idSiswa,idRombel',
                'currentRombel.rombel:id,kode,nama,idJurusan',
                'currentRombel.rombel.jurusan:id,kode,nama'
            ])
            ->makeHidden([
                'idAgama', 'username', 'qrcode', 'fotoOsis', 'fotoProfile',
                'waSiswa', 'waOrtu', 'waWali', 'tahunAngkatan',
                'createdBy', 'updatedBy', 'deletedBy',
                'created_at', 'updated_at', 'agama'
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

        $result = DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('jsk_label', function($row) {
                return $row->jsk == 1 ? 'Laki-laki' : 'Perempuan';
            })
            ->addColumn('isActive_badge', function($row) {
                return $row->isActive == 1
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Aktif</span>'
                    : ($row->isActive == 2
                        ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Off</span>'
                        : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Tidak Aktif</span>');
            })
            ->addColumn('rombel_info', function($row) {
                if ($row->currentRombel && $row->currentRombel->rombel) {
                    return $row->currentRombel->rombel->nama ?? '-';
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
                    'id', 'nama', 'nis', 'nisn', 'jsk_label',
                    'isActive_badge', 'rombel_info', 'jurusan_info',
                    'action', 'DT_RowIndex'
                ]));
            }, $data->data);
        }

        // Cache the filtered result for 5 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $data, 300);

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
    public function store(StoreSiswaRequest $request)
    {
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

        if (!$siswa) {
            abort(404);
        }

        return view('siswa.edit', compact('siswa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiswaRequest $request, string $id)
    {
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
}
