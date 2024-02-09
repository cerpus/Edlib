<?php

namespace App\Libraries\Games;

use App\ContentVersion;
use App\Events\GameWasSaved;
use App\Game;
use App\Gametype;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use App\Libraries\Games\Contracts\GameTypeContract;
use App\Libraries\Games\Millionaire\Millionaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class GameHandler
{
    public function store($values, GameTypeContract $gametype)
    {
        $game = Game::make();

        $game->title = $values['title'];
        $game->gametype = $gametype->getGameType();
        $game->language_code = $gametype->convertLanguageCode(App::getLocale());
        $game->owner = $values['authId'];
        $game->game_settings = $gametype->createGameSettings($values);
        $game->is_published = $values['is_published'];
        $game->license = $values['license'];

        $game->save();

        event(new GameWasSaved($game, new ResourceMetadataDataObject(
            license: $values['license'],
            share: $values['share'],
            reason: ContentVersion::PURPOSE_CREATE,
            owner: $values['authId'],
            tags: $values['tags'],
        )));

        return $game;
    }

    /**
     * @throws \Exception
     */
    public static function makeGameTypeFromId($gametypeId): GameTypeContract
    {
        $gametypes = Gametype::findOrFail($gametypeId)->get();

        if ($gametypes->isEmpty()) {
            throw new \Exception(trans('game.gametype-not-found'));
        }

        $gametype = $gametypes->first();
        switch ($gametype->name) {
            case Millionaire::$machineName:
                $className = Millionaire::class;
                break;
            default:
                throw new \Exception(trans('game.could-not-find-the-gametype', ["gametype" => $gametype->name]));
        }

        return app($className);
    }

    public function update(Game $game, Request $request)
    {
        /** @var Game $game */
        list($game, $reason) = $this->handleCopy($game, $request);

        $gametype = self::makeGameTypeFromId($game->gameType->id);

        $game->title = $request->get('title');
        $game->game_settings = $gametype->createGameSettings($request->all());
        $game->is_published = $game::isUserPublishEnabled() ? $request->input('isPublished', 1) : 1;
        $game->license = $request->input('license');

        $game->save();

        event(new GameWasSaved($game, new ResourceMetadataDataObject(
            license: $request->get('license'),
            share: $request->get('share'),
            reason: $reason,
            owner: Session::get('authId'),
            tags: $request->get('tags', []),
        )));

        return $game;
    }

    private function handleCopy(Game $game, Request $request)
    {
        $reason = $game->shouldCreateFork(Session::get('authId', false)) ? ContentVersion::PURPOSE_COPY : ContentVersion::PURPOSE_UPDATE;

        if ($reason === ContentVersion::PURPOSE_COPY && !$request->get("license", false)) {
            $request->merge(["license" => $game->getContentLicense()]);
        }

        // If you are a collaborator, use the old license
        if ($game->isCollaborator()) {
            $request->merge(["license" => $game->getContentLicense()]);
        }

        if ($game->requestShouldBecomeNewVersion($request)) {
            switch ($reason) {
                case ContentVersion::PURPOSE_UPDATE:
                    $game = $game->makeCopy();
                    break;
                case ContentVersion::PURPOSE_COPY:
                    $game = $game->makeCopy(Session::get('authId'));
                    break;
            }
        }

        return [$game, $reason];
    }
}
