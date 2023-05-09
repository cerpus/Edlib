<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\Searchable;

class Content extends Model
{
    use HasFactory;
    use HasUlids;
    use Searchable;

    protected $perPage = 48;

    public function createCopyBelongingTo(User $user): self
    {
        return DB::transaction(function () use ($user) {
            // TODO: title for resource copies
            // TODO: somehow denote content is copied
            $copy = new Content();
            $copy->save();
            $copy->versions()->save($this->latestPublishedVersion->replicate());
            $copy->users()->save($user, ['role' => ContentUserRole::Owner]);

            return $copy;
        });
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class)
            ->has('resource')
            ->latestOfMany();
    }

    /**
     * @return HasOne<ContentVersion>
     */
    public function latestPublishedVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class)
            ->with('resource')
            ->ofMany(['id' => 'max'], function (Builder $query) {
                $query->published();
            });
    }

    /**
     * @return HasMany<ContentVersion>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ContentVersion::class)->orderBy('id', 'DESC');
    }

    /**
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withCasts([
                'role' => ContentUserRole::class,
            ])
            ->withTimestamps();
    }

    /**
     * @return array<string, int|string>
     */
    public function toSearchableArray(): array
    {
        $version = $this->latestPublishedVersion ?? $this->latestVersion;
        assert($version !== null);

        return [
            'id' => $this->id,
            'has_draft' => $this->latestVersion !== $this->latestPublishedVersion,
            'published' => $this->latestPublishedVersion !== null,
            'title' => $version->resource->title,
            'user_ids' => $this->users()->allRelatedIds()->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->versions()->has('resource')->exists();
    }

    public static function findShared(string $query = ''): ScoutBuilder
    {
        return Content::search($query)
            ->where('published', true)
            ->orderBy('updated_at', 'desc')
            ->query(fn (Builder $query) => $query->with([
                'latestPublishedVersion',
                'latestPublishedVersion.resource'
            ]));
    }

    public static function findForUser(User $user, string $query = ''): ScoutBuilder
    {
        return Content::search($query)
            ->where('user_ids', $user->id)
            ->orderBy('updated_at', 'desc')
            ->query(fn (Builder $query) => $query->with([
                'latestVersion',
                'latestVersion.resource'
            ]));
    }
}
