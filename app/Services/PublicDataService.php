<?php

namespace App\Services;

use App\Models\LamtimSetting;
use App\Models\LamtimSekolah;
use Illuminate\Support\Facades\Cache;

class PublicDataService
{
    /**
     * Get public settings (logo and app name)
     */
    public function getPublicSettings(): array
    {
        return Cache::remember('public_settings', 3600, function () {
            $settings = LamtimSetting::first();
            
            return [
                'logo_aplikasi' => $settings->logo_aplikasi ?? null,
                'nama_aplikasi' => $settings->nama_aplikasi ?? 'Sistem Manajemen SPP',
            ];
        });
    }

    /**
     * Get public sekolah data (nama sekolah for footer)
     */
    public function getPublicSekolah(): array
    {
        return Cache::remember('public_sekolah', 3600, function () {
            $sekolah = LamtimSekolah::first(); // Ambil sekolah pertama
            
            return [
                'nama' => $sekolah->nama ?? 'Sekolah',
                'logo' => $sekolah->logo ?? null,
                'alamat' => $sekolah->alamat ?? null,
                'kota' => $sekolah->kota ?? null,
                'provinsi' => $sekolah->provinsi ?? null,
            ];
        });
    }

    /**
     * Clear cache for public data
     */
    public function clearCache(): void
    {
        Cache::forget('public_settings');
        Cache::forget('public_sekolah');
    }
}
