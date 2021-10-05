<?php

namespace App\Http\Controllers;

use App\Http\Libraries\ContentTypes\ArticleContentType;
use App\Http\Libraries\ContentTypes\ContentType;
use App\Http\Libraries\ContentTypes\ContentTypeInterface;
use App\Http\Libraries\ContentTypes\EmbedContentType;
use App\Http\Libraries\ContentTypes\InteractivityContentType;
use App\Http\Libraries\ContentTypes\LinkContentType;
use App\Http\Libraries\ContentTypes\H5PContentType;
use App\Http\Libraries\ContentTypes\QuestionsContentType;
use App\Http\Libraries\ContentTypes\TextContentType;
use App\Http\Libraries\ContentTypes\VideoContentType;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param H5PAdapterInterface $adapter
     * @param null $contentType
     * @return Application|\Illuminate\Contracts\View\Factory|RedirectResponse|Redirector|\Illuminate\View\View
     */
    public function index(Request $request, H5PAdapterInterface $adapter, $contentType = null)
    {
        if (config('h5p.isHubEnabled') === true) {
            return redirect(route('h5p.create'));
        }

        $contentTypes = $this->getActiveContentTypes()
            ->map(function ($contentTypeName) use ($request) {
                /** @var ContentTypeInterface $currentContentType */
                $currentContentType = resolve($contentTypeName);
                return $currentContentType->getContentTypes($request->get('redirectToken'));
            });

        $jwtTokenInfo = Session::get('jwtToken', null);
        $jwtToken = $jwtTokenInfo && isset($jwtTokenInfo['raw']) ? $jwtTokenInfo['raw'] : null;
        $adapterModes = json_encode($adapter::getAllAdapters());
        $currentAdapter = $adapter->getAdapterName();

        if (!is_null($contentType)) {
            $route = $contentTypes->map(function (ContentType $currentType) use ($contentType) {
                if (Str::lower($contentType) === Str::lower($currentType->mainContentType)) {
                    return $currentType->createUrl;
                } elseif (array_key_exists($contentType, $currentType->getSubContentTypes())) {
                    return $currentType->getSubContentTypes()[$contentType];
                }
                return null;
            })
                ->filter()
                ->first();
            if (!empty($route)) {
                return redirect($route);
            }
        }

        $contentTypes = $contentTypes
            ->sortBy(function ($contenttype) {
                return strtolower($contenttype->title);
            })
            ->values()
            ->all();

        $view = sprintf("content.%s", config('author.contentTypeSelector'));
        return view($view)->with(compact('contentTypes', 'jwtToken', 'adapterModes', 'currentAdapter'));
    }

    /**
     * @return Collection
     */
    private function getActiveContentTypes()
    {
        switch (config('author.contentTypeSelector')) {
            case "buttons":
                $activeContentTypes = collect([
                    VideoContentType::class,
                    TextContentType::class,
                    InteractivityContentType::class,
                    QuestionsContentType::class,
                ]);

                if (config('feature.use-add-link-resource') === true) {
                    $activeContentTypes->push(EmbedContentType::class);
                }
                break;
            case "grid":
            default:
                $activeContentTypes = collect([
                    ArticleContentType::class,
                    H5PContentType::class,
                    LinkContentType::class,
                ]);
        }

        return $activeContentTypes;
    }


}
