<?php

namespace App\Libraries\NDLA\API;

use App;
use Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;


class ArticleApiClient
{
    protected $client;
    protected $page = 1;
    protected $pageSize = 15;
    protected $maxPages = null;
    protected $searchContext = null;
    protected $totalArticleCount = null;

    public function __construct(Client $client = null, $pageSize = 15)
    {
        if ($client) {
            $this->client = $client;
        } else {
            $this->client = new Client([
                'base_uri' => 'https://api.ndla.no',
            ]);
        }

        $this->pageSize = $pageSize;
    }

    public function getArticles($page = null, $language = 'nb')
    {
        try {
            $articles = null;
            if ($page) {
                $this->setPage($page);
            }

            $path = '/article-api/v2/articles';

            $requestOptions = [
                'query' => [
                    'page' => $this->getPage(),
                    'page-size' => $this->getPageSize(),
                ]
            ];

            if ($language) {
                $requestOptions['query']['language'] = $language;
            }

            if ($this->searchContext) {
                $requestOptions['query']['search-context'] = $this->searchContext;
            }

            $response = $this->client->get($path, $requestOptions);

            $result = json_decode($response->getBody());
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->setTotalArticleCount($result->totalCount);
                $this->maxPages = ceil($this->getTotalArticleCount() / $this->getPageSize());
                $articles = $result->results;
            }

            if ($searchContext = $response->getHeader('search-context')) {
                if (is_array($searchContext) && !empty($searchContext) && $this->searchContext !== $searchContext[0]) {
                    $this->searchContext = $searchContext[0];
                }
            }
        } catch (ClientException $e) {
            if (App::environment('local')) {
                Log::debug("Request query: " . $e->getRequest()->getUri()->getQuery());
                Log::debug("Api request headers(exception):", $e->getRequest()->getHeaders());
                Log::debug("Api response headers(exception):", $e->getResponse()->getHeaders());
                Log::debug("Api response(exception): " . $e->getResponse()->getBody());
            }
            throw $e;
        }

        return $articles;
    }

    public function getArticle($id, $language = 'nb')
    {
        $path = sprintf('/article-api/v2/articles/%s', $id);

        $query['fallback'] = 'true';

        if ($language) {
            $query['language'] = $language;
        }

        $response = $this->client->get($path, [
            'query' => $query
        ]);

        $article = json_decode($response->getBody());

        return $article;
    }


    /**
     * @return mixed|null search-context header or null on failure.
     */
    protected function getSearchContextHeader()
    {
        try {
            $searchContextHeader = null;
            $path = '/article-api/v2/articles';

            $response = $this->client->get($path, [
                'query' => [
                    'page' => 1,
                    'page-size' => $this->getPageSize(),
                ]
            ]);

            $responseHeader = $response->getHeader('search-context');
            if (is_array($responseHeader) && count($responseHeader) > 0) {
                $searchContextHeader = $responseHeader[0];
            }
        } catch (RequestException $e) {
            $searchContextHeader = null;
        }

        return $searchContextHeader;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param int $pageSize
     */
    public function setPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return null
     */
    public function getMaxPages()
    {
        return $this->maxPages ?? 0;
    }

    /**
     * @param null $maxPages
     */
    public function setMaxPages($maxPages)
    {
        $this->maxPages = $maxPages;
    }

    /**
     * @return null
     */
    public function getTotalArticleCount()
    {
        return $this->totalArticleCount;
    }

    public function fetchTotalArticleCount()
    {
        $this->getArticles(1);

        return $this->getTotalArticleCount();
    }

    /**
     * @param null $totalArticleCount
     */
    public function setTotalArticleCount($totalArticleCount)
    {
        $this->totalArticleCount = $totalArticleCount;

        return $this;
    }

    public function fetchEffectiveUri($oldArticleId)
    {
        $url = null;

        try {
            $uri = config('ndla.linkBaseUrl', 'https://ndla.no') . '/node/' . $oldArticleId;
            file_get_contents($uri);
            $length = strlen('Location:');
            foreach ($http_response_header as $header) {
                if (substr($header, 0, $length) === 'Location:') {
                    $url = config('ndla.linkBaseUrl', 'https://ndla.no') . substr($header, $length + 1);
                }
            }
        } catch (\Throwable $t) {
            Log::debug(__METHOD__ . ':(' . $t->getCode() . ')' . $t->getMessage());
        }

        return $url;
    }
}
