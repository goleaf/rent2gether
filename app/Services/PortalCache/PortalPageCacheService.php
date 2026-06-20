<?php

namespace App\Services\PortalCache;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PortalPageCacheService
{
    public const HIT_ATTRIBUTE = 'portal_page_cache_hit';

    /**
     * @return array{content:string,status:int,headers:array<string, string>,cached_at:string}|null
     */
    public function cachedPayload(Request $request): ?array
    {
        if (! $this->canRead($request)) {
            return null;
        }

        try {
            $payload = Cache::store($this->store())->get($this->cacheKey($request));
        } catch (Throwable $exception) {
            return null;
        }

        if (! is_array($payload) || ! is_string($payload['content'] ?? null)) {
            return null;
        }

        return $payload;
    }

    public function cachedResponse(Request $request): ?Response
    {
        $payload = $this->cachedPayload($request);

        if (! $payload) {
            return null;
        }

        $headers = collect($payload['headers'] ?? [])
            ->filter(fn (mixed $value, mixed $name): bool => is_string($name) && is_string($value))
            ->all();

        $response = response($payload['content'], (int) ($payload['status'] ?? 200), $headers);
        $response->headers->set($this->headerName(), 'HIT');

        return $response;
    }

    public function warm(Request $request, Response $response): void
    {
        if (! $this->canWrite($request, $response)) {
            return;
        }

        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return;
        }

        if (mb_strlen($content, '8bit') > $this->maxResponseBytes()) {
            return;
        }

        try {
            Cache::store($this->store())->put(
                $this->cacheKey($request),
                [
                    'content' => $content,
                    'status' => $response->getStatusCode(),
                    'headers' => $this->cacheableHeaders($response),
                    'cached_at' => now()->toIso8601String(),
                ],
                $this->ttlSeconds($request),
            );
        } catch (Throwable $exception) {
            return;
        }
    }

    public function shouldHandle(Request $request): bool
    {
        if (! (bool) config('portal_cache.enabled', true)) {
            return false;
        }

        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return false;
        }

        if ($request->headers->has('X-Livewire') || $request->headers->has('X-Inertia')) {
            return false;
        }

        if ($this->hasActiveFlash($request)) {
            return false;
        }

        if (auth()->check()) {
            return (bool) config('portal_cache.cache_authenticated_pages', true);
        }

        if (! (bool) config('portal_cache.cache_guest_pages', true)) {
            return false;
        }

        return ! $this->isExcluded($request);
    }

    public function cacheKey(Request $request): string
    {
        $route = $request->route();
        $query = $this->normalizedQuery($request->query());
        $parts = [
            $request->getSchemeAndHttpHost(),
            '/'.ltrim($request->path(), '/'),
            Arr::query($query),
            (string) ($route?->getName() ?? ''),
            $this->localeScope($request),
            $this->visitorScope($request),
        ];

        return 'portal-page:'.hash('sha256', implode('|', $parts));
    }

    public function headerName(): string
    {
        return (string) config('portal_cache.header_name', 'X-Portal-Cache');
    }

    private function canRead(Request $request): bool
    {
        return $this->shouldHandle($request);
    }

    private function canWrite(Request $request, Response $response): bool
    {
        if (! $this->shouldHandle($request)) {
            return false;
        }

        if ($request->attributes->get(self::HIT_ATTRIBUTE) === true) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type');

        if ($contentType !== '' && ! str_contains($contentType, 'text/html')) {
            return false;
        }

        if ((bool) config('portal_cache.respect_response_no_store', false)) {
            return ! str_contains(strtolower((string) $response->headers->get('Cache-Control')), 'no-store');
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    private function cacheableHeaders(Response $response): array
    {
        $contentType = $response->headers->get('Content-Type') ?: 'text/html; charset=UTF-8';

        return [
            'Content-Type' => $contentType,
        ];
    }

    private function hasActiveFlash(Request $request): bool
    {
        if (! $request->hasSession()) {
            return false;
        }

        $session = $request->session();

        if ($session->has('errors')) {
            return true;
        }

        return collect($session->get('_flash.new', []))->isNotEmpty();
    }

    private function isExcluded(Request $request): bool
    {
        foreach ((array) config('portal_cache.excluded_path_prefixes', []) as $prefix) {
            $prefix = trim((string) $prefix, '/');

            if ($prefix !== '' && ($request->is($prefix) || $request->is($prefix.'/*'))) {
                return true;
            }
        }

        $routeName = (string) ($request->route()?->getName() ?? '');

        foreach ((array) config('portal_cache.excluded_route_names', []) as $pattern) {
            if ($routeName !== '' && Str::is((string) $pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function normalizedQuery(array $query): array
    {
        foreach (array_keys($query) as $key) {
            foreach ((array) config('portal_cache.ignored_query_parameters', []) as $pattern) {
                if (Str::is((string) $pattern, (string) $key)) {
                    unset($query[$key]);
                }
            }
        }

        return $this->sortQuery($query);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function sortQuery(array $query): array
    {
        ksort($query);

        foreach ($query as $key => $value) {
            if (is_array($value)) {
                $query[$key] = $this->sortQuery($value);
            }
        }

        return $query;
    }

    private function visitorScope(Request $request): string
    {
        $sessionId = $request->hasSession() ? $request->session()->getId() : 'no-session';
        $mode = (string) ($request->hasSession() ? $request->session()->get('account_mode', '') : '');

        if (auth()->check()) {
            return implode(':', ['user', (string) auth()->id(), $mode, $sessionId]);
        }

        return 'guest:'.$sessionId;
    }

    private function localeScope(Request $request): string
    {
        return (string) ($request->route('locale') ?? app()->getLocale());
    }

    private function ttlSeconds(Request $request): int
    {
        $key = auth()->check() ? 'private_ttl_seconds' : 'ttl_seconds';

        return max(1, (int) config('portal_cache.'.$key, auth()->check() ? 120 : 300));
    }

    private function maxResponseBytes(): int
    {
        return max(1, (int) config('portal_cache.max_response_bytes', 1048576));
    }

    private function store(): string
    {
        return (string) config('portal_cache.store', 'database');
    }
}
