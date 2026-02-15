<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format currency (Rupiah)
     */
    public static function currency(float $amount, bool $withSymbol = true): string
    {
        $formatted = number_format($amount, 0, ',', '.');
        return $withSymbol ? 'Rp ' . $formatted : $formatted;
    }

    /**
     * Format date
     */
    public static function date($date, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '-';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format($format);
    }

    /**
     * Format datetime
     */
    public static function datetime($datetime, string $format = 'd/m/Y H:i'): string
    {
        if (!$datetime) {
            return '-';
        }

        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }

        return $datetime->format($format);
    }

    /**
     * Format status badge
     */
    public static function statusBadge(mixed $status, string $type = 'tagihan'): string
    {
        $badges = [
            'tagihan' => [
                0 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Belum Lunas</span>',
                1 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Lunas</span>',
                2 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Terlambat</span>',
                3 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Sebagian</span>',
            ],
            'pembayaran' => [
                0 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Batal</span>',
                1 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Valid</span>',
                2 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>',
            ],
            'verifikasi' => [
                0 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Belum Verifikasi</span>',
                1 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Terverifikasi</span>',
            ],
            'siswa' => [
                1 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>',
                2 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Off</span>',
                0 => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Tidak Aktif</span>',
            ],
            'alumni' => [
                'AKTIF' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 uppercase">Alumni (Aktif)</span>',
                'OFF' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 uppercase font-bold">Alumni (Off)</span>',
            ],
            'sync' => [
                'pending' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Sync Pending</span>',
                'success' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Synced</span>',
                'failed'  => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Sync Failed</span>',
            ],
        ];

        return $badges[$type][$status] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' . ($status ?: 'N/A') . '</span>';
    }

    /**
     * Format sync status badge
     */
    public static function syncStatusBadge(?string $status): string
    {
        return self::statusBadge($status ?: 'pending', 'sync');
    }

    /**
     * Format pembayaran status badge
     */
    public static function pembayaranStatusBadge(int $status): string
    {
        return self::statusBadge($status, 'pembayaran');
    }

    /**
     * Format verifikasi status badge
     */
    public static function verifikasiStatusBadge(int $status): string
    {
        return self::statusBadge($status, 'verifikasi');
    }

    /**
     * Format persentase
     */
    public static function percentage(float $value, float $total, int $decimals = 2): string
    {
        if ($total == 0) {
            return '0%';
        }

        $percentage = ($value / $total) * 100;
        return number_format($percentage, $decimals, ',', '.') . '%';
    }

    /**
     * Format file size
     */
    public static function fileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Truncate text
     */
    public static function truncate(string $text, int $length = 50, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . $suffix;
    }
}
