<?php

namespace App\Http\Controllers;

use App\Services\PublicDataService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    protected $publicDataService;

    public function __construct(PublicDataService $publicDataService)
    {
        $this->publicDataService = $publicDataService;
    }

    /**
     * Get application configuration for frontend
     */
    public function index(Request $request)
    {
        // Cache config for 1 hour
        $config = \Illuminate\Support\Facades\Cache::remember('app_config', 3600, function () {
            return [
                'label_jurusan' => config('app.label_jurusan', 'Jurusan'),
            ];
        });

        return ResponseHelper::success($config);
    }

    /**
     * Get public settings (logo and app name) - no auth required
     */
    public function publicSettings()
    {
        $data = $this->publicDataService->getPublicSettings();
        return ResponseHelper::success($data);
    }

    /**
     * Get public sekolah data (nama sekolah) - no auth required
     */
    public function publicSekolah()
    {
        $data = $this->publicDataService->getPublicSekolah();
        return ResponseHelper::success($data);
    }
}
