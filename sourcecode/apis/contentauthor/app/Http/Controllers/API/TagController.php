<?php

namespace App\Http\Controllers\API;

use App;
use Cerpus\MetadataServiceClient\Contracts\MetadataServiceContract as MetadataService;
use Log;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;


class TagController extends Controller
{
    public function searchTags(Request $request)
    {
        $searchString = trim($request->query('search', ''));

        Log::debug('(' . resolve('requestId') . ') ' . __METHOD__ . ': Searching for ' . $searchString . ' in MetadataService.');

        try {
            $keywords = $this->searchForKeywordsInMetadataService($searchString);

            $responseCode = Response::HTTP_OK;
            $response = [
                'request_id' => resolve('requestId'),
                'success' => true,
                'message' => 'success',
                'keywords' => $keywords
            ];

            Log::debug('(' . resolve('requestId') . ') ' . __METHOD__ . ': Got result back from Metadataservice');
        } catch (\Exception $e) {
            Log::error('(' . resolve('requestId') . ') ' . __METHOD__ . ': Unable to fetch keywords from metadataService. ' . $e->getMessage());

            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response = [
                'request_id' => resolve('requestId'),
                'success' => false,
                'message' => 'Error fetching keywords from metadata service: (' . $e->getCode() . ') ' . $e->getMessage(),
                'keywords' => []
            ];
        }

        return response($response, $responseCode);
    }

    private function searchForKeywordsInMetadataService($searchFor = ''): array
    {
        $metadataService = resolve(MetadataService::class);

        $keywords = $metadataService->searchForKeywords($searchFor);

        $keywords = collect($keywords)->map(function ($keyword) {
            return $keyword->keyword;
        })->toArray();

        return $keywords;
    }
}
