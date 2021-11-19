<?php


namespace App\Libraries\NDLA\API;

use Illuminate\Support\Facades\Cache;
use Throwable;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class GraphQLApi
{
    protected $client;
    protected $path = '/graphql-api/graphql';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('ndla.api.uri'),
            'timeout' => 60.0,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => '*',
            ]
        ]);
    }

    public function fetchSubjects()
    {
        $result = [];

        $query = "{subjects{id name}}";

        $payload = [
            'operationName' => null,
            'query' => $query,
            'variables' => []
        ];

        try {
            $response = $this->client->post($this->path, [RequestOptions::JSON => $payload]);
            $result = json_decode($response->getBody()->getContents())->data->subjects;
        } catch (Throwable $t) {
            $a = 1;
        }

        return $result;
    }

    public function fetchSubject($subjectId)
    {
        $cacheKey = "NdlaGQLApiSubject" . $subjectId;
        $cacheTime = Carbon::now()->addMinutes(10);

        // Cache disabled on purpose. The GQL API is quite unstable
        // Re-enable if/when we get stable responses
        $result = Cache::get($cacheKey.'1', []);

        if (empty($result)) {
            $query = <<<'QUERY'
query {
  subject(id: $subjectId) {
    id
    name
    subjectpage {
      metaDescription
      banner {
        desktopUrl
      }
      about {
        description
      }
    }
    topics {
      id
      name
      subtopics {
        id
        name
        subtopics {
          id
          name
          coreResources {
            ...ResourceInfo
          }
          supplementaryResources {
            ...ResourceInfo
          }
        }
        meta {
          ...MetaInfo
        }
        coreResources {
          ...ResourceInfo
        }
        supplementaryResources {
          ...ResourceInfo
        }
      }
      meta {
        ...MetaInfo
      }
      coreResources {
        ...ResourceInfo
      }
      supplementaryResources {
        ...ResourceInfo
      }
    }
  }
}

fragment ResourceInfo on Resource {
  id
  name
  contentUri
  meta {
    metaDescription
    metaImage {
      url
    }
  }
  resourceTypes {
    id
  }
}

fragment MetaInfo on Meta {
  metaDescription
  metaImage {
    url
  }
}
QUERY;
            $query = str_replace('$subjectId', "\"$subjectId\"", $query);

            $payload = [
                'operationName' => null,
                'query' => $query,
                'variables' => []
            ];

            try {
                $response = $this->client->post($this->path, [RequestOptions::JSON => $payload]);
                $result = json_decode($response->getBody()->getContents())->data->subject;

                if (json_last_error() === JSON_ERROR_NONE) {
                    Cache::put($cacheKey, $result, $cacheTime);
                }
            } catch (Throwable $t) {
                $a = 1;
            }
        }

        return $result;
    }

    public function fetchSubjectMinimal($subjectId)
    {
        $cacheKey = "NdlaGQLApiSubjectMinimal|" . $subjectId;
        $cacheTime = Carbon::now()->addMinutes(10);

        $result = Cache::get($cacheKey . '1', []);

        if (empty($result)) {
            $query = <<<'QUERY'
query {
  subject(id: $subjectId) {
    id
    name
    topics {
      id
      name
      subtopics {
        id
        name
        coreResources {
          ...ResourceInfo
        }
        supplementaryResources {
          ...ResourceInfo
        }
        subtopics {
          id
          name
          coreResources {
            ...ResourceInfo
          }
          supplementaryResources {
            ...ResourceInfo
          }
        }
      }
      coreResources {
        ...ResourceInfo
      }
      supplementaryResources {
        ...ResourceInfo
      }
    }
  }
}

fragment ResourceInfo on Resource {
  contentUri
}
QUERY;
            $query = str_replace('$subjectId', "\"$subjectId\"", $query);

            $payload = [
                'operationName' => null,
                'query' => $query,
                'variables' => []
            ];

            try {
                $response = $this->client->post($this->path, [RequestOptions::JSON => $payload]);
                $result = json_decode($response->getBody()->getContents())->data->subject;

                if (json_last_error() === JSON_ERROR_NONE) {
                    Cache::put($cacheKey, $result, $cacheTime);
                }
            } catch (Throwable $t) {
                $a = 1;
            }
        }

        return $result;
    }

    public function fetchSubjectTopic($subjectId, $topicId)
    {
        $result = $this->fetchSubject($subjectId);

        // Remove all topics Except the one we are looking for
        $topicId = strtolower($topicId);
        foreach ($result->topics as $key => $topic) {
            if (strtolower($topic->id) !== $topicId) {
                unset($result->topics[$key]);
            }
        }
        dd($result);

        return $result;
    }

    public function fetchSubjectTopics($subjectId)
    {
        $result = [];

        $query = <<<'QUERY'
            query subjectTopicsQuery($subjectId: String!, $filterIds: String) {
              subject(id: $subjectId) {
                id
                name
                topics(all: true, filterIds: $filterIds) {
                  id
                  name
                  meta {
                    id
                    metaDescription
                  }
                }
              }
            }
QUERY;


        $variables = [
            'subjectId' => $subjectId,
            'filterIds' => "",
        ];

        $payload = [
            'operationName' => "subjectTopicsQuery",
            'query' => $query,
            'variables' => $variables
        ];

        try {
            $response = $this->client->post($this->path, [RequestOptions::JSON => $payload]);
            $result = json_decode($response->getBody()->getContents())->data->subject;
        } catch (Throwable $t) {
            $a = 1;
        }

        return $result;
    }

    public function fetchSubjectTopicResources($subjectId, $topicId)
    {
        $result = [];

        $query = <<<'QUERY'
            query topicResourcesQuery($topicId: String!, $filterIds: String, $subjectId: String) {
              topic(id: $topicId) {
                id
                name
                coreResources(filterIds: $filterIds, subjectId: $subjectId) {
                  ...ResourceInfo
                  __typename
                }
                supplementaryResources(filterIds: $filterIds, subjectId: $subjectId) {
                  ...ResourceInfo
                  __typename
                }
                __typename
              }
            }
            
            fragment ResourceInfo on Resource {
              id
              name
              contentUri
              path
              resourceTypes {
                id
                name
                __typename
              }
              __typename
            }
QUERY;

        $variables = [
            'topicId' => $topicId,
            'subjectId' => $subjectId,
            'filterIds' => "",
        ];

        $payload = [
            'operationName' => "topicResourcesQuery",
            'query' => $query,
            'variables' => $variables
        ];

        try {
            $response = $this->client->post($this->path, [RequestOptions::JSON => $payload]);
            $result = json_decode($response->getBody()->getContents())->data->topic;
        } catch (Throwable $t) {
            $a = 1;
        }

        return $result;
    }

}
