<?php

namespace App;

use App\Libraries\H5P\Dataobjects\H5PMetadataObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static self make(array $attributes = [])
 */
class H5PContentsMetadata extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $table = 'h5p_contents_metadata';

    /**
     * @return BelongsTo<H5PContent, self>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(H5PContent::class, 'content_id');
    }

    public function convertFromMetadataObject(H5PMetadataObject $metadataObject): array
    {
        return [
            'title' => $metadataObject->title,
            'authors' => $metadataObject->authors,
            'source' => $metadataObject->source,
            'year_from' => $metadataObject->yearFrom,
            'year_to' => $metadataObject->yearTo,
            'license' => $metadataObject->license,
            'license_version' => $metadataObject->licenseVersion,
            'license_extras' => $metadataObject->licenseExtras,
            'author_comments' => $metadataObject->authorComments,
            'changes' => $metadataObject->changes,
            'default_language' => $metadataObject->defaultLanguage,
        ];
    }

    public function convertToMetadataObject($title = null): H5PMetadataObject
    {
        return H5PMetadataObject::create([
            'title' => $title,
            'authors' => $this->authors,
            'source' => $this->source,
            'yearFrom' => $this->year_from,
            'yearTo' => $this->year_to,
            'license' => $this->license,
            'licenseVersion' => $this->license_version,
            'licenseExtras' => $this->license_extras,
            'authorComments' => $this->author_comments,
            'changes' => $this->getAttribute('changes'), // 'changes' conflicts with Eloquent variable that holds changed model attributes
            'defaultLanguage' => $this->default_language,
        ]);
    }
}
