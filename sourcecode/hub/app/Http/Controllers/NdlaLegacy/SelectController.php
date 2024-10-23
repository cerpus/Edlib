<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use App\Http\Requests\DeepLinkingReturnRequest;
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
            'locale' => str_replace('-', '_', $request->input('locale', '')),
            'deep_link' => $request->input('canReturnResources', 'false'),
        ]);

        return response()->json(['url' => $url]);
    }

    public function selectIframe(
        Request $request,
        NdlaLegacyConfig $config,
        SignerInterface $signer,
        Encrypter $encrypter,
    ): Response {
        $credentials = LtiPlatform::where('key', $config->getInternalLtiPlatformKey())
            ->firstOrFail()
            ->getOauth1Credentials();

        ['name' => $name, 'email' => $email] = $encrypter->decrypt($request->input('user'));
        $locale = $request->input('locale');
        // TODO: do something with this
        //$deepLink = $request->boolean('deep_link');

        $csrfToken = 'csrf_' . Str::random();
        $request->session()->put($csrfToken, true);

        $launch = $signer->sign(new Oauth1Request('POST', route('lti.select'), [
            'accept_media_types' => 'application/vnd.ims.lti.v1.ltilink',
            'accept_presentation_document_targets' => 'iframe',
            'content_item_return_url' => route('ndla-legacy.select-return'),
            'data' => $csrfToken,
            ...($locale ? ['launch_presentation_locale' => $locale] : []),
            'lis_person_name_full' => $name,
            'lis_person_contact_email_primary' => $email,
            'lti_message_type' => 'ContentItemSelectionRequest',
        ]), $credentials);

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
