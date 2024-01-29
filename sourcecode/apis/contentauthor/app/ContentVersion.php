<?php

declare(strict_types=1);

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property string $content_id
 * @property string $content_type
 * @property string $parent_id
 * @property Carbon $created_at
 * @property string $version_purpose
 * @property string $user_id
 * @property bool $linear_versioning
 *
 * @property Collection<self> $nextVersions
 * @property self|null $previousVersion
 *
 * @method static self|null create(array $attributes = [])
 * @method static self|Builder make(array $attributes = [])
 * @method static self|Collection<self> find(string|array $id, string|array $columns = ['*'])
 * @method static self|Collection|Builder|Builder[] findOrFail(mixed $id, array|string $columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class ContentVersion extends Model
{
    use HasFactory;
    use HasUuids;
    use HasTimestamps;

    // Disable 'updated_at'
    public const UPDATED_AT = null;

    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $guarded = [];
    protected $casts = [
        'linear_versioning' => 'boolean',
    ];

    public const PURPOSE_INITIAL = 'Initial';
    public const PURPOSE_CREATE = 'Create';
    public const PURPOSE_UPDATE = 'Update';
    public const PURPOSE_IMPORT = 'Import';
    public const PURPOSE_COPY = 'Copy';
    public const PURPOSE_UPGRADE = 'Upgrade';
    public const PURPOSE_TRANSLATION = 'Translation';

    /**
     * Previous, or parent, version
     */
    public function previousVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class, 'id', 'parent_id');
    }

    /**
     * All versions that are based on this version, i.e. this is the parent version
     */
    public function nextVersions(): HasMany
    {
        return $this->hasMany(ContentVersion::class, 'parent_id', 'id')->orderBy('created_at');
    }

    // Is this a leaf node, i.e. it has no nextVersion of types PURPOSE_UPDATE or PURPOSE_UPGRADE
    public function isLeaf(): bool
    {
        return $this->nextVersions()
            ->whereIn('version_purpose', [self::PURPOSE_UPDATE, self::PURPOSE_UPGRADE])
            ->doesntExist();
    }

    /**
     * Return the latest version using this version as start
     */
    public function latestVersion(): ?self
    {
        return self::findLatestLeaf($this);
    }

    /**
     * Returns the latest version using $versionId as start
     * @throws ModelNotFoundException If the start version does not exist
     */
    public static function latest(string $versionId): ?self
    {
        $version = self::findorFail($versionId);
        return self::findLatestLeaf($version);
    }

    /**
     * Find the latest created leaf node of $version
     */
    private static function findLatestLeaf(ContentVersion $version): ?self
    {
        if ($version->isLeaf()) {
            return $version;
        } else {
            while ($version) {
                $children = $version->nextVersions()->whereIn('version_purpose', [self::PURPOSE_UPDATE, self::PURPOSE_UPGRADE])->get();
                if ($children->count() === 0) {
                    return $version;
                } elseif ($children->count() === 1) {
                    $version = $children->first();
                } else {
                    // With multiple child nodes we must compare the created time of their leaf nodes
                    return $children->map(function ($item) {
                        return self::findLatestLeaf($item);
                    })
                    ->reduce(function ($latest, $leaf) {
                        if ($latest === null) {
                            return $leaf;
                        }
                        return $leaf->created_at->isAfter($latest->created_at) ? $leaf : $latest;
                    });
                }
            }
        }

        return null;
    }

    public function getContent(): H5PContent|Article|Game|Link|QuestionSet|null
    {
        return Content::findContentById($this->content_id);
    }
}
