<?php

declare(strict_types=1);

namespace App\Models;

use App\DataObjects\ContentStats;
use App\Enums\ContentRole;
use App\Enums\ContentViewSource;
use App\Events\ContentForceDeleting;
use App\Events\ContentSaving;
use App\Exceptions\ContentLockedException;
use App\Support\HasUlidsFromCreationDate;
use Carbon\CarbonImmutable;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;
use Database\Factories\ContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\Searchable;
use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use DOMDocument;
use InvalidArgumentException;
use PDO;

use function assert;

class Content extends Model
{
    /** @use HasFactory<ContentFactory> */
    use HasFactory;
    use HasUlidsFromCreationDate;
    use Searchable;
    use SoftDeletes;

    protected $perPage = 48;

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'saving' => ContentSaving::class,
        'forceDeleting' => ContentForceDeleting::class,
    ];

    protected $attributes = [
        'shared' => true,
    ];

    protected $casts = [
        'shared' => 'boolean',
    ];

    protected $fillable = [
        'shared',
        'created_at',
        'deleted_at',
    ];

    public static function booted(): void
    {
        static::addGlobalScope('atLeastOneVersion', function (Builder $query) {
            $query->whereHas('versions');
        });

        static::saved(function (self $content) {
            if (config('cache.edlib2_usage_lookups.enabled')) {
                $content->clearEdlib2UsageCache();
            }
            if (config('cache.content_versions.enabled')) {
                $content->clearVersionCache();
            }
        });

        static::deleted(function (self $content) {
            if (config('cache.edlib2_usage_lookups.enabled')) {
                $content->clearEdlib2UsageCache();
            }
            if (config('cache.content_versions.enabled')) {
                $content->clearVersionCache();
            }
        });
    }

    public function getTitle(): string
    {
        $version = $this->getCachedLatestPublishedVersion()
            ?? $this->getCachedLatestDraftVersion()
            ?? throw new DomainException('The content has no versions');

        return $version->getTitle();
    }

    /**
     * Get the appropriate URL to the content's details page. If the content is
     * unpublished, returns the URL to the latest draft version.
     * @throws DomainException if there is no version
     */
    public function getDetailsUrl(): string
    {
        if (isset($this->latestPublishedVersion)) {
            return route('content.details', [$this]);
        }

        $version = $this->latestDraftVersion
            ?? throw new DomainException('No usable URL for the content');

        return route('content.version-details', [$this, $version]);
    }

    public function createCopyBelongingTo(User $user, ContentVersion|null $version = null): self
    {
        $previousVersion = $version ?? $this->latestVersion()->firstOrFail();

        return DB::transaction(function () use ($user, $previousVersion) {
            $copy = new Content();
            $copy->saveQuietly();

            $version = $previousVersion->replicate();
            assert($version instanceof ContentVersion);
            $version->previousVersion()->associate($previousVersion);
            $version->published = false;
            $version->title .= ' ' . trans('messages.content-copy-suffix');
            $copy->versions()->save($version);
            // TODO: decide how tags in copied content should be handled.
            // as of now, they are not copied.
            $copy->users()->save($user, ['role' => ContentRole::Owner]);
            $copy->save();

            return $copy;
        });
    }

    /**
     * @return HasOne<ContentVersion, $this>
     */
    public function latestVersion(): HasOne
    {
        return $this
            ->hasOne(ContentVersion::class)
            ->with(['tool'])
            ->latestOfMany();
    }

    /**
     * @return HasOne<ContentVersion, $this>
     */
    public function latestDraftVersion(): HasOne
    {
        return $this
            ->hasOne(ContentVersion::class)
            ->with(['tool'])
            ->ofMany(['id' => 'max'], function (Builder $query) {
                /** @var Builder<ContentVersion> $query */
                $query->draft();
            });
    }

    /**
     * @return HasOne<ContentVersion, $this>
     */
    public function latestPublishedVersion(): HasOne
    {
        return $this
            ->hasOne(ContentVersion::class)
            ->with(['tool'])
            ->ofMany(['id' => 'max'], function (Builder $query) {
                /** @var Builder<ContentVersion> $query */
                $query->published();
            });
    }

    /**
     * @return HasMany<ContentVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ContentVersion::class)->orderBy('id', 'DESC');
    }

    /**
     * Get the latest version with caching
     *
     * Cache can be configured in config/cache.php under 'content_versions':
     * - enabled: Enable/disable caching (default: true)
     * - duration: Cache duration in seconds (default: 3600 = 1 hour)
     * - latest_version_key: Cache key prefix for latest version
     */
    public function getCachedLatestVersion(): ?ContentVersion
    {
        if (!config('cache.content_versions.enabled')) {
            return $this->latestVersion()->first();
        }

        $cacheKey = config('cache.content_versions.latest_version_key') . $this->id;
        $duration = config('cache.content_versions.duration');

        return Cache::remember($cacheKey, $duration, function () {
            return $this->latestVersion()->first();
        });
    }

    /**
     * Get the latest draft version with caching
     */
    public function getCachedLatestDraftVersion(): ?ContentVersion
    {
        if (!config('cache.content_versions.enabled')) {
            return $this->latestDraftVersion()->first();
        }

        $cacheKey = config('cache.content_versions.latest_draft_version_key') . $this->id;
        $duration = config('cache.content_versions.duration');

        return Cache::remember($cacheKey, $duration, function () {
            return $this->latestDraftVersion()->first();
        });
    }

    /**
     * Get the latest published version with caching
     */
    public function getCachedLatestPublishedVersion(): ?ContentVersion
    {
        if (!config('cache.content_versions.enabled')) {
            return $this->latestPublishedVersion()->first();
        }

        $cacheKey = config('cache.content_versions.latest_published_version_key') . $this->id;
        $duration = config('cache.content_versions.duration');

        return Cache::remember($cacheKey, $duration, function () {
            return $this->latestPublishedVersion()->first();
        });
    }

    public function createVersionFromLinkItem(
        ContentItem $item,
        LtiTool $tool,
        User $user,
    ): ContentVersion {
        $title = $item->getTitle() ?? throw new DomainException('Missing title');
        $url = $item->getUrl() ?? throw new DomainException('Missing URL');

        $version = $this->versions()->make();
        assert($version instanceof ContentVersion);

        $version->title = $title;
        $version->lti_launch_url = $url;
        $version->original_icon_url = $item->getIcon()?->getUri();
        $version->published = true;
        $version->tool()->associate($tool);
        $version->editedBy()->associate($user);

        if ($item instanceof EdlibLtiLinkItem) {
            $displayField = config('features.ca-content-type-display');
            $contentType = $item->getContentType(); // Content type machine name
            $contentTypeName = $item->getContentTypeName(); // Content type title
            $displayValue = ($displayField === 'h5p_title' ? $contentTypeName : $contentType);
            $version->published = $item->isPublished() ?? true;
            $version->language_iso_639_3 = strtolower($item->getLanguageIso639_3() ?? 'und');
            $version->license = $item->getLicense();
            $version->max_score = $item->getLineItem()?->getScoreConstraints()?->getTotalMaximum() ?? 0;
            $displayedType = $displayValue ?? $contentType ?? null;
            if ($displayedType) {
                $version->displayed_content_type = $displayedType;
            }

            $version->saveQuietly();
            // Add content type info as tags
            if ($contentType) {
                $version->tags()
                    ->attach(
                        Tag::firstOrCreate([
                            'prefix' => 'h5p',
                            'name' => strtolower($contentType),
                        ]), [
                            'verbatim_name' => strtolower($contentType),
                        ]
                    );
            }
            if ($contentTypeName) {
                $version->tags()
                    ->attach(
                        Tag::firstOrCreate([
                            'prefix' => 'h5p_title',
                            'name' => $contentTypeName,
                        ]), [
                            'verbatim_name' => $contentTypeName,
                        ]
                    );
            }

            if (count($item->getTags()) > 0) {
                $version->handleSerializedTags($item->getTags());
            }
        }

        $version->save();

        return $version;
    }

    /**
     * @return HasMany<ContentLock, $this>
     */
    public function locks(): HasMany
    {
        return $this->hasMany(ContentLock::class);
    }

    public function isLocked(): bool
    {
        return $this->locks()->active()->exists();
    }

    public function getActiveLock(): ContentLock|null
    {
        return $this->locks()->active()->first();
    }

    /**
     * @throws ContentLockedException
     */
    public function acquireLock(User $user): void
    {
        $this->locks()->inactive()->delete();

        try {
            $this->locks()->forceCreate(['user_id' => $user->id]);
        } catch (UniqueConstraintViolationException $e) {
            throw new ContentLockedException($this, $e);
        }
    }

    /**
     * @throws ContentLockedException
     */
    public function refreshLock(User $user): void
    {
        $lock = $this->locks()->active()->whereBelongsTo($user)->first();

        if ($lock) {
            $lock->touch();
        } else {
            $this->acquireLock($user);
        }
    }

    public function releaseLock(User $user): void
    {
        $this->locks()->whereBelongsTo($user)->delete();
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('verbatim_name');
    }

    /**
     * @param Builder<Content> $query
     * @param array{prefix: string, name: string}|string $tag
     */
    public function scopeOfTag(Builder $query, string|array $tag): void
    {
        if (is_string($tag)) {
            $tag = Tag::parse($tag);
        }

        ['prefix' => $prefix, 'name' => $name] = $tag;

        $query->whereHas('tags', function (Builder $query) use ($prefix, $name) {
            /** @var Builder<Tag> $query */
            return $query
                ->where('prefix', $prefix)
                ->where('name', $name);
        });
    }

    /**
     * @return HasMany<ContentEdlib2Usage, $this>
     */
    public function edlib2Usages(): HasMany
    {
        return $this->hasMany(ContentEdlib2Usage::class, 'content_id');
    }

    public static function firstWithEdlib2UsageIdOrFail(string $usageId): self
    {
        if (config('cache.edlib2_usage_lookups.enabled')) {
            $cacheKey = config('cache.edlib2_usage_lookups.key_prefix') . $usageId;
            $duration = config('cache.edlib2_usage_lookups.duration');

            /** @var Content */
            return Cache::remember($cacheKey, $duration, function () use ($usageId) {
                return self::whereHas('edlib2Usages', function (Builder $query) use ($usageId): void {
                    /** @var Builder<ContentEdlib2Usage> $query */
                    $query->where('edlib2_usage_id', $usageId);
                })->firstOrFail();
            });
        }

        /** @var Content */
        return self::whereHas('edlib2Usages', function (Builder $query) use ($usageId): void {
            /** @var Builder<ContentEdlib2Usage> $query */
            $query->where('edlib2_usage_id', $usageId);
        })->firstOrFail();
    }

    /**
     * @return HasMany<ContentView, $this>
     */
    public function views(): HasMany
    {
        return $this->hasMany(ContentView::class);
    }

    public function trackView(
        Request $request,
        ContentViewSource $source,
        LtiPlatform|null $sourcePlatform = null,
    ): void {
        if (!$source->isLtiPlatform() && $sourcePlatform !== null) {
            throw new InvalidArgumentException(
                '$sourcePlatform must only be set for LTI platform views',
            );
        }

        $user = $request->user();
        if ($user instanceof User && $this->hasUser($user)) {
            return;
        }

        $sessionKey = "content_views.{$this->id}";
        if ($request->session()->has($sessionKey)) {
            return;
        }

        $view = new ContentView();
        $view->source = $source;
        $view->ip = $request->ip();
        $view->lti_platform_id = $sourcePlatform?->id;
        $this->views()->save($view);

        $request->session()->put($sessionKey, true);
    }

    /**
     * @return HasMany<ContentViewsAccumulated, $this>
     */
    public function viewsAccumulated(): HasMany
    {
        return $this->hasMany(ContentViewsAccumulated::class);
    }

    public function countTotalViews(): int
    {
        // this is an int, despite what Larastan claims
        // @phpstan-ignore return.type
        return $this->views()->count() + $this->viewsAccumulated()->sum('view_count');
    }

    /**
     * @return BelongsToMany<User, $this, ContentUser, "pivot">
     */
    public function users(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps()
            ->using(ContentUser::class);
    }

    /**
     * @return BelongsToMany<Context, $this>
     */
    public function contexts(): BelongsToMany
    {
        return $this->belongsToMany(Context::class);
    }

    public function hasUser(User $user): bool
    {
        return $this->users->contains($user);
    }

    public function hasUserWithMinimumRole(User $user, ContentRole $role): bool
    {
        foreach ($this->users as $contentUser) {
            if ($contentUser->is($user)) {
                // @phpstan-ignore property.notFound
                $contentUserRole = $contentUser->pivot->role;
                assert($contentUserRole instanceof ContentRole);

                // User cannot be added more than once, so we return here.
                return $contentUserRole->grants($role);
            }
        }

        return false;
    }

    /**
     * @return array<array-key, array{
     *     content_id: string,
     *     source: value-of<ContentViewSource>,
     *     lti_platform_id: string|null,
     *     date: string,
     *     hour: int,
     *     count: int,
     * }>
     */
    public static function getAccumulatableViews(DateTimeImmutable $cutoff): array
    {
        $statement = DB::getPdo()->prepare(<<<'EOSQL'
            SELECT
                content_id,
                source,
                lti_platform_id,
                (created_at AT TIME ZONE 'UTC')::DATE AS date,
                EXTRACT(hour FROM created_at AT TIME ZONE 'UTC') AS hour,
                COUNT(*) AS count
            FROM content_views
            WHERE created_at < :cutoff
            GROUP BY content_id, source, lti_platform_id, date, hour
            ORDER BY date, hour
            EOSQL);
        $statement->bindValue(':cutoff', $cutoff->format('c'));
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buildStatsGraph(
        DateTimeImmutable|null $start,
        DateTimeImmutable|null $end,
    ): ContentStats {
        $start ??= new CarbonImmutable('@0');
        $end ??= new CarbonImmutable('now');

        $start = $start->setTimezone(new DateTimeZone('UTC'));
        $end = $end->setTimezone(new DateTimeZone('UTC'));

        // TODO: lti platforms as separate stats
        $statement = DB::getPdo()->prepare(<<<'EOSQL'
            SELECT
                source,
                COUNT(*) AS view_count,
                EXTRACT(YEAR FROM created_at AT TIME ZONE 'UTC') AS year,
                EXTRACT(MONTH FROM created_at AT TIME ZONE 'UTC') AS month,
                EXTRACT(DAY FROM created_at AT TIME ZONE 'UTC') AS day
            FROM content_views
            WHERE content_id = :content_id AND created_at >= :start_ts AND created_at <= :end_ts
            GROUP BY source, year, month, day
            UNION ALL
            SELECT
                source,
                SUM(view_count) AS view_count,
                EXTRACT(YEAR FROM date) AS year,
                EXTRACT(MONTH FROM date) AS month,
                EXTRACT(DAY FROM date) AS day
            FROM content_views_accumulated
            WHERE content_id = :content_id AND
                (date > :start_date OR date = :start_date AND hour >= :start_hour) AND
                (date < :end_date OR date = :end_date AND hour <= :end_hour)
            GROUP BY source, year, month, day
            EOSQL);
        $statement->bindValue(':content_id', $this->id);
        $statement->bindValue(':start_ts', $start->format('c'));
        $statement->bindValue(':start_date', $start->format('Y-m-d'));
        $statement->bindValue(':start_hour', $start->format('G'));
        $statement->bindValue(':end_ts', $end->format('c'));
        $statement->bindValue(':end_date', $end->format('Y-m-d'));
        $statement->bindValue(':end_hour', $end->format('G'));
        $statement->execute();

        $stats = new ContentStats($start, $end);

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $stats->addStat(
                ContentViewSource::from($row['source']),
                (int) $row['view_count'],
                (int) $row['year'],
                (int) $row['month'],
                (int) $row['day'],
            );
        }

        return $stats;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $version = $this->latestPublishedVersion ?? $this->latestVersion;
        assert($version !== null);

        $title = $version->title ?? null;
        assert($title !== null);

        return [
            'id' => $this->id,
            'has_draft' => $this->latestVersion !== $this->latestPublishedVersion,
            'published' => $this->latestPublishedVersion !== null,
            'shared' => $this->shared,
            'title' => $title,
            'user_ids' => $this->users()->allRelatedIds()->toArray(),
            'users' => $this->users->map(fn($user) => $user->name)->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->latestVersion?->created_at,
            'published_at' => $this->latestPublishedVersion?->created_at,
            'license' => $version->license,
            'language_iso_639_3' => $version->language_iso_639_3,
            'tags' => $version->getSerializedTags(),
            'gives_score' => $version->givesScore(),
            'content_type' => $version->displayed_content_type,
            'views' => $this->countTotalViews(),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->versions()->exists();
    }

    /**
     * @return ScoutBuilder<Content>
     */
    public static function findShared(string $keywords = ''): ScoutBuilder
    {
        return Content::search($keywords)
            ->where('published', true)
            ->where('shared', true)
            ->options(['facets' => ['views']]);
    }

    /**
     * @return ScoutBuilder<Content>
     */
    public static function findForUser(User $user, string $keywords = ''): ScoutBuilder
    {
        return Content::search($keywords)
            ->where('user_ids', $user->id)
            ->options(['facets' => ['views']]);
    }

    public static function generateSiteMap(): DOMDocument
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $root = $document->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset');

        /** @var Collection<int, Content> $contents */
        $contents = self::findShared()->get();

        $contents->each(function (Content $content) use ($document, $root) {
            $version = $content->latestPublishedVersion;
            assert($version?->created_at !== null);

            $item = $document->createElement('url');
            $item->appendChild($document->createElement('loc', route('content.details', [$content])));
            $item->appendChild($document->createElement('lastmod', $version->created_at->toIso8601String()));

            $root->appendChild($item);
        });

        $document->appendChild($root);

        return $document;
    }

    /**
     * Clear the cache for this content's Edlib2 usage IDs
     */
    private function clearEdlib2UsageCache(): void
    {
        $keyPrefix = config('cache.edlib2_usage_lookups.key_prefix');
        $this->edlib2Usages->each(function (ContentEdlib2Usage $usage) use ($keyPrefix) {
            Cache::forget($keyPrefix . $usage->edlib2_usage_id);
        });
    }

    /**
     * Clear all version-related caches for this content
     */
    public function clearVersionCache(): void
    {
        Cache::forget(config('cache.content_versions.latest_version_key') . $this->id);
        Cache::forget(config('cache.content_versions.latest_draft_version_key') . $this->id);
        Cache::forget(config('cache.content_versions.latest_published_version_key') . $this->id);
    }
}
