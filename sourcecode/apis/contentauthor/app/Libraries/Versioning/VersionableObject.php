<?php

namespace App\Libraries\Versioning;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TRelated of Model
 * @template TDeclaring of Model
 * @property-read TRelated|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<TRelated> $children
 */
interface VersionableObject
{
    public const PURPOSE_INITIAL = 'Initial';
    public const PURPOSE_CREATE = 'Create';
    public const PURPOSE_UPDATE = 'Update';
    public const PURPOSE_IMPORT = 'Import';
    public const PURPOSE_COPY = 'Copy';
    public const PURPOSE_UPGRADE = 'Upgrade';
    public const PURPOSE_TRANSLATION = 'Translation';

    /**
     * @return HasMany<TRelated, TDeclaring>
     */
    public function children(): HasMany;

    /**
     * @return BelongsTo<TRelated, TDeclaring>
     */
    public function parent(): BelongsTo;

    public function getVersionPurpose(): string;
}
