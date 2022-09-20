<?php

namespace App\Http\Controllers\API;

use App\Http\Libraries\EmbedlyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmbedlyController extends Controller
{
    public function get(Request $request)
    {
        $url = $request->get("link");
        $response = EmbedlyService::get($url);

        if ($response == null) {
            return response('Didn\'t find url: ' . $url, 404);
        }

        if (isset($response["html"])) {
            $responseData = [
                "type" => "embed",
                "title" => "title",
                "url" => $response["url"],
                "html" => $response["html"],
                "provider" => [
                    "name" => $response["provider_name"],
                    "url" => $response["provider_url"]
                ]
            ];
        } else {
            $responseData = [
                "type" => "card",
                "url" => $response["url"],
                "title" => $response["title"],
                "img" => isset($response['thumbnail_url']) ? $response["thumbnail_url"] : null,
                "description" => $response["description"],
                "provider" => [
                    "name" => $response["provider_name"],
                    "url" => $response["provider_url"]
                ]
            ];
        }

        return response()->json($responseData);
    }
}
