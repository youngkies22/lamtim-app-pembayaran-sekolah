<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface ActiveStudentAnalysisRepositoryInterface
{
    /**
     * Get monthly aggregated stats for active students.
     */
    public function getStatsByMonth(?string $idSekolah = null): Collection;
}
