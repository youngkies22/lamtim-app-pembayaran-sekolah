<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * Clear cache for master data lists
     * 
     * @param string|null $type - Type of cache to clear (jurusan, kelas, rombel, agama, all)
     * @return void
     */
    public static function clearMasterDataCache(?string $type = 'all'): void
    {
        if ($type === 'all' || $type === 'jurusan') {
            // Clear all jurusan cache patterns
            Cache::flush(); // For now, we'll flush all. In production, use tags or specific keys
        }
        
        if ($type === 'all' || $type === 'kelas') {
            Cache::forget('kelas_list_' . md5(json_encode([])));
        }
        
        if ($type === 'all' || $type === 'rombel') {
            Cache::forget('rombel_list_' . md5(json_encode([])));
        }
        
        if ($type === 'all' || $type === 'agama') {
            Cache::forget('agama_list_' . md5(json_encode([])));
        }
        
        if ($type === 'all' || $type === 'config') {
            Cache::forget('app_config');
        }
    }

    /**
     * Clear all cache
     * 
     * @return void
     */
    public static function clearAll(): void
    {
        Cache::flush();
    }

    /**
     * Get cache key for master data list
     * 
     * @param string $type - Type of master data (jurusan, kelas, rombel, agama)
     * @param array $filters - Filters applied
     * @return string
     */
    public static function getMasterDataCacheKey(string $type, array $filters = []): string
    {
        return $type . '_list_' . md5(json_encode($filters));
    }

    /**
     * Remember master data with cache
     * 
     * @param string $type - Type of master data
     * @param array $filters - Filters applied
     * @param int $ttl - Time to live in seconds
     * @param callable $callback - Callback to get data if not cached
     * @return mixed
     */
    public static function rememberMasterData(string $type, array $filters, int $ttl, callable $callback)
    {
        $cacheKey = self::getMasterDataCacheKey($type, $filters);
        return Cache::remember($cacheKey, $ttl, $callback);
    }
}
