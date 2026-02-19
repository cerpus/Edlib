<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\FractalTransformer;
use App\Transformers\LinkMetadataTransformer;
use Embed\Embed;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Http\Client\RequestExceptionInterface;

class LinkInfoController extends Controller
{
    use FractalTransformer;

    public function embed(Request $request)
    {
        $rawUrl = $url = $request->get("link");
        if (!empty($url)) {
            if ((bool) preg_match('/^https?:\/\//i', $url) === false) {
                $url = "http://" . $url;
            }
            try {
                $embed = (new Embed())->get($url);
                $request->session()->put('linksUrl', $rawUrl);
                return $this->buildItemResponse($embed, new LinkMetadataTransformer());
            } catch (RequestExceptionInterface $exception) {
                return response()->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Invalid url',
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }
}
