<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Disable query logging in production to save memory
        if ($this->app->environment('production')) {
            \DB::connection()->disableQueryLog();
        }

        // Test Redis connectivity and fall back to file cache if unavailable
        $this->setupCacheFallback();
    }

    /**
     * Test Redis connectivity at boot time and fallback to file cache if needed
     */
    private function setupCacheFallback(): void
    {
        // Only test Redis if it's configured as the default cache driver
        $cacheDriver = config('cache.default');
        
        if ($cacheDriver !== 'redis') {
            return;
        }

        // Get REDIS_URL to check if it's actually configured
        $redisUrl = env('REDIS_URL');
        
        if (empty($redisUrl) || $redisUrl === 'rediss://your-upstash-url-here' || 
            strpos($redisUrl, 'placeholder') !== false) {
            // Redis not properly configured, fallback to file cache
            config(['cache.default' => 'file']);
            logger('⚠️ Redis not configured properly, switched to file cache');
            return;
        }

        // Test Redis connection
        try {
            // Use a short-lived cache operation to test connectivity
            Cache::store('redis')->put('_redis_test', true, 1);
            Cache::store('redis')->forget('_redis_test');
            logger('✓ Redis connection verified');
        } catch (\Throwable $e) {
            // Redis connection failed, fallback to file cache
            logger('⚠️ Redis connection failed, switched to file cache: ' . $e->getMessage());
            config(['cache.default' => 'file']);
            
            // Also update session and queue drivers if they were set to redis
            if (config('session.driver') === 'redis') {
                config(['session.driver' => 'file']);
            }
            if (config('queue.default') === 'redis') {
                config(['queue.default' => 'sync']);
            }
        }
    }
}
