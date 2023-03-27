<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Content extends Model
{
    use HasFactory;
    use HasUlids;
    use Searchable;

    protected $perPage = 40;

    /**
     * @return HasOne<ContentVersion>
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class)
            ->with('resource')
            ->latestOfMany();
    }

    /**
     * @return HasMany<ContentVersion>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ContentVersion::class)->orderBy('id', 'DESC');
    }

    /**
     * @return array<string, int|string>
     */
    public function toSearchableArray(): array
    {
        $latest = $this->latestVersion;

        return [
            'id' => $this->id,
            'title' => $latest->resource->title,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return true; // TODO: draft/published
    }
}
