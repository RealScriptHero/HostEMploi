# Redis & Cache Configuration Guide

## Overview
This document explains how to configure Redis (via Upstash TCP) for your Laravel application deployed on Render.

## Current Configuration

### What's been implemented:
1. **Predis Client**: Added `predis/predis` to `composer.json` (required)
2. **Fallback Mechanism**: AppServiceProvider tests Redis at boot and falls back to file cache if unavailable
3. **Docker Optimization**: Dockerfile installs dependencies correctly and builds caches during build stage
4. **Entrypoint Script**: Smart detection of Redis availability with automatic driver fallback
5. **Environment Variables**: Correctly configured for both local development and production

### Production Cache Chain:
```
Redis (Upstash TCP) → Fallback to File Cache → Sync Queue
```

## Setup Instructions

### 1. Local Development (No Redis required)
Your `.env` is already configured for local development:
```env
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
REDIS_CLIENT=predis
REDIS_URL=  # Leave empty for local
```

Run tests locally:
```bash
php artisan config:clear
php artisan cache:clear
php artisan serve
```

### 2. Production Deployment on Render

#### Step 1: Get your Upstash Redis URL
1. Go to [Upstash Console](https://console.upstash.io/)
2. Create a free Redis database (TCP protocol)
3. Copy the "Redis URL" (should look like: `rediss://default:xxxxxxxxxxxx@us1-xxxxx.upstash.io:xxxxx`)

#### Step 2: Set Render Environment Variables
In your Render dashboard, set these environment variables:

```
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=predis
REDIS_URL=rediss://default:YOUR_ACTUAL_TOKEN@YOUR_UPSTASH_ENDPOINT:YOUR_PORT
```

**IMPORTANT**: Replace the REDIS_URL with your actual Upstash URL from the console.

#### Step 3: Deploy
```bash
git push origin main
# Render will automatically:
# 1. Build Docker image
# 2. Install dependencies (including predis)
# 3. Build caches  
# 4. Test Redis connection at startup
# 5. Fallback to file cache if Redis unavailable
```

### 3. Troubleshooting

#### Missing Predis Error
**Problem**: "Class Predis\Client not found"
**Solution**: 
- Verify `composer.json` includes `"predis/predis": "^3.4"` in `require` (not require-dev)
- Ensure Docker runs `composer install --no-dev` (doesn't skip predis)
- Check Render deployment logs for composer install output

#### Redis Connection Failed
**Problem**: "Redis connection failed" in logs
**What happens automatically**:
- App detects Redis unavailable
- Switches to file cache driver
- Logs warning message
- App continues working (just slower)

**To fix**: 
- Update REDIS_URL in Render environment variables
- Reconfigure with actual Upstash credentials
- Redeploy

#### Configuration Cache Issues
**Problem**: "config cache doesn't match"
**Solution**: The entrypoint script automatically:
1. Clears old cache: `php artisan optimize:clear`
2. Rebuilds fresh cache: `php artisan config:cache`
3. This happens every container start (no manual steps needed)

#### Session/Cache Data Lost After Deploy
**Expected behavior**: 
- File cache: Lost (new instance = new storage/)
- Redis cache: Persists (Upstash keeps data)

To keep session data: ensure REDIS_URL is set correctly

## Cache Performance

### Redis (with Upstash)
- ✅ Persists across deployments
- ✅ Shared between multiple processes
- ✅ Best for production
- ⚠️ Requires valid REDIS_URL

### File Cache (Fallback)
- ✅ Works without external services
- ✅ No configuration needed
- ❌ Lost on redeploy
- ❌ Not shared between processes

## Code Changes Made

### 1. `composer.json`
```json
"require": {
    ...
    "predis/predis": "^3.4"
    ...
}
```

### 2. `app/Providers/AppServiceProvider.php`
Added `setupCacheFallback()` method that:
- Tests Redis connectivity at boot
- Logs status (success or fallback)
- Automatically switches to file cache if Redis fails
- Updates session and queue drivers accordingly

### 3. `Dockerfile`
- Installs dependencies BEFORE copying project
- Runs artisan cache commands during build
- Uses `composer install --no-scripts` first, then with scripts
- Verifies artisan exists
- Builds optimized caches for production

### 4. `docker-entrypoint.sh`
- Tests Redis before starting app
- Dynamically sets drivers based on availability
- Falls back to file cache if Redis unavailable
- Logs all decisions for debugging

### 5. `.env` and `.env.production`
- `.env`: Local development (file cache)
- `.env.production`: Production (Redis with fallback)

## Monitoring

Check Render logs for:
```
✓ Redis connection successful
✓ Cache build successful
✓ Container startup complete

# or

⚠ Redis is NOT available - falling back to file-based caching
⚠ Redis connection failed: [error message]
⚠ Update REDIS_URL environment variable with your actual Upstash URL
```

## Next Steps

1. **Update Render environment variables** with your actual REDIS_URL
2. **Test locally**: `php artisan tinker` → `Cache::put('test', 'value'); Cache::get('test')`
3. **Deploy**: `git push origin main`
4. **Verify**: Check Render logs for "Redis connection successful"

## Support

If you continue having issues:
1. Check Render deployment logs (not app logs)
2. Verify REDIS_URL format: `rediss://default:TOKEN@ENDPOINT:PORT`
3. Test locally without Redis (file cache works fine)
4. Ensure predis is installed: `composer show predis/predis`
5. Clear config cache: `php artisan config:clear && php artisan config:cache`
