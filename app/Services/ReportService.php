<?php

namespace App\Services;

use App\Models\LamtimMasterPembayaran;
use App\Models\LamtimRombel;
use App\Models\LamtimSekolah;
use App\Models\LamtimSiswa;
use App\Models\LamtimTagihan;
use Illuminate\Support\Facades\Log;

class ReportService
{
    /**
     * Get rombel report headers (master pembayaran grouped by slug).
     */
    public function getRombelReportHeaders(?string $idRombel = null)
    {
        $query = LamtimMasterPembayaran::where('isActive', 1)
            ->whereNotNull('slug');

        if ($idRombel) {
            $query->whereHas('tagihans', function ($q) use ($idRombel) {
                $q->where('idRombel', $idRombel)
                    ->where('isActive', 1);
            });
        }

        $masters = $query->select('id', 'nama', 'slug')
            ->orderBy('nama')
            ->get();

        return $masters->groupBy('slug')->map(function ($group) {
            return [
                'slug' => $group->first()->slug,
                'nama' => $group->first()->slug,
                'original_names' => $group->pluck('nama')->toArray(),
            ];
        })->values();
    }

    /**
     * Build the rombel report query with dynamic slug columns.
     */
    public function buildRombelReportQuery(string $idRombel)
    {
        $masters = LamtimMasterPembayaran::where('isActive', 1)
            ->whereNotNull('slug')
            ->get();

        $uniqueSlugs = $masters->pluck('slug')->unique();

        $query = LamtimSiswa::query()
            ->join('lamtim_siswa_rombels', 'lamtim_siswas.id', '=', 'lamtim_siswa_rombels.idSiswa')
            ->leftJoin('lamtim_rombels', 'lamtim_siswa_rombels.idRombel', '=', 'lamtim_rombels.id')
            ->leftJoin('lamtim_kelas', 'lamtim_rombels.idKelas', '=', 'lamtim_kelas.id')
            ->leftJoin('lamtim_tagihans', function ($join) use ($idRombel) {
                $join->on('lamtim_siswas.id', '=', 'lamtim_tagihans.idSiswa')
                    ->where('lamtim_tagihans.idRombel', '=', $idRombel)
                    ->where('lamtim_tagihans.isActive', '=', 1);
            })
            ->select(
                'lamtim_siswas.id',
                'lamtim_siswas.nama as siswa_nama',
                'lamtim_siswas.nis as siswa_nis'
            )
            ->selectRaw('TRIM(CONCAT(COALESCE(lamtim_kelas.kode, \'\'), \' \', COALESCE(lamtim_rombels.nama, \'\'))) as rombel_nama')
            ->selectRaw('COALESCE(SUM("lamtim_tagihans"."nominalTagihan"), 0) as total_nominal')
            ->selectRaw('COALESCE(SUM("lamtim_tagihans"."totalSudahBayar"), 0) as total_terbayar')
            ->selectRaw('COALESCE(SUM("lamtim_tagihans"."totalSisa"), 0) as total_sisa')
            ->where('lamtim_siswa_rombels.idRombel', $idRombel)
            ->where('lamtim_siswas.isActive', 1)
            ->where('lamtim_siswas.isAlumni', 0);

        foreach ($uniqueSlugs as $slug) {
            $masterIds = $masters->where('slug', $slug)->pluck('id')->toArray();
            if (empty($masterIds)) {
                $query->selectRaw("0 as \"{$slug}\"");
                continue;
            }
            $idsString = "'" . implode("','", $masterIds) . "'";
            $query->selectRaw("COALESCE(SUM(CASE WHEN \"lamtim_tagihans\".\"idMasterPembayaran\" IN ($idsString) THEN \"lamtim_tagihans\".\"nominalTagihan\" ELSE 0 END), 0) as \"{$slug}\"");
        }

        $query->groupBy('lamtim_siswas.id', 'lamtim_siswas.nama', 'lamtim_siswas.nis', 'lamtim_rombels.nama', 'lamtim_kelas.kode');

        return $query;
    }

    /**
     * Get rombel report stats (totals).
     */
    public function getRombelReportStats(string $idRombel): array
    {
        $query = LamtimTagihan::query()
            ->where('isActive', 1)
            ->where('idRombel', $idRombel);

        return [
            'totalTagihan' => (float) $query->sum('nominalTagihan'),
            'totalDibayar' => (float) $query->sum('totalSudahBayar'),
            'totalSisa' => (float) $query->sum('totalSisa'),
        ];
    }

    /**
     * Get export context for rombel report.
     */
    public function getRombelExportContext(?string $idRombel): array
    {
        $sekolah = LamtimSekolah::first();
        $rombel = $idRombel ? LamtimRombel::find($idRombel) : null;

        return [
            'sekolahNama' => $sekolah->nama ?? 'Sekolah',
            'rombelNama' => $rombel->nama ?? 'Semua Rombel',
            'logo' => $sekolah->logo ?? null,
        ];
    }

    /**
     * Get analytics stats for general report.
     */
    public function getAnalyticsStats(array $filters): array
    {
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $jenisPembayaran = $filters['jenisPembayaran'] ?? null;

        // Total Pembayaran (Sukses)
        $pembayaranQuery = \App\Models\LamtimPembayaran::where('status', 1)->where('isActive', 1);
        if ($startDate) $pembayaranQuery->whereDate('tanggalBayar', '>=', $startDate);
        if ($endDate) $pembayaranQuery->whereDate('tanggalBayar', '<=', $endDate);
        if ($jenisPembayaran) {
            $pembayaranQuery->whereHas('masterPembayaran', function($q) use ($jenisPembayaran) {
                $q->where('kode', $jenisPembayaran);
            });
        }
        $totalPembayaran = (float) $pembayaranQuery->sum('nominalBayar');
        $jumlahTransaksi = $pembayaranQuery->count();

        return [
            'totalPembayaran' => $totalPembayaran,
            'jumlahTransaksi' => $jumlahTransaksi,
        ];
    }
}
