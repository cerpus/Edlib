<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class H5PLibraryLanguage extends Model
{
    use HasFactory;

    protected $table = 'h5p_libraries_languages';

    protected $fillable = [
        'library_id',
        'language_code',
        'translation'
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo<H5PLibrary, self>
     */
    public function library(): BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeFromLibrary(Builder $query, array $library): void
    {
        list($machineName, $majorVersion, $minorVersion) = array_values($library);
        $query->whereHas('library', function (Builder $query) use ($machineName, $majorVersion, $minorVersion) {
            /** @var Builder<self> $query */
            $query->fromLibrary([$machineName, $majorVersion, $minorVersion]);
        });
    }
}
