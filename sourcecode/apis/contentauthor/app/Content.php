<?php

namespace App;

use App\Apis\AuthApiService;
use App\Apis\ResourceApiService;
use App\EdlibResource\CaEdlibResource;
use App\Http\Libraries\License;
use App\Libraries\DataObjects\ContentTypeDataObject;
use App\Libraries\DataObjects\LtiContent;
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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use function htmlspecialchars_decode;

use const ENT_HTML5;
use const ENT_QUOTES;

/**
 * @property string|int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $title
 * @property bool $is_private
 * @property string|null $version_id
 * @property int|null $max_score
 * @property int $bulk_calculated
 * @property bool $is_published
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
    // HasLocks / Lockable

    public string $userColumn = 'user_id';
    private string $versionColumn = 'version_id';
    public string $editRouteName;

    protected $casts = [
        'is_private' => 'boolean',
        'is_published' => 'boolean',
        'is_draft' => 'boolean',
    ];

    protected $attributes = [
        'is_private' => false,
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
     * @return HasOne<NdlaIdMapper>
     */
    public function ndlaMapper(): HasOne
    {
        return $this->hasOne(NdlaIdMapper::class, 'ca_id');
    }

    public function isCopyable(): bool
    {
        return License::isContentCopyable($this->license);
    }

    /**
     * @return HasMany<ContentLock>
     */
    public function locks(): HasMany
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
     * @throws Exception
     */
    public function isExternalCollaborator($currentUserId): bool
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

    public function hasLock()
    {
        return (new ContentLock())->hasLock($this->id);
    }

    public function lock()
    {
        (new ContentLock())->lock($this->id);
    }

    public function unlock()
    {
        (new ContentLock())->unlock($this->id);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeUnversioned(Builder $query): void
    {
        $query->where('version_id', null);
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
            $user->lastname = $ownerData->getLastName() ?? '';
            $user->email = $ownerData->getEmail() ?? '';
        }

        return $user;
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

        $versionClient = app(VersionClient::class);
        $versionData = $versionClient->getVersion($this[$this->getVersionColumn()]);

        if (empty($versionData)) {
            return false;
        }
        $ndlaMapperCollection = NdlaIdMapper::whereIn('ca_id', $this->getVersionedIds($versionData))
            ->latest()
            ->get();
        return $ndlaMapperCollection->isNotEmpty();
    }

    private function getVersionedIds(VersionData $version): array
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

    public function canList(Request $request): bool
    {
        if (!self::isUserPublishEnabled() || !$this->exists) {
            return true;
        }

        $authId = $request->session()->get('authId') ?? false;
        return $this->isOwner($authId) || $this->isCollaborator() || $this->isExternalCollaborator($authId);
    }

    public function canPublish(Request $request): bool
    {
        if (self::isUserPublishEnabled() || !$this->exists || ($request->importRequest ?? false)) {
            return true;
        }

        return $this->canList($request) || $this->isCopyable();
    }

    public function canShow(bool $preview = false): bool
    {
        return $preview || $this->isActuallyPublished();
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
            /** @var VersionClient $versionClient */
            $versionClient = app()->make(VersionClient::class);
            $latest = $versionClient->latest($this->version_id);
            if ($this->version_id !== $latest->getId()) {
                $editUrl = route($this->editRouteName, $latest->getExternalReference());
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

    public function getEdlibDataObject(): CaEdlibResource
    {
        return new CaEdlibResource(
            (string) $this->id,
            $this->title_clean ?? $this->title,
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
                ->map(fn ($collaborator) => strtolower($collaborator->email))
                ->filter(fn ($email) => $email !== "")
                ->sort()
                ->values()
                ->toArray(),
            $this->getAuthorOverwrite()
        );
    }

    public function toLtiContent(): LtiContent
    {
        return new LtiContent(
            id: $this->id,
            url: $this->getUrl(),
            title: $this->title_clean,
            machineName: $this->getMachineName(),
            hasScore: ($this->getMaxScore() ?? 0) > 0,
            editUrl: $this->getEditUrl(),
            titleHtml: $this->title,
        );
    }

    public static function getContentTypeInfo(string $contentType): ?ContentTypeDataObject
    {
        return null;
    }
}
