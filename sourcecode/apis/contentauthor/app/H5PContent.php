<?php

namespace App;

use App\Events\H5pContentDeleted;
use App\Events\H5pContentUpdated;
use App\Http\Libraries\H5PFileVersioner;
use App\Libraries\H5P\Dataobjects\H5PMetadataObject;
use App\Libraries\H5P\H5PLibraryAdmin;
use App\Libraries\H5P\Packages\QuestionSet;
use App\Libraries\Versioning\VersionableObject;
use H5PCore;
use H5PMetadata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Iso639p3;

use function route;

/**
 * @property string $user_id
 * @property int $library_id
 * @property string $parameters
 * @property string $filtered
 * @property string $slug
 * @property string $embed_type
 * @property int $disable
 * @property string $content_type
 * @property string $author
 * @property string $keywords
 * @property string $description
 * @property string $content_create_mode
 * @property string $language_iso_639_3
 * @property ?int $max_score
 * @property int $bulk_calculated
 *
 * @property Collection<Collaborator> $collaborators
 * @property H5PLibrary $library
 *
 * @see H5PContent::noMaxScoreScope()
 * @method static Builder noMaxScore()
 * @method self replicate(array $except = null)
 * @method static self|Builder make(array $attributes = [])
 * @method static self|Collection<self> find(string|array $id, string|array $columns = ['*'])
 * @method static self|Collection|Builder|Builder[] findOrFail(mixed $id, array|string $columns = ['*'])
 */
class H5PContent extends Content implements VersionableObject
{
    use HasFactory;

    protected $table = 'h5p_contents';
    public string $editRouteName = 'h5p.edit';

    protected $guarded = [
        'user_id',
        'version_id',
        'library_id',
    ];

    protected $casts = [
        'library_id' => "int",
        'is_draft' => 'boolean',
    ];

    protected $dispatchesEvents = [
        'updated' => H5pContentUpdated::class,
        'deleted' => H5pContentDeleted::class,
    ];

    /**
     * @return HasMany<H5PCollaborator, $this>
     */
    public function collaborators(): HasMany
    {
        return $this->hasMany(H5PCollaborator::class, 'h5p_id');
    }

    /**
     * @return BelongsTo<H5PLibrary, $this>
     */
    public function library(): BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class, 'library_id');
    }

    /**
     * @return HasMany<H5PContentsUserData, $this>
     */
    public function contentUserData(): HasMany
    {
        return $this->hasMany(H5PContentsUserData::class, 'content_id');
    }

    /**
     * @return HasMany<H5PContentLibrary, $this>
     */
    public function contentLibraries(): HasMany
    {
        return $this->hasMany(H5PContentLibrary::class, 'content_id');
    }

    /**
     * @return HasOne<H5PContentsMetadata, $this>
     */
    public function metadata(): HasOne
    {
        return $this->hasOne(H5PContentsMetadata::class, 'content_id');
    }

    public function getMetadataStructure(): array
    {
        /** @var H5PContentsMetadata $h5pmetadata */
        $h5pmetadata = $this->metadata()->first();
        if (is_null($h5pmetadata)) {
            $h5pmetadata = H5PContentsMetadata::make(['title' => $this->title]);
        }

        $metadataObject = $h5pmetadata->convertToMetadataObject($this->title);
        return $this->parseStructure($metadataObject);
    }

    public function parseStructure(H5PMetadataObject $metadataObject): array
    {
        return collect(H5PMetadataObject::H5PMetadataFieldsInOrder)
            ->flip()
            ->merge($metadataObject->toArray())
            ->filter(function ($value) {
                return !empty($value);
            })
            ->map(function ($value, $index) {
                if (in_array($index, ['authors', 'changes'])) {
                    return json_decode($value);
                }
                return $value;
            })
            ->toArray();
    }

    // Abstract method implementations
    protected function getContentContent(): string
    {
        $metadata = $this->getMetadataStructure();
        return sprintf('{"params":%s,"metadata":%s}', $this->parameters, json_encode($metadata));
    }

    protected function getRequestContent(Request $request): string
    {
        $parameters = json_decode($request->get('parameters'));
        if (!empty($parameters->metadata)) {
            $metadataRaw = (array) $parameters->metadata;
            $metadata = H5PMetadata::toDBArray($metadataRaw);

            $h5pMetadata = H5PContentsMetadata::make($metadata);
            $metadataObject = $h5pMetadata->convertToMetadataObject($this->getRequestTitle($request));
            $parsedMetadata = $this->parseStructure($metadataObject);
        } else {
            $parsedMetadata = $this->getMetadataStructure();
        }

        return json_encode([
            'params' => $parameters->params,
            'metadata' => $parsedMetadata,
        ]);
    }

    protected function getRequestLibrary(Request $request): string
    {
        return $request->get('library');
    }

    public function getContentOwnerId(): string
    {
        return $this->getOwnerId();
    }

    public function getISO6393Language(): string
    {
        return Iso639p3::code3letters($this->language_iso_639_3 ?? $this->metadata->default_language ?? 'eng');
    }

    public function makeCopy($owner = null): self
    {
        $newH5P = $this->replicate();
        $newH5P->version_id = null;
        //unset($newH5P->id);
        if ($owner) {
            $newH5P->user_id = $owner;
        }
        $newH5P->save();

        $this->contentLibraries->each(function ($contentLibrary) use ($newH5P) {
            $newH5P->contentLibraries()->create($contentLibrary->toArray());
        });

        $H5PFileVersioner = new H5PFileVersioner($this, $newH5P);
        $H5PFileVersioner->copy();

        return $newH5P;
    }

    /**
     * @return HasMany<H5PContentsVideo, $this>
     */
    public function contentVideos(): HasMany
    {
        return $this->hasMany(H5PContentsVideo::class, 'h5p_content_id');
    }

    public function requestShouldBecomeNewVersion(Request $request): bool
    {
        if ($this->isDraft()) {
            return $request->get("isNewLanguageVariant", false);
        }

        if ($request->get('isDraft')) {
            return true;
        }

        if (parent::requestShouldBecomeNewVersion($request) === true) {
            return true;
        }

        /** @var H5PLibrary $contentLibrary */
        $contentLibrary = $this->library()->first();
        $contentRequestLibrary = H5PLibrary::fromLibrary(H5PCore::libraryFromString($this->getRequestLibrary($request)))->get();
        if ($contentRequestLibrary->isNotEmpty() && $contentLibrary->isLibraryNewer($contentRequestLibrary->first())) {
            return true;
        }

        if ($request->get("isNewLanguageVariant", false)) {
            return true;
        }

        return false;
    }

    public function getContentType($withSubType = false): string
    {
        if (!$withSubType || !isset($this->library->name)) {
            return Content::TYPE_H5P;
        }

        return Str::lower($this->library->name);
    }

    protected function noMaxScoreScope(Builder $query): void
    {
        $query
            ->whereNull('max_score')
            ->orWhere(function (Builder $query) {
                $query->where('bulk_calculated', H5PLibraryAdmin::BULK_UNTOUCHED)
                    ->where('max_score', 0)
                    ->whereIn('library_id', function (\Illuminate\Database\Query\Builder $query) {
                        $query->select('id')
                            ->from('h5p_libraries')
                            ->where('name', QuestionSet::$machineName);
                    });
            });
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeNoMaxScore(Builder $query): void
    {
        $this->noMaxScoreScope($query);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOfBulkCalculated(Builder $query, $type): void
    {
        $query->where('bulk_calculated', $type);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setParentVersionId(string $parentVersionId): bool
    {
        // Is not tracked
        return false;
    }

    public function setVersionId(string $versionId): void
    {
        $this->version_id = $versionId;
    }

    public function getOwnerId(): string
    {
        return $this->user_id;
    }

    // Overrides Method from trait
    public function getPublicId(): string
    {
        return "h5p-" . $this->id;
    }

    public function getMaxScore(): int|null
    {
        return $this->max_score;
    }

    public function getAuthorOverwrite(): string|null
    {
        $contentMetadata = $this->metadata()->first();
        if (is_null($contentMetadata)) {
            return null;
        }

        $authors = json_decode($contentMetadata->authors);

        if (!is_array($authors) || count($authors) == 0 || !isset($authors[0]->name)) {
            return null;
        }

        return $authors[0]->name;
    }

    public function getUrl(): string
    {
        return route('h5p.show', [$this->id]);
    }

    public function getMachineName(): string
    {
        return $this->library()->firstOrFail()->name;
    }

    public function getCopyrightCacheKey(): string
    {
        return 'h5p-copyright-' . $this->id;
    }

    public function getInfoCacheKey(): string
    {
        return 'h5p-info-' . $this->id;
    }

    protected function getIconUrl(): string
    {
        return $this->library()->firstOrFail()->getIconUrl();
    }

    protected function getTags(): array
    {
        return [
            'h5p:' . $this->getMachineName(),
        ];
    }
}
