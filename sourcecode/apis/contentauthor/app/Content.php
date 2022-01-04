<?php

namespace App;

use App\Apis\AuthApiService;
use App\Apis\ResourceApiService;
use App\Http\Libraries\License;
use App\Libraries\DataObjects\ContentTypeDataObject;
use App\Libraries\DataObjects\EdlibResourceDataObject;
use App\Libraries\DataObjects\ResourceUserDataObject;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Models\Traits\RecommendableInterface;
use App\Traits\Attributable;
use App\Traits\HasLanguage;
use App\Traits\HasTranslations;
use App\Traits\Recommendable;
use App\Traits\Versionable;
use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\VersionData;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Class Content
 * @package App
 *
 * @property string|int id
 * @property int created_at
 * @property int updated_at
 * @property string title
 * @property int is_private
 * @property string version_id
 * @property int|null max_score
 * @property int bulk_calculated
 */
abstract class Content extends Model implements RecommendableInterface
{
    use HasLanguage, HasTranslations, Attributable, Versionable;
    //use Recommendable;

    // These should be made to clean things up a bit:
    // HasLicense / Licenseable
    // HasCollaborators / Collaboratable(?)
    // HasVersions / Versionable
    // HasLocks / Lockable

    public $userColumn = 'user_id';
    private $versionColumn = 'version_id';
    public $editRouteName;

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public const RESOURCE_TYPE_CSS = '%s-resource';

    // What the POST field is named varies between H5P and Article
    abstract protected function getRequestContent(Request $request);

    // What the content field is named varies between H5P and Article
    abstract protected function getContentContent();

    abstract public function getContentOwnerId();

    abstract public function getISO6393Language();

    public function isOwner($currentUserId)
    {
        if ($currentUserId === false || $this->getContentOwnerId() != $currentUserId) {
            return false;
        }
        return true;
    }

    abstract public function getContentType($withSubType = false);

    public function ndlaMapper()
    {
        return $this->hasOne(NdlaIdMapper::class, 'ca_id');
    }

    public function isCopyable()
    {
        /** @var License $license */
        $license = app(License::class);
        return $license->isContentCopyable($this->id);
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
                $ownerName = trim(implode(' ', [$user->getFirstName(), $user->getLastName()]));
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

    public function shouldCreateFork($userId)
    {
        return !$this->isOwner($userId) && !$this->isCollaborator() && $this->isCopyable();
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

    public function getContentLicense()
    {
        $lic = app()->make(License::class);
        $license = $lic->getLicense($this->id);
        return $license;
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
     *
     * @param Request $request
     * @return bool
     */
    public function requestShouldBecomeNewVersion(Request $request)
    {
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
            $user->firstname = $ownerData->getFirstName();
            $user->lastName = $ownerData->getLastName();
            $user->email = $ownerData->getEmail();
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

    public function inDraftState(): bool
    {
        return !$this->is_published;
    }

    public function isPublished(): bool
    {
        return !$this->is_private;
    }

    public static function isDraftLogicEnabled()
    {
        $adapter = app(H5PAdapterInterface::class);
        return $adapter->enableDraftLogic();
//        $sessionKey = sprintf(SessionKeys::EXT_DRAFT_SETTING, $request->get('redirectToken'));
//        $ltiDraftSetting = $request->hasSession() && $request->session()->get($sessionKey) === true;
//        return $adapter->enableDraftLogic() === true && $ltiDraftSetting === true;
    }

    public function canList(Request $request)
    {
        if (self::isDraftLogicEnabled() !== true || $this->exists === false) {
            return true;
        }

        $authId = $request->session()->get('authId') ?? false;
        return $this->isOwner($authId) || $this->isCollaborator() || $this->isExternalCollaborator($authId);
    }

    public function canPublish(Request $request)
    {
        if (self::isDraftLogicEnabled() !== true || $this->exists === false || $request->importRequest ?? false === true) {
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
        return $preview === true || $this->inDraftState() !== true;
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

    public function getEdlibDataObject(): EdlibResourceDataObject
    {
        return new EdlibResourceDataObject(
            strval($this->id),
            $this->title,
            $this->getContentOwnerId(),
            $this->isPublished(),
            !$this->inDraftState(),
            $this->getISO6393Language(),
            $this->getContentType(true),
            $this->getContentLicense(),
            $this->getMaxScore(),
            $this->created_at,
            $this->updated_at,
            CollaboratorContext::getResourceContextCollaborators($this->id),
            $this->collaborators
                ->map(function ($collaborator) {
                    return strtolower($collaborator->email);
                })->filter(function ($email) {
                    return $email != "";
                })
                ->sort()
                ->all(),
            $this->getAuthorOverwrite()
        );
    }

    public static function getContentTypeInfo(string $contentType): ?ContentTypeDataObject
    {
        return null;
    }
}
