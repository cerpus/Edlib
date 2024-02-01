<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    public function getTitle(): string
    {
        return $this->title
            ?? throw new DomainException('The content version has no title');
    }

    /**
     * @return BelongsTo<Content, self>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
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
     */
    public static function getUsedLocales(): Collection
    {
        return DB::table('content_versions')
            ->select('language_iso_639_3')
            ->distinct()
            ->pluck('language_iso_639_3');
    }

    /**
     * The locales (ISO 639-3) used by contents
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
     */
    public static function getTranslatedUsedLocales(User $user = null): Collection
    {
        if ($user instanceof User) {
            $locales = self::getUsedLocalesForUser($user)->toArray();
        } else {
            $locales = self::getUsedLocales()->toArray();
        }

        $displayLocale = app()->getLocale();
        $fallBack = app()->getFallbackLocale();

        $displayNames = array_map(
            fn ($locale) => locale_get_display_name($locale, $displayLocale) ?: (locale_get_display_name($locale, $fallBack) ?: $locale),
            $locales,
        );

        return collect(array_combine($locales, $displayNames))->sort();
    }
}
