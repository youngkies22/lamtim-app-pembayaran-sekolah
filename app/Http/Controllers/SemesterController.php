<?php

namespace App\Http\Controllers;

use App\Models\LamtimSemester;
use App\Services\SemesterService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SemesterController extends Controller
{
    protected $service;

    public function __construct(SemesterService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search']);
            $semester = $this->service->getAll($filters);
            return ResponseHelper::success($semester);
        }

        if ($request->ajax()) {
            return $this->datatable();
        }
        return view('semester.index');
    }

    public function datatable()
    {
        $query = LamtimSemester::query()
            ->select('lamtim_semesters.*');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('isActive_badge', function($row) {
                return $row->isActive 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Aktif</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Tidak Aktif</span>';
            })
            ->addColumn('action', function($row) {
                $editBtn = '<button onclick="editSemester(\'' . $row->id . '\')" class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">Edit</button>';
                $setActiveBtn = '<button onclick="setActive(\'' . $row->id . '\')" class="px-3 py-1.5 text-xs font-medium text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">Set Aktif</button>';
                $deleteBtn = '<button onclick="deleteSemester(\'' . $row->id . '\', \'' . htmlspecialchars($row->kode) . '\')" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">Hapus</button>';
                return '<div class="flex gap-2">' . $editBtn . $setActiveBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['isActive_badge', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|unique:lamtim_semesters,kode',
                'nama' => 'required|string|max:255',
                'namaSingkat' => 'nullable|string|max:50',
            ]);

            $semester = $this->service->create($validated);
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($semester, 'Semester berhasil dibuat');
            }

            return redirect()->route('semester.index')->with('success', 'Semester berhasil dibuat');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function show(string $id)
    {
        $semester = $this->service->find($id);
        if (!$semester) {
            if (request()->expectsJson()) {
                return ResponseHelper::notFound('Semester tidak ditemukan');
            }
            abort(404);
        }
        if (request()->expectsJson()) {
            return ResponseHelper::success($semester);
        }
        return view('semester.show', compact('semester'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'kode' => 'required|string|unique:lamtim_semesters,kode,' . $id,
                'nama' => 'required|string|max:255',
                'namaSingkat' => 'nullable|string|max:50',
            ]);

            $semester = $this->service->update($id, $validated);
            
            if ($request->expectsJson()) {
                return ResponseHelper::success($semester, 'Semester berhasil diperbarui');
            }

            return redirect()->route('semester.index')->with('success', 'Semester berhasil diperbarui');
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
                return ResponseHelper::success(null, 'Semester berhasil dihapus');
            }

            return redirect()->route('semester.index')->with('success', 'Semester berhasil dihapus');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function setActive(Request $request, string $id)
    {
        try {
            $this->service->setActive($id);
            
            if ($request->expectsJson()) {
                return ResponseHelper::success(null, 'Semester berhasil diaktifkan');
            }

            return redirect()->route('semester.index')->with('success', 'Semester berhasil diaktifkan');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return ResponseHelper::error($e->getMessage(), 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
