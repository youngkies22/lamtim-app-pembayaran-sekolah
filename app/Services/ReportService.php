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
     * Get relevant master pembayarans for a specific rombel.
     * Only returns masters that have active tagihans in the given rombel,
     * AND where the master's class level matches the rombel (or is global).
     */
    private function getRelevantMasters(?string $idRombel)
    {
        $query = LamtimMasterPembayaran::where('isActive', 1)
            ->whereNotNull('slug');

        if ($idRombel) {
            $rombel = LamtimRombel::find($idRombel);

            // Get all students currently in this rombel
            $studentIds = \App\Models\LamtimSiswaRombel::where('idRombel', $idRombel)
                ->pluck('idSiswa')
                ->toArray();

            // 1. Ensure masters exist in tagihans for THESE students
            // 2. Only show tagihans that match this rombel's class level
            $idKelas = $rombel?->idKelas;
            $query->whereHas('tagihans', function ($q) use ($studentIds, $idKelas) {
                $q->whereIn('idSiswa', $studentIds)
                    ->where('isActive', 1);
                if ($idKelas) {
                    $q->where('idKelas', $idKelas);
                }
            });
        }

        return $query->orderBy('nama')->get();
    }

    /**
     * Get rombel report headers (master pembayaran grouped by slug).
     */
    public function getRombelReportHeaders(?string $idRombel = null)
    {
        $masters = $this->getRelevantMasters($idRombel);

        return $masters->groupBy('slug')->map(function ($group) {
            return [
                'slug' => $group->first()->slug,
                'nama' => $group->first()->nama,
                'original_names' => $group->pluck('nama')->toArray(),
            ];
        })->values();
    }

    /**
     * Build the rombel report query with dynamic slug columns.
     */
    public function buildRombelReportQuery(string $idRombel)
    {
        // Only get masters that are relevant to this Rombel
        $masters = $this->getRelevantMasters($idRombel);
        $uniqueSlugs = $masters->pluck('slug')->unique();

        // Get the class level of this rombel to filter tagihans
        $rombel = LamtimRombel::find($idRombel);
        $idKelas = $rombel?->idKelas;

        $query = LamtimSiswa::query()
            ->join('lamtim_siswa_rombels', 'lamtim_siswas.id', '=', 'lamtim_siswa_rombels.idSiswa')
            ->leftJoin('lamtim_rombels', 'lamtim_siswa_rombels.idRombel', '=', 'lamtim_rombels.id')
            ->leftJoin('lamtim_kelas', 'lamtim_rombels.idKelas', '=', 'lamtim_kelas.id')
            ->leftJoin('lamtim_tagihans', function ($join) use ($idKelas) {
                $join->on('lamtim_siswas.id', '=', 'lamtim_tagihans.idSiswa')
                    ->where('lamtim_tagihans.isActive', '=', 1);
                // Filter tagihans by class level so only tagihans matching this rombel's kelas are shown
                if ($idKelas) {
                    $join->where('lamtim_tagihans.idKelas', '=', $idKelas);
                }
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

        // Add dynamic columns only for the relevant masters
        foreach ($uniqueSlugs as $slug) {
            $masterIds = $masters->where('slug', $slug)->pluck('id')->toArray();
            
            // This shouldn't happen given getRelevantMasters logic, but safe to keep
            if (empty($masterIds)) { 
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
        $rombel = LamtimRombel::find($idRombel);
        $idKelas = $rombel?->idKelas;

        // Get students in this rombel
        $studentIds = \App\Models\LamtimSiswaRombel::where('idRombel', $idRombel)
            ->pluck('idSiswa');

        $query = LamtimTagihan::query()
            ->where('isActive', 1)
            ->whereIn('idSiswa', $studentIds);

        // Filter by class level to avoid showing tagihans from other kelas
        if ($idKelas) {
            $query->where('idKelas', $idKelas);
        }

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
        $rombel = $idRombel ? LamtimRombel::with('kelas')->find($idRombel) : null;

        $rombelNama = 'Semua Rombel';
        if ($rombel) {
            $kelasKode = $rombel->kelas->kode ?? '';
            $rombelNama = trim($kelasKode . ' ' . $rombel->nama);
        }

        return [
            'sekolahNama' => $sekolah->nama ?? 'Sekolah',
            'rombelNama' => $rombelNama,
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
