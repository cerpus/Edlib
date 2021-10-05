<?php

namespace App\Http\Controllers;

use App\Http\Requests\LTIRequest;
use App\SessionKeys;
use Session;
use App\ACL\ArticleAccess;
use App\Game;
use App\H5pLti;
use App\Http\Libraries\LtiTrait;
use App\Traits\CopiesCustomMetadataFields;
use App\Http\Requests\ApiQuestionsetRequest;
use App\Libraries\Games\GameHandler;
use App\Traits\ReturnToCore;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GameController extends Controller
{
    use LtiTrait;
    use ArticleAccess;
    use ReturnToCore;
    use CopiesCustomMetadataFields;

    public function __construct(H5pLti $h5pLti)
    {
        $this->lti = $h5pLti;
        $this->middleware('core.auth', ['only' => ['create', 'edit', 'store', 'update']]);
        $this->middleware('game-access', ['only' => ['ltiEdit']]);
        $this->middleware('draftaction', ['only' => ['edit', 'update', 'store', 'create']]);
    }

    public function show($id)
    {
        return $this->doShow($id, null);
    }

    public function doShow($id, $context, $preview = false)
    {
        $game = Game::findOrFail($id);
        if( !$game->canShow($preview)){
            /** @var LTIRequest $ltiRequest */
            $ltiRequest = LTIRequest::current();
            $styles = $ltiRequest && $ltiRequest->getLaunchPresentationCssUrl() ? [$ltiRequest->getLaunchPresentationCssUrl()] : [];
            return view('layouts.draft-resource', compact('styles'));
        }
        $gameType = GameHandler::makeGameTypeFromId($game->gametype);

        return $gameType->view($game, $context, $preview);
    }

    public function edit(Request $request, $game)
    {
        $game = Game::with('gametype')->findOrFail($game);

        if (!$this->canCreate()) {
            abort(403);
        }

        $gameType = GameHandler::makeGameTypeFromId($game->gametype);
        return $gameType->edit($game, $request);
    }

    public function update(Game $game, ApiQuestionsetRequest $request)
    {
        $gamehandler = app(GameHandler::class);
        $request->request->add(json_decode($request->get('questionSetJsonData'), true));
        $gameData = json_decode($request->questionSetJsonData);
        $updatedGame = $gamehandler->update($game, $request);
        $updatedGame->updateMetaTags($gameData->tags);
        $this->copyCustomFieldsMetadata($game->id, $updatedGame->id);
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
