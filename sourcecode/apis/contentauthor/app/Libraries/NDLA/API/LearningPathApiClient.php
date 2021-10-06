<?php

namespace App\Libraries\NDLA\API;

use GuzzleHttp\Exception\ClientException;

class LearningPathApiClient extends BaseNdlaApi
{
    public function fetchLearningPaths($page = null)
    {
        $learningPaths = null;

        try {

            $path = '/learningpath-api/v2/learningpaths/';

            if ($page) {
                $this->setPage($page);
            }

            $requestOptions = [
                'query' => [
                    'page' => $this->getPage(),
                    'page-size' => $this->getPageSize(),
                ]
            ];

            if ($this->getSearchContext()) {
                $requestOptions['query']['search-context'] = $this->getSearchContext();
            }

            $response = $this->client->get($path, $requestOptions);
            $this->updateSearchContext($response);

            $result = json_decode($response->getBody());
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->setTotalCount($result->totalCount);
                $this->setMaxPages(ceil($this->getTotalCount() / $this->getPageSize()));
                $learningPaths = $result->results;
            }
        } catch (ClientException $e) {
            $this->logException($e);
            throw $e;
        }

        return $learningPaths;
    }

    public function fetchLearningPath($learningPathId, $language = null)
    {
        $learningPath = null;
        try {
            $path = sprintf('/learningpath-api/v2/learningpaths/%s', $learningPathId);

            $query = [];

            if ($language) {
                $query['language'] = $language;
            }

            $response = $this->client->get($path, [
                'query' => $query
            ]);

            $result = json_decode($response->getBody());

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->setMaxPages(null);
                $this->setTotalCount(null);
                $learningPath = $result;
            }
        } catch (ClientException $e) {
            $this->logException($e);
            throw $e;
        }

        return $learningPath;
    }

    public function fetchLearningSteps($learningPathId, $language = null)
    {
        $learningSteps = [];
        try {
            $path = sprintf('/learningpath-api/v2/learningpaths/%s/learningsteps', $learningPathId);

            $query = [];

            if ($language) {
                $query['language'] = $language;
            }

            $response = $this->client->get($path, [
                'query' => $query
            ]);

            $result = json_decode($response->getBody());

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->setMaxPages(null);
                $this->setTotalCount(null);
                $learningSteps = $result;
            }
        } catch (ClientException $e) {
            $this->logException($e);
            throw $e;
        }

        return $learningSteps;
    }

    public function fetchLearningStep($learningPathId, $stepId, $language = null)
    {
        $learningStep = [];
        try {
            $path = sprintf('/learningpath-api/v2/learningpaths/%s/learningsteps/%s', $learningPathId, $stepId);

            $query = [];

            if ($language) {
                $query['language'] = $language;
            }

            $response = $this->client->get($path, [
                'query' => $query
            ]);

            $result = json_decode($response->getBody());

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->setMaxPages(null);
                $this->setTotalCount(null);
                $learningStep = $result;
            }
        } catch (ClientException $e) {
            $this->logException($e);
            throw $e;
        }

        return $learningStep;
    }

    public function fetchTotalCount()
    {
        $this->fetchLearningPaths();

        return $this->getTotalCount();
    }

    public function fetchSteps($learningPathId, $language = null)
    {
        $learningPathSteps = null;
        try {
            $path = sprintf('/learningpath-api/v2/learningpaths/', $learningPathId);

            $query = [];

            if ($language) {
                $query['language'] = $language;
            }

            $response = $this->client->get($path, [
                'query' => $query
            ]);

            $result = json_decode($response->getBody());

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->setMaxPages(null);
                $this->setTotalCount(null);
                $learningPathSteps = $result;
            }
        } catch (ClientException $e) {
            $this->logException($e);
            throw $e;
        }

        return $learningPathSteps;
    }
}
