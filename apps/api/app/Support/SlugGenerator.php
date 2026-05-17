<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugGenerator
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function uniqueFromTitle(string $title, string $modelClass, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'tour';
        }

        $slug = $base;
        $counter = 2;

        while (self::exists($modelClass, $slug, $ignoreId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    public static function ensureUnique(string $slug, string $modelClass, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug);
        if ($base === '') {
            return self::uniqueFromTitle('item', $modelClass, $ignoreId);
        }

        if (! self::exists($modelClass, $base, $ignoreId)) {
            return $base;
        }

        return self::uniqueFromTitle($base, $modelClass, $ignoreId);
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private static function exists(string $modelClass, string $slug, ?int $ignoreId): bool
    {
        $query = $modelClass::query()->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
