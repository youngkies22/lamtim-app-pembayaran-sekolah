<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface AlumniAnalysisRepositoryInterface
{
    /**
     * Get distinct available graduation years for alumni.
     */
    public function getAvailableYears(?string $idSekolah = null): Collection;

    /**
     * Get aggregated financial stats grouped by tahunAngkatan.
     *
     * Returns collection of objects with:
     * - tahunAngkatan, total_siswa, total_tagihan, total_terbayar, total_tunggakan
     */
    public function getStatsByYear(?string $tahunAngkatan = null, ?string $idSekolah = null): Collection;
}
