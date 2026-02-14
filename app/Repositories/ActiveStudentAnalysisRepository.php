<?php

namespace App\Repositories;

use App\Models\LamtimTagihan;
use App\Repositories\Interfaces\ActiveStudentAnalysisRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ActiveStudentAnalysisRepository implements ActiveStudentAnalysisRepositoryInterface
{
    /**
     * Get monthly aggregated stats for active (non-alumni) students.
     * Uses single-query aggregation with JOIN + GROUP BY to prevent N+1.
     */
    public function getStatsByMonth(?string $idSekolah = null): Collection
    {
        $query = LamtimTagihan::query()
            ->join('lamtim_siswas', 'lamtim_tagihans.idSiswa', '=', 'lamtim_siswas.id')
            ->where('lamtim_siswas.isAlumni', 0)
            ->where('lamtim_siswas.isActive', 1)
            ->where('lamtim_tagihans.isActive', 1);

        if ($idSekolah && $idSekolah !== 'all') {
            $query->where('lamtim_tagihans.idSekolah', $idSekolah);
        }

        return $query->select(
            'lamtim_tagihans.bulanKe',
            'lamtim_tagihans.namaBulan',
            DB::raw('COUNT(DISTINCT "lamtim_siswas"."id") as total_siswa'),
            DB::raw('SUM("lamtim_tagihans"."nominalTagihan" - "lamtim_tagihans"."nominalPotongan") as total_tagihan'),
            DB::raw('SUM("lamtim_tagihans"."totalSudahBayar") as total_terbayar'),
            DB::raw('SUM("lamtim_tagihans"."totalSisa") as total_tunggakan')
        )
            ->groupBy('lamtim_tagihans.bulanKe', 'lamtim_tagihans.namaBulan')
            ->orderBy('lamtim_tagihans.bulanKe', 'asc')
            ->get();
    }
}
