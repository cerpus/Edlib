<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentUserRole;
use App\Enums\ContentViewSource;
use App\Events\ContentDeleting;
use App\Lti\ContentItemSelectionFactory;
use App\Support\SessionScope;
use BadMethodCallException;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\Image;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use DomainException;
use DOMDocument;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\Searchable;

use function app;
use function assert;
use function is_string;
use function session;
use function url;

class Content extends Model
{
    use HasFactory;
    use HasUlids;
    use Searchable;

    protected $perPage = 48;

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'deleting' => ContentDeleting::class,
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
        ))
            ->withLanguageIso639_3($version->language_iso_639_3)
            ->withLicense($version->license)
            ->withTags($version->getSerializedTags())
        ;
    }

    public function createCopyBelongingTo(User $user): self
    {
        return DB::transaction(function () use ($user) {
            $version = $this->latestPublishedVersion
                ?? throw new Exception('No published version');

            // TODO: title for resource copies
            // TODO: somehow denote content is copied
            $copy = new Content();
            $copy->save();
            $copy->versions()->save($version->replicate());
            $copy->users()->save($user, ['role' => ContentUserRole::Owner]);

            return $copy;
        });
    }

    /**
     * @return HasOne<ContentVersion>
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class)->latestOfMany();
    }

    /**
     * @return HasOne<ContentVersion>
     */
    public function latestDraftVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class)
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
        }

        $version->save();

        if ($item instanceof EdlibLtiLinkItem) {
            foreach ($item->getTags() as $tag) {
                $version->tags()->attach(Tag::findOrCreateFromString($tag), [
                    'verbatim_name' => Tag::extractVerbatimName($tag)
                ]);
            }
        }

        return $version;
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
            ->withCasts([
                'role' => ContentUserRole::class,
            ])
            ->withTimestamps();
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
            'title' => $title,
            'user_ids' => $this->users()->allRelatedIds()->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'license' => $version->license,
            'language_iso_639_3' => $version->language_iso_639_3,
            'tags' => $version->getSerializedTags(),
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
            assert($content->updated_at !== null);

            $item = $document->createElement('url');
            $item->appendChild($document->createElement('loc', route('content.details', [$content])));
            $item->appendChild($document->createElement('lastmod', $content->updated_at->toIso8601String()));

            $root->appendChild($item);
        });

        $document->appendChild($root);

        return $document;
    }
}
