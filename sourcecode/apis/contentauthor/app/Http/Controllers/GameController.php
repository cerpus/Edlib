<?php

namespace App\Http\Controllers;

use App\ACL\ArticleAccess;
use App\Game;
use App\Http\Libraries\LtiTrait;
use App\Http\Requests\ApiQuestionsetRequest;
use App\Libraries\Games\GameHandler;
use App\Lti\Lti;
use App\Traits\ReturnToCore;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class GameController extends Controller
{
    use LtiTrait;
    use ArticleAccess;
    use ReturnToCore;

    public function __construct(private Lti $lti)
    {
        $this->middleware('lti.verify-auth', ['only' => ['create', 'edit', 'store', 'update']]);
        $this->middleware('game-access', ['only' => ['ltiEdit']]);
    }

    public function show($id)
    {
        return $this->doShow($id, null);
    }

    public function doShow($id, $context, $preview = false)
    {
        $game = Game::findOrFail($id);
        if (!$game->canShow($preview)) {
            $ltiRequest = app()->make(Lti::class)->getRequest(request());
            $styles = $ltiRequest?->getLaunchPresentationCssUrl() ? [$ltiRequest->getLaunchPresentationCssUrl()] : [];
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

        $url = $this->getRedirectToCoreUrl($game->toLtiContent(), $request->input('redirectToken'));

        return response()->json(['url' => $url], Response::HTTP_OK);
    }
}
