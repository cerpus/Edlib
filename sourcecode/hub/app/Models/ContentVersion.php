<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentRole;
use App\Events\ContentVersionDeleting;
use App\Events\ContentVersionSaving;
use App\Lti\ContentItemSelectionFactory;
use App\Lti\LtiLaunch;
use App\Lti\LtiLaunchBuilder;
use App\Support\HasUlidsFromCreationDate;
use App\Support\SessionScope;
use BadMethodCallException;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\Image;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LineItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ScoreConstraints;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Database\Factories\ContentVersionFactory;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use function app;
use function assert;
use function is_string;
use function mb_strtolower;
use function session;
use function url;

class ContentVersion extends Model
{
    /** @use HasFactory<ContentVersionFactory> */
    use HasFactory;
    use HasUlidsFromCreationDate;

    public const UPDATED_AT = null;

    protected $attributes = [
        'language_iso_639_3' => 'und',
        'published' => true,
        'max_score' => '0.00',
        'min_score' => '0.00',
    ];

    protected $casts = [
        'published' => 'boolean',
        'max_score' => 'decimal:2',
        'min_score' => 'decimal:2',
    ];

    /** @var string[] */
    protected $touches = [
        'content',
    ];

    protected $fillable = [
        'title',
        'lti_launch_url',
        'lti_tool_id',
        'language_iso_639_3',
        'license',
        'published',
        'min_score',
        'max_score',
        'created_at',
        'edited_by',
    ];

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'deleting' => ContentVersionDeleting::class,
        'saving' => ContentVersionSaving::class,
    ];

    public static function booted(): void
    {
        // Clear parent content's version cache when versions are created, updated, or deleted
        if (config('cache.content_versions.enabled')) {
            static::saved(function (self $version) {
                if ($version->content_id) {
                    $version->content?->clearVersionCache();
                }
            });

            static::deleted(function (self $version) {
                if ($version->content_id) {
                    $version->content?->clearVersionCache();
                }
            });
        }
    }

    public function toLtiLinkItem(LtiPlatform $platform): EdlibLtiLinkItem
    {
        $iconUrl = $this->icon?->getUrl();
        $ownerEmail = null;

        if (Session::get('lti.ext_edlib3_include_owner_info') === '1' && $platform->authorizes_edit) {
            $ownerEmail = $this->content?->users->first(
                fn(User $user) => $user->getRelationValue('pivot')->role === ContentRole::Owner,
            )?->email;
        }

        return (new EdlibLtiLinkItem(
            title: $this->getTitle(),
            url: $this->getExternalLaunchUrl(),
            icon: $iconUrl ? new Image($iconUrl) : null,
            lineItem: $this->max_score > 0
                ? new LineItem(new ScoreConstraints(normalMaximum: (float) $this->max_score))
                : null,
        ))
            ->withEdlibVersionId($this->id)
            ->withLanguageIso639_3($this->language_iso_639_3)
            ->withLicense($this->license)
            ->withTags($this->getSerializedTags())
            ->withOwnerEmail($ownerEmail)
        ;
    }

    /**
     * @param string[] $claims
     */
    public function toLtiLaunch(array $claims = []): LtiLaunch
    {
        $launch = app()->make(LtiLaunchBuilder::class)
            ->withClaim('resource_link_title', $this->getTitle());

        foreach ($claims as $name => $value) {
            $launch = $launch->withClaim((string) $name, $value);
        }

        if ($launch->getClaim('resource_link_id') === null) {
            // LTI spec says: "This is an opaque unique identifier that the
            // [platform] guarantees will be unique within the [platform] for
            // every placement of the link". Using the URL should be sufficient
            // to provide that guarantee.
            $launch = $launch->withClaim('resource_link_id', url()->current());
        }

        $tool = $this->tool;
        assert($tool instanceof LtiTool);

        $url = $this->lti_launch_url;
        assert(is_string($url));

        return $launch->toPresentationLaunch($this, $url);
    }

    public function toItemSelectionRequest(): Oauth1Request
    {
        $returnUrl = session()->get('lti.content_item_return_url')
            ?? throw new BadMethodCallException('Not in LTI selection context');
        assert(is_string($returnUrl));

        $platform = LtiPlatform::where('key', session()->get('lti.oauth_consumer_key'))->firstOrFail();
        $data = session()->get('lti.data');

        return app()
            ->make(ContentItemSelectionFactory::class)
            ->createItemSelection(
                [$this->toLtiLinkItem($platform)],
                $returnUrl,
                $platform->getOauth1Credentials(),
                $data,
            );
    }

    /**
     * Get the launch URL that will be returned on item selection requests.
     */
    public function getExternalLaunchUrl(): string
    {
        $content = $this->content ?? throw new DomainException('No content for version');

        if (session('lti.ext_edlib3_return_exact_version')) {
            return route('lti.content-version', [
                'content' => $content->id,
                'version' => $this->id,
                SessionScope::TOKEN_PARAM => null,
            ]);
        }

        return route('lti.content', [
            'content' => $content->id,
            SessionScope::TOKEN_PARAM => null,
        ]);
    }

    public function getTitle(): string
    {
        return $this->title
            ?? throw new DomainException('The content version has no title');
    }

    /**
     * Get the URL to the endpoint for using/inserting content.
     */
    public function getUseUrl(): string
    {
        return route('content.use', [$this->content, $this]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<Upload, $this>
     */
    public function icon(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'icon_upload_id');
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_version_id');
    }

    /**
     * @return BelongsTo<LtiTool, $this>
     */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(LtiTool::class, 'lti_tool_id');
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('verbatim_name');
    }

    /**
     * Get a list of tags in their "prefix:name" or "name" representation.
     * @return string[]
     */
    public function getSerializedTags(): array
    {
        return $this->tags->map(
            fn(Tag $tag) => $tag->prefix !== ''
            ? "{$tag->prefix}:{$tag->name}"
            : $tag->name,
        )
            ->toArray();
    }

    /**
     * @param string[] $tags
     */
    public function handleSerializedTags(array $tags): void
    {
        foreach ($tags as $tag) {
            // Could be used by REST API, not used by CA
            if (str_starts_with($tag, 'h5p:')) {
                $this->displayed_content_type = substr($tag, 4);
            }

            $this->tags()->attach(Tag::findOrCreateFromString($tag), [
                'verbatim_name' => Tag::extractVerbatimName($tag),
            ]);
        }
    }

    public function getRawDisplayedContentType(): string|null
    {
        return $this->attributes['displayed_content_type'];
    }

    public function getDisplayedContentTypeAttribute(): string
    {
        return $this->attributes['displayed_content_type'] ?? $this->tool->name ?? '';
    }

    public function setDisplayedContentTypeAttribute(string|null $contentType): void
    {
        $this->attributes['displayed_content_type'] = $contentType;
        $this->attributes['displayed_content_type_normalized'] = $contentType !== null
            ? mb_strtolower($contentType, 'UTF-8')
            : null;
    }

    public function givesScore(): bool
    {
        return bccomp((string) $this->max_score, '0', 2) !== 0 ||
            bccomp((string) $this->min_score, '0', 2) !== 0;
    }

    /**
     * @param Builder<self> $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('published', true);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where('published', false);
    }

    /**
     * The locales (ISO 639-3) used by contents
     *
     * @return Collection<int, string>
     */
    public static function getUsedLocales(User $user = null): Collection
    {
        return DB::table('content_versions')
            ->select('language_iso_639_3')
            ->distinct()
            ->join('contents', 'contents.id', '=', 'content_versions.content_id')
            ->when(
                $user instanceof User,
                function ($query) use ($user) {
                    /** @var User $user */
                    $query->join('content_user', 'content_user.content_id', '=', 'contents.id')
                        ->where('content_user.user_id', '=', $user->id);
                },
                function ($query) {
                    $query->where('published', true);
                },
            )
            ->whereNull('contents.deleted_at')
            ->pluck('language_iso_639_3');
    }

    /**
     * The locales (ISO 639-3) used by content as key, display name in the current locale as value
     *
     * @return Collection<string, string>
     */
    public static function getTranslatedUsedLocales(User $user = null): Collection
    {
        $locales = self::getUsedLocales($user);
        $displayLocale = app()->getLocale();
        $fallBack = app()->getFallbackLocale();

        return $locales
            ->mapWithKeys(fn(string $locale) => [$locale => locale_get_display_name($locale, $displayLocale) ?: (locale_get_display_name($locale, $fallBack) ?: $locale)])
            ->sort();
    }

    public function getTranslatedLanguage(): string
    {
        return locale_get_display_name($this->language_iso_639_3, app()->getLocale()) ?: (locale_get_display_name($this->language_iso_639_3, app()->getFallbackLocale()) ?: $this->language_iso_639_3);
    }
}
