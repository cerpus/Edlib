<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

use function collect;

/**
 * @property string $id
 *
 * @see Gametype::scopeOfName()
 * @method static Builder ofName($machineName)
 *
 * @method static findOrFail($id, $columns = ['*'])
 */

class Gametype extends Model
{
    use HasFactory;
    use HasUuids;

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
            'href' => 'appmanifest.json',
        ],
        [
            'rel' => 'apple-touch-icon',
            'href' => 'icons/icon-128.png',
            'sizes' => '128x128',
        ],
        [
            'rel' => 'apple-touch-icon',
            'href' => 'icons/icon-256.png',
            'sizes' => '256x256',
        ],
        [
            'rel' => 'icon',
            'href' => 'icons/icon-256.png',
            'type' => 'image/png',
        ],
    ];

    /**
     * @return HasMany<Game, $this>
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class, 'gametype');
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOfName(Builder $query, $machineName): void
    {
        $query->where('name', $machineName);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOfMajorVersion(Builder $query, $version): void
    {
        $query->where('major_version', $version);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOfMinorVersion(Builder $query, $version): void
    {
        $query->where('minor_version', $version);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOfGameType(Builder $query, $machineName, $majorVersion, $minorVersion): void
    {
        $this->scopeOfName($query, $machineName);
        $this->scopeOfMajorVersion($query, $majorVersion);
        $this->scopeOfMinorVersion($query, $minorVersion);
    }

    public function getVersion(): string
    {
        return $this->major_version . '.' . $this->minor_version;
    }

    // Return the most updated game by version of a machine

    public static function mostRecent(string $machineName = null): self|null
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

    public function getBasePath(): string
    {
        return Storage::disk()->url('games/millionaire/' . $this->getVersion() . '/');
    }

    /**
     * @return array<int, object{
     *     rel: string,
     *     href: string,
     *     sizes?: string,
     *     type?: string,
     * }>
     */
    public function getLinks(): array
    {
        $fs = Storage::disk();
        $prefix = 'games/millionaire/' . $this->getVersion();

        return collect($this->links)->map(fn(array $link): object => (object) [
            ...$link,
            'href' => $fs->url("$prefix/{$link['href']}"),
        ])->toArray();
    }

    /**
     * Get a list of fully qualified URLs for the game CSS.
     * @return string[]
     */
    public function getCss(): array
    {
        $fs = Storage::disk();
        $prefix = 'games/millionaire/' . $this->getVersion();

        return collect($this->css)
            ->map(fn($css) => $fs->url("$prefix/$css"))
            ->toArray();
    }

    /**
     * Get a list of fully qualified URLs for the game scripts.
     * @return string[]
     */
    public function getScripts(): array
    {
        $fs = Storage::disk();
        $prefix = 'games/millionaire/' . $this->getVersion();

        return collect($this->scripts)
            ->map(fn($script) => $fs->url("$prefix/$script"))
            ->toArray();
    }
}
