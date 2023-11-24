<?php

declare(strict_types=1);

namespace App\Models;

use App\Lti\ContentItemSelectionFactory;
use App\Lti\LtiContent;
use App\Support\SessionScope;
use BadMethodCallException;
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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    public function toLtiLinkItem(): LtiContent
    {
        $version = $this->latestPublishedVersion
            ?? throw new BadMethodCallException('Calling the thing on content without published version');
        assert($version->resource !== null);

        return new LtiContent(
            title: $version->resource->title,
            url: url()->route('lti.content', [
                'content' => $this->id,
                SessionScope::TOKEN_PARAM => null,
            ]),
            languageIso639_3: $version->resource->language_iso_639_3,
            license: $version->resource->license,
        );
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
        return $this->hasOne(ContentVersion::class)
            ->has('resource')
            ->latestOfMany();
    }

    /**
     * @return HasOne<ContentVersion>
     */
    public function latestDraftVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class)
            ->has('resource')
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
            ->has('resource')
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

        $title = $version->resource?->title ?? null;
        assert($title !== null);

        return [
            'id' => $this->id,
            'has_draft' => $this->latestVersion !== $this->latestPublishedVersion,
            'published' => $this->latestPublishedVersion !== null,
            'title' => $title,
            'user_ids' => $this->users()->allRelatedIds()->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'license' => $version->resource?->license,
            'language_iso_639_3' => $version->resource?->language_iso_639_3,
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
