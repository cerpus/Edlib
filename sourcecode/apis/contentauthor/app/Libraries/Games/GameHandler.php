<?php

namespace App\Libraries\Games;

use App\ContentVersion;
use App\Events\GameWasSaved;
use App\Exceptions\GameTypeNotFoundException;
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
    public function store(array $values, GameTypeContract $gametype): Game
    {
        $game = new Game();

        $game->title = $values['title'];
        $game->gametype = $gametype->getGameType();
        $game->language_code = $gametype->convertLanguageCode($values['language_code'] ?? App::getLocale());
        $game->owner = $values['authId'];
        $game->game_settings = $gametype->createGameSettings($values);
        $game->license = $values['license'];

        $game->save();

        event(new GameWasSaved($game, new ResourceMetadataDataObject(
            license: $values['license'],
            reason: ContentVersion::PURPOSE_CREATE,
            tags: $values['tags'],
        )));

        return $game;
    }

    /**
     * @throws GameTypeNotFoundException
     */
    public static function getGameTypeInstance(string $type): GameTypeContract
    {
        return match ($type) {
            'millionaire', Millionaire::$machineName => app(Millionaire::class),
            default => throw new GameTypeNotFoundException(trans('game.could-not-find-the-gametype', ["gametype" => $type])),
        };
    }

    /**
     * @throws GameTypeNotFoundException
     */
    public static function makeGameTypeFromId($gametypeId): GameTypeContract
    {
        $gametypes = Gametype::find($gametypeId);

        if ($gametypes === null) {
            throw new GameTypeNotFoundException(trans('game.gametype-not-found'));
        }

        return self::getGameTypeInstance($gametypes->name);
    }

    public function update(Game $game, Request $request): Game
    {
        $game = $this->handleCopy($game, $request);
        $gametype = self::makeGameTypeFromId($game->gameType->id);

        $game->title = $request->get('title');
        $game->game_settings = $gametype->createGameSettings($request->all());
        $game->license = $request->input('license');

        $game->save();

        event(new GameWasSaved($game, new ResourceMetadataDataObject(
            license: $request->get('license'),
            reason: ContentVersion::PURPOSE_COPY,
            tags: $request->get('tags', []),
        )));

        return $game;
    }

    private function handleCopy(Game $game, Request $request): Game
    {
        if (!$request->get("license", false)) {
            $request->merge(["license" => $game->getContentLicense()]);
        }

        return $game->makeCopy(Session::get('authId'));
    }
}
