<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class H5PLibrary
 * @package App
 *
 * @method static Builder fromLibrary($value)
 * @method static Builder fromLibraryName($value)
 * @method static Builder latestVersion()
 */
class H5PLibrary extends Model
{
    protected $table = 'h5p_libraries';

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        self::deleting(function($library){
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
        return $this->hasOne('App\H5PLibraryCapability', 'library_id');
    }

    public function description()
    {
        return $this->hasOne('App\LibraryDescription', 'library_id');
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

    public function libraries()
    {
        return $this->hasMany(H5PLibraryLibrary::class, 'library_id');
    }

    public function scopeFromLibraryName($query, $value)
    {
        return $query->where('name', 'LIKE', '%' . strtolower($value) . '%');
    }

    public function scopeLatestVersion($query)
    {
        return $query->orderBy('major_version', 'DESC')
            ->orderBy('minor_version', 'DESC')
            ->limit(1);
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

    public function getLibraryString($folderName = false)
    {
        return \H5PCore::libraryToString($this->getLibraryH5PFriendly(), $folderName);
    }

    public function getLibraryH5PFriendly($machineName = 'name')
    {
        return [
            'machineName' => $this->$machineName,
            'majorVersion' => $this->major_version,
            'minorVersion' => $this->minor_version,
        ];
    }

    public function getTitleAndVersionString()
    {
        return \H5PCore::libraryToString($this->getLibraryH5PFriendly('title'));
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
            ->leftJoin('h5p_libraries as l2', function($join){
                $join->on('l1.name', 'l2.name');
                $join->on(function($query)
                {
                    $query->on('l1.major_version', '<', 'l2.major_version');
                    $query->on(function ($query){
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
            ->map(function($addon){
                return (array)$addon;
            })
            ->toArray();
    }

    public function supportsMaxScore(): bool
    {
        $h5pLibraryDisk = \Storage::disk('h5p-uploads');
        $libraryLocation = sprintf('libraries/%s/presave.js', self::getLibraryString(true));
        if ($h5pLibraryDisk->exists($libraryLocation)){
            return true;
        }
        return false;
    }

    public function includeImageWidth(): bool
    {
        return !in_array($this->name, ['H5P.ThreeImage']);
    }
}
