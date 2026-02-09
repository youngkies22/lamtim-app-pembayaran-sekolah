<?php

namespace App\Http\Controllers;

use App\Models\LamtimInvoice;
use App\Http\Resources\InvoiceResource;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // API always returns JSON
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search', 'idSiswa', 'idTagihan', 'idMasterPembayaran', 'status', 'isActive']);
            $query = LamtimInvoice::query()
                ->with(['siswa', 'tagihan', 'masterPembayaran'])
                ->where('isActive', 1);

            if (isset($filters['idSiswa'])) {
                $query->where('idSiswa', $filters['idSiswa']);
            }
            if (isset($filters['idTagihan'])) {
                $query->where('idTagihan', $filters['idTagihan']);
            }
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $invoices = $query->paginate($request->get('per_page', 15));
            return ResponseHelper::success(InvoiceResource::collection($invoices));
        }

        // For DataTables (web)
        if ($request->ajax()) {
            return $this->datatable($request);
        }

        return view('invoice.index');
    }

    /**
     * DataTables server-side processing
     */
    public function datatable(Request $request)
    {
        $query = LamtimInvoice::query()
            ->with(['siswa', 'tagihan', 'masterPembayaran'])
            ->select('lamtim_invoices.*')
            ->orderBy('tanggalInvoice', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('startDate')) {
            $query->whereDate('tanggalInvoice', '>=', $request->startDate);
        }
        if ($request->filled('endDate')) {
            $query->whereDate('tanggalInvoice', '<=', $request->endDate);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('siswa_nama', function($row) {
                return $row->siswa->nama ?? '-';
            })
            ->addColumn('tanggal_formatted', function($row) {
                return \App\Helpers\FormatHelper::date($row->tanggalInvoice);
            })
            ->addColumn('nominal_formatted', function($row) {
                return \App\Helpers\FormatHelper::currency($row->nominalInvoice);
            })
            ->addColumn('status_badge', function($row) {
                return \App\Helpers\FormatHelper::statusBadge($row->status, 'tagihan');
            })
            ->addColumn('action', function($row) {
                return '<button data-action="detail" data-id="' . $row->id . '" class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800">Detail</button>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $invoice = LamtimInvoice::with(['siswa', 'tagihan', 'masterPembayaran', 'pembayarans'])->find($id);
        
        if (!$invoice) {
            return ResponseHelper::notFound('Invoice tidak ditemukan');
        }

        return ResponseHelper::success(new InvoiceResource($invoice));
    }
}
