<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use App\Http\Requests\DeepLinkingReturnRequest;
use App\Http\Requests\NdlaLegacy\SelectRequest;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiPlatform;
use App\Models\Tag;
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

    public function selectIframe(
        SelectRequest $request,
        NdlaLegacyConfig $config,
        SignerInterface $signer,
    ): Response {
        $credentials = LtiPlatform::where('key', $config->getInternalLtiPlatformKey())
            ->firstOrFail()
            ->getOauth1Credentials();

        $locale = $request->validated('locale');
        $admin = $request->safe()->boolean('admin');
        $deepLink = $request->safe()->boolean('deep_link');

        $csrfToken = 'csrf_' . Str::random();
        $request->session()->put($csrfToken, true);

        $params = [
            'accept_media_types' => 'application/vnd.ims.lti.v1.ltilink',
            'accept_presentation_document_targets' => 'iframe',
            'data' => $csrfToken,
            'lti_message_type' => 'ContentItemSelectionRequest',
            'lis_person_name_full' => $request->validated('user.name'),
            'lis_person_contact_email_primary' => $request->validated('user.email'),
            'lti_version' => 'LTI-1p0',
            ...($admin ? ['roles' => 'Administrator'] : []),
            ...($deepLink ? ['content_item_return_url' => route('ndla-legacy.select-return')] : []),
            ...($locale ? ['launch_presentation_locale' => $locale] : []),
        ];

        $launch = $signer->sign(
            new Oauth1Request('POST', route('lti.select'), $params),
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

        // TODO: should we create a new usage every time?
        $tag = $content->tags()
            ->where('prefix', 'edlib2_usage_id')
            ->firstOr(function () use ($content) {
                $tag = Tag::findOrCreateFromString('edlib2_usage_id:' . Str::uuid());
                $content->tags()->attach($tag);

                return $tag;
            });
        assert($tag instanceof Tag);

        return response()->view('ndla-legacy.return', [
            'type' => 'h5p',
            'embed_id' => $tag->name,
            'oembed_url' => route('ndla-legacy.oembed', [$tag->name]),
        ]);
    }
}
