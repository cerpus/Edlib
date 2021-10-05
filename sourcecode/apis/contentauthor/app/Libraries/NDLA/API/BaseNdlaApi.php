<?php

namespace App\Libraries\NDLA\API;

use App;
use Log;
use GuzzleHttp\Client;

abstract class BaseNdlaApi
{
    protected $client;

    protected $page = 1;
    protected $pageSize = 15;
    protected $maxPages = null;
    protected $language = 'nb';
    protected $totalCount = null;
    protected $searchContext = null;

    public function __construct(Client $client = null, $pageSize = 15)
    {
        if ($client) {
            $this->setClient($client);
        } else {
            $this->client = new Client([
                'base_uri' => 'https://api.ndla.no',
            ]);
        }

        $this->setPageSize($pageSize);
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
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
     * @return null|string
     */
    public function getSearchContext()
    {
        return $this->searchContext;
    }

    /**
     * @param null|string $searchContext
     */
    public function setSearchContext($searchContext)
    {
        $this->searchContext = $searchContext;

        return $this;
    }

    /**
     * @return null
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @param null $totalCount
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @return null
     */
    public function getMaxPages()
    {
        return $this->maxPages;
    }

    /**
     * @param null $maxPages
     */
    public function setMaxPages($maxPages)
    {
        $this->maxPages = $maxPages;
    }

    /**
     * @param $e
     */
    protected function logException($e)
    {
        if (App::environment('local')) {
            Log::debug("Request query: " . $e->getRequest()->getUri()->getQuery());
            Log::debug("Api request headers(exception):", $e->getRequest()->getHeaders());
            Log::debug("Api response headers(exception):", $e->getResponse()->getHeaders());
            Log::debug("Api response(exception): " . $e->getResponse()->getBody());
        }
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    protected function updateSearchContext(\Psr\Http\Message\ResponseInterface $response)
    {
        if ($searchContext = $response->getHeader('search-context')) {
            if (is_array($searchContext) && !empty($searchContext) && $this->getSearchContext() !== $searchContext[0]) {
                $this->setSearchContext($searchContext[0]);
            }
        }
    }

    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

}
