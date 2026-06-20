<?php

namespace App\Http\Middleware;

use App\Services\PortalCache\PortalPageCacheService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WarmPortalPageCache
{
    public function __construct(
        private readonly PortalPageCacheService $cache,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cached = $this->cache->cachedResponse($request);

        if ($cached instanceof Response) {
            $request->attributes->set(PortalPageCacheService::HIT_ATTRIBUTE, true);

            return $cached;
        }

        $response = $next($request);

        if ($this->cache->shouldHandle($request) && $response->getStatusCode() === 200) {
            $response->headers->set($this->cache->headerName(), 'MISS');
        }

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        $this->cache->warm($request, $response);
    }
}
