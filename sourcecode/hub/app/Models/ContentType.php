<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentType extends Model
{
    use HasFactory;
    use HasUuids;

    public $timestamps = false;

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }
}
