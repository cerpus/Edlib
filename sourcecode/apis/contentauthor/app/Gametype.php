<?php

namespace App;

use App\Libraries\ContentAuthorStorage;
use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\Games\Contracts\GameTypeModelContract;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 *
 * @see Gametype::scopeOfName()
 * @method static Builder ofName($machineName)
 *
 * @method static findOrFail($id, $columns = ['*'])
 */

class Gametype extends Model implements GameTypeModelContract
{
    use HasFactory;
    use UuidForKey;

    private $scripts = [
        'c2runtime.js',
        'start.js',
        'register-sw.js',
    ];

    private $css = [
        'style.css',
    ];

    private $links = [
        [
            'rel' => 'manifest',
            'href' => 'appmanifest.json'
        ],
        [
            'rel' => 'apple-touch-icon',
            'href' => 'icons/icon-128.png',
            'sizes' => '128x128'
        ],
        [
            'rel' => 'apple-touch-icon',
            'href' => 'icons/icon-256.png',
            'sizes' => '256x256'
        ],
        [
            'rel' => 'icon',
            'href' => 'icons/icon-256.png',
            'type' => 'image/png'
        ],
    ];

    public function games()
    {
        return $this->hasMany(Game::class, 'gametype');
    }

    public function scopeOfName($query, $machineName)
    {
        return $query->where('name', $machineName);
    }

    public function scopeOfMajorVersion($query, $version)
    {
        return $query->where('major_version', $version);
    }

    public function scopeOfMinorVersion($query, $version)
    {
        return $query->where('minor_version', $version);
    }

    public function scopeOfGameType($query, $machineName, $majorVersion, $minorVersion)
    {
        $query = $this->scopeOfName($query, $machineName);
        $query = $this->scopeOfMajorVersion($query, $majorVersion);
        return $this->scopeOfMinorVersion($query, $minorVersion);
    }

    public function getVersion()
    {
        return $this->major_version . '.' . $this->minor_version;
    }

    // Return the most updated game by version of a machine

    /**
     * @param string $machineName
     */
    public static function mostRecent($machineName = null): self|null
    {
        if (!$machineName) {
            return null;
        }

        return self::where('name', $machineName)
            ->orderBy('major_version', 'desc')
            ->orderBy('minor_version', 'desc')
            ->limit(1)
            ->first();
    }

    public function getScripts()
    {
        return $this->scripts;
    }

    public function getCss()
    {
        return $this->css;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function getPublicFolder()
    {
        $contentAuthorStorage = app(ContentAuthorStorage::class);
        return $contentAuthorStorage->getAssetUrl(sprintf(ContentStorageSettings::GAMES_PATH, $this->getMachineFolder()), true) . '/';
    }

    public function getMachineFolder()
    {
        return 'millionaire/' . $this->getVersion() . '/';
    }

    public function getAssets($type = null)
    {
        $assets = collect();
        $machinePath = $this->getMachineFolder();
        $contentAuthorStorage = app(ContentAuthorStorage::class);
        switch ($type) {
            case 'scripts':
                $assets = collect($this->getScripts())->map(function ($script) use ($machinePath, $contentAuthorStorage) {
                    return $contentAuthorStorage->getAssetUrl(sprintf(ContentStorageSettings::GAMES_FILE, $machinePath, $script));
                });
                break;

            case 'css':
                $assets = collect($this->getCss())->map(function ($css) use ($machinePath, $contentAuthorStorage) {
                    return $contentAuthorStorage->getAssetUrl(sprintf(ContentStorageSettings::GAMES_FILE, $machinePath, $css));
                });
                break;

            case 'links':
                $assets = collect($this->getLinks())->map(function ($link) use ($machinePath, $contentAuthorStorage) {
                    $link['href'] = $contentAuthorStorage->getAssetUrl(sprintf(ContentStorageSettings::GAMES_FILE, $machinePath, $link['href']));
                    return (object)$link;
                });
                break;
            default:
                break;
        }

        return $assets->toArray();
    }
}
