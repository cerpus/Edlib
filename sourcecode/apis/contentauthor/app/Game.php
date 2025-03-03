<?php

namespace App;

use App\Exceptions\GameTypeNotFoundException;
use App\Libraries\Games\Contracts\GameTypeContract;
use App\Libraries\Games\GameHandler;
use App\Libraries\Versioning\VersionableObject;
use App\Traits\Collaboratable;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Iso639p3;

use function route;

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
 * @method static self|Builder make(array $attributes = [])
 * @method static self|Collection<self> find(string|array $id, string|array $columns = ['*'])
 * @method static self|Collection|Builder|Builder[] findOrFail(mixed $id, array|string $columns = ['*'])
 */
class Game extends Content implements VersionableObject
{
    use Collaboratable;
    use HasFactory;
    use HasUuids;

    public string $editRouteName = 'game.edit';

    /**
     * @throws GameTypeNotFoundException
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
     * @return BelongsTo<Gametype, $this>
     */
    public function gameType(): BelongsTo
    {
        return $this->belongsTo(Gametype::class, 'gametype');
    }

    public function getGameSettingsAttribute(string $gameSettings): Object
    {
        return !empty($gameSettings) ? json_decode($gameSettings) : (object) [];
    }

    /**
     * @throws GameTypeNotFoundException
     */
    public function getGameTypeHandler(): GameTypeContract
    {
        return GameHandler::makeGameTypeFromId($this->gametype);
    }

    /**
     * @throws Exception
     */
    public function makeCopy(string|null $owner = null): Game
    {
        $game = $this->replicate();
        if (!is_null($owner)) {
            $game->owner = $owner;
        }
        if ($game->save() !== true) {
            throw new Exception(trans('game.could-not-make-copy-of-game', ["title" => $this->title]));
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

    public function setVersionId(string $versionId): void
    {
        $this->version_id = $versionId;
    }

    public function getUrl(): string
    {
        return route('game.show', [$this->id]);
    }

    public function getMachineName(): string
    {
        return 'Game';
    }

    protected function getTags(): array
    {
        return [
            'h5p:' . $this->getMachineName(),
        ];
    }

    public function getMaxScore(): int|null
    {
        try {
            $handler = $this->getGameTypeHandler();
            return $handler->getMaxScore();
        } catch (GameTypeNotFoundException) {
            return null;
        }
    }
}
