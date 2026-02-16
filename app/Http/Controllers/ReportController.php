<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Exports\RombelReportExport;
use App\Exports\SiswaReportExport;
use App\Helpers\ResponseHelper;
use App\Helpers\FormatHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $service
    ) {}

    /**
     * Get rombel report headers (dynamic columns).
     */
    public function rombelReportHeaders(Request $request)
    {
        return ResponseHelper::success($this->service->getRombelReportHeaders($request->idRombel));
    }

    /**
     * Get rombel report data for DataTables.
     */
    public function rombelReport(Request $request)
    {
        try {
            $idRombel = $request->idRombel;

            if (empty($idRombel)) {
                return datatables()->of(collect([]))->make(true);
            }

            $query = $this->service->buildRombelReportQuery($idRombel);



            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('nominal_formatted', fn($row) => FormatHelper::currency($row->total_nominal))
                ->addColumn('terbayar_formatted', fn($row) => FormatHelper::currency($row->total_terbayar))
                ->addColumn('sisa_formatted', fn($row) => FormatHelper::currency($row->total_sisa))
                ->addColumn('status_label', function ($row) {
                    if ($row->total_nominal <= 0) return 'Belum Ada Tagihan';
                    if ($row->total_sisa <= 0) return 'Lunas';
                    if ($row->total_terbayar > 0) return 'Sebagian';
                    return 'Belum Bayar';
                })
                ->make(true);
        } catch (\Exception $e) {
            Log::error('ReportController@rombelReport error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * Get rombel report stats.
     */
    public function rombelReportStats(Request $request)
    {
        $idRombel = $request->idRombel;

        if (empty($idRombel)) {
            return ResponseHelper::success([
                'totalTagihan' => 0,
                'totalDibayar' => 0,
                'totalSisa' => 0,
            ]);
        }

        $stats = $this->service->getRombelReportStats($idRombel);



        return ResponseHelper::success($stats);
    }

    /**
     * Export rombel report to Excel.
     */
    public function exportRombelReport(Request $request)
    {
        $filters = $request->only(['idRombel', 'idMasterPembayaran', 'search']);
        $context = $this->service->getRombelExportContext($filters['idRombel'] ?? null);
        $fileName = 'Laporan_Tagihan_Rombel_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new RombelReportExport($filters, $context['sekolahNama'], $context['rombelNama'], $context['logo'] ?? null),
            $fileName
        );
    }

    /**
     * Export siswa report to Excel.
     */
    public function exportSiswaReport(Request $request)
    {
        $idSiswa = $request->idSiswa;
        
        if (empty($idSiswa)) {
            return ResponseHelper::error('ID Siswa tidak ditemukan');
        }

        $context = $this->service->getRombelExportContext(null); // Getting school name only
        $siswa = \App\Models\LamtimSiswa::find($idSiswa);
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '_', $siswa->nama ?? 'Siswa');
        $fileName = 'Laporan_Biaya_' . $cleanName . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new SiswaReportExport($idSiswa, $context['sekolahNama'], $context['logo'] ?? null),
            $fileName
        );
    }

    /**
     * Get summary stats for analytics report.
     */
    public function analyticsStats(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'jenisPembayaran']);
        return ResponseHelper::success($this->service->getAnalyticsStats($filters));
    }
}
