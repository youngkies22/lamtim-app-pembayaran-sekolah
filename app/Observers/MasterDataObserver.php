<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Database\Eloquent\Model;
use App\Models\LamtimJurusan;
use App\Models\LamtimKelas;
use App\Models\LamtimRombel;
use App\Models\LamtimAgama;
use App\Models\LamtimSiswa;
use App\Models\LamtimSekolah;
use App\Models\LamtimMasterPembayaran;
use App\Models\LamtimPembayaran;

class MasterDataObserver
{
    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Handle the model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Handle the model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        $this->clearCache($model);
    }

    /**
     * Clear cache based on model type
     */
    protected function clearCache(Model $model): void
    {
        $modelName = $this->getModelName($model);
        
        if (!$modelName) {
            return;
        }

        // Clear all cache patterns for this model type using Redis pattern matching
        $this->clearModelCache($modelName);
        
        // Also clear related caches
        $this->clearRelatedCaches($modelName, $model);
    }

    /**
     * Get model name for cache key
     */
    protected function getModelName(Model $model): ?string
    {
        $class = get_class($model);
        
        // Map model classes to cache key names
        $map = [
            LamtimJurusan::class => 'jurusan',
            LamtimKelas::class => 'kelas',
            LamtimRombel::class => 'rombel',
            LamtimAgama::class => 'agama',
            LamtimSiswa::class => 'siswa',
            LamtimSekolah::class => 'sekolah',
            LamtimMasterPembayaran::class => 'master_pembayaran',
            LamtimPembayaran::class => 'pembayaran',
        ];

        return $map[$class] ?? null;
    }

    /**
     * Clear all cache patterns for a model type using Redis pattern matching
     */
    protected function clearModelCache(string $modelName): void
    {
        try {
            // Get Redis connection
            $redis = Redis::connection();
            
            // Pattern untuk datatable cache
            $datatablePattern = config('cache.prefix', 'lamtim_cache') . ':' . $modelName . '_datatable_*';
            $this->clearRedisPattern($redis, $datatablePattern);
            
            // Pattern untuk select cache
            $selectPattern = config('cache.prefix', 'lamtim_cache') . ':' . $modelName . '_select_*';
            $this->clearRedisPattern($redis, $selectPattern);
            
            // Pattern untuk list cache (jika ada)
            $listPattern = config('cache.prefix', 'lamtim_cache') . ':' . $modelName . '_list_*';
            $this->clearRedisPattern($redis, $listPattern);
            
        } catch (\Exception $e) {
            // Fallback to Cache facade if Redis fails
            $this->clearCacheFallback($modelName);
        }
    }

    /**
     * Clear Redis keys by pattern
     */
    protected function clearRedisPattern($redis, string $pattern): void
    {
        try {
            // Get all keys matching pattern
            $keys = $redis->keys($pattern);
            
            if (!empty($keys)) {
                // Remove prefix if exists (Laravel adds prefix automatically)
                $prefix = config('database.redis.options.prefix', '');
                if ($prefix) {
                    $keys = array_map(function($key) use ($prefix) {
                        return str_replace($prefix, '', $key);
                    }, $keys);
                }
                
                // Delete keys
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if Redis pattern matching fails
        }
    }

    /**
     * Fallback cache clearing method
     */
    protected function clearCacheFallback(string $modelName): void
    {
        // Clear common filter combinations
        $commonFilters = [
            [],
            ['search' => ''],
            ['idSekolah' => null],
            ['idJurusan' => null],
            ['idKelas' => null],
            ['isActive' => 1],
            ['isActive' => 0],
        ];

        foreach ($commonFilters as $filters) {
            // Datatable cache
            $datatableKey = $modelName . '_datatable_' . md5(json_encode($filters));
            Cache::forget($datatableKey);
            
            // Select cache
            $selectKey = $modelName . '_select_' . md5(json_encode($filters));
            Cache::forget($selectKey);
            
            // List cache
            $listKey = $modelName . '_list_' . md5(json_encode($filters));
            Cache::forget($listKey);
        }
    }

    /**
     * Clear related caches when a model changes
     */
    protected function clearRelatedCaches(string $modelName, Model $model): void
    {
        // Clear related model caches
        switch ($modelName) {
            case 'rombel':
                // Clear siswa cache when rombel changes
                $this->clearModelCache('siswa');
                break;
            case 'jurusan':
                // Clear rombel and siswa cache when jurusan changes
                $this->clearModelCache('rombel');
                $this->clearModelCache('siswa');
                break;
            case 'kelas':
                // Clear rombel cache when kelas changes
                $this->clearModelCache('rombel');
                break;
            case 'sekolah':
                // Clear all related caches when sekolah changes
                $this->clearModelCache('jurusan');
                $this->clearModelCache('rombel');
                $this->clearModelCache('siswa');
                break;
            case 'pembayaran':
                Cache::forget('pembayaran_stats');
                break;
        }
    }
}
