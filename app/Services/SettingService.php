<?php

namespace App\Services;

use App\Models\LamtimSetting;
use App\Models\LamtimSekolah;
use App\Models\LamtimTahunAjaran;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    public function __construct(
        protected PublicDataService $publicDataService
    ) {}

    /**
     * Get cached settings with related data.
     */
    public function getSettings(): ?LamtimSetting
    {
        $settings = Cache::rememberForever('app_settings', function () {
            return LamtimSetting::first();
        });

        if ($settings) {
            $settings->label_jurusan = env('LABEL_JURUSAN', 'Jurusan');
            $settings->label_nip = env('LABEL_NIP', 'NIP');
            $settings->sekolah = LamtimSekolah::first();
            $settings->tahun_ajaran = LamtimTahunAjaran::active()->first();
        }

        return $settings;
    }

    /**
     * Get public settings (no auth required).
     */
    public function getPublicSettings(): array
    {
        $settings = Cache::rememberForever('app_settings', function () {
            return LamtimSetting::first();
        });

        return [
            'logo_aplikasi' => $settings->logo_aplikasi ?? null,
            'nama_aplikasi' => $settings->nama_aplikasi ?? 'Sistem Manajemen SPP',
        ];
    }

    /**
     * Find setting by ID.
     */
    public function find(string $id): ?LamtimSetting
    {
        return LamtimSetting::find($id);
    }

    /**
     * Save settings (create or update).
     */
    public function save(array $data, ?UploadedFile $logoFile = null): LamtimSetting
    {
        try {
            $settings = LamtimSetting::first();

            if ($logoFile) {
                // Delete old logo if updating
                if ($settings && $settings->logo_aplikasi) {
                    Storage::disk('public')->delete($settings->logo_aplikasi);
                }
                $data['logo_aplikasi'] = $logoFile->store('settings', 'public');
            }

            if ($settings) {
                $settings->update($data);
            } else {
                $settings = LamtimSetting::create($data);
            }

            $this->clearCache();



            return $settings;
        } catch (\Exception $e) {
            Log::error('Error saving settings', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update existing setting by ID.
     */
    public function update(string $id, array $data, ?UploadedFile $logoFile = null): LamtimSetting
    {
        $settings = LamtimSetting::find($id);
        if (!$settings) {
            throw new \Exception('Settings tidak ditemukan');
        }

        if ($logoFile) {
            if ($settings->logo_aplikasi) {
                Storage::disk('public')->delete($settings->logo_aplikasi);
            }
            $data['logo_aplikasi'] = $logoFile->store('settings', 'public');
        }

        $settings->update($data);
        $this->clearCache();



        return $settings;
    }

    /**
     * Delete setting by ID.
     */
    public function delete(string $id): void
    {
        $settings = LamtimSetting::find($id);
        if (!$settings) {
            throw new \Exception('Settings tidak ditemukan');
        }

        if ($settings->logo_aplikasi) {
            Storage::disk('public')->delete($settings->logo_aplikasi);
        }

        $settings->delete();
        $this->clearCache();


    }

    /**
     * Clear all setting-related caches.
     */
    protected function clearCache(): void
    {
        Cache::forget('app_settings');
        $this->publicDataService->clearCache();
    }
}
