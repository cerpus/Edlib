<?php

namespace App\Http\Controllers;

use App\Game;
use App\Http\Requests\ApiQuestionsetRequest;
use App\Libraries\Games\GameHandler;
use App\Traits\ReturnToCore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class GameController extends Controller
{
    use ReturnToCore;

    public function show($id)
    {
        $game = Game::findOrFail($id);
        $gameType = GameHandler::makeGameTypeFromId($game->gametype);

        return $gameType->view($game);
    }

    public function create(Request $request): View
    {
        $type = $request->route()->parameter('type');
        $handler = GameHandler::getGameTypeInstance($type);

        return $handler->create($request);
    }

    public function edit(Request $request, $gameId): View
    {
        /** @var Game $game */
        $game = Game::with('gametype')->findOrFail($gameId);
        $gameType = GameHandler::makeGameTypeFromId($game->gametype);

        return $gameType->edit($game, $request);
    }

    public function update(Game $game, ApiQuestionsetRequest $request): JsonResponse
    {
        $gamehandler = app(GameHandler::class);
        $request->request->add(json_decode($request->get('questionSetJsonData'), true));
        $updatedGame = $gamehandler->update($game, $request);
        if ($game->isOwner(Session::get('authId'))) {
            $collaborators = explode(',', $request->input('col-emails', ''));
            $game->setCollaborators($collaborators);
        }

        $url = $this->getRedirectToCoreUrl(
            $updatedGame->toLtiContent(
                published: $request->validated('isPublished'),
                shared: $request->validated('isShared'),
            ),
            $request->input('redirectToken'),
        );

        return response()->json(['url' => $url], Response::HTTP_OK);
    }
}
