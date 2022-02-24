<?php

namespace App\Http\Controllers;

use App\Article;
use App\H5PContent;
use App\Libraries\H5P\H5PCopyright;
use Cerpus\Helper\Clients\Client;
use Cerpus\Helper\DataObjects\OauthSetup;
use Masterminds\HTML5;

class ArticleCopyrightController extends Controller
{
    public function copyright(Article $article)
    {
        return response()->json([
            'article' => [
                'license' => $article->getContentLicense(),
                'attribution' => $article->getAttribution(),
                'assets' => $this->getAttributionsForEmbeddedContent($article),
            ],
        ]);
    }

    /**
     * Find all the H5Ps embedded in the article and retrieve the attribution information for all of them.
     *
     * @return array
     */
    private function getAttributionsForEmbeddedContent(Article $article): array
    {
        $dom = (new HTML5())->loadHTML($article->content);

        /** @var \DOMElement $iframe */
        foreach ($dom->getElementsByTagName('iframe') as $iframe) {
            $classNames = preg_split('/\s+/', $iframe->getAttribute('class'));

            if (array_intersect(['edlib_resource', 'oerlearningorg_resource'], $classNames)) {
                $h5pId = $this->getH5pId($iframe->getAttribute('src'));

                if ($h5pId !== null) {
                    /** @var H5PContent $content */
                    $content = H5PContent::find($h5pId);
                    $copyrights = (resolve(H5PCopyright::class))->getCopyrights($content);

                    $attributions[] = $copyrights;
                }
            }
        }

        return $attributions ?? [];
    }

    private function getH5pId(string $embedUrl): ?string
    {
        $url = $embedUrl;
        $urlParts = parse_url($url);

        // core url
        if (!empty($urlParts['query'])) {
            parse_str($urlParts['query'], $params);

            $client = $this->getClient($params['url']);
            $response = $client->request('GET', '');
            $ltiData = \GuzzleHttp\json_decode($response->getBody());

            return $ltiData->resource->h5pId;
        }

        // edlib url
        if (preg_match('!/h5p/(\d+)!', $urlParts['path'], $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function getClient($url)
    {
        $oAuth = OauthSetup::create(['coreUrl' => $url]);
        return Client::getClient($oAuth);
    }
}
