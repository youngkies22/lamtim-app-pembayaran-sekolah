<?php

namespace App\Services;

use App\Helpers\CacheHelper;
use App\Models\LamtimPembayaran;
use App\Models\LamtimSiswa;
use App\Models\LamtimTagihan;
use Illuminate\Database\Eloquent\Collection;

class DashboardService
{
    /**
     * Statistik dashboard.
     *
     * Seluruh angka di-cache 5 menit dengan tag 'dashboard' dan
     * di-invalidasi otomatis oleh MasterDataObserver saat data
     * siswa/tagihan/pembayaran berubah.
     */
    public function getStats(): array
    {
        return CacheHelper::remember(['dashboard'], 'dashboard_stats', 300, function () {
            // Statistik siswa — satu query agregat, bukan tiga count terpisah
            $siswaStats = LamtimSiswa::query()
                ->selectRaw('
                    COUNT(CASE WHEN "isActive" = 1 AND "isAlumni" = 0 THEN 1 END) as total_siswa,
                    COUNT(CASE WHEN "isAlumni" = 1 THEN 1 END) as total_alumni,
                    COUNT(CASE WHEN "isActive" = 2 THEN 1 END) as siswa_off
                ')
                ->first();

            // Statistik pembayaran — satu query agregat untuk total & bulan berjalan
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            $pembayaranStats = LamtimPembayaran::query()
                ->where('status', 1)
                ->selectRaw('
                    COALESCE(SUM("nominalBayar"), 0) as total_pembayaran,
                    COUNT(*) as jumlah_transaksi,
                    COALESCE(SUM(CASE WHEN "tanggalBayar" BETWEEN ? AND ? THEN "nominalBayar" ELSE 0 END), 0) as pembayaran_bulan_ini,
                    COUNT(CASE WHEN "tanggalBayar" BETWEEN ? AND ? THEN 1 END) as transaksi_bulan_ini
                ', [$startOfMonth, $endOfMonth, $startOfMonth, $endOfMonth])
                ->first();

            // Statistik tagihan belum lunas — satu query agregat
            $tagihanStats = LamtimTagihan::query()
                ->where('status', 0)
                ->selectRaw('COUNT(*) as jumlah, COALESCE(SUM("nominalTagihan"), 0) as nominal')
                ->first();

            return [
                'totalSiswa' => (int) $siswaStats->total_siswa,
                'totalAlumni' => (int) $siswaStats->total_alumni,
                'totalSiswaOff' => (int) $siswaStats->siswa_off,
                'totalPembayaran' => (float) $pembayaranStats->total_pembayaran,
                'jumlahTransaksi' => (int) $pembayaranStats->jumlah_transaksi,
                'tagihanBelumLunas' => (int) $tagihanStats->jumlah,
                'nominalBelumLunas' => (float) $tagihanStats->nominal,
                'pembayaranBulanIni' => (float) $pembayaranStats->pembayaran_bulan_ini,
                'transaksiBulanIni' => (int) $pembayaranStats->transaksi_bulan_ini,
            ];
        });
    }

    /**
     * Pembayaran terbaru untuk dashboard (eager loaded, anti N+1).
     */
    public function getRecentPayments(int $limit = 5): Collection
    {
        return LamtimPembayaran::query()
            ->with(['siswa:id,nis,nama', 'masterPembayaran:id,kode,nama'])
            ->orderBy('tanggalBayar', 'desc')
            ->limit($limit)
            ->get();
    }
}
