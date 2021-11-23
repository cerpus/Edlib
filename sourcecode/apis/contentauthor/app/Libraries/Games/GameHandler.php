<?php

namespace App\Libraries\Games;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use App\Game;
use App\Gametype;
use App\Events\GameWasSaved;
use Illuminate\Http\Request;
use Cerpus\VersionClient\VersionData;
use App\Libraries\Games\Millionaire\Millionaire;
use App\Libraries\Games\Contracts\GameTypeContract;
use App\Libraries\DataObjects\ResourceMetadataDataObject;

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

        $game->save();

        event(new GameWasSaved($game, ResourceMetadataDataObject::create([
            'license' => $values['license'],
            'share' => $values['share'],
            'reason' => VersionData::CREATE,
            'owner' => $values['authId'],
            'session' => Session::all(),
            'tags' => $values['tags'],
        ])));

        return $game;
    }

    /**
     * @param $gametypeId
     * @return GameTypeContract
     * @throws \Exception
     */
    public static function makeGameTypeFromId($gametypeId)
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
        list($game, $reason) = $this->handleCopy($game, $request);

        $gametype = self::makeGameTypeFromId($game->gameType->id);

        $game->title = $request->get('title');
        $game->game_settings = $gametype->createGameSettings($request->all());
        $game->is_published = $game::isDraftLogicEnabled() ? $request->input('isPublished', 1) : 1;

        $game->save();

        event(new GameWasSaved($game, ResourceMetadataDataObject::create([
            'license' => $request->get('license'),
            'share' => $request->get('share'),
            'reason' => $reason,
            'owner' => Session::get('authId'),
            'session' => Session::all(),
            'tags' => $request->get('tags'),
        ])));

        return $game;

    }

    private function handleCopy(Game $game, Request $request)
    {
        if ($game->useVersioning() !== true) {
            return [$game, VersionData::UPDATE];
        }

        $reason = $game->shouldCreateFork(Session::get('authId', false)) ? VersionData::COPY : VersionData::UPDATE;

        if ($reason === VersionData::COPY && !$request->get("license", false)) {
            $request->merge(["license" => $game->getContentLicense()]);
        }

        // If you are a collaborator, use the old license
        if ($game->isCollaborator()) {
            $request->merge(["license" => $game->getContentLicense()]);
        }

        if ($game->requestShouldBecomeNewVersion($request)) {
            switch ($reason) {
                case VersionData::UPDATE:
                    $game = $game->makeCopy();
                    break;
                case VersionData::COPY:
                    $game = $game->makeCopy(Session::get('authId'));
                    break;
            }
        }

        return [$game, $reason];
    }
}
