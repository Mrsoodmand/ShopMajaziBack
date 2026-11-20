<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    // The storefront (Next.js) keeps posts for an hour; a saved or deleted post shows there at once.
    // View counts don't pass here (they are counted with toBase()).
    protected static function booted()
    {
        $Refresh = fn () => app(\App\Services\CacheManager::class)->refreshStorefront('blog');
        static::saved($Refresh);
        static::deleted($Refresh);
    }
}
