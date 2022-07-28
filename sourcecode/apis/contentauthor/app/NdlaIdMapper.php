<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class NdlaIdMapper
 * @package App
 *
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

    public function getOerLink()
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

    public static function byNdlaId($id)
    {
        return self::where('ndla_id', $id)->first();
    }

    public static function articleByNdlaId($id)
    {
        return self::where('ndla_id', $id)->where('type', 'article')->first();

    }

    public static function articlesByNdlaId($id)
    {
        return self::where('ndla_id', $id)->where('type', 'article')->get();

    }

    public static function h5pByNdlaId($id)
    {
        return self::where('ndla_id', $id)->where('type', 'h5p')->first();

    }

    public static function byNdlaIdAndLanguage($id, $language)
    {
        return self::where('ndla_id', $id)->where('language_code', $language)->first();
    }

    public static function articleByNdlaIdAndLanguage($id, $language)
    {
        return self::where('ndla_id', $id)
            ->where('language_code', $language)
            ->where('type', 'article')
            ->first();
    }

    public static function h5pByNdlaIdAndLanguage($id, $language)
    {
        return self::where('ndla_id', $id)
            ->where('language_code', $language)
            ->where('type', 'h5p')
            ->first();
    }

    public static function byNdlaChecksum($checksum)
    {
        return self::where('ndla_checksum', $checksum)->latest()->first();
    }

    public function scopeH5P($query)
    {
        return $query->where('type', 'h5p');
    }

    public function h5pContents()
    {
        return $this->belongsTo(H5PContent::class, 'ca_id');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithH5PMetadata($query)
    {
        $query
            ->with('h5pContents.metadata')
            ->h5p()
            ->whereNull('metadata_fetch')
            ->where(function ($query) {
                /** @var \Illuminate\Database\Eloquent\Builder $query */
                $query->whereHas('h5pContents.metadata', function ($query) {
                    /** @var \Illuminate\Database\Eloquent\Builder $query */
                    $query->whereNull('license')
                        ->orWhereNull('authors')
                        ->orWhere('authors', '[]')
                        ->orWhere('authors', '');
                })
                    ->orDoesntHave('h5pContents.metadata');
            });
        return $query;
    }

    public static function byLaunchUrl($url)
    {
        return self::where('launch_url', $url)->first();
    }

    public function scopeArticle($query)
    {
        return $query->where('type', 'article');
    }
}
