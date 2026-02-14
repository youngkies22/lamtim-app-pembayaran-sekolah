<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\ActiveStudentAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActiveStudentAnalysisController extends Controller
{
    public function __construct(
        private readonly ActiveStudentAnalysisService $service
    ) {}

    /**
     * Get active student monthly analysis data.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $idSekolah = $request->get('idSekolah');
            $refresh = $request->boolean('refresh');

            $data = $this->service->getAnalysisData($idSekolah, $refresh);

            return ResponseHelper::success($data);
        } catch (\Exception $e) {
            return ResponseHelper::error(
                'Gagal mengambil data analisa siswa aktif: ' . $e->getMessage(),
                500
            );
        }
    }
}
