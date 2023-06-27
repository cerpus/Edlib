<?php

declare(strict_types=1);

namespace App\Libraries\H5P;

use App\Libraries\H5P\Helper\UrlHelper;
use App\Libraries\H5P\Interfaces\ConfigInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use Illuminate\Support\Facades\Session;
use Ramsey\Uuid\Uuid;

use function Cerpus\Helper\Helpers\profile as config;

abstract class H5PConfigAbstract implements ConfigInterface
{
    protected const CACHE_BUSTER_STRING = '2.0.3';
    protected const EMBED_TEMPLATE = '<iframe src="%s" width=":w" height=":h" frameborder="0" allowfullscreen="allowfullscreen" allow="geolocation *; microphone *; camera *; midi *; encrypted-media *" title="%s"></iframe>';

    public function __construct(
        protected H5PAdapterInterface $adapter,
        public \H5PCore $h5pCore,
        public ?int $id = null,
        protected array $config = [],
        protected array $contentConfig = [],
        protected array $editorConfig = [],
        protected array $assets = ['styles' => [], 'scripts' => []],
        protected string|bool|null $userId = null,
        protected string|bool|null $userName = null,
        protected string|bool|null $userUsername = null,
        protected string|bool|null $userEmail = null,
        protected ?string $redirectToken = null,
        protected array $content = [],
        protected ?string $language = null,
    )
    {
        $this->adapter->setConfig($this);

        $url = UrlHelper::getCurrentBaseUrl();
        $locale = Session::get('locale', config('app.fallback_locale'));

        $this->config = [
            'baseUrl' => $url,
            'url' => $this->h5pCore->fs->getDisplayPath(false),
            'postUserStatistics' => false,
            'ajaxPath' => '/ajax?action=',
            'user' => [
                'name' => trans('h5p-editor.anonymous'),
                'mail' => false,
            ],
            'canGiveScore' => false,
            'hubIsEnabled' => false,
            'ajax' => [
                'contentUserData' => '',
                'setFinished' => '',
            ],
            'tokens' => [
                'result' => Uuid::uuid4()->toString(),
                'contentUserData' => Uuid::uuid4()->toString(),
            ],
            'saveFreq' => config('h5p.saveFrequency'),
            'siteUrl' => $url,
            'l10n' => $this->getL10n(),
            'loadedJs' => [],
            'loadedCss' => [],
            'core' => [
                'scripts' => [],
                'styles' => [],
            ],
            'contents' => [],
            'crossorigin' => config('h5p.crossOrigin'),
            'crossoriginRegex' => config('h5p.crossOriginRegexp'),
            'locale' => $locale,
            'localeConverted' => LtiToH5PLanguage::convert($locale),
            'pluginCacheBuster' => '?v=' . self::CACHE_BUSTER_STRING,
            'libraryUrl' => url('/h5p-php-library/js'),
        ];
    }

    final public function getConfig(): object
    {
        if (config('h5p.saveFrequency') !== false) {
            $name = trans('h5p-editor.anonymous');
            if (!empty($this->userName)) {
                $name = $this->userName;
            } elseif (!empty($this->userUsername)) {
                $name = $this->userUsername;
            } elseif (!empty($this->userId)) {
                $name = $this->userId;
            }

            $this->config['user'] = (object) [
                'name' => $name,
                'mail' => $this->userEmail,
            ];
        }

        $this->addInheritorConfig();

        return (object) $this->config;
    }

    public function getScriptAssets()
    {
        return $this->assets['scripts'] ?? [];
    }

    public function getStyleAssets()
    {
        return $this->assets['styles'] ?? [];
    }

    public function getH5PCore(): \H5PCore
    {
        return $this->h5pCore;
    }

    public function setUserId(string|bool|null $id): static
    {
        $this->userId = $id;
        $this->config['postUserStatistics'] = !empty($this->userId);

        return $this;
    }

    public function setUserName(string|bool|null $name): static
    {
        $this->userName = $name;
        return $this;
    }

    public function setUserEmail(string|bool|null $email): static
    {
        $this->userEmail = $email;
        return $this;
    }

    public function setUserUsername(string|bool|null $name): static
    {
        $this->userUsername = $name;
        return $this;
    }

    public function setRedirectToken(string $token): static
    {
        $this->redirectToken = $token;
        return $this;
    }

    public function loadContent(int $id): static
    {
        $this->id = $id;
        $this->content = $this->h5pCore->loadContent($id);

        return $this;
    }

    public function getContent(): array
    {
        return $this->content;
    }

    public function setLanguage(?string $languageCode): static
    {
        $this->language = $languageCode;
        return $this;
    }

    /**
     * @param 'scripts'|'styles' $type
     */
    protected function addAsset(string $type, mixed $asset): void
    {
        match ($type) {
            'scripts' => $this->assets['scripts'][] = $asset,
            'styles' => $this->assets['styles'][] = $asset,
        };
    }

    protected function addInheritorConfig(): void
    {
        // Implemented by inheritor
    }

    protected function getAssetUrl(?string $h5pType, string $script): string
    {
        $prefix = match ($h5pType) {
            'core' => "/h5p-php-library/",
            'editor' => "/h5p-editor-php-library/",
            default => '',
        };

        return $prefix . $script . "?ver=" . self::CACHE_BUSTER_STRING;
    }

    protected function getEditorAssets(): object
    {
        $editorStyles[] = (string) mix('css/h5p-core.css');
        $editorStyles[] = (string) mix('css/h5p-admin.css');
        foreach (\H5peditor::$styles as $style) {
            $editorStyles[] = $this->getAssetUrl("editor", $style);
        }
        $editorStyles[] = '//fonts.googleapis.com/css?family=Lato:400,700';

        $editorScripts = [];

        foreach (\H5PCore::$scripts as $script) {
            $scriptPath = $this->getAssetUrl("core", $script);
            $editorScripts[] = $scriptPath;
        }

        $replaceScripts = [
            'scripts/h5peditor-metadata-author-widget.js',
            'scripts/h5peditor-metadata.js',
            'scripts/h5peditor-number.js',
            'scripts/h5peditor-select.js',
            'scripts/h5peditor-text.js',
            'scripts/h5peditor-textarea.js',
            'scripts/h5peditor-editor.js',
            'scripts/h5peditor-list-editor.js',
            'scripts/h5peditor-list.js',
        ];
        foreach (\H5peditor::$scripts as $script) {
            if (!in_array($script, $replaceScripts)) {
                $editorScripts[] = $this->getAssetUrl("editor", $script);
            }
        }

        foreach ([(string) mix("js/h5pmetadata.js"), '/js/editor-setup.js'] as $script) {
            $scriptPath = $this->getAssetUrl(null, $script);
            $editorScripts[] = $scriptPath;
        }

        $customScripts = array_map(
            fn ($script) => $this->getAssetUrl(null, $script),
            $this->adapter->getCustomEditorScripts()
        );

        return (object) [
            'css' => array_merge($editorStyles, $this->adapter->getEditorCss()),
            'js' => array_merge($editorScripts, $customScripts),
        ];
    }

    protected function addCoreAssets(): void
    {
        $coreAssets = array_merge(\H5PCore::$scripts, ["js/h5p-display-options.js"]);
        foreach ($coreAssets as $script) {
            $scriptPath = $this->getAssetUrl("core", $script);
            if (!in_array($scriptPath, $this->getScriptAssets())) {
                $this->addAsset("scripts", $scriptPath);
                $this->config['core']['scripts'][] = $scriptPath;
            }
        }

        foreach (\H5PCore::$styles as $style) {
            $stylePath = $this->getAssetUrl("core", $style);
            if (!in_array($stylePath, $this->getStyleAssets())) {
                $this->addAsset("styles", $stylePath);
                $this->config['core']['styles'][] = $stylePath;
            }
        }
    }

    protected function addDefaultEditorAssets(): void
    {
        $this->addAsset("scripts", $this->getAssetUrl(null, "/js/cerpus.js"));
        $this->addAsset("scripts", $this->getAssetUrl("editor", "scripts/h5peditor-editor.js"));
        $this->addAsset("scripts", $this->getAssetUrl("editor", "scripts/h5peditor-init.js"));
        $this->addAsset("scripts", $this->getAssetUrl("editor", $this->getLanguage()));
    }

    protected function addCustomEditorStyles(): void
    {
        foreach ($this->adapter->getCustomEditorStyles() as $style) {
            $this->addAsset('styles', $style);
        }
    }

    private function getLanguage(): string
    {
        $preferredH5PLanguage = LtiToH5PLanguage::convert(Session::get('locale'));

        if ($this->languageFileExists($preferredH5PLanguage)) {
            return "language/$preferredH5PLanguage.js";
        }

        return 'language/en.js';
    }

    private function languageFileExists($preferredLanguage): bool
    {
        $path = public_path('h5p-editor-php-library/language/' . $preferredLanguage . '.js');

        return file_exists($path);
    }

    private function getL10n(): array
    {
        return [
            "H5P" => array_merge($this->h5pCore->getLocalization(), [
                "fullscreen" => trans("h5p-editor.fullscreen"),
                "disableFullscreen" => trans("h5p-editor.disableFullscreen"),
                "download" => trans("h5p-editor.download"),
                "copyrights" => trans("h5p-editor.copyrights"),
                "embed" => trans("h5p-editor.embed"),
                "size" => trans("h5p-editor.size"),
                "showAdvanced" => trans("h5p-editor.showAdvanced"),
                "hideAdvanced" => trans("h5p-editor.hideAdvanced"),
                "advancedHelp" => trans("h5p-editor.advancedHelp"),
                "copyrightInformation" => trans("h5p-editor.copyrightInformation"),
                "close" => trans("h5p-editor.close"),
                "author" => trans("h5p-editor.author"),
                "year" => trans("h5p-editor.year"),
                "source" => trans("h5p-editor.source"),
                "license" => trans("h5p-editor.license"),
                "thumbnail" => trans("h5p-editor.thumbnail"),
                "noCopyrights" => trans("h5p-editor.noCopyrights"),
                "downloadDescription" => trans("h5p-editor.downloadDescription"),
                "copyrightsDescription" => trans("h5p-editor.copyrightsDescription"),
                "embedDescription" => trans("h5p-editor.embedDescription"),
                "h5pDescription" => trans("h5p-editor.h5pDescription"),
                "contentChanged" => trans("h5p-editor.contentChanged"),
                "startingOver" => trans("h5p-editor.startingOver"),
                "confirmDialogHeader" => trans("h5p-editor.confirmDialogHeader"),
                "confirmDialogBody" => trans("h5p-editor.confirmDialogBody"),
                "cancelLabel" => trans("h5p-editor.cancelLabel"),
                "confirmLabel" => trans("h5p-editor.confirmLabel"),
                'title' => trans("h5p.title"),
                'reuse' => trans("h5p.reuse"),
                'reuseContent' => trans("h5p.reuseContent"),
                'reuseDescription' => trans("h5p.reuseDescription"),
                'by' => trans("h5p.by"),
                'showMore' => trans("h5p.showMore"),
                'showLess' => trans("h5p.showLess"),
                'subLevel' => trans("h5p.subLevel"),
                'licenseU' => trans("h5p.licenseU"),
                'licenseCCBY' => trans("h5p.licenseCCBY"),
                'licenseCCBYSA' => trans("h5p.licenseCCBYSA"),
                'licenseCCBYND' => trans("h5p.licenseCCBYND"),
                'licenseCCBYNC' => trans("h5p.licenseCCBYNC"),
                'licenseCCBYNCSA' => trans("h5p.licenseCCBYNCSA"),
                'licenseCCBYNCND' => trans("h5p.licenseCCBYNCND"),
                'licenseCC40' => trans("h5p.licenseCC40"),
                'licenseCC30' => trans("h5p.licenseCC30"),
                'licenseCC25' => trans("h5p.licenseCC25"),
                'licenseCC20' => trans("h5p.licenseCC20"),
                'licenseCC10' => trans("h5p.licenseCC10"),
                'licenseGPL' => trans("h5p.licenseGPL"),
                'licenseV3' => trans("h5p.licenseV3"),
                'licenseV2' => trans("h5p.licenseV2"),
                'licenseV1' => trans("h5p.licenseV1"),
                'licensePD' => trans("h5p.licensePD"),
                'licenseCC010' => trans("h5p.licenseCC010"),
                'licensePDM' => trans("h5p.licensePDM"),
                'licenseC' => trans("h5p.licenseC"),
                'contentType' => trans("h5p.contentType"),
                'licenseExtras' => trans("h5p.licenseExtras"),
                'changes' => trans("h5p.changes"),
                'contentCopied' => trans("h5p.contentCopied"),
                'connectionLost' => trans("h5p.connectionLost"),
                'connectionReestablished' => trans("h5p.connectionReestablished"),
                'resubmitScores' => trans("h5p.resubmitScores"),
                'offlineDialogHeader' => trans("h5p.offlineDialogHeader"),
                'offlineDialogBody' => trans("h5p.offlineDialogBody"),
                'offlineDialogRetryMessage' => trans("h5p.offlineDialogRetryMessage"),
                'offlineDialogRetryButtonLabel' => trans("h5p.offlineDialogRetryButtonLabel"),
                'offlineSuccessfulSubmit' => trans("h5p.offlineSuccessfulSubmit"),
            ]),
        ];
    }
}
