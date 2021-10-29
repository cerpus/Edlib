<?php
namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use App\Models\ContentLicense;
use App\Http\Controllers\ContentController;

class SiteContentController extends Controller
{

    /**
     * Create new content
     *
     * @param Request $request
     * @param $site_id
     * @return array
     */
    public function store(Request $request, $site_id)
    {
        $request->merge(['site' => $site_id]);
        $contentController = new ContentController();

        return $contentController->addContent($request);
    }

    /**
     * Add license to content
     * @param Request $request
     * @param $site_id
     * @param $content_id
     * @return array|\Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function update(Request $request, $site_id, $content_id)
    {
        $content = $this->getContent($request, $site_id, $content_id);
        if (!is_a($content, 'App\Models\Content')) {
            return $content;
        }

        $license_id = $request->input('license_id');

        foreach ($content->licenses as $license) {
            if ($license->license_id == $license_id) {
                if ($request->isJson() || $request->ajax()) {
                    return response()->json(['message' => 'Conflict'], 409);
                }
                return response('Conflict', 409);
            }
        }

        $content->touch();

        $license = new ContentLicense();
        $license->license_id = $license_id;
        $content->licenses()->save($license);

        return (new ContentController)->getContentById($content->id);
    }

    /**
     * Show a piece of content
     *
     * @param Request $request
     * @param $site_id
     * @param $content_id
     * @return array|\Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request, $site_id, $content_id)
    {
        $content = $this->getContent($request, $site_id, $content_id);
        if (!is_a($content, 'App\Models\Content')) {
            return $content;
        }

        return (new ContentController)->getContentById($content->id);
    }

    /**
     * Remove license from content
     *
     * @param Request $request
     * @param $site_id
     * @param $content_id
     * @return array|\Laravel\Lumen\Http\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, $site_id, $content_id)
    {
        $content = $this->getContent($request, $site_id, $content_id);
        if (!is_a($content, 'App\Models\Content')) {
            return $content;
        }

        $license_id = $request->input('license_id');

        foreach ($content->licenses as $license) {
            if ($license->license_id == $license_id) {
                $content->touch();

                $license->delete();
            }
        }

        return (new ContentController)->getContentById($content->id);
    }

    protected function getContent(Request $request, $site_id, $content_id)
    {
        $content = Content::where('content_id', $content_id)
            ->where('site', $site_id)
            ->with('licenses')
            ->first();

        if (is_null($content)) {
            if ($request->isJson() || $request->ajax()) {
                return response()->json(['message' => 'Not found'], 404);
            }
            return response("Not found", 404);
        }

        return $content;
    }

    public function getMultipleContent(Request $request, $site_id)
    {
        $this->validate($request, [
            'content_ids' => 'required|array|min:1',
        ]);

        return response()->json(Content::whereIn('content_id', $request->input("content_ids", []))
            ->where('site', $site_id)
            ->with('licenses')
            ->get()
        );
    }
}
