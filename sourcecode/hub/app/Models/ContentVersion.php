<?php

declare(strict_types=1);

namespace App\Models;

use App\Lti\LtiLaunch;
use App\Lti\LtiLaunchBuilder;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function is_string;
use function url;

class ContentVersion extends Model
{
    use HasFactory;
    use HasUlids;

    public const UPDATED_AT = null;

    /** @var array<string, string> */
    protected $attributes = [
        'language_iso_639_3' => 'und',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    /** @var string[] */
    protected $touches = [
        'content',
    ];

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

        return $launch->toPresentationLaunch($tool, $url);
    }

    public function getTitle(): string
    {
        return $this->title
            ?? throw new DomainException('The content version has no title');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * @return BelongsTo<Content, self>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<Upload, self>
     */
    public function icon(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'icon_upload_id');
    }

    /**
     * @return BelongsTo<LtiTool, self>
     */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(LtiTool::class, 'lti_tool_id');
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
    public static function getUsedLocales(): Collection
    {
        return DB::table('content_versions')
            ->select('language_iso_639_3')
            ->distinct()
            ->where('published', true)
            ->pluck('language_iso_639_3');
    }

    /**
     * The locales (ISO 639-3) used by contents
     *
     * @return Collection<int, string>
     */
    public static function getUsedLocalesForUser(User $user): Collection
    {
        return DB::table('content_versions')
            ->select('language_iso_639_3')
            ->distinct()
            ->leftJoin('content_user', 'content_versions.content_id', '=', 'content_user.content_id')
            ->where('content_user.user_id', '=', $user->id)
            ->pluck('language_iso_639_3');
    }

    /**
     * The locales (ISO 639-3) used by content as key, display name in the current locale as value
     *
     * @return Collection<string, string>
     */
    public static function getTranslatedUsedLocales(User $user = null): Collection
    {
        if ($user instanceof User) {
            $locales = self::getUsedLocalesForUser($user);
        } else {
            $locales = self::getUsedLocales();
        }

        $displayLocale = app()->getLocale();
        $fallBack = app()->getFallbackLocale();

        return $locales
            ->mapWithKeys(fn (string $locale) => [$locale => locale_get_display_name($locale, $displayLocale) ?: (locale_get_display_name($locale, $fallBack) ?: $locale)])
            ->sort();
    }
}
