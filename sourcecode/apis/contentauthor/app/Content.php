<?php

namespace App;

use App\Http\Libraries\License;
use App\Libraries\DataObjects\LtiContent;
use App\Libraries\Versioning\VersionableObject;
use App\Traits\Attributable;
use App\Traits\HasLanguage;
use App\Traits\HasTranslations;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as LaravelCollection;

use function htmlspecialchars_decode;
use function property_exists;

use const ENT_HTML5;
use const ENT_QUOTES;

/**
 * @property string|int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $title
 * @property int|null $max_score
 * @property int $bulk_calculated
 * @property string $license
 * @property string $node_id
 * @property bool $is_draft
 * @property-read string|null $title_clean
 * @property-read NdlaIdMapper|null $ndlaMapper
 *
 * @method static Collection findMany($ids, $columns = ['*'])
 * @method static Builder select($columns = ['*'])
 * @method static int count($columns = '*')
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
abstract class Content extends Model
{
    use HasLanguage;
    use HasTranslations;
    use Attributable;

    public const TYPE_ARTICLE = 'article';
    public const TYPE_GAME = 'game';
    public const TYPE_H5P = 'h5p';
    public const TYPE_LINK = 'link';
    public const TYPE_QUESTIONSET = 'questionset';

    public string $userColumn = 'user_id';
    public string $editRouteName;

    protected $casts = [
        'is_draft' => 'boolean',
    ];

    public const RESOURCE_TYPE_CSS = '%s-resource';

    // What the POST field is named varies between H5P and Article
    abstract protected function getRequestContent(Request $request);

    // What the content field is named varies between H5P and Article
    abstract protected function getContentContent();

    abstract public function getContentOwnerId();

    abstract public function getISO6393Language();

    /**
     * Get the URL for displaying the content
     */
    abstract public function getUrl(): string;

    abstract public function getMachineName(): string;

    public function getTitleCleanAttribute(): string|null
    {
        if ($this->title === null) {
            return null;
        }

        return htmlspecialchars_decode($this->title, ENT_HTML5 | ENT_QUOTES);
    }

    public function isOwner($currentUserId): bool
    {
        if ($currentUserId === false || $this->getContentOwnerId() != $currentUserId) {
            return false;
        }
        return true;
    }

    abstract public function getContentType(bool $withSubType = false);

    /**
     * @return HasOne<NdlaIdMapper, $this>
     */
    public function ndlaMapper(): HasOne
    {
        return $this->hasOne(NdlaIdMapper::class, 'ca_id');
    }

    public function isCopyable(): bool
    {
        return License::isContentCopyable($this->license);
    }

    protected function getRequestTitle(Request $request)
    {
        return $request->get('title');
    }

    protected function getContentTitle()
    {
        return $this->title;
    }

    protected function getRequestLicense(Request $request)
    {
        return $request->get('license');
    }

    public function getContentLicense(): string
    {
        return $this->license ?? '';
    }

    /**
     * Determine if the request should result in a new version
     */
    public function requestShouldBecomeNewVersion(Request $request): bool
    {
        if ($this->isDraft()) {
            return false;
        }

        if ($request->get('isDraft')) {
            return true;
        }

        $ct = $this->getContentTitle();
        $rt = $this->getRequestTitle($request);
        $title = $ct !== $rt; // Titles not the same

        $cc = $this->getContentContent();
        $rc = $this->getRequestContent($request);
        $content = $cc !== $rc; // Content not the same

        $cl = $this->getContentLicense();
        $rl = $this->getRequestLicense($request);
        $license = $cl !== $rl; // License not the same

        return $title || $content || $license;
    }

    /** TODO: on the chopping block */
    public function isImported(): bool
    {
        if ($this->ndlaMapper) {
            return true;
        }

        $content = $this;
        while ($content = $content->parent) {
            if ($content->ndlaMapper) {
                return true;
            }
        }

        return false;
    }

    public function isDraft(): bool
    {
        return $this->is_draft;
    }

    public function canList(Request $request): bool
    {
        return true;
    }

    /**
     * Poor mans morphism...
     */
    public static function findContentById($contentId): H5PContent|Article|Game|Link|QuestionSet|null
    {
        if ((preg_match('/^\d+$/', $contentId) && ($content = H5PContent::find($contentId))) ||
            ($content = Article::find($contentId)) ||
            ($content = Game::find($contentId)) ||
            ($content = Link::find($contentId)) ||
            ($content = QuestionSet::find($contentId))
        ) {
            return $content;
        }

        return null;
    }

    public function getEditUrl(): string
    {
        return route($this->editRouteName, $this->id);
    }

    public function getMaxScore(): int|null
    {
        return null;
    }

    public function getAuthorOverwrite(): string|null
    {
        return null;
    }

    protected function getIconUrl(): string|null
    {
        return null;
    }

    /**
     * @return string[]
     */
    protected function getTags(): array
    {
        return [];
    }

    public function toLtiContent(
        bool|null $published = null,
        bool|null $shared = null,
    ): LtiContent {
        return new LtiContent(
            id: $this->id,
            url: $this->getUrl(),
            title: $this->title_clean,
            machineName: $this->getMachineName(),
            hasScore: ($this->getMaxScore() ?? 0) > 0,
            editUrl: $this->getEditUrl(),
            titleHtml: $this->title,
            languageIso639_3: property_exists($this, 'language_iso_639_3')
                ? $this->language_iso_639_3
                : $this->getISO6393Language(),
            license: $this->license,
            iconUrl: $this->getIconUrl(),
            published: $published,
            shared: $shared,
            tags: $this->getTags(),
            maxScore: $this->getMaxScore(),
        );
    }

    /**
     * Used for some admin pages.
     *
     * @return LaravelCollection<int|string, array{
     *     content: array{
     *         id: int|string,
     *         title: string,
     *         created: string,
     *         contentType: string,
     *         library?: string
     *     },
     *     version_purpose: VersionableObject::PURPOSE_*,
     *     children: list<int|string>,
     *     parent: int|string,
     * }>
     */
    public static function collectVersionData(
        Content&VersionableObject $content,
        LaravelCollection $stack = new LaravelCollection(),
        bool $getChildren = true,
    ): LaravelCollection {
        $versionData = [
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'created_at' => $content->created_at,
                'contentType' => $content->getContentType(),
                'license' => $content->getContentLicense(),
                'language' => $content->getISO6393Language(),
            ],
            'version_purpose' => $content->getVersionPurpose(),
        ];

        if ($content instanceof H5PContent) {
            $versionData['content']['library_id'] = $content->library_id;
            $versionData['content']['library'] = $content->library->getLibraryString(true);
        }

        if ($content->parent) {
            self::collectVersionData($content->parent, $stack, false);
            $versionData['parent'] = $content->parent->id;
        }

        $versionData['children'] = [];
        foreach ($content->children as $child) {
            if ($getChildren) {
                self::collectVersionData($child, $stack);
            }
            $versionData['children'][] = $child->id;
        }

        if (!$stack->has($content->id)) {
            $stack->put($content->id, $versionData);
        }

        return $stack;
    }
}
