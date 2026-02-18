<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProsesPembayaranRequest;
use App\Http\Resources\PembayaranResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\TagihanResource;
use App\Services\PembayaranService;
use App\Helpers\ResponseHelper;
use App\Helpers\FormatHelper;
use App\Exports\PembayaranExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Closing;

class PembayaranController extends Controller
{
    public function __construct(
        protected PembayaranService $service
    ) {}

    /**
     * Get pembayaran stats.
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
            $filters = $request->only(['search', 'idSiswa', 'idInvoice', 'idTagihan', 'idMasterPembayaran', 'jenisPembayaran', 'status', 'isVerified', 'isActive', 'start_date', 'end_date']);
            $pembayarans = $this->service->getPaginated($filters, $request->get('per_page', 15));

            return ResponseHelper::success(PembayaranResource::collection($pembayarans));
        }

        if ($request->ajax()) {
            return $this->datatable($request);
        }

        return view('pembayaran.index');
    }

    /**
     * DataTables server-side processing.
     */
    /**
     * DataTables server-side processing.
     */
    public function datatable(Request $request)
    {
        $filters = $request->only(['status', 'isVerified', 'startDate', 'endDate', 'idRombel']);
        return $this->service->getDatatable($filters);
    }

    /**
     * Proses pembayaran - Buat invoice + input pembayaran.
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
                'invoice' => new InvoiceResource($result['invoice']),
                'pembayaran' => new PembayaranResource($result['pembayaran']),
                'tagihan' => new TagihanResource($result['tagihan']),
            ], 'Pembayaran berhasil diproses. Invoice telah dibuat.');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Verifikasi pembayaran.
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
     * Batalkan pembayaran.
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
        $pembayaran = $this->service->find($id);

        if (!$pembayaran) {
            return ResponseHelper::notFound('Pembayaran tidak ditemukan');
        }

        return ResponseHelper::success(new PembayaranResource($pembayaran));
    }

    /**
     * Export pembayaran to Excel.
     */
    public function export(Request $request)
    {
        $filters = $request->only(['status', 'isVerified', 'startDate', 'endDate', 'jenisPembayaran']);
        $context = $this->service->getExportContext();
        $filename = 'pembayaran_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new PembayaranExport($filters, $context['sekolahNama'], $context['tahunAjaran'], $context['logo'] ?? null),
            $filename
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->service->destroy($id);

            return ResponseHelper::success(null, 'Pembayaran berhasil dihapus');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Retry sync for a specific pembayaran.
     */
    public function retrySync(string $id)
    {
        try {
            $pembayaran = $this->service->find($id);
            if (!$pembayaran) {
                return ResponseHelper::notFound('Pembayaran tidak ditemukan');
            }

            if (!\App\Services\SettingService::isJobEnabled('job_push_academic_enabled')) {
                return ResponseHelper::error('Push Academic Job tidak aktif. Aktifkan di Pengaturan.', 400);
            }

            \App\Jobs\PushAcademicDataJob::dispatch($pembayaran);

            return ResponseHelper::success(null, 'Sync job dispatched');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
