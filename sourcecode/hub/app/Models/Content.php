<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentRole;
use App\Enums\ContentViewSource;
use App\Events\ContentForceDeleting;
use App\Events\ContentSaving;
use App\Support\HasUlidsFromCreationDate;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;
use Database\Factories\ContentFactory;
use DomainException;
use DOMDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\Searchable;

use function assert;
use function property_exists;

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
    }

    public function getTitle(): string
    {
        $version = $this->latestPublishedVersion
            ?? $this->latestDraftVersion
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

            foreach ($previousVersion->tags()->where('prefix', 'h5p')->get() as $tag) {
                assert(property_exists($tag, 'original') && array_key_exists('pivot_verbatim_name', $tag->original));

                $version->tags()->attach($tag, [
                    'verbatim_name' => $tag->original['pivot_verbatim_name'],
                ]);
            }

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
        return $this->hasOne(ContentVersion::class)
            ->with(['tool'])
            ->latestOfMany();
    }

    /**
     * @return HasOne<ContentVersion, $this>
     */
    public function latestDraftVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class)
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
        return $this->hasOne(ContentVersion::class)
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
            $version->published = $item->isPublished() ?? true;
            $version->language_iso_639_3 = strtolower($item->getLanguageIso639_3() ?? 'und');
            $version->license = $item->getLicense();
            $version->max_score = $item->getLineItem()?->getScoreConstraints()?->getTotalMaximum() ?? 0;

            if (count($item->getTags()) > 0) {
                $version->saveQuietly();

                foreach ($item->getTags() as $tag) {
                    $version->tags()->attach(Tag::findOrCreateFromString($tag), [
                        'verbatim_name' => Tag::extractVerbatimName($tag)
                    ]);
                }
            }
        }

        $version->save();

        return $version;
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
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps()
            ->using(ContentUser::class);
    }

    /**
     * @return BelongsToMany<Context, $this>
     */
    public function contexts(): BelongsToMany
    {
        return $this->belongsToMany(Context::class)
            ->withPivot('role')
            ->using(ContextPivot::class);
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
            'users' => $this->users->map(fn ($user) => $user->name)->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->latestVersion?->created_at,
            'published_at' => $this->latestPublishedVersion?->created_at,
            'license' => $version->license,
            'language_iso_639_3' => $version->language_iso_639_3,
            'tags' => $version->getSerializedTags(),
            'gives_score' => $version->givesScore(),
            'content_type' => $version->getDisplayedContentType(),
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
        ;
    }

    /**
     * @return ScoutBuilder<Content>
     */
    public static function findForUser(User $user, string $keywords = ''): ScoutBuilder
    {
        return Content::search($keywords)
            ->where('user_ids', $user->id)
        ;
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
}
