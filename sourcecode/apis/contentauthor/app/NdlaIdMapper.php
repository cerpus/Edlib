<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $metadata_fetch
 *
 * @method static Builder withH5PMetadata()
 */
class NdlaIdMapper extends Model
{
    protected $fillable = [
        'ndla_id',
        'ca_id',
        'core_id',
        'launch_url',
        'type',
        'ndla_checksum',
        'language_code',
    ];

    public function getOerLink(): string|false
    {
        if (!empty($this->launch_url)) {
            return route("lti.launch", [], false) . "?" . http_build_query(['url' => $this->launch_url]);
        }

        //We really should link to the LTI launch URL, but if it's not available link directly to CA URL
        switch ($this->type) {
            case 'article':
                $url = url(route('article.show', $this->ca_id));
                break;
            case 'h5p':
                $url = url(route('h5p.show', $this->ca_id));
                break;
            default:
                return false;
        }
        return $url;
    }

    public static function byNdlaId($id): ?self
    {
        return self::where('ndla_id', $id)->first();
    }

    public static function articleByNdlaId($id): ?self
    {
        return self::where('ndla_id', $id)->where('type', 'article')->first();
    }

    public static function articlesByNdlaId($id): Collection
    {
        return self::where('ndla_id', $id)->where('type', 'article')->get();
    }

    public static function h5pByNdlaId($id): ?self
    {
        return self::where('ndla_id', $id)->where('type', 'h5p')->first();
    }

    public static function byNdlaIdAndLanguage($id, $language): ?self
    {
        return self::where('ndla_id', $id)->where('language_code', $language)->first();
    }

    public static function articleByNdlaIdAndLanguage($id, $language): ?self
    {
        return self::where('ndla_id', $id)
            ->where('language_code', $language)
            ->where('type', 'article')
            ->first();
    }

    public static function h5pByNdlaIdAndLanguage($id, $language): ?self
    {
        return self::where('ndla_id', $id)
            ->where('language_code', $language)
            ->where('type', 'h5p')
            ->first();
    }

    public static function byNdlaChecksum($checksum): ?self
    {
        return self::where('ndla_checksum', $checksum)->latest()->first();
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeH5P(Builder $query): void
    {
        $query->where('type', 'h5p');
    }

    /**
     * @return BelongsTo<H5PContent, $this>
     */
    public function h5pContents(): BelongsTo
    {
        return $this->belongsTo(H5PContent::class, 'ca_id');
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeWithH5PMetadata(Builder $query): void
    {
        $query
            ->with('h5pContents.metadata')
            ->h5p()
            ->whereNull('metadata_fetch')
            ->where(function (Builder $query) {
                $query->whereHas('h5pContents.metadata', function (Builder $query) {
                    $query->whereNull('license')
                        ->orWhereNull('authors')
                        ->orWhere('authors', '[]')
                        ->orWhere('authors', '');
                })
                    ->orDoesntHave('h5pContents.metadata');
            });
    }

    public static function byLaunchUrl($url): ?self
    {
        return self::where('launch_url', $url)->first();
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeArticle(Builder $query): void
    {
        $query->where('type', 'article');
    }
}
