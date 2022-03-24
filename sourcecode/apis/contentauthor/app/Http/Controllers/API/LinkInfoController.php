<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Link;
use App\Traits\FractalTransformer;
use App\Transformers\LinkMetadataTransformer;
use Embed\Embed;
use Embed\Exceptions\InvalidUrlException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LinkInfoController extends Controller
{
    use FractalTransformer;

    public function index($id)
    {
        $response = Link::whereIn('id', explode(',', $id))
            ->with('collaborators')
            ->get()
            ->map(function ($link) {
                /** @var Link $link */
                return [
                    'id' => $link->id,
                    'owner_id' => $link->owner_id,
                    'is_private' => true,
                    'shares' => $link->collaborators->map(function ($collaborator) {
                        return [
                            'email' => $collaborator->email,
                            'created_at' => $collaborator->created_at->timestamp,
                        ];
                    }),
                    'scoreable' => false,
                    'inDraftState' => !$link->isPublished(),
                    'title' => $link->title,
                ];
            })->toArray();

        if (empty($response)) {
            return response()->json([
                'code' => 404,
                'message' => 'No link(s) found.',
            ], 404);
        }

        return $response;

    }

    public function embed(Request $request)
    {
        $rawUrl = $url = $request->get("link");
        if (!empty($url)) {
            if ((bool)preg_match('/^https?:\/\//i', $url) === false) {
                $url = "http://" . $url;
            }
            try {
                $embed = Embed::create($url);
                $request->session()->put('linksUrl', $rawUrl);
                return $this->buildItemResponse($embed, new LinkMetadataTransformer);
            } catch (InvalidUrlException $exception) {
                return response()->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Invalid url',
                ], Response::HTTP_BAD_REQUEST);
            }

        }
    }
}
