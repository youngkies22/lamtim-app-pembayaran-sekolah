<?php

namespace App\Repositories;

use App\Models\LamtimSiswa;
use App\Models\LamtimTagihan;
use App\Repositories\Interfaces\AlumniAnalysisRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AlumniAnalysisRepository implements AlumniAnalysisRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAvailableYears(?string $idSekolah = null): Collection
    {
        $query = LamtimSiswa::query()
            ->where('isAlumni', 1)
            ->whereNotNull('tahunAngkatan');

        if ($idSekolah && $idSekolah !== 'all') {
            $query->whereExists(function ($sub) use ($idSekolah) {
                $sub->select(DB::raw(1))
                    ->from('lamtim_tagihans')
                    ->whereColumn('lamtim_tagihans.idSiswa', 'lamtim_siswas.id')
                    ->where('lamtim_tagihans.idSekolah', $idSekolah);
            });
        }

        return $query->distinct()
            ->orderBy('tahunAngkatan', 'desc')
            ->pluck('tahunAngkatan');
    }

    /**
     * {@inheritDoc}
     *
     * Single aggregated query — no N+1. Uses JOIN + GROUP BY
     * to compute all financial metrics in one pass.
     */
    public function getStatsByYear(?string $tahunAngkatan = null, ?string $idSekolah = null): Collection
    {
        $query = LamtimTagihan::query()
            ->join('lamtim_siswas', 'lamtim_tagihans.idSiswa', '=', 'lamtim_siswas.id')
            ->where('lamtim_siswas.isAlumni', 1)
            ->where('lamtim_tagihans.isActive', 1);

        if ($idSekolah && $idSekolah !== 'all') {
            $query->where('lamtim_tagihans.idSekolah', $idSekolah);
        }

        if ($tahunAngkatan) {
            $query->where('lamtim_siswas.tahunAngkatan', $tahunAngkatan);
        }

        return $query->select(
            'lamtim_siswas.tahunAngkatan',
            DB::raw('COUNT(DISTINCT "lamtim_siswas"."id") as total_siswa'),
            DB::raw('SUM("lamtim_tagihans"."nominalTagihan" - "lamtim_tagihans"."nominalPotongan") as total_tagihan'),
            DB::raw('SUM("lamtim_tagihans"."totalSudahBayar") as total_terbayar'),
            DB::raw('SUM("lamtim_tagihans"."totalSisa") as total_tunggakan')
        )
            ->groupBy('lamtim_siswas.tahunAngkatan')
            ->orderBy('lamtim_siswas.tahunAngkatan', 'desc')
            ->get();
    }
}
