<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\AuditLog;
use App\ContentVersion;
use App\Events\H5PWasSaved;
use App\H5PContent;
use App\H5PLibrary;
use App\Http\Controllers\Controller;
use App\Libraries\H5P\h5p;
use App\Lti\LtiRequest;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializerInterface;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\Image;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LineItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ScoreConstraints;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use H5PContentValidator;
use H5PCore;
use H5PFrameworkInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use JsonException;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class AdminContentMigrateController extends Controller
{
    public function __construct(
        private readonly h5p $h5p,
        private readonly H5PCore $h5pCore,
        private readonly H5PFrameworkInterface $framework,
        private readonly SignerInterface $signer,
        private readonly ContentItemsSerializerInterface $serializer,
    ) {}

    public function index(Request $request): View
    {
        $pageSize = 25;
        $page = (int) $request->get('page', 1);
        $contents = [];
        $count = 0;

        $fromLibrary = H5PLibrary::where('name', 'H5P.NDLAThreeImage')
            ->where('major_version', 0)
            ->where('minor_version', 5)
            ->first();
        $toLibrary = H5PLibrary::where('name', 'H5P.EscapeRoom')
            ->where('major_version', 0)
            ->where('minor_version', 7)
            ->first();

        if ($fromLibrary !== null && $toLibrary !== null) {
            if ($request->method() === 'POST' && $request->has('content')) {
                $migrated = $this->migrate($fromLibrary, $toLibrary, $request->input('content'));
            }
            $itemsQuery = H5PContent::select(['h5p_contents.id', 'h5p_contents.title'])
                ->leftJoin('content_versions', 'content_versions.id', '=', 'h5p_contents.version_id')
                ->leftJoin('content_versions as cv', 'cv.parent_id', '=', 'content_versions.id')
                ->where('h5p_contents.library_id', $fromLibrary->id)
                ->where(function ($query) {
                    $query
                        ->whereNull('cv.id')
                        ->orWhereNotIn('cv.version_purpose', [ContentVersion::PURPOSE_UPGRADE, ContentVersion::PURPOSE_UPDATE]);
                })
                ->orderBy('h5p_contents.id');

            $count = $itemsQuery->count();
            $contents = $itemsQuery->limit($pageSize)->offset($pageSize * ($page - 1))->get();
        }
        return view('admin.migrate.index', [
            'fromLibrary' => $fromLibrary,
            'toLibrary' => $toLibrary,
            'migrated' => $migrated ?? [],
            'paginator' => (new LengthAwarePaginator($contents, $count, $pageSize))
                ->withPath(route('admin.migrate.library-content')),
        ]);
    }

    private function migrate(H5pLibrary $fromLibrary, H5pLibrary $toLibrary, array $contentIds): array
    {
        $migrated = [];
        $runId = Uuid::uuid4()->toString();

        foreach ($contentIds as $contentId) {
            $sourceH5p = H5PContent::where('id', $contentId)->where('library_id', $fromLibrary->id)->first();
            if ($sourceH5p !== null) {
                $logData = [
                    'runId' => $runId,
                    'fromLibrary' => [
                        'id' => $fromLibrary->id,
                        'name' => $fromLibrary->getLibraryString(true),
                    ],
                    'toLibrary' => [
                        'id' => $toLibrary->id,
                        'name' => $toLibrary->getLibraryString(true),
                    ],
                    'fromContentId' => $sourceH5p->id,
                    'toContentId' => null,
                    'title' => $sourceH5p->title,
                    'error' => null,
                ];
                $result = [
                    'id' => null,
                    'title' => $sourceH5p->title,
                    'message' => '',
                ];
                try {
                    $this->checkContent($sourceH5p);
                    $hubData = $this->getHubInfo($sourceH5p);
                    $newParameters = $this->alterParameters($sourceH5p->parameters);
                    $newH5pContent = $this->save($sourceH5p, $newParameters, $fromLibrary, $toLibrary);
                    $result['id'] = $newH5pContent->id;
                    $result['message'] = 'Migrated';
                    $logData['toContentId'] = $newH5pContent->id;
                    $logData['error'] = false;
                    $this->createHubVersion($hubData['update_url'], $newH5pContent);
                } catch (RuntimeException | GuzzleException | JsonException $e) {
                    Log::error('Failed to migrate content: ' . $e->getMessage());
                    $result['message'] = 'Failed to migrate content: ' . $e->getMessage();
                    $logData['error'] = true;
                    $logData['errorMessage'] = $e->getMessage();
                } finally {
                    $migrated[$sourceH5p->id] = $result;
                    AuditLog::log(
                        'Migrate content from H5P.NDLAThreeImage to H5P.EscapeRoom',
                        json_encode($logData),
                    );
                }
            }
        }

        return $migrated;
    }

    /**
     * Update the semantics
     */
    private function alterParameters(string $parameters): string
    {
        $content = json_decode($parameters, associative: true);
        $content['threeImage']['wasConvertedFromVirtualTour'] = true;

        return json_encode($content, flags: JSON_THROW_ON_ERROR);
    }

    private function checkContent(H5PContent $content): void
    {
        $version = $content->getVersion();
        if (!$version->isLeaf()) {
            throw new RuntimeException('Content is not latest version');
        }
    }

    /**
     * @throws JsonException
     */
    private function save(H5pContent $sourceH5p, string $params, H5PLibrary $fromLibrary, H5PLibrary $toLibrary): H5pContent
    {
        $request = new Request();
        $request->attributes->set('library', $toLibrary->getLibraryString(false));
        $request->attributes->set('title', $sourceH5p->title);
        $request->attributes->set('parameters', json_encode(
            (object) [
                'params' => json_decode($params, associative: true, flags: JSON_THROW_ON_ERROR),
                'metadata' => $sourceH5p->getMetadataStructure(),
            ],
            flags: JSON_THROW_ON_ERROR,
        ));

        $request->attributes->set('isDraft', $sourceH5p->is_draft);
        $request->attributes->set('language_iso_639_3', $sourceH5p->language_iso_639_3);
        $request->attributes->set('license', $sourceH5p->license);
        $request->attributes->set('max_score', $sourceH5p->max_score);

        $oldH5p = $sourceH5p->toArray();
        $oldH5p['library'] = [
            'name' => $fromLibrary->name,
            'majorVersion' => $fromLibrary->major_version,
            'minorVersion' => $fromLibrary->minor_version,
        ];
        $oldH5p['useVersioning'] = true;
        $oldH5p['params'] = $oldH5p['parameters'];

        // Store new content and duplicate any files
        $newContent = $this->h5p->storeContent($request, $oldH5p, $sourceH5p->user_id);
        $newH5p = H5PContent::findOrFail($newContent['id']);

        // Copy license and H5P footer buttons config
        $newH5p->license = $sourceH5p->license;
        $newH5p->disable = $sourceH5p->disable;
        $newH5p->saveQuietly();

        // Update dependencies in the database from old to new content type
        $this->fixDependencies($newH5p);

        // Create new version
        event(new H5PWasSaved($newH5p, $request, ContentVersion::PURPOSE_UPDATE, $sourceH5p));

        return $newH5p;
    }

    /**
     * Get URL to create a new version in Hub for the content
     *
     * @throws GuzzleException|JsonException|RuntimeException
     */
    private function getHubInfo(H5PContent $content)
    {
        /** @var LtiRequest $ltiRequest */
        $ltiRequest = Session::get('lti_requests.admin');
        $requestUrl = $ltiRequest->param('ext_edlib3_content_info_endpoint');

        $infoRequest = new Oauth1Request('POST', $requestUrl, [
            'lti_launch_url' => route('h5p.ltishow', $content->id),
        ]);

        $infoRequest = $this->signer->sign(
            $infoRequest,
            new Credentials(config('app.consumer-key'), config('app.consumer-secret')),
        );

        $client = app(Client::class);
        $response = $client->post($requestUrl, [
            'form_params' => $infoRequest->toArray(),
        ])
            ->getBody()
            ->getContents();

        $decoded = json_decode($response, associative: true, flags: JSON_THROW_ON_ERROR);
        if (empty($decoded)) {
            throw new RuntimeException('No content info received');
        }

        return $decoded;
    }

    /**
     * Create new version of the content in Hub
     *
     * @throws JsonException | GuzzleException
     */
    private function createHubVersion(string $returnUrl, H5PContent $content): void
    {
        $data = $content->toLtiContent();
        $item = (new EdlibLtiLinkItem(
            icon: $data->iconUrl ? new Image($data->iconUrl) : null,
            title: $data->title,
            url: $data->url,
            lineItem: $data->maxScore > 0 ?
                (new LineItem(new ScoreConstraints(normalMaximum: $data->maxScore))) :
                null,
        ))
            ->withLanguageIso639_3($data->languageIso639_3)
            ->withLicense($data->license)
            ->withPublished($data->published)
            ->withShared($data->shared)
            ->withTags($data->tags)
            ->withContentType($data->machineName)
            ->withContentTypeName($data->machineDisplayName)
        ;

        $returnRequest = new Oauth1Request('POST', $returnUrl, [
            'content_items' => json_encode($this->serializer->serialize([$item]), flags: JSON_THROW_ON_ERROR),
            'lti_message_type' => 'ContentItemSelection',
            'lti_version' => 'LTI-1p0',
            'user_id' => Session::get('lti_requests.admin')->param('user_id'),
        ]);

        $returnRequest = $this->signer->sign(
            $returnRequest,
            new Credentials(config('app.consumer-key'), config('app.consumer-secret')),
        );

        $client = app(Client::class);
        $client->post($returnUrl, [
            'form_params' => $returnRequest->toArray(),
        ])
            ->getBody()
            ->getContents();
    }

    /**
     * Delete stored dependencies for old content type, then find and store dependencies for new content type
     */
    private function fixDependencies(H5PContent $h5pContent): void
    {
        $this->framework->deleteLibraryUsage($h5pContent->id);

        // Get the dependencies based on the new main library
        $validator = new H5PContentValidator($this->framework, $this->h5pCore);
        $params = (object) [
            'library' => $h5pContent->library->getLibraryString(false),
            'params' => json_decode($h5pContent->parameters),
        ];
        $validator->validateLibrary($params, (object) [
            'options' => [
                (object) [
                    'name' => $params->library,
                ],
            ],
        ]);
        $dependencies = $validator->getDependencies();

        $this->framework->saveLibraryUsage($h5pContent->id, $dependencies);
    }
}
