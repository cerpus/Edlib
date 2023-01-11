<?php

namespace App;

use App\Libraries\DataObjects\ContentTypeDataObject;
use App\Libraries\Games\GameHandler;
use App\Libraries\Versioning\VersionableObject;
use App\Traits\Collaboratable;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Iso639p3;

/**
 * @property string $id
 * @property string $gametype
 * @property string $language_code
 * @property string $owner
 * @property object $game_settings
 * @property int $deleted_at
 *
 * @property Gametype $gameType
 *
 * @method static self find($id, $columns = ['*'])
 * @method static self findOrFail($id, $columns = ['*'])
 */
class Game extends Content implements VersionableObject
{
    use Collaboratable;
    use HasFactory;
    use UuidForKey;

    public string $editRouteName = 'game.edit';
    /**
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

    public function gameType(): BelongsTo
    {
        return $this->belongsTo(Gametype::class, 'gametype');
    }

    public function getGameSettingsAttribute(string $gameSettings): Object
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
        if ($game->save() !== true) {
            throw new \Exception(trans('game.could-not-make-copy-of-game', ["title" => $this->title]));
        }

        return $game;
    }

    public function getContentType(bool $withSubType = false): string
    {
        return Content::TYPE_GAME;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwnerId(): string
    {
        return $this->owner;
    }

    public function setParentVersionId(string $parentVersionId): bool
    {
        // Do nothing
        return false;
    }

    public function setVersionId(string $versionId)
    {
        $this->version_id = $versionId;
    }

    public static function getContentTypeInfo(string $contentType): ?ContentTypeDataObject
    {
        return new ContentTypeDataObject('Game', $contentType, 'Millionaire mini game', "mui:VideogameAsset");
    }
}
