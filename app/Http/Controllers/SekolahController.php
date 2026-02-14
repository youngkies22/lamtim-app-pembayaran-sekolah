<?php

namespace App\Http\Controllers;

use App\Models\LamtimSekolah;

use App\Services\SekolahService;
use App\Services\PublicDataService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class SekolahController extends Controller
{
    protected $service;
    protected $publicDataService;

    public function __construct(SekolahService $service, PublicDataService $publicDataService)
    {
        $this->service = $service;
        $this->publicDataService = $publicDataService;
    }

    public function index(Request $request)
    {
        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search']);
            $sekolah = $this->service->getAll($filters);
            return ResponseHelper::success($sekolah);
        }

        if ($request->ajax()) {
            return $this->datatable();
        }
        return view('sekolah.index');
    }

    /**
     * Get options for select dropdown (only id, kode, nama)
     */
    public function select(Request $request)
    {
        $cacheKey = 'sekolah_select';
        
        // Try to get from cache first
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached && !$request->has('_t')) {
            return ResponseHelper::success($cached);
        }

        $sekolah = LamtimSekolah::query()
            ->select('id', 'kode', 'nama')
            ->orderBy('kode')
            ->get()
            ->toArray();
        
        // Cache for 5 minutes
        \Illuminate\Support\Facades\Cache::put($cacheKey, $sekolah, 300);
        
        return ResponseHelper::success($sekolah);
    }

    public function datatable()
    {
        $query = LamtimSekolah::query()
            ->select('lamtim_sekolahs.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row) {
                $editBtn = '<a href="' . route('sekolah.edit', $row->id) . '" class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800">Edit</a>';
                $deleteBtn = '<button onclick="deleteSekolah(' . $row->id . ')" class="px-3 py-1 text-sm text-red-600 hover:text-red-800">Hapus</button>';
                return $editBtn . ' | ' . $deleteBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('sekolah.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'npsn' => 'nullable|string|unique:lamtim_sekolahs,npsn',
                'kode' => 'nullable|string|max:50|unique:lamtim_sekolahs,kode',
                'nama' => 'required|string|max:255',
                'namaYayasan' => 'nullable|string|max:255',
                'alamat' => 'nullable|string',
                'kota' => 'nullable|string',
                'provinsi' => 'nullable|string',
                'email' => 'nullable|email',
                'kepala_sekolah' => 'nullable|string|max:255',
                'nip_kepsek' => 'nullable|string|max:50',
                'telepon' => 'nullable|string|max:20',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle logo upload
            if ($request->hasFile('image')) {
                $logo = $request->file('image');
                $logoPath = $logo->store('sekolah/logos', 'public');
                $validated['logo'] = $logoPath;
            }

            $sekolah = $this->service->create($validated);

            // Clear public data cache after create
            $this->publicDataService->clearCache();
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($sekolah, 'Sekolah berhasil dibuat');
            }

            return redirect()->route('sekolah.index')->with('success', 'Sekolah berhasil dibuat');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(string $id)
    {
        $sekolah = $this->service->find($id);
        if (!$sekolah) {
            if (request()->expectsJson()) {
                return ResponseHelper::notFound('Sekolah tidak ditemukan');
            }
            abort(404);
        }
        if (request()->expectsJson()) {
            return ResponseHelper::success($sekolah);
        }
        return view('sekolah.show', compact('sekolah'));
    }

    public function edit(string $id)
    {
        $sekolah = $this->service->find($id);
        if (!$sekolah) abort(404);
        
        $this->authorize('update', $sekolah);
        
        return view('sekolah.edit', compact('sekolah'));
    }

    public function update(Request $request, string $id)
    {
        $sekolah = $this->service->find($id);
        if (!$sekolah) {
            if ($request->expectsJson()) return ResponseHelper::notFound('Sekolah tidak ditemukan');
            return abort(404);
        }

        $this->authorize('update', $sekolah);

        try {
            $validated = $request->validate([
                'npsn' => 'sometimes|string|unique:lamtim_sekolahs,npsn,' . $id,
                'kode' => 'sometimes|string|unique:lamtim_sekolahs,kode,' . $id,
                'nama' => 'sometimes|string|max:255',
                'alamat' => 'nullable|string',
                'kota' => 'nullable|string',
                'provinsi' => 'nullable|string',
                'email' => 'nullable|email',
                'kepala_sekolah' => 'nullable|string|max:255',
                'nip_kepsek' => 'nullable|string|max:50',
                'telepon' => 'nullable|string|max:20',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($request->hasFile('image')) {
                // Get existing sekolah to delete old logo
                $sekolah = $this->service->find($id);
                if ($sekolah && $sekolah->logo) {
                    Storage::disk('public')->delete($sekolah->logo);
                }

                $logo = $request->file('image');
                $logoPath = $logo->store('sekolah/logos', 'public');
                $validated['logo'] = $logoPath;
            }

            $sekolah = $this->service->update($id, $validated);

            // Clear public data cache after update
            $this->publicDataService->clearCache();
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($sekolah, 'Sekolah berhasil diupdate');
            }

            return redirect()->route('sekolah.index')->with('success', 'Sekolah berhasil diupdate');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(string $id)
    {
        $sekolah = $this->service->find($id);
        if (!$sekolah) {
            if (request()->expectsJson()) {
                return ResponseHelper::notFound('Sekolah tidak ditemukan');
            }
            return back()->withErrors(['error' => 'Sekolah tidak ditemukan']);
        }

        $this->authorize('delete', $sekolah);

        try {
            // Delete logo file if exists
            if ($sekolah->logo) {
                Storage::disk('public')->delete($sekolah->logo);
            }
            
            $this->service->delete($id);

            // Clear public data cache after delete
            $this->publicDataService->clearCache();

            if (request()->expectsJson()) {
                return ResponseHelper::success(null, 'Sekolah berhasil dihapus');
            }
            return redirect()->route('sekolah.index')->with('success', 'Sekolah berhasil dihapus');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
