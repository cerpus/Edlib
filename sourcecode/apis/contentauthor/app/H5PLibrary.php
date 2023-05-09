<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read Collection<array-key, H5PLibraryLibrary> $libraries
 *
 * @see H5PLibrary::scopeFromLibrary()
 * @method static Builder|static fromLibrary($value)
 *
 * @see H5PLibrary::scopeFromMachineName()
 * @method static Builder|static fromMachineName($machineName)
 *
 * @see H5PLibrary::scopeLatestVersion()
 * @method static Builder|static latestVersion()
 *
 * @see H5PLibrary::scopeVersion()
 * @method static Builder|static version($majorVersion, $minorVersion)
 *
 * @method static find($id, $columns = ['*'])
 */
class H5PLibrary extends Model
{
    use HasFactory;

    protected $table = 'h5p_libraries';

    protected $guarded = ['id'];

    protected $casts = [
        'patch_version_in_folder_name' => 'bool',
    ];

    protected static function boot()
    {
        parent::boot();

        self::deleting(function ($library) {
            $library->languages()->delete();
            $library->libraries()->delete();
        });
    }

    public function getMachineNameAttribute()
    {
        return $this->name;
    }

    public function capability()
    {
        return $this->hasOne(H5PLibraryCapability::class, 'library_id');
    }

    public function description(): HasOne
    {
        return $this->hasOne(LibraryDescription::class, 'library_id');
    }

    public function contents()
    {
        return $this->hasMany(H5PContent::class, 'library_id');
    }

    public function contentsWithNoScore()
    {
        return $this->contents()->whereNull('max_score');
    }

    public function languages()
    {
        return $this->hasMany(H5PLibraryLanguage::class, 'library_id');
    }

    public function libraries(): HasMany
    {
        return $this->hasMany(H5PLibraryLibrary::class, 'library_id');
    }

    public function scopeFromMachineName($query, $machineName)
    {
        return $query->where('name', $machineName);
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderBy('major_version', 'DESC')
            ->orderBy('minor_version', 'DESC')
            ->limit(1);
    }

    public function scopeVersion($query, $majorVersion, $minorVersion)
    {
        return $query
            ->where('major_version', $majorVersion)
            ->where('minor_version', $minorVersion);
    }

    public function scopeRunnable($query)
    {
        return $query->where('runnable', 1);
    }

    public function getVersions($asModels = false)
    {
        $versions = DB::table("h5p_libraries")
            ->select('*')
            ->where('name', $this->name)
            ->orderBy('title', 'ASC')
            ->orderBy('major_version', 'ASC')
            ->orderBy('minor_version', 'ASC')
            ->get();
        return $asModels !== true ? $versions : $this->hydrate($versions->toArray());
    }

    /**
     * @param bool $folderName True to get name suitable for folder
     * @param bool|null $fullVersion Null to use patchVersionInFolderName, true/false to override
     */
    public function getLibraryString(bool $folderName = false, ?bool $fullVersion = null): string
    {
        return self::getLibraryName([
            'machineName' => $this->name,
            'majorVersion' => $this->major_version,
            'minorVersion' => $this->minor_version,
            'patchVersion' => $this->patch_version,
            'patchVersionInFolderName' => $this->patch_version_in_folder_name,

        ], $folderName, $fullVersion);
    }

    /**
     * @param array $libraryData Key names: machineName/name, majorVersion, minorVersion, patchVersion, patchVersionInFolderName
     * @param bool|null $fullVersion Null to use patchVersionInFolderName, true/false to override
     * @throws \InvalidArgumentException If requesting full version without patchVersion present in data
     */
    public static function libraryToFolderName(array $libraryData, ?bool $fullVersion = null): string
    {
        return self::getLibraryName($libraryData, true, $fullVersion);
    }

    /**
     * @param array $libraryData Key names: machineName/name, majorVersion, minorVersion, patchVersion, patchVersionInFolderName
     * @param bool|null $fullVersion Null to use patchVersionInFolderName, true/false to override
     * @throws \InvalidArgumentException If requesting full version without patchVersion present in data
     */
    public static function libraryToString(array $libraryData, ?bool $fullVersion = null): string
    {
        return self::getLibraryName($libraryData, false, $fullVersion);
    }

    /**
     * @throws \InvalidArgumentException If requesting full version without patchVersion present in data
     */
    private static function getLibraryName(array $libraryData, bool $asFolder, ?bool $fullVersion): string
    {
        $usePatch = $fullVersion === true || ($fullVersion === null && array_key_exists('patchVersionInFolderName', $libraryData) && $libraryData['patchVersionInFolderName']);
        if ($usePatch && !isset($libraryData['patchVersion'])) {
            throw new \InvalidArgumentException('Full version name requested but patch version missing');
        }

        if ($usePatch) {
            $format = $asFolder ? '%s-%d.%d.%d' : '%s %d.%d.%d';
        } else {
            $format = $asFolder ? '%s-%d.%d' : '%s %d.%d';
        }

        return sprintf(
            $format,
            $libraryData['machineName'] ?? $libraryData['name'],
            $libraryData['majorVersion'],
            $libraryData['minorVersion'],
            $libraryData['patchVersion'] ?? ''
        );
    }

    public function getLibraryH5PFriendly($machineName = 'name')
    {
        return [
            'machineName' => $this->$machineName,
            'majorVersion' => $this->major_version,
            'minorVersion' => $this->minor_version,
            'patchVersion' => $this->patch_version,
            'patchVersionInFolderName' => $this->patch_version_in_folder_name,
        ];
    }

    public function getTitleAndVersionString()
    {
        return self::getLibraryName([
            'machineName' => $this->title,
            'majorVersion' => $this->major_version,
            'minorVersion' => $this->minor_version,
            'patchVersion' => $this->patch_version,
        ], false, true);
    }

    public function scopeFromLibrary($query, $value)
    {
        list($machineName, $majorVersion, $minorVersion) = array_values($value);
        return $query->where('name', $machineName)
            ->where('major_version', $majorVersion)
            ->where('minor_version', $minorVersion);
    }

    public function getUpgrades($toArray = true)
    {
        $versions = $this->getVersions()->toArray();
        /** @var \H5PCore $core */
        $core = resolve(\H5PCore::class);
        $upgradeVersions = collect($core->getUpgrades($this, $versions))
            ->map(function ($version, $id) {
                $library = H5PLibrary::find($id);
                return [
                    'id' => $id,
                    'version' => $version,
                    'name' => $library->getLibraryString(),
                    'machineName' => $library->name,
                    'majorVersion' => $library->major_version,
                    'minorVersion' => $library->minor_version,
                ];
            })
            ->values();

        return $toArray === true ? $upgradeVersions->toArray() : $upgradeVersions;
    }

    public function isUpgradable()
    {
        return $this->getUpgrades(false)->isNotEmpty() && $this->contents()->count() > 0;
    }

    public function isLibraryTypeIdentical(H5PLibrary $comparingLibrary)
    {
        return $this->name === $comparingLibrary->name;
    }

    public function isLibraryNewer(H5PLibrary $compareLibrary)
    {
        return $this->getUpgrades(false)
            ->filter(function ($library) use ($compareLibrary) {
                return $library['id'] === $compareLibrary->id;
            })
            ->isNotEmpty();
    }

    public function getAddons()
    {
        return DB::table("h5p_libraries as l1")
            ->leftJoin('h5p_libraries as l2', function ($join) {
                $join->on('l1.name', 'l2.name');
                $join->on(function ($query) {
                    $query->on('l1.major_version', '<', 'l2.major_version');
                    $query->on(function ($query) {
                        $query->on('l1.major_version', 'l2.major_version')
                            ->orOn('l1.minor_version', '<', 'l2.minor_version');
                    });
                });
            })
            ->select([
                'l1.id as libraryId',
                'l1.add_to as addTo',
                'l1.name as machineName',
                'l1.major_version as majorVersion',
                'l1.minor_version as minorVersion',
                'l1.patch_version as patchVersion',
                'l1.preloaded_js as preloadedJs',
                'l1.preloaded_css as preloadedCss',
            ])
            ->whereNull('l2.name')
            ->whereNotNull('l1.add_to')
            ->get()
            ->map(function ($addon) {
                return (array)$addon;
            })
            ->toArray();
    }

    public function supportsMaxScore(): bool
    {
        $libraryLocation = sprintf('libraries/%s/presave.js', self::getLibraryString(true));
        if (Storage::disk()->exists($libraryLocation)) {
            return true;
        }
        return false;
    }

    public function includeImageWidth(): bool
    {
        return !in_array($this->name, ['H5P.ThreeImage', 'H5P.NDLAThreeImage']);
    }
}
