<?php

namespace App\Http\Controllers;

use App\Models\LamtimTahunAjaran;

use App\Services\TahunAjaranService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TahunAjaranController extends Controller
{
    protected $service;

    public function __construct(TahunAjaranService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search']);
            $tahunAjaran = $this->service->getAll($filters);
            return ResponseHelper::success($tahunAjaran);
        }

        if ($request->ajax()) {
            return $this->datatable();
        }
        return view('tahun-ajaran.index');
    }

    public function datatable()
    {
        $query = LamtimTahunAjaran::query()
            ->select('lamtim_tahun_ajarans.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('isActive_badge', function($row) {
                return $row->isActive 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Tidak Aktif</span>';
            })
            ->addColumn('action', function($row) {
                $editBtn = '<a href="' . route('tahun-ajaran.edit', $row->id) . '" class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800">Edit</a>';
                $setActiveBtn = '<button onclick="setActive(' . $row->id . ')" class="px-3 py-1 text-sm text-green-600 hover:text-green-800">Set Aktif</button>';
                $deleteBtn = '<button onclick="deleteTahunAjaran(' . $row->id . ')" class="px-3 py-1 text-sm text-red-600 hover:text-red-800">Hapus</button>';
                return $editBtn . ' | ' . $setActiveBtn . ' | ' . $deleteBtn;
            })
            ->rawColumns(['isActive_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('tahun-ajaran.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|unique:lamtim_tahun_ajarans,kode',
                'slag' => 'required|string|max:100',
                'nama' => 'required|string|max:255',
            ]);

            $tahunAjaran = $this->service->create($validated);
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($tahunAjaran, 'Tahun ajaran berhasil dibuat');
            }

            return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil dibuat');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(string $id)
    {
        $tahunAjaran = $this->service->find($id);
        if (!$tahunAjaran) {
            if (request()->expectsJson()) {
                return ResponseHelper::notFound('Tahun ajaran tidak ditemukan');
            }
            abort(404);
        }
        if (request()->expectsJson()) {
            return ResponseHelper::success($tahunAjaran);
        }
        return view('tahun-ajaran.show', compact('tahunAjaran'));
    }

    public function edit(string $id)
    {
        $tahunAjaran = $this->service->find($id);
        if (!$tahunAjaran) abort(404);
        return view('tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'kode' => 'sometimes|string|unique:lamtim_tahun_ajarans,kode,' . $id,
                'slag' => 'sometimes|string|max:100',
                'nama' => 'sometimes|string|max:255',
            ]);

            $tahunAjaran = $this->service->update($id, $validated);
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($tahunAjaran, 'Tahun ajaran berhasil diupdate');
            }

            return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil diupdate');
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
            if (request()->expectsJson()) {
                return ResponseHelper::success(null, 'Tahun ajaran berhasil dihapus');
            }
            return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil dihapus');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function setActive(string $id)
    {
        try {
            $this->service->setActive($id);
            if (request()->expectsJson()) {
                return ResponseHelper::success(null, 'Tahun ajaran berhasil diaktifkan');
            }
            return redirect()->route('tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil diaktifkan');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
