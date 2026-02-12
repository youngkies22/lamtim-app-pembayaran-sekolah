<?php

namespace App\Http\Controllers;

use App\Services\RombelService;
use App\Http\Requests\StoreRombelRequest;
use App\Http\Requests\UpdateRombelRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RombelController extends Controller
{
    public function __construct(
        protected RombelService $service
    ) {}

    public function index(Request $request)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['idSekolah', 'idJurusan', 'idKelas', 'search']);
            $rombel = $this->service->getCachedList($filters);

            return ResponseHelper::success($rombel);
        }

        if ($request->ajax()) {
            return $this->datatable($request);
        }

        return view('rombel.index');
    }

    public function datatable(Request $request)
    {
        $search = $request->input('search');
        if (is_array($search)) {
            $search = $search['value'] ?? $search[0] ?? '';
        }
        $searchTerm = trim((string) ($search ?? ''));

        $query = $this->service->buildDatatableQuery($searchTerm ?: null);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('sekolah_kode', fn($row) => $row->sekolah?->kode ?? '-')
            ->addColumn('jurusan_kode', fn($row) => $row->jurusan?->kode ?? '-')
            ->addColumn('kelas_kode', fn($row) => $row->kelas?->kode ?? '-')
            ->addColumn('isActive_badge', function ($row) {
                return $row->isActive == 1
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Tidak Aktif</span>';
            })
            ->addColumn('action', function ($row) {
                return '<div class="flex gap-1">' .
                    '<button data-action="edit" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Edit</button>' .
                    '<button data-action="delete" data-id="' . $row->id . '" data-nama="' . htmlspecialchars($row->nama) . '" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Hapus</button>' .
                    '</div>';
            })
            ->rawColumns(['isActive_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('rombel.create');
    }

    public function store(StoreRombelRequest $request)
    {
        try {
            $rombel = $this->service->create($request->validated());
            $this->service->invalidateCache();

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

    public function update(UpdateRombelRequest $request, string $id)
    {
        try {
            $rombel = $this->service->update($id, $request->validated());
            $this->service->invalidateCache();

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
            $this->service->invalidateCache();

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
     * Get options for select dropdown.
     */
    public function select(Request $request)
    {
        $filters = $request->only(['idSekolah', 'idJurusan', 'idKelas']);
        $bustCache = $request->has('_t');
        $rombel = $this->service->getSelectOptions($filters, $bustCache);

        return ResponseHelper::success($rombel);
    }
}
