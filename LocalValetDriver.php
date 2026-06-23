<?php

use Herd\HerdDumper\HerdInjector;
use Valet\Drivers\ValetDriver;

class LocalValetDriver extends ValetDriver
{
    public function serves(string $sitePath, string $siteName, string $uri): bool
    {
        return file_exists($sitePath.'/artisan')
            && file_exists($sitePath.'/index.php');
    }

    public function beforeLoading(string $sitePath, string $siteName, string $uri): void
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
        }

        if (function_exists('herd_inject_cb') && class_exists('\Herd\HerdDumper\HerdInjector')) {
            HerdInjector::create($sitePath)->inject();
        }
    }

    public function isStaticFile(string $sitePath, string $siteName, string $uri): string|false
    {
        if ($this->containsUnsafePathSegment($uri)) {
            return false;
        }

        if (in_array($uri, ['/favicon.ico', '/favicon.svg', '/robots.txt'], true)) {
            return $this->existingFile($sitePath.$uri);
        }

        if (str_starts_with($uri, '/build/')) {
            return $this->existingFile($sitePath.$uri);
        }

        if (str_starts_with($uri, '/storage/')) {
            return $this->existingFile($sitePath.'/storage/app/public/'.substr($uri, strlen('/storage/')));
        }

        return false;
    }

    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        $_SERVER['SCRIPT_FILENAME'] = $sitePath.'/index.php';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['DOCUMENT_ROOT'] = $sitePath;

        return $sitePath.'/index.php';
    }

    private function existingFile(string $path): string|false
    {
        return $this->isActualFile($path) ? $path : false;
    }

    private function containsUnsafePathSegment(string $uri): bool
    {
        return str_contains($uri, '..')
            || str_contains($uri, '\\')
            || preg_match('/[\x00-\x1F\x7F]/', $uri) === 1;
    }
}
