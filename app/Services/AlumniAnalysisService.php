<?php

namespace App\Services;

use App\Repositories\Interfaces\AlumniAnalysisRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class AlumniAnalysisService
{
    private const CACHE_TTL = 60 * 60 * 24; // 24 hours

    public function __construct(
        private readonly AlumniAnalysisRepositoryInterface $repository
    ) {}

    /**
     * Get available graduation years for filter dropdown.
     */
    public function getAvailableYears(?string $idSekolah = null): array
    {
        $cacheKey = 'alumni_analysis_years_' . ($idSekolah ?? 'all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($idSekolah) {
            return $this->repository->getAvailableYears($idSekolah)->toArray();
        });
    }

    /**
     * Get full analysis data: summary + byYear breakdown.
     */
    public function getAnalysisData(?string $tahunAngkatan, ?string $idSekolah, bool $refresh = false): array
    {
        $statsCacheKey = $this->buildStatsCacheKey($idSekolah, $tahunAngkatan);
        $yearsCacheKey = 'alumni_analysis_years_' . ($idSekolah ?? 'all');

        if ($refresh) {
            Cache::forget($statsCacheKey);
            Cache::forget($yearsCacheKey);
        }

        $availableYears = $this->getAvailableYears($idSekolah);

        $data = Cache::remember($statsCacheKey, self::CACHE_TTL, function () use ($tahunAngkatan, $idSekolah) {
            $byYear = $this->repository->getStatsByYear($tahunAngkatan, $idSekolah);

            $summary = [
                'totalTagihan'   => $byYear->sum('total_tagihan'),
                'totalTerbayar'  => $byYear->sum('total_terbayar'),
                'totalTunggakan' => $byYear->sum('total_tunggakan'),
                'totalSiswa'     => $byYear->sum('total_siswa'),
            ];

            return [
                'summary' => $summary,
                'byYear'  => $byYear->toArray(),
            ];
        });

        return [
            'summary'        => $data['summary'],
            'byYear'         => $data['byYear'],
            'availableYears' => $availableYears,
        ];
    }

    /**
     * Get export data from cache (must be loaded first via getAnalysisData).
     */
    public function getExportData(?string $tahunAngkatan, ?string $idSekolah): ?array
    {
        $cacheKey = $this->buildStatsCacheKey($idSekolah, $tahunAngkatan);

        return Cache::get($cacheKey);
    }

    /**
     * Build consistent cache key for stats data.
     */
    private function buildStatsCacheKey(?string $idSekolah, ?string $tahunAngkatan): string
    {
        return 'alumni_analysis_stats_' . ($idSekolah ?? 'all') . '_' . ($tahunAngkatan ?? 'all');
    }
}
