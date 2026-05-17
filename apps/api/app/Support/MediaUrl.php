<?php

namespace App\Support;

class MediaUrl
{
    public static function publicPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relative = '/storage/'.ltrim($path, '/');
        $publicBase = env('PUBLIC_STORAGE_URL');

        if (is_string($publicBase) && $publicBase !== '') {
            return rtrim($publicBase, '/').$relative;
        }

        return $relative;
    }
}
