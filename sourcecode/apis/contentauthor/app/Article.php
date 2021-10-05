<?php

namespace App;

use Log;
use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\DataObjects\ResourceDataObject;
use App\Libraries\Versioning\VersionableObject;
use Cerpus\Helper\Clients\Client;
use Cerpus\Helper\DataObjects\OauthSetup;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Iso639p3;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use App\Http\Libraries\ArticleFileVersioner;

/**
 * Class Article
 * @package App
 *
 * @property string parent_id
 * @property string perent_version_id
 * @property string original_id
 * @property string owner_id
 * @property string content
 * @property int deleted_at
 * @property string note_id
 * @property string ndla_url
 *
 * @method null|self noMaxScore()
 * @method null|self ofBulkCalculated($type)
 */
class Article extends Content implements VersionableObject
{
    const TMP_UPLOAD_SESSION_KEY = 'articleTmpFiles';

    public $incrementing = false;

    public $userColumn = 'owner_id';
    public $editRouteName = 'article.edit';

    const BULK_UNTOUCHED = 0;
    const BULK_PROGRESS = 1;
    const BULK_UPDATED = 2;
    const BULK_FAILED = 4;

    protected $dates = ['deleted_at', "updated_at", "created_at"];
    protected $fillable = ['title', 'content'];

    public function collaborators()
    {
        return $this->hasMany('App\ArticleCollaborator');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function rewriteUrls($original, $new)
    {
        $this->content = str_replace($original, $new, $this->content);
    }

    public function givesScore()
    {
        return 0;
    }

    public function parent()
    {
        return $this->belongsTo(Article::class, 'parent_id');
    }

    public function getOriginalIdAttribute($originalId)
    {
        if (is_null($originalId)) { // This is an old article without this attribute set
            return $this->id;
        }

        return $originalId;
    }

    // Abstract method implementations
    protected function getContentContent()
    {
        return $this->content;
    }

    protected function getRequestContent(Request $request)
    {
        return $request->get('content');
    }

    public function getContentOwnerId(): string
    {
        return $this->getOwnerId();
    }

    public function getISO6393Language(): string
    {
        return Iso639p3::code3letters('eng');
    }

    public function makeCopy($owner = null)
    {
        $newArticle = $this->replicate();
        $newArticle->id = Uuid::uuid4()->toString();
        if ($owner) {
            $newArticle->owner_id = $owner;
        }
        $newArticle->parent_id = $this->id;
        $newArticle->parent_version_id = $this->version_id;
        $newArticle->original_id = $this->original_id;
        $newArticle->version_id = null;
        $newArticle->save();

        $newArticle->setAttribution($this->getAttribution());

        $articleFileVersioning = new ArticleFileVersioner($this, $newArticle);
        $articleFileVersioning->copy()->updateDatabase()->rewriteFilePath();

        return $newArticle;
    }

    public function getContentType($withSubType = false): string
    {
        return ResourceDataObject::ARTICLE;
    }

    public function scopeOfBulkCalculated($query, $type)
    {
        $query->where('bulk_calculated', $type);
    }

    /**
     * @param Builder $query
     */
    public function scopeNoMaxScore($query)
    {
        $query->whereNull('max_score');
    }


    public function getMaxScoreHelper($content, $haltIfNotCalculated = false)
    {
        $pattern = '/src=.\/lti\/launch\?url=([^"]+)"?/m';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
        return collect($matches)
            ->map(function ($match) {
                return $match[1];
            })
            ->filter(function ($url) {
                return filter_var(urldecode($url), FILTER_VALIDATE_URL);
            })
            ->map(function ($url) use ($haltIfNotCalculated) {
                try {
                    $decodedUrl = urldecode($url);

                    $client = Client::getClient(OauthSetup::create(['coreUrl' => $decodedUrl]));
                    $response = $client->request("GET");
                    $metadata = \GuzzleHttp\json_decode($response->getBody());
                    if ($haltIfNotCalculated === true && is_null($metadata->resource->maxScore ?? null)) {
                        throw new Exception("Not calculated");
                    }
                    return $metadata->resource->maxScore ?? 0;
                } catch (Exception $exception) {
                    Log::error($exception->getMessage());

                    if ($haltIfNotCalculated) {
                        throw new Exception("Not calculated");
                    }
                }

                return 0;
            })
            ->sum();
    }

    public function getMaxScore()
    {
        return $this->getMaxScoreHelper($this->content);
    }

    function getId(): string
    {
        return $this->id;
    }

    function getOwnerId(): string
    {
        return $this->owner_id;
    }

    function setParentVersionId(string $parentVersionId): bool
    {
        if ($this->parent_version_id !== $parentVersionId) {
            $this->parent_version_id = $parentVersionId;
            return true;
        } else {
            return false;
        }
    }

    function setVersionId(string $versionId)
    {
        $this->version_id = $versionId;
    }

    /**
     * Used by Eloquent to get primary key type.
     * UUID Identified as a string.
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Used by Eloquent to get if the primary key is auto increment value.
     * UUID is not.
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }

    public function convertToCloudPaths()
    {
        $this->content = str_replace(config('app.article-public-path'), route('content.asset', ['path' => ContentStorageSettings::ARTICLE_DIR], false), $this->content);
    }
}
