<?php

namespace App\Http\Controllers;

use App\Models\LamtimPembayaran;

use App\Http\Requests\ProsesPembayaranRequest;
use App\Http\Resources\PembayaranResource;
use App\Services\PembayaranService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;

class PembayaranController extends Controller
{
    protected $service;

    public function __construct(PembayaranService $service)
    {
        $this->service = $service;
    }

    /**
     * Get pembayaran stats
     */
    public function stats()
    {
        $stats = Cache::rememberForever('pembayaran_stats', function () {
            return LamtimPembayaran::where('isActive', 1)
                ->selectRaw('
                    SUM(CASE WHEN "status" = 1 THEN "nominalBayar" ELSE 0 END) as total,
                    COUNT(CASE WHEN "status" = 1 AND "isVerified" = 1 THEN 1 END) as verified,
                    COUNT(CASE WHEN "status" = 0 THEN 1 END) as pending,
                    COUNT(CASE WHEN "status" = 2 THEN 1 END) as cancelled
                ')
                ->first()
                ->toArray();
        });

        // Ensure defaults if null (though sum returns 0 usually, but logic safety)
        $stats['total'] = (int) ($stats['total'] ?? 0);
        
        return ResponseHelper::success($stats);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search', 'idSiswa', 'idInvoice', 'idTagihan', 'idMasterPembayaran', 'jenisPembayaran', 'status', 'isVerified', 'isActive', 'start_date', 'end_date']);
            $query = LamtimPembayaran::query()
                ->with(['siswa', 'invoice', 'tagihan', 'masterPembayaran'])
                ->where('isActive', 1);

            if (isset($filters['idSiswa'])) {
                $query->where('idSiswa', $filters['idSiswa']);
            }
            if (isset($filters['idInvoice'])) {
                $query->where('idInvoice', $filters['idInvoice']);
            }
            if (isset($filters['idMasterPembayaran'])) {
                $query->where('idMasterPembayaran', $filters['idMasterPembayaran']);
            }
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (isset($filters['isVerified'])) {
                $query->where('isVerified', $filters['isVerified']);
            }

            // Filter by date range
            if (isset($filters['start_date'])) {
                $query->where('tanggalBayar', '>=', $filters['start_date']);
            }
            if (isset($filters['end_date'])) {
                $query->where('tanggalBayar', '<=', $filters['end_date']);
            }

            // Filter by jenis pembayaran (dari master pembayaran)
            if (isset($filters['jenisPembayaran'])) {
                $query->whereHas('masterPembayaran', function($q) use ($filters) {
                    $q->where('jenisPembayaran', $filters['jenisPembayaran']);
                });
            }

            $pembayarans = $query->orderBy('tanggalBayar', 'desc')->paginate($request->get('per_page', 15));
            return ResponseHelper::success(PembayaranResource::collection($pembayarans));
        }

        // For DataTables (web)
        if ($request->ajax()) {
            return $this->datatable($request);
        }

        return view('pembayaran.index');
    }

    /**
     * DataTables server-side processing
     */
    public function datatable(Request $request)
    {
        $query = LamtimPembayaran::query()
            ->with(['siswa', 'invoice', 'tagihan', 'masterPembayaran'])
            ->select('lamtim_pembayarans.*')
            ->orderBy('tanggalBayar', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('isVerified')) {
            $query->where('isVerified', $request->isVerified);
        }
        if ($request->filled('startDate')) {
            $query->whereDate('tanggalBayar', '>=', $request->startDate);
        }
        if ($request->filled('endDate')) {
            $query->whereDate('tanggalBayar', '<=', $request->endDate);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('siswa_nama', function($row) {
                return $row->siswa->nama ?? '-';
            })
            ->addColumn('nominal_formatted', function($row) {
                return \App\Helpers\FormatHelper::currency($row->nominalBayar);
            })
            ->addColumn('tanggal_formatted', function($row) {
                return \App\Helpers\FormatHelper::date($row->tanggalBayar);
            })
            ->addColumn('status_badge', function($row) {
                return \App\Helpers\FormatHelper::statusBadge($row->status, 'pembayaran');
            })
            ->addColumn('verifikasi_badge', function($row) {
                return \App\Helpers\FormatHelper::statusBadge($row->isVerified ? 1 : 0, 'verifikasi');
            })
            ->addColumn('action', function($row) {
                $buttons = '<button data-action="detail" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">Detail</button>';
                
                if (!$row->isVerified && $row->status == 1) {
                    $buttons .= '<button data-action="verify" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">Verifikasi</button>';
                }
                
                if ($row->status == 1 && !$row->isVerified) {
                    $buttons .= '<button data-action="cancel" data-id="' . $row->id . '" class="px-2 py-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Batal</button>';
                }
                
                return '<div class="flex gap-1">' . $buttons . '</div>';
            })
            ->rawColumns(['status_badge', 'verifikasi_badge', 'action'])
            ->make(true);
    }

    /**
     * Proses pembayaran - Buat invoice + input pembayaran
     */
    public function prosesPembayaran(ProsesPembayaranRequest $request)
    {
        try {
            $result = $this->service->prosesPembayaran(
                $request->idSiswa,
                $request->idTagihan,
                $request->nominalBayar,
                $request->metodeBayar,
                $request->buktiBayar,
                $request->keterangan
            );

            return ResponseHelper::success([
                'invoice' => new \App\Http\Resources\InvoiceResource($result['invoice']),
                'pembayaran' => new PembayaranResource($result['pembayaran']),
                'tagihan' => new \App\Http\Resources\TagihanResource($result['tagihan']),
            ], 'Pembayaran berhasil diproses. Invoice telah dibuat.');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Verifikasi pembayaran
     */
    public function verify(Request $request, string $id)
    {
        try {
            $pembayaran = $this->service->verifyPembayaran($id);
            
            return ResponseHelper::success(
                new PembayaranResource($pembayaran),
                'Pembayaran berhasil diverifikasi'
            );
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Batalkan pembayaran
     */
    public function cancel(Request $request, string $id)
    {
        try {
            $this->service->cancelPembayaran($id, $request->alasan);
            
            return ResponseHelper::success(null, 'Pembayaran berhasil dibatalkan');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $pembayaran = LamtimPembayaran::with(['siswa', 'invoice', 'tagihan', 'masterPembayaran'])->find($id);
        
        if (!$pembayaran) {
            return ResponseHelper::notFound('Pembayaran tidak ditemukan');
        }

        return ResponseHelper::success(new PembayaranResource($pembayaran));
    }
}
