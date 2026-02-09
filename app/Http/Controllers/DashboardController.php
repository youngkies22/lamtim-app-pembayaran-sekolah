<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\LamtimSiswa;
use App\Models\LamtimPembayaran;
use App\Models\LamtimTagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        try {
            // Total siswa
            $totalSiswa = LamtimSiswa::count();
            $siswaAktif = LamtimSiswa::active()->count();

            // Total pembayaran (all time)
            $totalPembayaran = LamtimPembayaran::where('status', 1)->sum('nominalBayar');
            $jumlahTransaksi = LamtimPembayaran::where('status', 1)->count();

            // Tagihan belum lunas
            $tagihanBelumLunas = LamtimTagihan::where('status', 0)->count();
            $nominalBelumLunas = LamtimTagihan::where('status', 0)->sum('nominalTagihan');

            // Pembayaran bulan ini
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
            
            $pembayaranBulanIni = LamtimPembayaran::where('status', 1)
                ->whereBetween('tanggalBayar', [$startOfMonth, $endOfMonth])
                ->sum('nominalBayar');
            
            $transaksiBulanIni = LamtimPembayaran::where('status', 1)
                ->whereBetween('tanggalBayar', [$startOfMonth, $endOfMonth])
                ->count();

            return ResponseHelper::success([
                'totalSiswa' => $totalSiswa,
                'siswaAktif' => $siswaAktif,
                'totalPembayaran' => $totalPembayaran,
                'jumlahTransaksi' => $jumlahTransaksi,
                'tagihanBelumLunas' => $tagihanBelumLunas,
                'nominalBelumLunas' => $nominalBelumLunas,
                'pembayaranBulanIni' => $pembayaranBulanIni,
                'transaksiBulanIni' => $transaksiBulanIni,
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal memuat statistik: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get recent payments for dashboard
     */
    public function recentPayments(Request $request)
    {
        try {
            $payments = LamtimPembayaran::with(['siswa', 'masterPembayaran'])
                ->orderBy('tanggalBayar', 'desc')
                ->limit($request->get('limit', 5))
                ->get();

            return ResponseHelper::success($payments);
        } catch (\Exception $e) {
            return ResponseHelper::error('Gagal memuat data pembayaran: ' . $e->getMessage(), 500);
        }
    }
}
