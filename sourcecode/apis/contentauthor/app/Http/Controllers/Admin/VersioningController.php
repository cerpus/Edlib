<?php

namespace App\Http\Controllers\Admin;

use App\Content;
use App\ContentVersions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class VersioningController extends Controller
{
    public function index(Request $request)
    {
        $isContentVersioned = true;
        $versionData = collect();
        $contentId = $request->input('contentId');
        if ($contentId) {
            $content = Content::findContentById($contentId);
            if (!empty($content->version_id)) {
                $this->traverseVersion($content->getVersion(), $versionData);
            } elseif (!empty($content)) {
                $isContentVersioned = false;
            }
        }

        return view('admin.support.versioning')->with([
            'contentId' => $contentId,
            'versionData' => $versionData->isNotEmpty() ? $versionData->reverse() : null,
            'isContentVersioned' => $isContentVersioned,
        ]);
    }

    /**
     * @return Collection
     */
    private function traverseVersion(ContentVersions $versionData, Collection $stack, $getChildren = true)
    {
        $versionArray = [
            'version' => $versionData->toArray(),
        ];
        $versionArray['version']['created_at'] = $versionData->created_at->format('Y-m-d H:i:s.u e');
        $content = $versionData->getContent();
        if (!empty($content)) {
            $versionArray['content'] = [
                'title' => $content->title,
                'created' => $content->created_at->format('Y-m-d H:i:s e'),
                'contentType' => $content->getContentType(),
            ];
            if ($versionArray['version']['content_type'] === Content::TYPE_H5P) {
                if ($content->library_id) {
                    $versionArray['content']['library'] = $content->library->getLibraryString(true);
                } else {
                    $versionArray['content']['library'] = '';
                }
            }
        }
        $versionArray['parent'] = $versionData->getPreviousVersion()?->content_id;

        /** @var \Illuminate\Database\Eloquent\Collection<ContentVersions> $children */
        $children = $versionData->getNextVersions();
        $versionArray['children'] = [];
        if ($children->isNotEmpty()) {
            foreach ($children as $child) {
                if ($getChildren) {
                    $this->traverseVersion($child, $stack);
                }
                $versionArray['children'][] = $child->content_id;
            }
        }
        if (!$stack->has($versionData->content_id)) {
            $stack->put($versionData->content_id, $versionArray);
        }

        return $stack;
    }
}
