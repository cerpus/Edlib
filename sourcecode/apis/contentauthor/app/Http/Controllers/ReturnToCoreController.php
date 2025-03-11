<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Libraries\DataObjects\LtiContent;
use App\Lti\LtiRequest;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializerInterface;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\Image;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LineItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ScoreConstraints;
use Cerpus\EdlibResourceKit\Oauth1\Claim;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function assert;
use function redirect;
use function unserialize;

final readonly class ReturnToCoreController
{
    public function __construct(
        private ContentItemsSerializerInterface $serializer,
        private SignerInterface $signer,
        private CredentialStoreInterface $credentials,
    ) {}

    public function __invoke(Request $request): Response
    {
        $ltiRequest = $this->getLtiRequest($request);
        $content = $this->getLtiContent($request);

        if (!$ltiRequest?->isContentItemSelectionRequest()) {
            // no return url
            return redirect()->away($content->url);
        }

        // perform an LTI deep-linking return
        $item = (new EdlibLtiLinkItem(
            title: $content->title,
            url: $content->url,
            icon: $content->iconUrl ? new Image($content->iconUrl) : null,
            lineItem: $content->maxScore > 0 ?
                (new LineItem(new ScoreConstraints(normalMaximum: $content->maxScore))) :
                null,
        ))
            ->withLanguageIso639_3($content->languageIso639_3)
            ->withLicense($content->license)
            ->withPublished($content->published)
            ->withShared($content->shared)
            ->withTags($content->tags)
        ;

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

    private function getLtiRequest(Request $request): LtiRequest|null
    {
        $ltiRequest = unserialize($request->input('lti_request'), [
            'allowed_classes' => [LtiRequest::class],
        ]);
        assert($ltiRequest instanceof LtiRequest || $ltiRequest === null);

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
