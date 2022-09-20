<?php

namespace App\Http\Controllers\Admin;

use App\Content;
use App\Http\Controllers\Controller;
use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\VersionData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class VersioningController extends Controller
{
    private $versionData;

    public function index(Request $request)
    {
        $isContentVersioned = true;
        $this->versionData = collect();
        $contentId = $request->input('contentId');
        if ($contentId) {
            $content = Content::findContentById($contentId);
            if (!empty($content->version_id)) {
                /** @var VersionClient $versionClient */
                $versionClient = resolve(VersionClient::class);
                /** @var VersionData $versionData */
                $versionData = $versionClient->getVersion($content->version_id);
                $this->traverseVersion($versionData, $this->versionData);
            } elseif (!empty($content)) {
                $isContentVersioned = false;
            }
        }

        return view('admin.support.versioning')->with([
            'contentId' => $contentId,
            'versionData' => $this->versionData->isNotEmpty() ? $this->versionData : null,
            'isContentVersioned' => $isContentVersioned,
        ]);
    }

    /**
     * @return Collection
     */
    private function traverseVersion(VersionData $versionData, Collection $stack)
    {
        $versionArray = $versionData->toArray();
        $versionArray['versionCreatedAtRaw'] = $versionData->getCreatedAt();
        $versionArray['versionCreatedAtFormatted'] = Carbon::createFromTimestampMs($versionData->getCreatedAt())->toIso8601String();
        $content = Content::findContentById($versionData->getExternalReference());
        if (!empty($content)) {
            $versionArray['content'] = [
                'title' => $content->title,
                'created' => $content->created_at->toIso8601String(),
                'update' => $content->updated_at->toIso8601String(),
                'version_id' => $content->version_id,
                'isDraftState' => !$content->isPublished(),
                'isPublished' => !$content->isListed(),
                'contentType' => $content->getContentType(),
            ];
        }
        if ($versionData->getParent()) {
            $parent = collect();
            $this->traverseVersion($versionData->getParent(), $parent);
            $versionArray['parent'] = $parent;
        }
        if ($versionData->getChildren()) {
            $children = collect();
            foreach ($versionData->getChildren() as $child) {
                $this->traverseVersion($child, $children);
            }
            $versionArray['children'] = $children;
        }

        $stack->push($versionArray);

        return $stack;
    }
}
