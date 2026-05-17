<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
    ];

    public function tours(): BelongsToMany
    {
        return $this->belongsToMany(Tour::class, 'tour_category');
    }
}
