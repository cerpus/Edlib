<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LTIRequest;
use App\Libraries\DataObjects\LtiContent;
use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializerInterface;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;
use Cerpus\EdlibResourceKit\Oauth1\Claim;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function assert;
use function http_build_query;
use function redirect;
use function unserialize;

final readonly class ReturnToCoreController
{
    public function __construct(
        private ContentItemsSerializerInterface $serializer,
        private SignerInterface $signer,
        private CredentialStoreInterface $credentials,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $ltiRequest = $this->getLtiRequest($request);
        $content = $this->getLtiContent($request);

        // Edlib 3: perform an LTI request
        if ($ltiRequest?->isContentItemSelectionRequest()) {
            // TODO: score, icons, license, etc.
            $item = new LtiLinkItem(title: $content->title, url: $content->url);

            $returnRequest = new Oauth1Request('POST', $ltiRequest->getReturnUrl(), [
                'content_items' => json_encode($this->serializer->serialize([$item])),
                'lti_message_type' => 'ContentItemSelection',
                'lti_version' => 'LTI-1p0',
            ]);

            if ($ltiRequest->has('data')) {
                $returnRequest = $returnRequest->with('data', $ltiRequest->get('data'));
            }

            $returnRequest = $this->signer->sign(
                $returnRequest,
                $this->credentials->findByKey($ltiRequest->get(Claim::CONSUMER_KEY)),
            );

            return response()
                ->view('lti-return', ['request' => $returnRequest]);
        }

        // Old Edlib: redirect with info in query
        if ($ltiRequest?->getReturnUrl()) {
            return redirect()->away(
                $ltiRequest->getReturnUrl() . '?' . http_build_query([
                    'id' => $content->id,
                    'url' => $content->url,
                    'title' => $content->titleHtml,
                    'type' => $content->machineName,
                    'score' => $content->hasScore,
                ]),
            );
        }

        // no return url
        return redirect()->away($content->url);
    }

    private function getLtiRequest(Request $request): LTIRequest|null
    {
        $ltiRequest = unserialize($request->input('lti_request'), [
            'allowed_classes' => [LTIRequest::class],
        ]);
        assert($ltiRequest instanceof LTIRequest || $ltiRequest === null);

        return $ltiRequest;
    }

    private function getLtiContent(Request $request): LtiContent
    {
        $content = unserialize($request->input('lti_content'), [
            'allowed_classes' => [LtiContent::class],
        ]);
        assert($content instanceof LtiContent);

        return $content;
    }
}
