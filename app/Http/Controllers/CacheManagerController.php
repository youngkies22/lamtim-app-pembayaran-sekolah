<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CacheManagerController extends Controller
{
    /**
     * Get current cache status and environment info.
     */
    public function getStatus(): JsonResponse
    {
        $driver = config('cache.default');
        $redisConnected = false;

        if ($driver === 'redis') {
            try {
                $redisConnected = Redis::connection()->ping() ? true : false;
            } catch (\Exception $e) {
                $redisConnected = false;
            }
        }

        $status = [
            'driver' => $driver,
            'redis_connected' => $redisConnected,
            'config_cached' => file_exists(base_path('bootstrap/cache/config.php')),
            'routes_cached' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
            'events_cached' => file_exists(base_path('bootstrap/cache/events.php')),
            'views_cached'  => $this->hasViewCache(),
        ];

        return ResponseHelper::success($status);
    }

    /**
     * Clear the standard Laravel application cache.
     */
    public function clearLaravelCache(): JsonResponse
    {
        try {
            Artisan::call('cache:clear');
            return ResponseHelper::success(null, 'Laravel cache cleared successfully');
        } catch (\Exception $e) {
            Log::error('CacheManager: Failed to clear Laravel cache', ['error' => $e->getMessage()]);
            return ResponseHelper::error('Failed to clear Laravel cache: ' . $e->getMessage());
        }
    }

    /**
     * Flush Redis database if being used as cache.
     */
    public function clearRedisCache(): JsonResponse
    {
        if (config('cache.default') !== 'redis') {
            return ResponseHelper::error('Redis is not the active cache driver');
        }

        try {
            Redis::connection()->flushdb();
            return ResponseHelper::success(null, 'Redis database flushed successfully');
        } catch (\Exception $e) {
            Log::error('CacheManager: Failed to flush Redis', ['error' => $e->getMessage()]);
            return ResponseHelper::error('Failed to flush Redis: ' . $e->getMessage());
        }
    }

    /**
     * Run optimize command to cache config, routes, and events.
     */
    public function optimizeCache(): JsonResponse
    {
        try {
            Artisan::call('optimize');
            return ResponseHelper::success(null, 'Application optimized (Config/Routes/Events cached)');
        } catch (\Exception $e) {
            Log::error('CacheManager: Failed to optimize application', ['error' => $e->getMessage()]);
            return ResponseHelper::error('Failed to optimize: ' . $e->getMessage());
        }
    }

    /**
     * Helper to check if view cache directory has files.
     */
    protected function hasViewCache(): bool
    {
        $path = config('view.compiled');
        if (!$path || !is_dir($path)) return false;
        
        $files = glob($path . '/*.php');
        return count($files) > 0;
    }
}
