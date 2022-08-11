<?php

namespace App;

use App\Apis\AuthApiService;
use App\Apis\ResourceApiService;
use App\EdlibResource\CaEdlibResource;
use App\Http\Libraries\License;
use App\Libraries\DataObjects\ContentTypeDataObject;
use App\Libraries\DataObjects\ResourceUserDataObject;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Traits\Attributable;
use App\Traits\HasLanguage;
use App\Traits\HasTranslations;
use App\Traits\Versionable;
use Carbon\Carbon;
use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\VersionData;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * @property string|int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $title
 * @property int $is_private
 * @property string $version_id
 * @property int|null $max_score
 * @property int $bulk_calculated
 * @property bool $is_published
 * @property string $license
 * @property string $node_id
 * @property Collection $collaborators
 * @property bool $is_draft
 *
 * @method static Collection findMany($ids, $columns = ['*'])
 * @method static Builder select($columns = ['*'])
 * @method static int count($columns = '*')
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
abstract class Content extends Model
{
    public const TYPE_ARTICLE = 'article';
    public const TYPE_GAME = 'game';
    public const TYPE_H5P = 'h5p';
    public const TYPE_LINK = 'link';
    public const TYPE_QUESTIONSET = 'questionset';

    use HasLanguage, HasTranslations, Attributable, Versionable;

    // These should be made to clean things up a bit:
    // HasLicense / Licenseable
    // HasCollaborators / Collaboratable(?)
    // HasVersions / Versionable
    // HasLocks / Lockable

    public string $userColumn = 'user_id';
    private string $versionColumn = 'version_id';
    public string $editRouteName;

    protected $casts = [
        'is_published' => 'boolean',
        'is_draft' => 'boolean',
    ];

    public const RESOURCE_TYPE_CSS = '%s-resource';

    // What the POST field is named varies between H5P and Article
    abstract protected function getRequestContent(Request $request);

    // What the content field is named varies between H5P and Article
    abstract protected function getContentContent();

    abstract public function getContentOwnerId();

    abstract public function getISO6393Language();

    public function isOwner($currentUserId): bool
    {
        if ($currentUserId === false || $this->getContentOwnerId() != $currentUserId) {
            return false;
        }
        return true;
    }

    abstract public function getContentType(bool $withSubType = false);

    public function ndlaMapper()
    {
        return $this->hasOne(NdlaIdMapper::class, 'ca_id');
    }

    public function isCopyable(): bool
    {
        return License::isContentCopyable($this->license);
    }

    public function locks()
    {
        return $this->hasMany(ContentLock::class, 'content_id');
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

    public function getOwnerName($ownerId): ?string
    {
        $ownerName = null;
        try {
            /** @var AuthApiService $authApi */
            $authApi = app(AuthApiService::class);
            $user = $authApi->getUser($ownerId);
            if ($user) {
                $ownerName = trim(implode(' ', [$user->getFirstName() ?? '', $user->getLastName() ?? '']));
            }

        } catch (Exception $e) {
        }

        return $ownerName;
    }

    /**
     * @param $currentUserId
     * @return bool
     * @throws Exception
     */
    public function isExternalCollaborator($currentUserId)
    {
        if (CollaboratorContext::isUserCollaborator($currentUserId, $this->id)) {
            return true;
        }

        $resourceApi = app(ResourceApiService::class);
        $collaborators = $resourceApi->getCollaborators("contentauthor", $this->id);

        $isCollaborator = false;

        foreach ($collaborators as $collaborator) {
            if ($collaborator->getTenantId() === $currentUserId) {
                $isCollaborator = true;
            }
        }

        return $isCollaborator;
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

    public function shouldCreateForkBasedOnSession($username = 'authId')
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
     * @param Request $request
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

    public function useVersioning()
    {
        return !empty(config('feature.versioning'));
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

        if ($this->useVersioning() === true) {
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

        return false;
    }

    public function hasLock()
    {
        return (new ContentLock)->hasLock($this->id);
    }

    public function lock()
    {
        (new ContentLock)->lock($this->id);
    }

    public function unlock()
    {
        (new ContentLock)->unlock($this->id);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnversioned($query)
    {
        return $query->where('version_id', null);
    }

    /**
     * @throws \JsonException
     */
    public function getOwnerData(): ResourceUserDataObject
    {
        $user = ResourceUserDataObject::create();
        $user->id = $this->getAttribute($this->userColumn);

        /** @var AuthApiService $authApiService */
        $authApiService = app(AuthApiService::class);

        $ownerData = $authApiService->getUser($user->id);

        if ($ownerData) {
            $user->firstname = $ownerData->getFirstName() ?? '';
            $user->lastName = $ownerData->getLastName() ?? '';
            $user->email = $ownerData->getEmail() ?? '';
        }

        return $user;
    }

    public function getVersionColumn()
    {
        return $this->versionColumn;
    }

    public function isImported($returnMapperObject = false)
    {
        $ndlaMapper = $this->ndlaMapper;
        if (!config('feature.versioning') || !empty($ndlaMapper)) {
            return $returnMapperObject === true ? $ndlaMapper : !empty($ndlaMapper);
        }
        $versionClient = app(VersionClient::class);
        $versionData = $versionClient->getVersion($this[$this->getVersionColumn()]);

        if (empty($versionData)) {
            return false;
        }
        $ndlaMapperCollection = NdlaIdMapper::whereIn('ca_id', $this->getVersionedIds($versionData))
            ->latest()
            ->get();
        return $returnMapperObject === true ? $ndlaMapperCollection->first() : $ndlaMapperCollection->isNotEmpty();
    }

    private function getVersionedIds(VersionData $version)
    {
        $id = [$version->getExternalReference()];
        if (!is_null($version->getParent())) {
            return array_merge($id, $this->getVersionedIds($version->getParent()));
        }
        return $id;
    }

    /**
     * The reason we have this function is that the isPublished function only returns the db value.
     * We need a way to evaluate if a resource actually is published by using both the isPublished and isDraft flags
     */
    public function isActuallyPublished(): bool
    {
        return $this->isPublished() && !$this->isDraft();
    }

    public function isPublished(): bool
    {
        return $this->is_published;
    }

    public function isListed(): bool
    {
        return !$this->is_private;
    }

    public function isDraft(): bool
    {
        return $this->is_draft;
    }

    public static function isUserPublishEnabled(): bool
    {
        /** @var H5PAdapterInterface $adapter */
        $adapter = app(H5PAdapterInterface::class);
        return $adapter->isUserPublishEnabled();
    }

    public function canList(Request $request)
    {
        if (self::isUserPublishEnabled() !== true || $this->exists === false) {
            return true;
        }

        $authId = $request->session()->get('authId') ?? false;
        return $this->isOwner($authId) || $this->isCollaborator() || $this->isExternalCollaborator($authId);
    }

    public function canPublish(Request $request)
    {
        if (self::isUserPublishEnabled() !== true || $this->exists === false || $request->importRequest ?? false === true) {
            return true;
        }

        return $this->canList($request) || $this->isCopyable();
    }

    /**
     * @param boolean $preview
     * @return boolean
     */
    public function canShow($preview = false)
    {
        return $preview === true || $this->isActuallyPublished();
    }

    /**
     * @param $contentId
     * @return H5PContent|Article|Game|Link|QuestionSet|null
     *
     * Poor mans morphism...
     */
    static public function findContentById($contentId)
    {
        if ((preg_match('/^\d+$/', $contentId) && ($content = H5PContent::find($contentId))) ||
            ($content = Article::find($contentId)) ||
            ($content = Game::find($contentId)) ||
            ($content = Link::find($contentId)) ||
            ($content = QuestionSet::find($contentId))
        ) {
            return $content;
        }
    }

    public function getEditUrl($latest = false): ?string
    {
        if (empty($this->editRouteName)) {
            return null;
        }

        $editUrl = route($this->editRouteName, $this->id);
        if ($latest && !empty(config('feature.versioning'))) {
            /** @var VersionClient $versionClient */
            $versionClient = app()->make(VersionClient::class);
            $latest = $versionClient->latest($this->version_id);
            if ($this->version_id !== $latest->getId()) {
                $editUrl = route($this->editRouteName, $latest->getExternalReference());
            }
        }

        return $editUrl;
    }

    public function getMaxScore()
    {
        return null;
    }

    public function getAuthorOverwrite()
    {
        return null;
    }

    public function getEdlibDataObject(): CaEdlibResource
    {
        return new CaEdlibResource(
            (string) $this->id,
            $this->title,
            $this->getContentOwnerId(),
            $this->isPublished(),
            $this->isDraft(),
            $this->isListed(),
            $this->getISO6393Language(),
            $this->getContentType(true),
            $this->getContentLicense(),
            $this->getMaxScore(),
            $this->created_at->toDateTimeImmutable(),
            $this->updated_at->toDateTimeImmutable(),
            CollaboratorContext::getResourceContextCollaborators($this->id),
            $this->collaborators
                ->map(fn($collaborator) => strtolower($collaborator->email))
                ->filter(fn($email) => $email !== "")
                ->sort()
                ->values()
                ->toArray(),
            $this->getAuthorOverwrite()
        );
    }

    public static function getContentTypeInfo(string $contentType): ?ContentTypeDataObject
    {
        return null;
    }
}
