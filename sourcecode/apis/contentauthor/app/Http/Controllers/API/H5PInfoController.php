<?php

namespace App\Http\Controllers\API;

use App\H5PContent;
use App\Http\Controllers\Controller;
use Iso639p3;

class H5PInfoController extends Controller
{
    public function index($id)
    {
        $response = H5PContent::whereIn('id', explode(',', $id))
            ->with(['collaborators', 'library.capability', 'metadata'])
            ->get()
            ->map(function ($h5p) {
                /** @var H5PContent $h5p */
                return [
                    'id' => $h5p->id,
                    'owner_id' => $h5p->user_id,
                    'is_private' => (boolean)$h5p->is_private,
                    'shares' => $h5p->collaborators->map(function ($collaborator) {
                        return [
                            'email' => $collaborator->email,
                            'created_at' => $collaborator->created_at->timestamp,
                        ];
                    }),
                    'scoreable' => !is_null($h5p->max_score) && $h5p->max_score > 0,
                    'maxScore' => $h5p->max_score,
                    'inDraftState' => $h5p->inDraftState(),
                    'language' => Iso639p3::code2letters($h5p->getISO6393Language()),
                    'title' => $h5p->title,
                ];
            })->toArray();

        if (empty($response)) {
            return response()->json([
                'code' => 404,
                'message' => 'No h5p(s) found.',
            ], 404);
        }

        return $response;

    }
}
