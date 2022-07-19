<?php

namespace App\Http\Controllers;

use App\ACL\ArticleAccess;
use App\Game;
use App\H5pLti;
use App\Http\Libraries\LtiTrait;
use App\Http\Requests\ApiQuestionsetRequest;
use App\Http\Requests\LTIRequest;
use App\Libraries\Games\GameHandler;
use App\Traits\ReturnToCore;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class GameController extends Controller
{
    use LtiTrait;
    use ArticleAccess;
    use ReturnToCore;

    protected H5pLti $lti;

    public function __construct(H5pLti $h5pLti)
    {
        $this->lti = $h5pLti;
        $this->middleware('core.auth', ['only' => ['create', 'edit', 'store', 'update']]);
        $this->middleware('game-access', ['only' => ['ltiEdit']]);
    }

    public function show($id)
    {
        return $this->doShow($id, null);
    }

    public function doShow($id, $context, $preview = false)
    {
        $game = Game::findOrFail($id);
        if( !$game->canShow($preview)){
            /** @var Request $request */
            $request = \Illuminate\Support\Facades\Request::instance();
            $ltiRequest = LTIRequest::fromRequest($request);
            $styles = $ltiRequest && $ltiRequest->getLaunchPresentationCssUrl() ? [$ltiRequest->getLaunchPresentationCssUrl()] : [];
            return view('layouts.draft-resource', compact('styles'));
        }
        $gameType = GameHandler::makeGameTypeFromId($game->gametype);

        return $gameType->view($game, $context, $preview);
    }

    public function edit(Request $request, $gameId)
    {
        /** @var Game $game */
        $game = Game::with('gametype')->findOrFail($gameId);

        if (!$this->canCreate()) {
            abort(403);
        }

        $gameType = GameHandler::makeGameTypeFromId($game->gametype);
        return $gameType->edit($game, $request);
    }

    public function update(Game $game, ApiQuestionsetRequest $request)
    {
        /** @var GameHandler $gamehandler */
        $gamehandler = app(GameHandler::class);
        $request->request->add(json_decode($request->get('questionSetJsonData'), true));
        //$gameData = json_decode($request->questionSetJsonData);
        $updatedGame = $gamehandler->update($game, $request);
        if ($game->isOwner(Session::get('authId'))) {
            $collaborators = explode(',', $request->input('col-emails', ''));
            $game->setCollaborators($collaborators)->notifyNewCollaborators();
        }

        $urlToCore = $this->getRedirectToCoreUrl(
            $updatedGame->id,
            $updatedGame->title,
            "Game",
            true,
            $request->get('redirectToken')
        ); // Will not return if we have a returnURL

        $responseValues = [
            'url' => !is_null($urlToCore) ? $urlToCore : route("game.show", ['game' => $updatedGame->id])
        ];

        return response()->json($responseValues, Response::HTTP_OK);
    }
}
