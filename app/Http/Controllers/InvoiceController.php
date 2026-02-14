<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;
use App\Http\Resources\InvoiceResource;
use App\Helpers\ResponseHelper;
use App\Helpers\FormatHelper;
use App\Exports\InvoiceExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $service
    ) {}

    /**
     * Get invoice stats.
     */
    public function stats()
    {
        return ResponseHelper::success($this->service->getStats());
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            $filters = $request->only(['search', 'idSiswa', 'idTagihan', 'idMasterPembayaran', 'status', 'isActive', 'startDate', 'endDate']);
            $invoices = $this->service->getPaginated($filters, $request->get('per_page', 15));

            return ResponseHelper::success(InvoiceResource::collection($invoices));
        }

        if ($request->ajax()) {
            return $this->datatable($request);
        }

        return view('invoice.index');
    }

    /**
     * DataTables server-side processing.
     */
    public function datatable(Request $request)
    {
        $filters = $request->only(['status', 'startDate', 'endDate']);
        $query = $this->service->buildDatatableQuery($filters);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('siswa_nama', fn($row) => $row->siswa->nama ?? '-')
            ->addColumn('rombel_nama', function($row) {
                if ($row->siswa && $row->siswa->currentRombel && $row->siswa->currentRombel->rombel) {
                    $rombel = $row->siswa->currentRombel->rombel;
                    $kelasKode = $rombel->kelas->kode ?? '';
                    return trim(($kelasKode ? "$kelasKode " : "") . ($rombel->nama ?? ''));
                }
                return '-';
            })
            ->addColumn('tanggal_formatted', fn($row) => FormatHelper::date($row->tanggalInvoice))
            ->addColumn('nominal_formatted', fn($row) => FormatHelper::currency($row->nominalInvoice))
            ->addColumn('status_badge', fn($row) => FormatHelper::statusBadge($row->status, 'tagihan'))
            ->addColumn('action', fn($row) => '<button data-action="detail" data-id="' . $row->id . '" class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800">Detail</button>')
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $invoice = $this->service->find($id);

        if (!$invoice) {
            return ResponseHelper::notFound('Invoice tidak ditemukan');
        }

        return ResponseHelper::success(new InvoiceResource($invoice));
    }

    /**
     * Export invoices to Excel.
     */
    public function export(Request $request)
    {
        $filters = $request->only(['status', 'startDate', 'endDate']);
        $context = $this->service->getExportContext();
        $filename = 'invoice_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new InvoiceExport($filters, $context['sekolahNama'], $context['tahunAjaran']),
            $filename
        );
    }
}
