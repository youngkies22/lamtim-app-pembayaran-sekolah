<?php

namespace App\Http\Controllers;

use App\Services\AgamaService;
use App\Helpers\ResponseHelper;
use App\Models\LamtimAgama;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AgamaController extends Controller
{
    protected $service;

    public function __construct(AgamaService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search']);

            // Create cache key based on filters
            $cacheKey = 'agama_list_' . md5(json_encode($filters));

            // Cache for 1 hour (agama rarely changes)
            $agama = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($filters) {
                return $this->service->getAll($filters);
            });

            return ResponseHelper::success($agama);
        }

        if ($request->ajax()) {
            return $this->datatable($request);
        }
        return view('agama.index');
    }

    public function datatable(Request $request)
    {
        $query = LamtimAgama::query()
            ->select('lamtim_agama.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row) {
                // Use API routes instead of web routes
                $editBtn = '<button data-action="edit" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Edit</button>';
                $deleteBtn = '<button data-action="delete" data-id="' . $row->id . '" data-nama="' . htmlspecialchars($row->nama) . '" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Hapus</button>';
                return '<div class="flex gap-1">' . $editBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('agama.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|unique:lamtim_agama,kode',
                'nama' => 'required|string|max:255',
            ]);

            $agama = $this->service->create($validated);

            if ($request->expectsJson()) {
                return ResponseHelper::success($agama, 'Agama berhasil dibuat');
            }

            return redirect()->route('agama.index')->with('success', 'Agama berhasil dibuat');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(string $id)
    {
        $agama = $this->service->find($id);
        if (!$agama) {
            if (request()->expectsJson()) {
                return ResponseHelper::notFound('Agama tidak ditemukan');
            }
            abort(404);
        }
        if (request()->expectsJson()) {
            return ResponseHelper::success($agama);
        }
        return view('agama.show', compact('agama'));
    }

    public function edit(string $id)
    {
        $agama = $this->service->find($id);
        if (!$agama) abort(404);
        return view('agama.edit', compact('agama'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'kode' => 'sometimes|string|unique:lamtim_agama,kode,' . $id,
                'nama' => 'sometimes|string|max:255',
            ]);

            $agama = $this->service->update($id, $validated);

            // Cache cleared automatically by Observer

            if ($request->expectsJson()) {
                return ResponseHelper::success($agama, 'Agama berhasil diupdate');
            }

            return redirect()->route('agama.index')->with('success', 'Agama berhasil diupdate');
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

            // Cache cleared automatically by Observer

            if (request()->expectsJson()) {
                return ResponseHelper::success(null, 'Agama berhasil dihapus');
            }
            return redirect()->route('agama.index')->with('success', 'Agama berhasil dihapus');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
