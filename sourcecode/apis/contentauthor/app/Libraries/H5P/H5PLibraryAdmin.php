<?php

namespace App\Libraries\H5P;

use App\Events\ResourceSaved;
use App\H5PContent;
use App\H5PContentsMetadata;
use App\H5PLibrary;
use App\Http\Controllers\H5P_Plugin_Admin;
use App\Libraries\H5P\Packages\QuestionSet;
use Illuminate\Http\Request;

class H5PLibraryAdmin
{
    const BULK_UNTOUCHED = 0;
    const BULK_UPDATED = 1;
    const BULK_FAILED = 2;

    /**
     * Handles upload of H5P libraries.
     *
     * @since 1.1.0
     */
    public function process_libraries()
    {
        $post = ($_SERVER['REQUEST_METHOD'] === 'POST');

        if ($post && isset($_FILES['h5p_file']) && $_FILES['h5p_file']['error'] === 0) {
            H5P_Plugin_Admin::handle_upload(null, filter_input(INPUT_POST, 'h5p_upgrade_only') ? true : false);
            return;
        }

        if ($post && isset($_FILES['h5p_file']) && $_FILES['h5p_file']['error']) {
            $phpFileUploadErrors = array(
                1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                3 => 'The uploaded file was only partially uploaded',
                4 => 'No file was uploaded',
                6 => 'Missing a temporary folder',
                7 => 'Failed to write file to disk.',
                8 => 'A PHP extension stopped the file upload.',
            );

            $errorMessage = $phpFileUploadErrors[$_FILES['h5p_file']['error']];
            echo $errorMessage;
            // TODO: H5P_Plugin_Admin::set_error(__($errorMessage, $this->plugin_slug));
            return;
        }
    }

    public function upgradeProgress(Request $request)
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::findOrFail(filter_input(INPUT_GET, 'id'));

        $out = new \stdClass();
        $out->params = array();
        $out->token = csrf_token();

        $params = filter_input(INPUT_POST, 'params');
        if ($params !== null) {
            if (!$request->filled('libraryId')) {
                throw new \HttpInvalidParamException("Missing library to update to");
            }

            collect(json_decode($params))
                ->each(function ($param, $id) use ($request) {
                    $params = json_decode($param);
                    if (isset($params->params)) {
                        $param = json_encode($params->params);
                    }
                    $content = H5PContent::findOrFail($id);
                    $content->library_id = $request->get('libraryId');
                    $content->parameters = $param;
                    $content->filtered = '';
                    if ($content->save() !== true) {
                        throw new \Exception("Update failed");
                    }

                    if (isset($params->metadata)) {
                        $metadata = \H5PMetadata::toDBArray((array)$params->metadata);
                        unset($metadata['title']);
                        /** @var H5PContentsMetadata $H5PContentMetadata */
                        $H5PContentMetadata = H5PContentsMetadata::firstOrNew([
                            'content_id' => $id
                        ]);
                        $H5PContentMetadata->fill($metadata);
                        $H5PContentMetadata->save();
                    }

                });
        }

        $out->left = $library->contents()->count();
        if ($out->left) {

            $contents = collect();
            $library
                ->contents()
                ->chunk(40, function ($contentsChunk) use ($contents) {
                    foreach ($contentsChunk as $content) {
                        $contents->push($content);
                    }
                    return false;
                });

            $out->params = $contents
                ->map(function ($content) {
                    $metadata = $content->metadata()->first();
                    if (is_null($metadata)) {
                        $metadata = H5PContentsMetadata::make([
                            'title' => $content->title,
                        ]);
                    }
                    $content->parameters = sprintf('{"params":%s,"metadata":%s}', $content->parameters, \H5PMetadata::toJSON($metadata));

                    return $content;
                })
                ->pluck('parameters', 'id')
                ->toArray();
        }

        return $out;
    }

    /**
     * @param Request $request
     * @return \stdClass
     * @throws \HttpInvalidParamException
     */
    public function upgradeMaxscore($libraries, $scores = null)
    {
        if (!is_array($libraries)) {
            $libraries = [$libraries];
        }

        $out = new \stdClass();
        $out->params = array();
        $out->token = csrf_token();

        $libraryVersions = H5PLibrary::whereIn('name', $libraries)
            ->get()
            ->pluck('name', 'id');

        if ($scores !== null) {
            collect(json_decode($scores))
                ->each(function ($scoreObject, $id) use ($libraryVersions) {
                    $content = H5PContent::findOrFail($id);
                    if (!$libraryVersions->has($content->library_id)) {
                        throw new \InvalidArgumentException("Library don't match");
                    }
                    $content->max_score = $scoreObject->score;
                    $content->bulk_calculated = empty($scoreObject->error) ? self::BULK_UPDATED : self::BULK_FAILED;
                    if ($content->save() !== true) {
                        throw new \Exception("Setting of score failed");
                    }
                    event(new ResourceSaved($content->getEdlibDataObject()));
                });
        }

        $contentsQuery = H5PContent::whereNull('max_score')
            ->whereIn('library_id', $libraryVersions->keys());
        if ($libraryVersions->contains(QuestionSet::$machineName)) {
            $contentsQuery->orWhere(function ($query) {
                $query->where('max_score', 0)
                    ->where('bulk_calculated', self::BULK_UNTOUCHED)
                    ->whereIn('library_id', function ($query){
                        $query->select('id')
                            ->from('h5p_libraries')
                            ->where('name', QuestionSet::$machineName);
                    });
            });
        }

        $out->left = $contentsQuery->count();
        if ($out->left) {
            $contents = $contentsQuery
                ->limit(200)
                ->get();

            $out->params = $contents
                ->mapWithKeys(function ($content) use ($libraryVersions) {
                    return [
                        $content->id => [
                            'id' => $content->id,
                            'library' => $libraryVersions->get($content->library_id),
                            'params' => $content->parameters,
                        ]];
                })
                ->toArray();
        }
        return $out;
    }

    /**
     *
     * @since 1.1.0
     * @param string $get_library
     *
     * @throws \Exception
     */
    public function upgradeLibrary(\H5PCore $core, $get_library = '')
    {
        $library_string = $get_library;

        if (!$library_string) {
            throw new \Exception('Error, missing library!');
        }

        $library_parts = explode('/', $library_string);
        if (count($library_parts) !== 4) {
            throw new \Exception('Error, invalid library!');
        }

        $library = (object)array(
            'name' => $library_parts[1],
            'version' => (object)array(
                'major' => $library_parts[2],
                'minor' => $library_parts[3]
            )
        );
        $library->semantics = $core->loadLibrarySemantics($library->name, $library->version->major, $library->version->minor);
        if ($library->semantics === null) {
            throw new \Exception('Error, could not library semantics!');
        }


        if (isset($dev_lib)) {
            $upgrades_script_path = $upgrades_script_url = $dev_lib['path'] . '/upgrades.js';
        } else {
            $upgrades_script_path = $core->fs->getUpgradeScript($library->name, $library->version->major, $library->version->minor);
        }

        if (!empty($upgrades_script_path)) {
            $library->upgradesScript = $core->fs->getDisplayPath() . $upgrades_script_path;
        }

        return $library;
    }
}
