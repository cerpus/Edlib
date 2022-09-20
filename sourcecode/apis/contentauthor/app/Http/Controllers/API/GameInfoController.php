<?php

namespace App\Http\Controllers\API;

use App\Game;
use App\Http\Controllers\Controller;

class GameInfoController extends Controller
{
    public function index($id)
    {
        $response = Game::whereIn('id', explode(',', $id))
//            ->with('collaborators')
            ->get()
            ->map(function ($game) {
                /** @var Game $game */
                $gameType = $game->getGameTypeHandler();
                return [
                    'id' => $game->id,
                    'owner_id' => $game->owner,
                    'is_private' => $game->is_private,
//                    'shares' => $questionset->collaborators->map(function ($collaborator) {
//                        return [
//                            'email' => $collaborator->email,
//                            'created_at' => $collaborator->created_at->timestamp,
//                        ];
//                    }),
                    'shares' => [],
                    'scoreable' => true,
                    'game_type' => $game->gameType->name,
                    'maxScore' => $gameType->getMaxScore(),
                    'inDraftState' => !$game->isPublished(),
                    'title' => $game->title,
                ];
            })->toArray();

        if (empty($response)) {
            return response()->json([
                'code' => 404,
                'message' => 'No games(s) found.',
            ], 404);
        }

        return $response;
    }
}
