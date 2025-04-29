<?php

namespace App;

use App\Http\Libraries\License;
use App\Libraries\DataObjects\LtiContent;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Traits\Attributable;
use App\Traits\HasLanguage;
use App\Traits\HasTranslations;
use App\Traits\Versionable;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use function htmlspecialchars_decode;
use function property_exists;

use const ENT_HTML5;
use const ENT_QUOTES;

/**
 * @property string|int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $title
 * @property string|null $version_id
 * @property int|null $max_score
 * @property int $bulk_calculated
 * @property string $license
 * @property string $node_id
 * @property Collection $collaborators
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
    use Versionable;
    public const TYPE_ARTICLE = 'article';
    public const TYPE_GAME = 'game';
    public const TYPE_H5P = 'h5p';
    public const TYPE_LINK = 'link';
    public const TYPE_QUESTIONSET = 'questionset';

    // These should be made to clean things up a bit:
    // HasLicense / Licenseable
    // HasCollaborators / Collaboratable(?)
    // HasVersions / Versionable

    public string $userColumn = 'user_id';
    private string $versionColumn = 'version_id';
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

    public function isCollaborator(): bool
    {
        if (app(H5PAdapterInterface::class)->enableEverybodyIsCollaborators()) {
            return true;
        }

        if (empty($this->collaborators)) {
            return false;
        }

        return $this->collaborators
            ->map(function ($collaborator) {
                return $collaborator->email;
            })
            ->filter(function ($email) {
                return in_array($email, Session::get('verifiedEmails', [Session::get('email')]));
            })
            ->isNotEmpty();
    }

    /**
     * @throws Exception
     */
    public function isExternalCollaborator($currentUserId): bool
    {
        if (CollaboratorContext::isUserCollaborator($currentUserId, $this->id)) {
            return true;
        }

        return false;
    }

    /**
     * @deprecated This is flawed logic
     */
    public function shouldCreateFork($userId): bool
    {
        return !$this->isOwner($userId) && !$this->isCollaborator() && $this->isCopyable();
    }

    public function canUpdateOriginalResource(mixed $userId): bool
    {
        return $this->isOwner($userId) || $this->isCollaborator();
    }

    public function shouldCreateForkBasedOnSession($username = 'authId'): bool
    {
        return $this->shouldCreateFork(Session::get($username, false));
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
     * Return a sorted, lowercased, comma-separated list of collaborators in the request
     *
     * @return string
     */
    protected function getRequestCollaborators(Request $request)
    {
        $requestCollaborators = collect(explode(',', $request->get('collaborators')))
            ->each(function ($collaborator) {
                return strtolower($collaborator);
            })
            ->sort()
            ->all();

        return implode(',', $requestCollaborators);
    }

    /**
     * Return a sorted, lowercased, comma-separated list of collaborators attached to the content
     *
     * @return string
     */
    protected function getContentCollaborators()
    {
        $collaborators = $this->collaborators
            ->map(function ($collaborator) {
                return strtolower($collaborator->email);
            })
            ->sort()
            ->all();

        return implode(',', $collaborators);
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

    /**
     * @param Builder<self> $query
     */
    public function scopeUnversioned(Builder $query): void
    {
        $query->where('version_id', null);
    }

    public function getVersionColumn()
    {
        return $this->versionColumn;
    }

    public function isImported(): bool
    {
        if ($this->ndlaMapper) {
            return true;
        }

        $versionData = $this->getVersion();

        if (empty($versionData)) {
            return false;
        }
        $ndlaMapperCollection = NdlaIdMapper::whereIn('ca_id', $this->getVersionedIds($versionData))
            ->latest()
            ->get();
        return $ndlaMapperCollection->isNotEmpty();
    }

    private function getVersionedIds(ContentVersion $version): array
    {
        $id = [$version->content_id];
        $parent = $version->previousVersion;
        if ($parent) {
            return array_merge($id, $this->getVersionedIds($parent));
        }
        return $id;
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

    public function getEditUrl($latest = false): ?string
    {
        if (empty($this->editRouteName)) {
            return null;
        }

        $editUrl = route($this->editRouteName, $this->id);
        if ($latest) {
            $latestVersion = ContentVersion::latestLeaf($this->version_id);
            if ($this->version_id !== $latestVersion->id) {
                $editUrl = route($this->editRouteName, $latestVersion->content_id);
            }
        }

        return $editUrl;
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
}
