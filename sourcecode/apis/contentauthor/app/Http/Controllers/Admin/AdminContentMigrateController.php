<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use JsonException;
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
        $fromLibrary = H5PLibrary::where('name', 'H5P.NDLAThreeImage')
            ->where('major_version', 0)
            ->where('minor_version', 4)
            ->first();
        $toLibrary = H5PLibrary::where('name', 'H5P.EscapeRoom')
            ->where('major_version', 0)
            ->where('minor_version', 6)
            ->first();

        if ($fromLibrary !== null && $toLibrary !== null) {
            if ($request->method() === 'POST' && $request->has('content')) {
                $migrated = $this->migrate($fromLibrary, $toLibrary, $request->input('content'));
            }

            $contents = ContentVersion::leftJoin(DB::raw('content_versions as cv'), 'cv.parent_id', '=', 'content_versions.id')
                ->where(function ($query) {
                    $query
                        ->whereNull('cv.content_id')
                        ->orWhereNotIn('cv.version_purpose', [ContentVersion::PURPOSE_UPGRADE, ContentVersion::PURPOSE_UPDATE]);
                })
                ->join('h5p_contents', 'h5p_contents.id', '=', 'content_versions.content_id')
                ->where('h5p_contents.library_id', $fromLibrary->id)
                ->get();
        }

        return view('admin.migrate.index', [
            'fromLibrary' => $fromLibrary,
            'toLibrary' => $toLibrary,
            'contents' => $contents ?? [],
            'migrated' => $migrated ?? [],
        ]);
    }

    private function migrate(H5pLibrary $fromLibrary, H5pLibrary $toLibrary, array $contentIds): array
    {
        $migrated = [];

        foreach ($contentIds as $contentId) {
            $sourceH5p = H5PContent::where('id', $contentId)->where('library_id', $fromLibrary->id)->first();
            if ($sourceH5p !== null) {
                try {
                    $this->checkContent($sourceH5p);
                    $hubData = $this->getHubInfo($sourceH5p);
                    $newParameters = $this->alterParameters($sourceH5p->parameters);
                    $newParameters = $this->upgradeContentActionLibraries($newParameters);
                    $newH5pContent = $this->save($sourceH5p, $newParameters, $fromLibrary, $toLibrary);
                    $this->createHubVersion($hubData['update_url'], $newH5pContent);

                    $migrated[$sourceH5p->id] = [
                        'id' => $newH5pContent->id,
                        'title' => $sourceH5p->title,
                        'message' => 'Updated',
                    ];
                } catch (RuntimeException | GuzzleException | JsonException $e) {
                    Log::error('Failed to migrate content: ' . $e->getMessage());

                    $migrated[$sourceH5p->id] = [
                        'id' => null,
                        'title' => $sourceH5p->title,
                        'message' => 'Failed to migrate content: ' . $e->getMessage(),
                    ];
                }
            }
        }

        return $migrated;
    }

    /**
     * Update the content from H5P.NDLAThreeImage 0.4.x to H5P.EsacapeRoom 0.6.x
     * See upgrades.js in H5P.EscapeRoom 0.6.x
     */
    private function alterParameters(string $parameters): string
    {
        $content = json_decode($parameters, associative: true);
        for ($i = 0; $i < count($content['threeImage']['scenes'] ?? []); $i++) {
            $content['threeImage']['scenes'][$i]['enableZoom'] = false;

            for ($j = 0; $j < count($content['threeImage']['scenes'][$i]['interactions'] ?? []); $j++) {
                $content['threeImage']['scenes'][$i]['interactions'][$j]['passwordSettings'] = [];
                $content['threeImage']['scenes'][$i]['interactions'][$j]['hotspotSettings'] = [];

                if (array_key_exists('label', $content['threeImage']['scenes'][$i]['interactions'][$j])) {
                    if (array_key_exists('showAsHotspot', $content['threeImage']['scenes'][$i]['interactions'][$j]['label'])) {
                        $content['threeImage']['scenes'][$i]['interactions'][$j]['showAsHotspot'] = $content['threeImage']['scenes'][$i]['interactions'][$j]['label']['showAsHotspot'];
                        unset($content['threeImage']['scenes'][$i]['interactions'][$j]['label']['showAsHotspot']);
                    }

                    if (array_key_exists('showAsOpenSceneContent', $content['threeImage']['scenes'][$i]['interactions'][$j]['label'])) {
                        $content['threeImage']['scenes'][$i]['interactions'][$j]['showAsOpenSceneContent'] = $content['threeImage']['scenes'][$i]['interactions'][$j]['label']['showAsOpenSceneContent'];
                        unset($content['threeImage']['scenes'][$i]['interactions'][$j]['label']['showAsOpenSceneContent']);
                    }

                    if (array_key_exists('interactionPassword', $content['threeImage']['scenes'][$i]['interactions'][$j]['label'])) {
                        $content['threeImage']['scenes'][$i]['interactions'][$j]['passwordSettings']['interactionPassword'] = $content['threeImage']['scenes'][$i]['interactions'][$j]['label']['interactionPassword'];
                        unset($content['threeImage']['scenes'][$i]['interactions'][$j]['label']['interactionPassword']);
                    }

                    if (array_key_exists('interactionPasswordHint', $content['threeImage']['scenes'][$i]['interactions'][$j]['label'])) {
                        $content['threeImage']['scenes'][$i]['interactions'][$j]['passwordSettings']['interactionPasswordHint'] = $content['threeImage']['scenes'][$i]['interactions'][$j]['label']['interactionPasswordHint'];
                        unset($content['threeImage']['scenes'][$i]['interactions'][$j]['label']['interactionPasswordHint']);
                    }

                    if (array_key_exists('showHotspotOnHover', $content['threeImage']['scenes'][$i]['interactions'][$j]['label'])) {
                        $content['threeImage']['scenes'][$i]['interactions'][$j]['hotspotSettings']['showHotspotOnHover'] = $content['threeImage']['scenes'][$i]['interactions'][$j]['label']['showHotspotOnHover'];
                        unset($content['threeImage']['scenes'][$i]['interactions'][$j]['label']['showHotspotOnHover']);
                    }

                    if (array_key_exists('isHotspotTabbable', $content['threeImage']['scenes'][$i]['interactions'][$j]['label'])) {
                        $content['threeImage']['scenes'][$i]['interactions'][$j]['hotspotSettings']['isHotspotTabbable'] = $content['threeImage']['scenes'][$i]['interactions'][$j]['label']['isHotspotTabbable'];
                        unset($content['threeImage']['scenes'][$i]['interactions'][$j]['label']['isHotspotTabbable']);
                    }

                    if ($content['threeImage']['scenes'][$i]['sceneType'] === 'static') {
                        if (array_key_exists('hotSpotSizeValues', $content['threeImage']['scenes'][$i]['interactions'][$j]['label'])) {
                            $content['threeImage']['scenes'][$i]['interactions'][$j]['hotspotSettings']['hotSpotSizeValues'] = $content['threeImage']['scenes'][$i]['interactions'][$j]['label']['hotSpotSizeValues'];
                            unset($content['threeImage']['scenes'][$i]['interactions'][$j]['label']['hotSpotSizeValues']);

                            if ($content['threeImage']['scenes'][$i]['interactions'][$j]['hotspotSettings']['hotSpotSizeValues'] === '256,128') {
                                $content['threeImage']['scenes'][$i]['interactions'][$j]['hotspotSettings']['hotSpotSizeValues'] = '25,25';
                            }
                        } else {
                            $content['threeImage']['scenes'][$i]['interactions'][$j]['hotspotSettings']['hotSpotSizeValues'] = '25,25';
                        }
                    } elseif (array_key_exists('hotSpotSizeValues', $content['threeImage']['scenes'][$i]['interactions'][$j]['label'])) {
                        $content['threeImage']['scenes'][$i]['interactions'][$j]['hotspotSettings']['hotSpotSizeValues'] = $content['threeImage']['scenes'][$i]['interactions'][$j]['label']['hotSpotSizeValues'];
                        unset($content['threeImage']['scenes'][$i]['interactions'][$j]['label']['hotSpotSizeValues']);
                    } else {
                        $content['threeImage']['scenes'][$i]['interactions'][$j]['hotspotSettings']['hotSpotSizeValues'] = '256,128';
                    }
                } else {
                    $content['threeImage']['scenes'][$i]['interactions'][$j]['label'] = [];
                }
            }

        }

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
     * These libraries allow the use of sub-content. The library semantics.json file contains which libraries that
     * are allowed and the version. Allowed version has been updated for some of these libraries. The dependency to
     * these libraries are only stored in the content, so find and "update" them
     *
     * @throws JsonException
     */
    private function upgradeContentActionLibraries(string $params): string
    {
        $parameters = json_decode($params, associative: true, flags: JSON_THROW_ON_ERROR);

        for ($i = 0; $i < count($parameters['threeImage']['scenes'] ?? []); $i++) {
            for ($j = 0; $j < count($parameters['threeImage']['scenes'][$i]['interactions'] ?? []); $j++) {
                if (isset($parameters['threeImage']['scenes'][$i]['interactions'][$j]['action']['library'])) {
                    $parameters['threeImage']['scenes'][$i]['interactions'][$j]['action']['library'] = $this->getUpdatedLibraryVersion($parameters['threeImage']['scenes'][$i]['interactions'][$j]['action']['library']);
                }
            }
        }

        return json_encode($parameters, flags: JSON_THROW_ON_ERROR);
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

    /**
     * Get the new version for the dynamic/soft/sub-content dependencies
     */
    private function getUpdatedLibraryVersion(string $oldLibrary): string
    {
        /*
         * Other libraries that could be in the content that have unchanged version:
         *   H5P.GoToScene 0.1
         *   H5P.AdvancedText 1.1
         *   H5P.Image 1.1
         *   H5P.Summary 1.10
         *   H5P.SingleChoiceSet 1.11
        */

        // Should be safe since their respective 'upgrades.js' do not have entries for these changes
        return match ($oldLibrary) {
            'H5P.Audio 1.4' => 'H5P.Audio 1.5',
            'H5P.Video 1.5' => 'H5P.Video 1.6',
            'H5P.MultiChoice 1.14' => 'H5P.MultiChoice 1.16',
            'H5P.Blanks 1.12' => 'H5P.Blanks 1.14',
            default => $oldLibrary,
        };
    }
}
