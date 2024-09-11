<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentUserRole;
use App\Enums\ContentViewSource;
use App\Events\ContentForceDeleting;
use App\Lti\ContentItemSelectionFactory;
use App\Support\HasUlidsFromCreationDate;
use App\Support\SessionScope;
use BadMethodCallException;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\Image;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LineItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ScoreConstraints;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
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

use function app;
use function assert;
use function is_string;
use function property_exists;
use function session;
use function url;

class Content extends Model
{
    use HasFactory;
    use HasUlidsFromCreationDate;
    use Searchable;
    use SoftDeletes;

    protected $perPage = 48;

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'forceDeleting' => ContentForceDeleting::class,
    ];

    /**
     * @var array<string, mixed>
     */
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

    public function toItemSelectionRequest(): Oauth1Request
    {
        $returnUrl = session()->get('lti.content_item_return_url')
            ?? throw new BadMethodCallException('Not in LTI selection context');
        assert(is_string($returnUrl));

        $credentials = LtiPlatform::where('key', session()->get('lti.oauth_consumer_key'))
            ->firstOrFail()
            ->getOauth1Credentials();

        return app()->make(ContentItemSelectionFactory::class)
            ->createItemSelection([$this->toLtiLinkItem()], $returnUrl, $credentials);
    }

    public function toLtiLinkItem(): EdlibLtiLinkItem
    {
        $version = $this->latestPublishedVersion
            ?? throw new DomainException('No version for content');
        $tool = $version->tool
            ?? throw new DomainException('No tool for LTI resource');

        if ($tool->proxy_launch) {
            $url = url()->route('lti.content', [
                'content' => $this->id,
                SessionScope::TOKEN_PARAM => null,
            ]);
        } else {
            $url = $version->lti_launch_url;
        }
        assert(is_string($url));

        $iconUrl = $version->icon?->getUrl();

        return (new EdlibLtiLinkItem(
            title: $version->getTitle(),
            url: $url,
            icon: $iconUrl ? new Image($iconUrl) : null,
            lineItem: $version->max_score > 0 ?
                new LineItem(new ScoreConstraints(normalMaximum: (float) $version->max_score)) :
                null,
        ))
            ->withLanguageIso639_3($version->language_iso_639_3)
            ->withLicense($version->license)
            ->withTags($version->getSerializedTags())
        ;
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

            $copy->users()->save($user, ['role' => ContentUserRole::Owner]);
            $copy->save();

            return $copy;
        });
    }

    /**
     * @return HasOne<ContentVersion>
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class)
            ->with(['tool'])
            ->latestOfMany();
    }

    /**
     * @return HasOne<ContentVersion>
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
     * @return HasOne<ContentVersion>
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
     * @return HasMany<ContentVersion>
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
     * @return BelongsToMany<Tag>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('verbatim_name');
    }

    /**
     * @return HasMany<ContentView>
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
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps()
            ->using(ContentUser::class);
    }

    /**
     * @return BelongsToMany<User>
     */
    public function usersWithTimestamps(): BelongsToMany
    {
        return $this->users()->withTimestamps();
    }

    public function hasUser(User $user): bool
    {
        return $this->users()->where('id', $user->id)->exists();
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
            'created_at' => $this->created_at,
            'updated_at' => $this->latestVersion?->created_at,
            'published_at' => $this->latestPublishedVersion?->created_at,
            'license' => $version->license,
            'language_iso_639_3' => $version->language_iso_639_3,
            'tags' => $version->getSerializedTags(),
            'gives_score' => $version->givesScore(),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->versions()->exists();
    }

    public static function findShared(string $keywords = ''): ScoutBuilder
    {
        return Content::search($keywords)
            ->where('published', true)
            ->where('shared', true)
            ->query(
                fn (Builder $query) => $query
                    ->with(['latestPublishedVersion', 'users'])
                    ->withCount(['views']),
            )
        ;
    }

    public static function findForUser(User $user, string $keywords = ''): ScoutBuilder
    {
        return Content::search($keywords)
            ->where('user_ids', $user->id)
            ->query(
                fn (Builder $query) => $query
                    ->with(['latestVersion', 'users'])
                    ->withCount(['views']),
            )
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
