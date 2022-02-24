<?php

namespace App\Libraries\H5P;

use App\Content;
use App\Rules\canPublishContent;
use App\Rules\LicenseContent;
use App\Rules\shareContent;
use Illuminate\Support\Facades\DB;
use App\H5PContent;
use App\Libraries\H5P\Interfaces\ConfigInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class h5p
{

    private $scripts;
    private $styles;
    private $settings = array();

    public $pdo;
    private $plugin;

    private $isValidated = false;
    private $errorMessage;

    private static $h5pcore;

    private $userId;

    private $editorBasePath;

    private $editorFilesDir;

    private $directoryFiles;

    public function __construct(\PDO $pdo = null, H5Plugin $h5Plugin = null)
    {
        $this->pdo = $pdo;
        if (is_null($this->pdo)) {
            $this->pdo = DB::connection()->getPdo();
        }
        if (is_null($h5Plugin)) {
            $this->plugin = H5Plugin::get_instance($this->pdo);
        }
    }

    public static function setUp()
    {
        self::$h5pcore = null;
    }

    /**
     * @return string
     */
    public function getEditorBasePath()
    {
        $plugin = $this->plugin;
        return !is_null($this->editorBasePath) ? $this->editorBasePath : $plugin->getPath();
    }

    /**
     * @param string $editorBasePath
     */
    public function setEditorBasePath($editorBasePath)
    {
        $this->editorBasePath = $editorBasePath;
    }

    /**
     * @return string
     */
    public function getEditorFilesDir()
    {
        $plugin = $this->plugin;
        return !is_null($this->editorFilesDir) ? $this->editorFilesDir : $plugin->getPath();
    }

    /**
     * @param string $editorFilesDir
     */
    public function setEditorFilesDir($editorFilesDir)
    {
        $this->editorFilesDir = $editorFilesDir;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getH5pCore()
    {
        if (is_null(self::$h5pcore)) {
            self::$h5pcore = resolve(\H5PCore::class);
        }

        return self::$h5pcore;
    }

    public function init(ConfigInterface $config)
    {
        $config->setH5pPlugin($this->plugin);
        $config->h5pCore = $config->getH5PCore() ?? $this->getH5pCore();
        if (!empty($config->id)) {
            $config->setContent($this->getContents($config->id));
        }
        $this->settings = $config->getConfig();
        $this->scripts = $config->assets['scripts'];
        $this->styles = $config->assets['styles'];
    }

    public function getSettings($settingsName = "H5PIntegration")
    {
        return "<script>$settingsName = " . json_encode($this->settings) . "</script>";
    }

    public function getContents($id)
    {
        return $this->getH5pCore()->loadContent($id);
    }

    public function getScripts($objectsToArray = true)
    {
        return $objectsToArray === true ? $this->objectsToArray($this->scripts) : $this->scripts;
    }

    public function getStyles($objectsToArray = true)
    {
        return $objectsToArray === true ? $this->objectsToArray($this->styles) : $this->styles;
    }

    private function objectsToArray($array)
    {
        $res = [];
        foreach ($array as $a) {
            $res[] = is_object($a) ? $a->path : $a;
        }
        return array_values($res);
    }

    public function validateStoreInput(Request $request, Content $content)
    {
        $inputFields = $request->all();
        $this->setIsValidated(false);
        $validator = Validator::make($inputFields, [
            'title' => 'required|string|min:1|max:255',
            'libraryid' => 'nullable|sometimes|exists:h5p_libraries,id',
            'library' => 'required_without:libraryid|string',
            'parameters' => 'required|json',
            'language_iso_639_3' => 'nullable|string|min:3|max:3',
            'isNewLanguageVariant' => 'nullable|boolean',
            'isPublished' => [Rule::requiredIf($content::isDraftLogicEnabled()), 'boolean', new canPublishContent($content, $request, 'publish')],
            'share' => ['sometimes', new shareContent(), new canPublishContent($content, $request, 'list')],
            'license' => [Rule::requiredIf($request->input('share') === 'share'), config('app.enable_licensing') ? 'string' : 'nullable', app(LicenseContent::class)],
        ]);

        if ($validator->fails()) {
            $this->errorMessage = $validator->messages()->first();
            return false;
        }
        $this->setIsValidated(true);
        return true;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getIsValidated()
    {
        return $this->isValidated;
    }

    public function setIsValidated($validated)
    {
        $this->isValidated = (bool)$validated;
    }

    public function storeContent(Request $request, $content = null)
    {
        if ($this->getIsValidated() !== true) {
            throw new \Exception("Content must be validated before storing");
        }

        /** @var \H5PCore $core */
        $core = $this->getH5pCore();

        $oldLibrary = null;
        $oldParams = null;
        if ($content !== null) {
            if (!empty($content['useVersioning']) && $content['useVersioning'] === true) {
                $content['parent_content_id'] = $content['id'];
                unset($content['id']);
            }
            $oldLibrary = $content['library'];
            $oldParams = json_decode($content['params']);
        } else {
            $content = array(
                'disable' => $core::DISABLE_NONE,
                'user_id' => $this->userId
            );
        }

        if (empty($content['user_id'])) {
            $content['user_id'] = $this->userId;
        }

        // Get library
        $content['library'] = $core->libraryFromString($request->get('library'));
        // Check if library exists.
        $content['library']['libraryId'] = $core->h5pF->getLibraryId($content['library']['machineName'],
            $content['library']['majorVersion'], $content['library']['minorVersion']);

        // Get title
        $content['title'] = $request->get("title");
        $content['params'] = $request->get('parameters');
        $content['is_published'] = Content::isDraftLogicEnabled() ? $request->input('isPublished', 1) : 1;
        $content['language_iso_639_3'] = $request->get('language_iso_639_3');
        $content['license'] = $request->get('license');

        $params = json_decode($content['params']);
        if (isset($params->params) && isset($params->metadata)) {
            $content['metadata'] = $params->metadata;
            $content['params'] = json_encode($params->params);
            $params = $params->params;
        } else {
            $content['metadata'] = [];
        }

        $content['embed_type'] = empty($library['embedTypes']) ? 'div' : $library['embedTypes'];
        $content['slug'] = \H5PCore::slugify($content['title']);

        $content['max_score'] = $request->get('max_score', 0);
        // Set disabled features
        $this->get_disabled_content_features($core, $content);

        $content['id'] = $core->saveContent($content);
        $editor = resolve(\H5peditor::class);
        if (!empty($content['parent_content_id'])) {
            $this->copyContentFromParent($content['id'], $content['parent_content_id']);
            $editor->processParameters($content['parent_content_id'], $oldLibrary, $oldParams, $oldLibrary, $params);
        }
        // Move images and find all content dependencies
        $editor->processParameters($content['id'], $content['library'], $params, $oldLibrary, $oldParams);

        return $content;
    }

    public function storeVersionContent($contentId, $versionId)
    {
        $h5pContent = H5PContent::findOrFail($contentId);
        $h5pContent->version_id = $versionId;
        $h5pContent->save();
    }

    private function copyContentFromParent($contentId, $parentId)
    {
        /** @var \H5PStorage $storage */
        $storage = resolve(\H5PStorage::class);
        $storage->copyPackage($contentId, $parentId);

        return true;
    }

    public function slugify($str, $replace = array(), $delimiter = '-')
    {

        if (!empty($replace)) {
            $str = str_replace((array)$replace, ' ', $str);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return substr($clean, 0, 127); // Limit in table
    }

    /**
     * Extract disabled content features from input post.
     *
     * @param \H5PCore $core
     * @param int $current
     * @return int
     * @since 1.2.0
     */
    private function get_disabled_content_features($core, &$content)
    {
        $set = array(
            $core::DISPLAY_OPTION_FRAME => filter_input(INPUT_POST, 'frame', FILTER_VALIDATE_BOOLEAN),
            $core::DISPLAY_OPTION_DOWNLOAD => filter_input(INPUT_POST, 'download', FILTER_VALIDATE_BOOLEAN),
            $core::DISPLAY_OPTION_EMBED => filter_input(INPUT_POST, 'embed', FILTER_VALIDATE_BOOLEAN),
            $core::DISPLAY_OPTION_COPYRIGHT => filter_input(INPUT_POST, 'copyright', FILTER_VALIDATE_BOOLEAN),
        );
        $content['disable'] = $core->getStorableDisplayOptions($set, $content['disable']);
    }
}
