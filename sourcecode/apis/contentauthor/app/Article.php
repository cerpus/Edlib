<?php

namespace App;

use App\Http\Libraries\ArticleFileVersioner;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\DataObjects\ContentTypeDataObject;
use App\Libraries\Versioning\VersionableObject;
use Carbon\Carbon;
use Cerpus\Helper\Clients\Client;
use Cerpus\Helper\DataObjects\OauthSetup;
use DOMDocument;
use DOMElement;
use Exception;
use GuzzleHttp\Utils as GuzzleUtils;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Iso639p3;
use Ramsey\Uuid\Uuid;

use function libxml_use_internal_errors;
use function preg_replace_callback;
use function route;

use const LIBXML_HTML_NOIMPLIED;

/**
 * @property string $id
 * @property string $parent_id
 * @property string $parent_version_id
 * @property string $original_id
 * @property string $owner_id
 * @property string $content
 * @property Carbon $deleted_at
 * @property string $note_id
 * @property string $ndla_url
 *
 * @property Collection<Collaborator> $collaborators
 *
 * @method static Builder|null|self noMaxScore()
 * @method static Builder|null|self ofBulkCalculated($type)
 * @method static self find($id, $columns = ['*'])
 * @method static self findOrFail($id, $columns = ['*'])
 */
class Article extends Content implements VersionableObject
{
    use HasFactory;

    public const TMP_UPLOAD_SESSION_KEY = 'articleTmpFiles';

    public $incrementing = false;

    public string $userColumn = 'owner_id';
    public string $editRouteName = 'article.edit';

    public const BULK_UNTOUCHED = 0;
    public const BULK_PROGRESS = 1;
    public const BULK_UPDATED = 2;
    public const BULK_FAILED = 4;

    protected $dates = ['deleted_at', "updated_at", "created_at"];
    protected $fillable = ['title', 'content'];

    public function render(): string
    {
        if (!$this->content) {
            return '';
        }

        return self::rewriteUploadUrls($this->content);
    }

    /**
     * @return HasMany<ArticleCollaborator>
     */
    public function collaborators(): HasMany
    {
        return $this->hasMany(ArticleCollaborator::class);
    }

    /**
     * @return HasMany<File>
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function rewriteUrls($original, $new): void
    {
        $this->content = str_replace($original, $new, $this->content);
    }

    public function givesScore(): int
    {
        return 0;
    }

    /**
     * @return BelongsTo<Article, self>
     */
    public function parent(): BelongsTo
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
    protected function getContentContent(): string
    {
        return $this->content;
    }

    protected function getRequestContent(Request $request): mixed
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

    public function makeCopy($owner = null): self
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

    public function getContentType(bool $withSubType = false): string
    {
        return Content::TYPE_ARTICLE;
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOfBulkCalculated(Builder $query, $type): void
    {
        $query->where('bulk_calculated', $type);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeNoMaxScore(Builder $query): void
    {
        $query->whereNull('max_score');
    }

    public function getMaxScoreHelper($content, $haltIfNotCalculated = false): int
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
                    $metadata = GuzzleUtils::jsonDecode($response->getBody());
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

    public function getMaxScore(): int
    {
        return $this->getMaxScoreHelper($this->content);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwnerId(): string
    {
        return $this->owner_id;
    }

    public function setParentVersionId(string $parentVersionId): bool
    {
        if ($this->parent_version_id !== $parentVersionId) {
            $this->parent_version_id = $parentVersionId;
            return true;
        } else {
            return false;
        }
    }

    public function setVersionId(string $versionId): void
    {
        $this->version_id = $versionId;
    }

    public function getUrl(): string
    {
        return route('article.show', [$this->id]);
    }

    public function getMachineName(): string
    {
        return 'Article';
    }

    /**
     * Used by Eloquent to get primary key type.
     * UUID Identified as a string.
     */
    public function getKeyType(): string
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

    public static function getContentTypeInfo(string $contentType): ?ContentTypeDataObject
    {
        return new ContentTypeDataObject('Article', $contentType, 'Article', "fa:newspaper-o");
    }

    private static function rewriteUploadUrls(string $content): string
    {
        $cas = app()->make(ContentAuthorStorage::class);
        assert($cas instanceof ContentAuthorStorage);

        $previous = libxml_use_internal_errors(true);
        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->loadHTML($content, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);

            collect($dom->getElementsByTagName('img'))
                ->filter(fn (DOMElement $node) => $node->hasAttribute('src'))
                ->each(fn (DOMElement $node) => $node->setAttribute(
                    'src',
                    preg_replace_callback(
                        '@^/h5pstorage/article-uploads/(.*?)@',
                        fn (array $matches) => $cas->getAssetUrl(ContentStorageSettings::ARTICLE_DIR . $matches[1]),
                        $node->getAttribute('src'),
                    ),
                ));

            return $dom->saveHTML();
        } finally {
            libxml_use_internal_errors($previous);
        }
    }
}
