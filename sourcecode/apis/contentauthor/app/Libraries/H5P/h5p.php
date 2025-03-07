<?php

declare(strict_types=1);

namespace App\Libraries\H5P;

use App\Content;
use App\Libraries\H5P\Dataobjects\H5PView;
use App\Libraries\H5P\Interfaces\ConfigInterface;
use H5PCore;
use H5peditor;
use H5PStorage;
use Illuminate\Http\Request;

class h5p
{
    public function __construct(
        private H5PCore $core,
        private H5PStorage $storage,
        private H5pEditor $editor,
    ) {}

    public function createView(ConfigInterface $config): H5PView
    {
        return new H5PView(
            $config->getScriptAssets(),
            $config->getStyleAssets(),
            $config->getConfig(),
        );
    }

    public function storeContent(Request $request, array|null $content, mixed $userId): array
    {
        $oldLibrary = null;
        $oldParams = null;
        if ($content !== null) {
            if ($content['useVersioning'] ?? false) {
                $content['parent_content_id'] = $content['id'];
                unset($content['id']);
            }
            $oldLibrary = $content['library'];
            $oldParams = json_decode($content['params']);
        } else {
            $content = [
                'disable' => H5PCore::DISABLE_NONE,
                'user_id' => $userId,
            ];
        }

        if (empty($content['user_id'])) {
            $content['user_id'] = $userId;
        }

        // Get library
        $content['library'] = $this->core->libraryFromString($request->get('library'));
        // Check if library exists.
        $content['library']['libraryId'] = $this->core->h5pF->getLibraryId(
            $content['library']['machineName'],
            $content['library']['majorVersion'],
            $content['library']['minorVersion'],
        );

        // Get title
        $content['title'] = $request->get("title");
        $content['params'] = $request->get('parameters');
        $content['is_draft'] = $request->input('isDraft');
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
        $content['slug'] = H5PCore::slugify($content['title']);

        $content['max_score'] = $request->get('max_score', 0);
        // Set disabled features
        $content['disable'] = $this->getDisabledContentFeatures($this->core, $content['disable'], $request);

        $content['id'] = $this->core->saveContent($content);
        if (!empty($content['parent_content_id'])) {
            $this->copyContentFromParent($content['id'], $content['parent_content_id']);
            $this->editor->processParameters($content['parent_content_id'], $oldLibrary, $oldParams, $oldLibrary, $params);
        }
        // Move images and find all content dependencies
        /** @noinspection PhpParamsInspection */
        $this->editor->processParameters($content['id'], $content['library'], $params, $oldLibrary, $oldParams);

        return $content;
    }

    private function copyContentFromParent($contentId, $parentId): void
    {
        $this->storage->copyPackage($contentId, $parentId);
    }

    /**
     * Extract disabled content features from input post.
     */
    private function getDisabledContentFeatures(
        H5PCore $core,
        int $current,
        Request $request,
    ): int {
        $set = [
            H5PCore::DISPLAY_OPTION_FRAME => $request->boolean('frame'),
            H5PCore::DISPLAY_OPTION_DOWNLOAD => $request->boolean('download'),
            H5PCore::DISPLAY_OPTION_EMBED => $request->boolean('embed'),
            H5PCore::DISPLAY_OPTION_COPYRIGHT => $request->boolean('copyright'),
        ];

        return $core->getStorableDisplayOptions($set, $current);
    }
}
