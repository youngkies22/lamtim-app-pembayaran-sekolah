<?php

namespace App\Http\Controllers;

use App\Services\TagihanService;
use App\Http\Resources\TagihanResource;
use App\Helpers\ResponseHelper;
use App\Helpers\FormatHelper;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TagihanController extends Controller
{
    public function __construct(
        protected TagihanService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search', 'idSiswa', 'idMasterPembayaran', 'jenisPembayaran', 'status', 'isActive', 'start_date', 'end_date']);
            $tagihans = $this->service->getPaginated($filters, $request->get('per_page', 15));

            return ResponseHelper::success(TagihanResource::collection($tagihans));
        }

        if ($request->ajax()) {
            return $this->datatable($request);
        }

        return view('tagihan.index');
    }

    /**
     * DataTables server-side processing.
     */
    public function datatable(Request $request)
    {
        $query = $this->service->buildDatatableQuery($request->only(['status', 'idMasterPembayaran', 'idRombel']));

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('siswa_nama', fn($row) => $row->siswa->nama ?? '-')
            ->addColumn('siswa_angkatan', fn($row) => $row->siswa->tahunAngkatan ?? '-')
            ->addColumn('rombel_nama', function ($row) {
                if ($row->rombel) {
                    $kelasKode = $row->rombel->kelas->kode ?? '';
                    return trim(($kelasKode ? "$kelasKode " : "") . ($row->rombel->nama ?? ''));
                }
                return '-';
            })
            ->addColumn('master_nama', fn($row) => $row->masterPembayaran->nama ?? '-')
            ->addColumn('tanggal_formatted', fn($row) => FormatHelper::date($row->tanggalTagihan))
            ->addColumn('nominal_formatted', fn($row) => FormatHelper::currency($row->nominalTagihan))
            ->addColumn('terbayar_formatted', fn($row) => FormatHelper::currency($row->totalSudahBayar ?? 0))
            ->addColumn('sisa_formatted', fn($row) => FormatHelper::currency($row->totalSisa ?? 0))
            ->addColumn('status_badge', fn($row) => FormatHelper::statusBadge($row->status, 'tagihan'))
            ->addColumn('sync_status_badge', fn($row) => FormatHelper::syncStatusBadge($row->sync_status))
            ->addColumn('action', function ($row) {
                $buttons = '<li><button data-action="detail" data-id="' . $row->id . '" class="w-full text-left px-3 py-2 text-sm font-medium text-emerald-600 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-900/20 rounded-lg transition-all duration-200 flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></div> Detail</button></li>';
                $buttons .= '<li><button data-action="retry-sync" data-id="' . $row->id . '" class="w-full text-left px-3 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20 rounded-lg transition-all duration-200 flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div> Retry Sync</button></li>';

                if ($row->status === 0) {
                    $buttons .= '<li><button data-action="delete" data-id="' . $row->id . '" data-kode="' . htmlspecialchars($row->kodeTagihan) . '" class="w-full text-left px-3 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-900/20 rounded-lg transition-all duration-200 flex items-center gap-3"><div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div> Hapus</button></li>';
                }

                return '
                <div class="relative inline-block text-left" data-dropdown>
                    <button type="button" class="group flex items-center justify-center w-9 h-9 rounded-xl hover:bg-emerald-500/10 dark:hover:bg-emerald-500/20 transition-all duration-300 border border-transparent hover:border-emerald-500/30 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-emerald-500 transition-colors duration-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>
                    </button>
                    <div class="hidden absolute left-full top-0 ml-3 w-44 rounded-2xl shadow-2xl bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl ring-1 ring-black/5 dark:ring-white/10 z-[100] overflow-hidden transform transition-all duration-300 border border-gray-100 dark:border-gray-700 p-1.5" data-dropdown-menu>
                        <ul class="space-y-1">
                            ' . $buttons . '
                        </ul>
                    </div>
                </div>';
            })
            ->rawColumns(['status_badge', 'sync_status_badge', 'action'])
            ->make(true);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $tagihan = $this->service->find($id);

        if (!$tagihan) {
            return ResponseHelper::notFound('Tagihan tidak ditemukan');
        }

        return ResponseHelper::success(new TagihanResource($tagihan));
    }

    /**
     * Generate tagihan batch.
     */
    public function generateBatch(Request $request)
    {
        try {
            $validated = $request->validate([
                'idMasterPembayaran' => 'required|uuid|exists:lamtim_master_pembayarans,id',
                'siswaIds' => 'nullable|array',
                'idKelas' => 'nullable|uuid|exists:lamtim_kelas,id',
                'idJurusan' => 'nullable|uuid|exists:lamtim_jurusans,id',
            ]);

            $result = $this->service->generateTagihanBatch(
                $validated['idMasterPembayaran'],
                $validated['siswaIds'] ?? [],
                $validated['idKelas'] ?? null,
                $validated['idJurusan'] ?? null
            );

            return ResponseHelper::success($result, 'Tagihan berhasil di-generate');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get tagihan by siswa (untuk billing).
     */
    public function getBySiswa(Request $request, string $idSiswa)
    {
        try {
            $tagihans = $this->service->getUnpaidBySiswa($idSiswa);

            return ResponseHelper::success(TagihanResource::collection($tagihans));
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $tagihan = $this->service->find($id);

            if (!$tagihan) {
                return ResponseHelper::notFound('Tagihan tidak ditemukan');
            }

            if ($tagihan->status != 0) {
                return ResponseHelper::error('Tagihan yang sudah dibayar tidak dapat dihapus', 422);
            }

            $this->service->delete($id);

            return ResponseHelper::success(null, 'Tagihan berhasil dihapus');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Retry sync for a specific tagihan.
     */
    public function retrySync(string $id)
    {
        try {
            $tagihan = $this->service->find($id);
            if (!$tagihan) {
                return ResponseHelper::notFound('Tagihan tidak ditemukan');
            }

            if (!\App\Services\SettingService::isJobEnabled('job_push_academic_enabled')) {
                return ResponseHelper::error('Push Academic Job tidak aktif. Aktifkan di Pengaturan.', 400);
            }

            \App\Jobs\PushAcademicDataJob::dispatch($tagihan);

            return ResponseHelper::success(null, 'Sync job dispatched');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
