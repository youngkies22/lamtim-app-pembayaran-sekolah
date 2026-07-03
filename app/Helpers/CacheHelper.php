<?php

namespace App\Helpers;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Utilitas cache berbasis tag.
 *
 * Semua cache aplikasi wajib lewat helper ini agar Observer bisa
 * meng-invalidasi secara presisi per-tag (Redis/array store).
 * Pada store yang tidak mendukung tag (file/database), helper ini
 * turun ke cache biasa per-key sehingga aplikasi tetap berjalan.
 *
 * Konvensi tag:
 * - Tag list global : 'siswa', 'tagihan', 'invoice', 'pembayaran', ...
 * - Tag per-record  : 'siswa:{uuid}', 'invoice:{uuid}'
 * - Statistik       : 'dashboard'
 */
class CacheHelper
{
    /**
     * Remember dengan tag. Fallback ke remember biasa jika store tidak mendukung tag.
     *
     * @param array<int, string>|string $tags
     */
    public static function remember(array|string $tags, string $key, int $ttl, Closure $callback): mixed
    {
        if (self::supportsTags()) {
            return Cache::tags((array) $tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Simpan nilai dengan tag.
     *
     * @param array<int, string>|string $tags
     */
    public static function put(array|string $tags, string $key, mixed $value, int $ttl): void
    {
        if (self::supportsTags()) {
            Cache::tags((array) $tags)->put($key, $value, $ttl);
            return;
        }

        Cache::put($key, $value, $ttl);
    }

    /**
     * Ambil nilai dari cache bertag.
     *
     * @param array<int, string>|string $tags
     */
    public static function get(array|string $tags, string $key, mixed $default = null): mixed
    {
        if (self::supportsTags()) {
            return Cache::tags((array) $tags)->get($key, $default);
        }

        return Cache::get($key, $default);
    }

    /**
     * Hapus satu key dari cache bertag.
     *
     * @param array<int, string>|string $tags
     */
    public static function forget(array|string $tags, string $key): void
    {
        if (self::supportsTags()) {
            Cache::tags((array) $tags)->forget($key);
            return;
        }

        Cache::forget($key);
    }

    /**
     * Hapus seluruh cache yang memiliki salah satu tag.
     * Setiap tag di-flush terpisah agar entri yang hanya punya satu tag ikut terhapus.
     *
     * @param array<int, string>|string $tags
     */
    public static function flushTags(array|string $tags): void
    {
        if (!self::supportsTags()) {
            // Store tanpa dukungan tag: tidak ada cara aman menghapus per-tag.
            // Entri akan kedaluwarsa lewat TTL masing-masing.
            return;
        }

        try {
            foreach ((array) $tags as $tag) {
                Cache::tags([$tag])->flush();
            }
        } catch (\Throwable $e) {
            Log::warning('CacheHelper: gagal flush tag', [
                'tags' => (array) $tags,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cache key untuk kombinasi filter (mis. datatable/select).
     */
    public static function keyFor(string $prefix, array $filters = []): string
    {
        return $prefix . ':' . md5(json_encode($filters));
    }

    /**
     * Apakah cache store aktif mendukung tag (Redis, Memcached, Array).
     */
    public static function supportsTags(): bool
    {
        try {
            return Cache::getStore() instanceof \Illuminate\Cache\TaggableStore;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Hapus semua cache. Hanya untuk keperluan administratif (menu Cache Manager),
     * jangan dipakai untuk invalidasi rutin — gunakan flushTags().
     */
    public static function clearAll(): void
    {
        Cache::flush();
    }
}
