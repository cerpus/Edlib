<?php

declare(strict_types=1);

namespace App\Libraries\H5P;

use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Interfaces\ContentTypeInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\SessionKeys;
use App\Traits\H5PBehaviorSettings;
use H5PCore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function htmlspecialchars;
use function request;
use function sprintf;
use function url;

class H5PViewConfig extends H5PConfigAbstract
{
    use H5PBehaviorSettings;

    private bool $preview = false;
    private ?string $context = null;
    private ?H5PAlterParametersSettingsDataObject $alterParametersSettings = null;
    private ?string $filterParams = null;
    private ?string $embedId = null;
    private string $embedCode = '';
    private string $embedResizeCode = '';
    private ?string $resourceLinkTitle = null;

    public function __construct(H5PAdapterInterface $adapter, H5PCore $h5pCore)
    {
        parent::__construct($adapter, $h5pCore);

        $this->config['documentUrl'] = '';
        $this->contentConfig = [
            'library' => '',
            'jsonContent' => '',
            'fullScreen' => false,
            'exportUrl' => '',
            'embedCode' => '',
            'resizeCode' => '',
            'url' => request()->getSchemeAndHttpHost() . request()->getBasePath(),
            'title' => '',
            'metadata' => [],
            'displayOptions' => [],
            'contentUserData' => '',
        ];

        $this->behaviorSettings = \Session::get(SessionKeys::EXT_BEHAVIOR_SETTINGS);
    }

    public function setPreview(bool $isPreview): static
    {
        $this->preview = $isPreview;
        return $this;
    }

    public function setContext(string $context): static
    {
        $this->context = $context;
        return $this;
    }

    public function loadContent(int $id): static
    {
        parent::loadContent($id);

        $this->setDependentFiles();

        $displayOptions = config('h5p.overrideDisableSetting') === false ? $this->content['disable'] : config('h5p.overrideDisableSetting');
        $this->contentConfig['displayOptions'] = (object) $this->h5pCore->getDisplayOptionsForView(
            $displayOptions,
            (int) $this->content['user_id'],
        );

        $this->contentConfig['exportUrl'] = route('content-download', ['h5p' => $this->content['id']]);
        $this->contentConfig['library'] = $this->content['libraryFullVersionName'];
        $this->contentConfig['fullScreen'] = $this->content['library']['fullscreen'];
        $this->contentConfig['title'] = $this->content['title'];
        $this->contentConfig['metadata'] = $this->content['metadata'];

        $embedPathTemplate = config('edlib.embedPath');
        if ($this->embedCode) {
            $this->contentConfig['embedCode'] = $this->embedCode;
            if ($this->embedResizeCode) {
                $this->contentConfig['resizeCode'] = $this->embedResizeCode;
            }
        } elseif ($embedPathTemplate && $this->embedId !== null) {
            $this->config['documentUrl'] = str_replace('<resourceId>', $this->embedId, $embedPathTemplate);
            $this->contentConfig['embedCode'] = sprintf(
                self::EMBED_TEMPLATE,
                htmlspecialchars($this->config['documentUrl'], ENT_QUOTES),
                htmlspecialchars($this->resourceLinkTitle ?? $this->content['title'] ?? '', ENT_QUOTES),
            );
            $this->contentConfig['resizeCode'] = sprintf(
                '<script src="%s"></script>',
                htmlspecialchars(url('/h5p-php-library/js/h5p-resizer.js')),
            );
        }

        $this->config['saveFreq'] = $this->getSaveFrequency();
        $this->config['canGiveScore'] = !($this->content['max_score'] === null) && $this->content['max_score'] > 0;

        return $this;
    }

    public function setAlterParameterSettings(H5PAlterParametersSettingsDataObject $settings): static
    {
        $this->alterParametersSettings = $settings;
        return $this;
    }

    public function setEmbedId(string|null $embedId): static
    {
        $this->embedId = $embedId;
        return $this;
    }

    public function setEmbedCode(string $embedCode): static
    {
        $this->embedCode = $embedCode;
        return $this;
    }

    public function setEmbedResizeCode(string $embedResizeCode): static
    {
        $this->embedResizeCode = $embedResizeCode;
        return $this;
    }

    public function setResourceLinkTitle(string|null $resourceLinkTitle): static
    {
        $this->resourceLinkTitle = $resourceLinkTitle;
        return $this;
    }

    protected function addInheritorConfig(): void
    {
        if ($this->context) {
            $this->config['ajax']['contentUserData'] = $this->preview ? '/api/progress?action=h5p_preview&c=1' : '/api/progress?action=h5p_contents_user_data&content_id=:contentId&data_type=:dataType&sub_content_id=:subContentId&context=' . $this->context;
        }
        $this->config['ajax']['setFinished'] = $this->preview ? '/api/progress?action=h5p_preview&f=1' : '/api/progress?action=h5p_setFinished';

        if (!empty($this->content)) {
            $this->contentConfig['jsonContent'] = $this->behaviorSettings();

            $state = false;
            if ($this->userId && $this->context &&
                (is_null($this->behaviorSettings) || $this->behaviorSettings->includeAnswers === true)
            ) {
                $progress = new H5PProgress(DB::connection()->getPdo(), $this->userId);
                $state = $progress->getState($this->content['id'], $this->context);
            }
            $this->contentConfig['contentUserData'] = $state !== false ? $state : ['state' => false];

            $this->config['contents'] = (object) [
                'cid-' . $this->content['id'] => (object) $this->contentConfig,
            ];
        }
    }

    private function getSaveFrequency(): int|false
    {
        if (array_key_exists("library", $this->content) &&
            array_key_exists("name", $this->content["library"]) &&
            Str::contains($this->content["library"]["name"], ["H5P.Questionnaire"])
        ) {
            // The questionnaire does not blindly hammer CA with updates unless there really is a change.
            return 1;
        }

        return config('h5p.saveFrequency');
    }

    private function setDependentFiles(): void
    {
        $this->filterParams = $this->h5pCore->filterParameters($this->content);
        $assets = $this->h5pCore->getDependenciesFiles(
            $this->h5pCore->loadContentDependencies($this->content['id'], "preloaded"),
        );
        $this->assets['scripts'] = $assets['scripts'] ?? [];
        $this->assets['styles'] = $assets['styles'] ?? [];

        array_map(function ($type) {
            return array_map(function ($file) {
                if (isset($file->url)) {
                    $file->path = $file->url;
                } elseif (!filter_var($file->path, FILTER_VALIDATE_URL)) {
                    $file->path = $this->h5pCore->fs->getDisplayPath(false) . "/" . $file->path;
                }
                return $file;
            }, $type);
        }, $this->assets);

        array_map(function ($css) {
            $this->addAsset('styles', $this->getAssetUrl(null, $css));
        }, $this->adapter->getCustomViewCss());

        array_map(function ($script) {
            $this->addAsset('scripts', (object) [
                'path' => $script,
                'version' => null,
            ]);
        }, $this->adapter->getCustomViewScripts());
    }

    private function behaviorSettings(): false|string
    {
        $parameters = $this->adapter->alterParameters(
            $this->filterParams ?? '',
            $this->alterParametersSettings ?? new H5PAlterParametersSettingsDataObject(),
        );

        if (!is_null($this->behaviorSettings)) {
            try {
                /** @var ContentTypeInterface $package */
                $package = H5PPackageProvider::make($this->content['library']['name'], $parameters);
                $parameters = $package->applyBehaviorSettings($this->behaviorSettings);
                $css = $package->getCss();
                if (!empty($css)) {
                    array_walk($css, function ($cssString) {
                        $this->addCss($cssString);
                    });
                }
            } catch (UnknownH5PPackageException) {
                $this->packageStructure = json_decode($parameters);
                $parameters = $this->applyBehaviorSettings($this->behaviorSettings);
            }
        }

        return $parameters;
    }
}
