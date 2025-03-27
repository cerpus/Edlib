<?php

declare(strict_types=1);

namespace App\Libraries\H5P;

use App\Libraries\H5P\Interfaces\ConfigInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use H5PCore;
use H5peditor;
use Illuminate\Support\Facades\Session;
use Iso639p3;
use Ramsey\Uuid\Uuid;

use function config;

abstract class H5PConfigAbstract implements ConfigInterface
{
    public const CACHE_BUSTER_STRING = '2.0.3';
    protected const EMBED_TEMPLATE = '<iframe src="%s" width=":w" height=":h" frameborder="0" allowfullscreen="allowfullscreen" allow="geolocation *; microphone *; camera *; midi *; encrypted-media *" title="%s"></iframe>';

    public function __construct(
        protected H5PAdapterInterface $adapter,
        public H5PCore $h5pCore,
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
    ) {
        $this->adapter->setConfig($this);

        $url = request()->getSchemeAndHttpHost() . request()->getBasePath();
        $locale = Session::get('locale', config('app.fallback_locale'));

        $this->config = [
            'baseUrl' => $url,
            'url' => $this->h5pCore->fs->getDisplayPath(false),
            'postUserStatistics' => false,
            'ajaxPath' => '/ajax?action=',
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
            'libraryUrl' => '/h5p-php-library/js',
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
                'mail' => $this->userEmail ?? false,
            ];
        }

        if (!empty($this->userId)) {
            $this->config['postUserStatistics'] = true;
            unset($this->config['siteUrl']);
        } else {
            $this->config['postUserStatistics'] = false;
            $this->config['siteUrl'] = request()->getSchemeAndHttpHost() . request()->getBasePath();
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

    public function getH5PCore(): H5PCore
    {
        return $this->h5pCore;
    }

    public function setUserId(string|bool|null $id): static
    {
        $this->userId = $id;

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

    public function setRedirectToken(string|null $token): static
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
        foreach (H5peditor::$styles as $style) {
            $editorStyles[] = $this->getAssetUrl("editor", $style);
        }
        $editorStyles[] = '//fonts.googleapis.com/css?family=Lato:400,700';

        $editorScripts = [];

        foreach (H5PCore::$scripts as $script) {
            $editorScripts[] = $this->getAssetUrl("core", $script);
        }

        foreach ($this->getEditorScripts() as $script) {
            $editorScripts[] = $this->getAssetUrl("editor", $script);
        }

        $customScripts = array_map(
            fn($script) => $this->getAssetUrl(null, $script),
            $this->getCustomEditorScripts(),
        );

        return (object) [
            'css' => array_merge($editorStyles, $this->adapter->getEditorCss()),
            'js' => array_merge($editorScripts, $customScripts),
        ];
    }

    protected function addCoreAssets(): void
    {
        $coreAssets = array_merge(H5PCore::$scripts, ["js/h5p-display-options.js"]);
        foreach ($coreAssets as $script) {
            $scriptPath = $this->getAssetUrl("core", $script);
            if (!in_array($scriptPath, $this->getScriptAssets())) {
                $this->addAsset("scripts", $scriptPath);
                $this->config['core']['scripts'][] = $scriptPath;
            }
        }

        foreach (H5PCore::$styles as $style) {
            $stylePath = $this->getAssetUrl("core", $style);
            if (!in_array($stylePath, $this->getStyleAssets())) {
                $this->addAsset("styles", $stylePath);
                $this->config['core']['styles'][] = $stylePath;
            }
        }
    }

    protected function addDefaultEditorAssets(): void
    {
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

    /**
     * Language file to load for H5P Editor
     */
    private function getLanguage(): string
    {
        return "language/" . $this->resolveEditorLocale(Session::get('locale')) . ".js";
    }

    private function resolveEditorLocale($locale): string
    {
        if (is_string($locale)) {
            if (file_exists(base_path('vendor/h5p/h5p-editor/language/' . $locale . '.js'))) {
                return $locale;
            } elseif (strlen($locale) > 2) {
                $lang = Iso639p3::code2letters($locale);
                if (file_exists(base_path('vendor/h5p/h5p-editor/language/' . $lang . '.js'))) {
                    return $lang;
                }
            }
        }

        return 'en';
    }

    private function getL10n(): array
    {
        return [
            "H5P" => $this->h5pCore->getLocalization(),
        ];
    }

    private function getEditorScripts(): array
    {
        return array_diff(
            H5peditor::$scripts,
            [
                // TODO: Review why these are replaced
                // Replaced by js/h5pmetadata.js
                'scripts/h5peditor-metadata-author-widget.js',
                'scripts/h5peditor-metadata.js',
                'scripts/h5peditor-number.js',
                'scripts/h5peditor-select.js',
                'scripts/h5peditor-text.js',
                'scripts/h5peditor-textarea.js',
                'scripts/h5peditor-list.js',
                'scripts/h5peditor-list-editor.js',

                // Delay loading, added in addDefaultEditorAssets()
                'scripts/h5peditor-editor.js',
            ],
            $this->adapter->filterEditorScripts(),
        );
    }

    private function getCustomEditorScripts(): array
    {
        return array_merge(
            [
                (string) mix("js/h5pmetadata.js"),
                // Used by 'Update content' functionality, upgrading to a newer version of the content type, in Editor
                '/js/editor-setup.js',
            ],
            $this->adapter->getCustomEditorScripts(),
        );
    }
}
