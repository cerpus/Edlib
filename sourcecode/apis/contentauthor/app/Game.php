<?php

namespace App;

use App\Libraries\DataObjects\ContentTypeDataObject;
use App\Libraries\DataObjects\ResourceDataObject;
use App\Libraries\Versioning\VersionableObject;
use App\Traits\UuidForKey;
use Illuminate\Http\Request;
use App\Traits\Collaboratable;
use App\Libraries\Games\GameHandler;
use Iso639p3;

/**
 * Class Game
 * @package App
 *
 * @property string gametype
 * @property string language_code
 * @property string owner
 * @property string game_settings
 * @property int deleted_at
 *
 * @method Game findOrFail($id, $columns = ['*'])
 */
class Game extends Content implements VersionableObject
{
    use UuidForKey, Collaboratable;

    public $editRouteName = 'game.edit';
    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    protected function getRequestContent(Request $request)
    {
        $gametype = $this->getGameTypeHandler();
        return $gametype->createGameSettings($request->all());
    }

    /**
     * @return object
     */
    protected function getContentContent()
    {
        return $this->getOriginal('game_settings');
    }

    public function getContentOwnerId(): string
    {
        return $this->getOwnerId();
    }

    public function getISO6393Language(): string
    {
        return Iso639p3::code3letters('eng');
    }

    /**
     * @return Gametype
     */
    public function gameType()
    {
        return $this->belongsTo(Gametype::class, 'gametype');
    }

    /**
     * @param $gameSettings
     * @return object
     */
    public function getGameSettingsAttribute($gameSettings)
    {
        return !empty($gameSettings) ? json_decode($gameSettings) : (object)[];
    }


    /**
     * @return Libraries\Games\Contracts\GameTypeContract
     * @throws \Exception
     */
    public function getGameTypeHandler()
    {
        return GameHandler::makeGameTypeFromId($this->gametype);
    }

    /**
     * @param null|string $owner
     * @return Game
     * @throws \Exception
     */
    public function makeCopy($owner = null)
    {
        $game = $this->replicate();
        if (!is_null($owner)) {
            $game->owner = $owner;
        }
        if( $game->save() !== true ){
            throw new \Exception(trans('game.could-not-make-copy-of-game', ["title" => $this->title]));
        }

        return $game;
    }

    public function getContentType($withSubType = false): string
    {
        return ResourceDataObject::GAME;
    }

    function getId(): string
    {
        return $this->id;
    }

    function getOwnerId(): string
    {
        return $this->owner;
    }

    function setParentVersionId(string $parentVersionId): bool
    {
        // Do nothing
        return false;
    }

    function setVersionId(string $versionId)
    {
        $this->version_id = $versionId;
    }

    public static function getContentTypeInfo(string $contentType): ?ContentTypeDataObject
    {
        return new ContentTypeDataObject('Game', $contentType, 'Game', "mui:VideogameAsset");
    }
}
