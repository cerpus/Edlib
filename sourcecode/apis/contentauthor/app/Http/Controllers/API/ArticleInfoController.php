<?php

namespace App\Http\Controllers\API;

use App\Article;
use App\Http\Controllers\Controller;

class ArticleInfoController extends Controller
{
    public function index($id)
    {
        $response = Article::whereIn('id', explode(',', $id))
            ->with('collaborators')
            ->get()
            ->map(function ($article) {
                /** @var Article $article */
                return [
                    'id' => $article->id,
                    'owner_id' => $article->owner_id,
                    'is_private' => $article->is_private,
                    'shares' => $article->collaborators->map(function($collaborator){
                        return [
                            'email' => $collaborator->email,
                            'created_at' => $collaborator->created_at->timestamp,
                        ];
                    }),
                    'scoreable' => !is_null($article->max_score) && $article->max_score > 0,
                    'maxScore' => $article->max_score,
                    'inDraftState' => !$article->isPublished(),
                    'title' => $article->title,
                ];
            })->toArray();

        if (empty($response)) {
            return response()->json([
                'code' => 404,
                'message' => 'No article(s) found.',
            ], 404);
        }

        return $response;
    }
}
