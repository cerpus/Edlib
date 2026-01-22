<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use App\Http\Requests\DeepLinkingReturnRequest;
use App\Http\Requests\NdlaLegacy\SelectRequest;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\User;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemsMapperInterface;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

use function response;
use function route;
use function str_replace;
use function url;

/**
 * @deprecated
 */
final readonly class SelectController
{
    public function select(Request $request, Encrypter $encrypter): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        $url = url()->temporarySignedRoute('ndla-legacy.select-iframe', 30, [
            'user' => $encrypter->encrypt([
                'name' => $user->name,
                'email' => $user->email,
            ]),
            'admin' => $user->admin,
            'deep_link' => $request->boolean('canReturnResources'),
            'locale' => str_replace('-', '_', $request->input('locale', '')),
        ]);

        return response()->json(['url' => $url]);
    }

    public function selectByUrl(Request $request, Encrypter $encrypter, NdlaLegacyConfig $config): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        $resourceId = $config->extractEdlib2IdFromUrl($request->input('url', ''));
        if ($resourceId === null) {
            abort(404, 'No Edlib 2 ID');
        }

        $contentId = Content::firstWithEdlib2UsageIdOrFail($resourceId)->id;

        $url = url()->temporarySignedRoute('ndla-legacy.select-iframe', 30, [
            'user' => $encrypter->encrypt([
                'name' => $user->name,
                'email' => $user->email,
            ]),
            'admin' => $user->admin,
            'content_id' => $contentId,
            'locale' => str_replace('-', '_', $request->input('locale', '')),
        ]);

        return response()->json(['url' => $url]);
    }

    public function selectIframe(
        SelectRequest $request,
        NdlaLegacyConfig $config,
        SignerInterface $signer,
    ): Response {
        $credentials = $config->getInternalLtiPlatform()
            ->getOauth1Credentials();

        $locale = $request->validated('locale');
        $admin = $request->safe()->boolean('admin');
        $contentId = $request->validated('content_id');

        if ($contentId !== null) {
            $deepLink = true;
            $ltiUrl = route('lti.content', [$contentId]);
        } else {
            $deepLink = $request->safe()->boolean('deep_link');
            $ltiUrl = route('lti.select');
        }

        $csrfToken = 'csrf_' . Str::random();
        $request->session()->put($csrfToken, true);

        $params = [
            'accept_media_types' => 'application/vnd.ims.lti.v1.ltilink',
            'accept_presentation_document_targets' => 'iframe',
            'data' => $csrfToken,
            'lti_message_type' => $deepLink ? 'ContentItemSelectionRequest' : 'basic-lti-launch-request',
            'lis_person_name_full' => $request->validated('user.name'),
            'lis_person_contact_email_primary' => $request->validated('user.email'),
            'lti_version' => 'LTI-1p0',
            ...($admin ? ['roles' => 'Administrator'] : []),
            ...($deepLink ? ['content_item_return_url' => route('ndla-legacy.select-return')] : []),
            ...($locale ? ['launch_presentation_locale' => $locale] : []),
        ];

        $launch = $signer->sign(
            new Oauth1Request('POST', $ltiUrl, $params),
            $credentials,
        );

        return response()->view('lti.redirect', [
            'url' => $launch->getUrl(),
            'method' => $launch->getMethod(),
            'parameters' => $launch->toArray(),
        ]);
    }

    public function return(
        DeepLinkingReturnRequest $request,
        ContentItemsMapperInterface $mapper,
    ): Response {
        $csrfToken = $request->input('data', '');

        if (
            !str_starts_with($csrfToken, 'csrf_') ||
            !$request->session()->pull($csrfToken)
        ) {
            abort(400, 'Missing or invalid CSRF token');
        }

        $item = $mapper->map($request->input('content_items'))[0] ?? null;
        assert($item instanceof EdlibLtiLinkItem || $item === null);

        if (!$item) {
            return response()->view('ndla-legacy.close');
        }

        $content = Content::whereHas(
            'versions',
            function (Builder $query) use ($item) {
                /** @var Builder<ContentVersion> $query */
                $query->where('id', $item->getEdlibVersionId());
            },
        )->firstOrFail();

        $usage = $content->edlib2Usages()->firstOrCreate();

        return response()->view('ndla-legacy.return', [
            'type' => 'h5p',
            'embed_id' => $usage->edlib2_usage_id,
            'oembed_url' => route('ndla-legacy.oembed', [
                'url' => route('ndla-legacy.resource', [$usage->edlib2_usage_id]),
            ]),
        ]);
    }
}
