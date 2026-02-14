<?php

namespace App\Services;

use App\Repositories\Interfaces\ActiveStudentAnalysisRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ActiveStudentAnalysisService
{
    private const CACHE_TTL = 60 * 60 * 24; // 24 hours

    public function __construct(
        private readonly ActiveStudentAnalysisRepositoryInterface $repository
    ) {}

    /**
     * Get monthly analysis data for active students.
     */
    public function getAnalysisData(?string $idSekolah, bool $refresh = false): array
    {
        $cacheKey = 'active_student_analysis_' . ($idSekolah ?? 'all');

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($idSekolah) {
            $byMonth = $this->repository->getStatsByMonth($idSekolah);

            $summary = [
                'totalTagihan'   => $byMonth->sum('total_tagihan'),
                'totalTerbayar'  => $byMonth->sum('total_terbayar'),
                'totalTunggakan' => $byMonth->sum('total_tunggakan'),
                'totalSiswa'     => $byMonth->max('total_siswa') ?? 0,
            ];

            return [
                'summary' => $summary,
                'byMonth' => $byMonth->toArray(),
            ];
        });
    }
}
