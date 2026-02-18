<?php

declare(strict_types=1);

namespace App\Libraries\Hub;

use App\Content;
use App\Lti\LtiRequest;
use Cerpus\EdlibResourceKit\Lti\Edlib\DeepLinking\EdlibLtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializerInterface;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\Image;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LineItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ScoreConstraints;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class HubClient
{
    private readonly Credentials $credentials;

    public function __construct(
        private readonly SignerInterface $signer,
        private readonly Client $client,
        private readonly ContentItemsSerializerInterface $serializer,
    ) {
        $this->credentials = new Credentials(
            config('app.consumer-key'),
            config('app.consumer-secret'),
        );
    }

    /**
     * Make an OAuth1-signed POST request to Hub.
     *
     * Relative paths (e.g. '/content/info') are resolved against the base
     * URL from the LTI session. Absolute URLs are used as-is.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException|\JsonException
     */
    public function post(string $url, array $params = []): array
    {
        if (!str_starts_with($url, 'http')) {
            $url = $this->baseUrl() . $url;
        }

        $oauthRequest = new Oauth1Request('POST', $url, $params);
        $oauthRequest = $this->signer->sign($oauthRequest, $this->credentials);

        $response = $this->client->post($url, [
            'form_params' => $oauthRequest->toArray(),
        ])
            ->getBody()
            ->getContents();

        return json_decode($response, associative: true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Create a new content version in the Hub.
     *
     * @param Content $content The content to notify the Hub about.
     * @param string|null $updateUrl The Hub update URL. If null, it will be
     *     looked up via the /content/info endpoint using the content's URL.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException|\JsonException
     */
    public function createContentVersion(Content $content, ?string $updateUrl = null): void
    {
        if ($updateUrl === null) {
            $info = $this->post('/content/info', [
                'lti_launch_url' => $content->getUrl(),
            ]);
            $updateUrl = $info['update_url'] ?? null;
            if ($updateUrl === null) {
                throw new \RuntimeException('Hub did not return update_url for content ' . $content->id);
            }
        }

        $data = $content->toLtiContent();
        $item = (new EdlibLtiLinkItem(
            icon: $data->iconUrl ? new Image($data->iconUrl) : null,
            title: $data->title,
            url: $data->url,
            lineItem: $data->maxScore > 0
                ? (new LineItem(new ScoreConstraints(normalMaximum: $data->maxScore)))
                : null,
        ))
            ->withLanguageIso639_3($data->languageIso639_3)
            ->withLicense($data->license)
            ->withPublished($data->published)
            ->withShared($data->shared)
            ->withTags($data->tags)
            ->withContentType($data->machineName)
            ->withContentTypeName($data->machineDisplayName);

        $this->post($updateUrl, [
            'content_items' => json_encode($this->serializer->serialize([$item]), flags: JSON_THROW_ON_ERROR),
            'lti_message_type' => 'ContentItemSelection',
            'lti_version' => 'LTI-1p0',
            'user_id' => Session::get('lti_requests.admin')->param('user_id'),
        ]);
    }

    public function baseUrl(): string
    {
        /** @var LtiRequest $ltiRequest */
        $ltiRequest = Session::get('lti_requests.admin');

        return $ltiRequest->param('ext_edlib3_author_endpoint');
    }
}
