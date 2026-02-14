<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\AlumniAnalysisService;
use App\Exports\AlumniAnalysisExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlumniAnalysisController extends Controller
{
    public function __construct(
        private readonly AlumniAnalysisService $service
    ) {}

    /**
     * Get alumni financial analysis data.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tahunAngkatan = $request->get('tahunAngkatan');
            $idSekolah = $request->get('idSekolah');
            $refresh = $request->boolean('refresh');

            // Years-only request for filter dropdown
            if ($request->boolean('only_years')) {
                return ResponseHelper::success([
                    'availableYears' => $this->service->getAvailableYears($idSekolah),
                ]);
            }

            $data = $this->service->getAnalysisData($tahunAngkatan, $idSekolah, $refresh);

            return ResponseHelper::success($data);
        } catch (\Exception $e) {
            return ResponseHelper::error(
                'Gagal mengambil data analisa alumni: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Export alumni financial analysis data to Excel.
     */
    public function export(Request $request)
    {
        try {
            $tahunAngkatan = $request->get('tahunAngkatan');
            $idSekolah = $request->get('idSekolah');

            $data = $this->service->getExportData($tahunAngkatan, $idSekolah);

            if (!$data) {
                return ResponseHelper::error(
                    'Data tidak ditemukan. Silakan tampilkan data terlebih dahulu sebelum export.',
                    404
                );
            }

            $sekolahNama = $this->resolveSchoolName($idSekolah);
            $logo = config('app.school_logo');
            $fileName = 'Laporan_Alumni_' . str_replace(' ', '_', $sekolahNama) . '_' . ($tahunAngkatan ?: 'Semua_Angkatan') . '.xlsx';

            return Excel::download(
                new AlumniAnalysisExport($data, $sekolahNama, $logo, $tahunAngkatan),
                $fileName
            );
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal export data alumni: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Resolve school display name from ID.
     */
    private function resolveSchoolName(?string $idSekolah): string
    {
        if ($idSekolah && $idSekolah !== 'all') {
            $sekolah = \App\Models\LamtimSekolah::find($idSekolah);
            return $sekolah?->nama ?? 'Sekolah';
        }

        return config('app.school_name', 'Seluruh Sekolah');
    }
}
