<?php

namespace App\Http\Controllers\API;

use App\H5PContent;
use App\Http\Controllers\Controller;
use App\QuestionSet;

class QuestionSetInfoController extends Controller
{
    public function index($id)
    {
        $response = QuestionSet::whereIn('id', explode(',', $id))
//            ->with('collaborators')
            ->get()
            ->map(function ($questionset) {
                return [
                    'id' => $questionset->id,
                    'owner_id' => $questionset->owner,
                    'is_private' => $questionset->is_private,
//                    'shares' => $questionset->collaborators->map(function ($collaborator) {
//                        return [
//                            'email' => $collaborator->email,
//                            'created_at' => $collaborator->created_at->timestamp,
//                        ];
//                    }),
                    'shares' => [],
                    'scoreable' => true,
                    'title' => $questionset->title,
                ];
            })->toArray();

        if (empty($response)) {
            return response()->json([
                'code' => 404,
                'message' => 'No questionset(s) found.',
            ], 404);
        }

        return $response;

    }
}
