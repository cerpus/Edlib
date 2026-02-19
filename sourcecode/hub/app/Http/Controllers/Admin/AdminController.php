<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RebuildContentIndex;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use App\EdlibResourceKit\Oauth1\Signer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

use function back;
use function response;
use function route;
use function trans;
use function view;

final class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'toolExtras' => LtiToolExtra::forAdmins()->get(),
        ]);
    }

    public function rebuildContentIndex(Dispatcher $dispatcher, Request $request): Response
    {
        $dispatcher->dispatch(new RebuildContentIndex());

        $request->session()
            ->flash('alert', trans('messages.alert-rebuilding-content-index'));

        if ($request->header('HX-Request')) {
            return response()->noContent()
                ->header('HX-Redirect', route('admin.index'));
        }

        return back()
            ->with('alert', trans('messages.alert-rebuilding-content-index'));
    }

    public function listDeletedContent(): View
    {
        return view('admin.contents.list-deleted')
            ->with('contents', Content::onlyTrashed()->orderBy('deleted_at', 'DESC')->paginate(25),
        );
    }

    /**
     * @throws \Throwable
     */
    public function restore(Request $request, Content $content): RedirectResponse
    {
        DB::transaction(fn () => $content->restore());

        $request->session()->flash('alert', 'Content was restored');

        return redirect()->route('content.version-details', [$content, $content->latestVersion]);
    }

    public function destroy(Request $request, Content $content): Response
    {
        try {
            DB::transaction(function () use ($content) {
                // All LTI Tool launch urls that are connected to the content we want to delete
                $launchUrls = $content->versions->pluck('lti_launch_url', 'id')->toArray();

                // The launch urls that are used by other content, these should not be deleted
                $otherUses = ContentVersion::select('id', 'lti_launch_url')
                    ->whereIn('lti_launch_url', array_values($launchUrls))
                    ->where('content_id', '<>', $content->id)
                    ->pluck('lti_launch_url', 'id')
                    ->toArray();

                $latestVersion = $content->latestVersion;
                $tool = $latestVersion?->tool;

                // Delete our local data
                $content->forceDelete();

                if ($tool && $tool->supports_destroy) {
                    // Request LTI Tool to delete data
                    $result = $this->requestResourceDestroy($tool, [
                        'delete' => array_values($launchUrls),
                        'shared' => array_values($otherUses),
                    ]);
                    if (!$result) {
                        throw new \Exception('LTI Tool destroy failed');
                    }
                } else {
                    Log::warning('LTI Tool used to create content was not found or does not support request', ['toolId' => $tool?->id, 'supportsDestroy' => $tool?->supports_destroy]);
                }
            });
            $result = true;
        } catch (\Throwable $e) {
            Log::error('Failed to delete content', ['message' => $e->getMessage(), 'type' => get_class($e), 'code' => $e->getCode()]);
            $result = false;
        }

        $request->session()->flash('alert', $result ? trans('messages.destroy-success') : trans('messages.destroy-failed'));
        return response()
            ->noContent()
            ->header('HX-Redirect', route('admin.content.deleted'));
    }

    /**
     * @param LtiTool $tool
     * @param array<string, array<array-key, string>> $resourceUrls
     * @return bool
     * @throws \Exception|GuzzleException|\JsonException
     */
    private function requestResourceDestroy(LtiTool $tool, array $resourceUrls): bool
    {
        if (!$tool->supports_destroy || count($resourceUrls) === 0) {
            return true;
        }

        $urlParts = parse_url($tool->creator_launch_url);
        $host = $urlParts['host'] ?? null;
        if ($urlParts === false || $host === null) {
            Log::error('Failed parsing tool url', ['url' => $tool->creator_launch_url]);
            return false;
        }
        $url = ($urlParts['scheme'] ?? 'https') . '://' . $host . '/v1/h5p/destroy';

        $signer = app(Signer::class);
        $json = json_encode([
            'user' => [
                'id' => auth()->user()?->id,
                'name' => auth()->user()?->name,
            ],
            'resources' => $resourceUrls,
        ], JSON_THROW_ON_ERROR);
        $deleteRequest = new \App\EdlibResourceKit\Oauth1\Request('DELETE', $url, [
            'oauth_body_hash' => base64_encode(sha1($json, true)),
        ]);

        $deleteRequest = $signer->sign(
            $deleteRequest,
            $tool->getOauth1Credentials()
        );

        $authHeader = sprintf(
            'OAuth realm="%s",oauth_version="%s",oauth_nonce="%s",oauth_timestamp="%s",oauth_consumer_key="%s",oauth_body_hash="%s",oauth_signature_method="%s",oauth_signature="%s"',
            $deleteRequest->getUrl(),
            $deleteRequest->get('oauth_version'),
            rawurlencode($deleteRequest->get('oauth_nonce')),
            $deleteRequest->get('oauth_timestamp'),
            $deleteRequest->get('oauth_consumer_key'),
            rawurlencode($deleteRequest->get('oauth_body_hash')),
            $deleteRequest->get('oauth_signature_method'),
            rawurlencode($deleteRequest->get('oauth_signature')),
        );
        $options = [
            'headers' => [
                'Authorization' => $authHeader,
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Length' => mb_strlen($json),
            ],
            'body' => $json,
        ];

        $httpClient = app(Client::class);
        $result = $httpClient->request('DELETE', $deleteRequest->getUrl(), $options)->getBody()->getContents();
        $data = json_decode($result, true, flags: JSON_THROW_ON_ERROR);

        return $data['success'];
    }
}
