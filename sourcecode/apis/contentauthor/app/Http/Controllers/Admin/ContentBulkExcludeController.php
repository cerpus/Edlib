<?php

namespace App\Http\Controllers\Admin;

use App\ContentBulkExclude;
use App\H5PContent;
use App\Http\Controllers\Controller;
use App\Lti\LtiRequest;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use GuzzleHttp\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class ContentBulkExcludeController extends Controller
{
    public function index(): View {
        return view('admin.bulkexclude.index')->with([
            'contents' => $this->excludedContent()->paginate(50),
            'activeTab' => 'tabExcluded',
        ]);
    }

    public function find(Request $request): View {
        $searchContentId = trim($request->get('contentId'));
        $searchTitle = trim($request->get('title'));
        $searchHub = trim($request->get('hubId') );
        $results = null;

        $params = [
            'activeTab' => 'tabFind',
            'searchParams' => [
                'contentId' => '',
                'hubId' => '',
                'title' => '',
            ],
            'message' => null,
            'contents' => $this->excludedContent()->paginate(50),
        ];

        if ($searchContentId) {
            $params['searchParams']['contentId'] = $searchContentId;
            $items = collect();
            $search = H5PContent::where('id', $searchContentId)->get();
            if ($search->isNotEmpty()) {
                $item = $search->first();
                $latest = $item->versions()->latest()->first()->latestLeafVersion()->getContent();
                if ($latest) {
                    $items->push($latest);
                } else {
                    $items->push($item);
                }
            }
            $results = new LengthAwarePaginator($items, $items->count(), 25, 1, options: [
                'activeTab' => 'tabFind',
                'title' => $searchTitle,
            ]);

        } elseif ($searchHub) {
            $params['searchParams']['hubId'] = $searchHub;
            $items = collect();
            $data = $this->getHubInfo($searchHub);

            if (is_array($data) && array_key_exists('lti_launch_url', $data)) {
                // We should now have the launch URL to the latest version, according to Hub versioning
                $route = Route::getRoutes()->match(request()->create($data['lti_launch_url'], 'POST'));
                if ($route && $route->getName() === 'h5p.ltishow') {
                    $contentId = $route->parameter('id');
                    $contents = H5PContent::where('id', $contentId)->get();
                    if ($contents->isNotEmpty()) {
                        $content = $contents->first();
                        $items->push($content);
                    }
                } else {
                    $params['message'] = 'Not a valid URL for finding content';
                }
            } else {
                $params['message'] = 'No data found for url or id';
            }
            $results = new LengthAwarePaginator($items, $items->count(), 25, 1, options: [
                'activeTab' => 'tabFind',
                'title' => $searchTitle,
            ]);
        } elseif ($searchTitle) {
            $params['searchParams']['title'] = $searchTitle;

            $results = H5PContent::select(['h5p_contents.*'])
                ->with(['library', 'exclutions', 'metadata'])
                ->leftJoin('content_versions', 'content_versions.id', '=', 'h5p_contents.version_id')
                ->leftJoin('content_versions as cv', 'cv.parent_id', '=', 'content_versions.id')
                ->where('h5p_contents.title', 'like', '%'.$searchTitle.'%')
                ->whereNull('cv.id')
                ->orderBy('h5p_contents.id')
                ->paginate(25);

            $results->appends([
                'title' => $searchTitle,
            ]);
        }

        $params['results'] = $results;

        return view('admin.bulkexclude.index')->with($params);
    }

    public function add(Request $request): RedirectResponse
    {
        $selected = $request->get('contentIds');
        $exclude_from = $request->get('excludeFrom');
        foreach ($selected as $contentId) {
            $row = new ContentBulkExclude();
            $row->content_id = $contentId;
            $row->exclude_from = $exclude_from;
            $row->user_id = Session::get('authId');
            try {
                $row->save();
            } catch (UniqueConstraintViolationException) {
                // Already added, just ignore and continue
            }
        }

        return redirect()->route('admin.bulkexclude.content.index');
    }

    public function delete(Request $request): RedirectResponse
    {
        $selected = $request->get('excludeIds', []);
        if (count($selected)) {
            DB::table('content_bulk_excludes')->whereIn('id', $selected)->delete();
        }
        return redirect()->route('admin.bulkexclude.content.index');
    }

    /**
     * Request info from Hub
     */
    private function getHubInfo(string $searchString)
    {
        $requestData = [];
        if (filter_var($searchString, FILTER_VALIDATE_URL)) {
            $requestData['content_url'] = $searchString;
        } else {
            $requestData['content_or_version_id'] = $searchString;
        }

        /** @var LtiRequest $ltiRequest */
        $ltiRequest = Session::get('lti_requests.admin');
        $requestUrl = $ltiRequest->param('ext_edlib3_content_info_endpoint');

        $infoRequest = new Oauth1Request('POST', $requestUrl, $requestData);

        $signer = app(SignerInterface::class);
        $infoRequest = $signer->sign(
            $infoRequest,
            new Credentials(config('app.consumer-key'), config('app.consumer-secret')),
        );

        try {
            $client = app(Client::class);
            $response = $client->post($requestUrl, [
                'form_params' => $infoRequest->toArray(),
            ]);

            return json_decode($response->getBody()->getContents(), associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': ' . $e->getMessage(), ['searchstring' => $searchString]);
            return null;
        }
    }

    private function excludedContent()
    {
        return ContentBulkExclude::
            with(['content', 'content.library', 'content.metadata'])
            ->orderBy('content_id');
    }
}
