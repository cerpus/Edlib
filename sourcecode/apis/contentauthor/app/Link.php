<?php

namespace App;

use App\Libraries\Versioning\VersionableObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Iso639p3;

/**
 * @property string $id
 * @property string $link_url
 * @property string $link_type
 * @property string $owner_id
 * @property int $deleted_at
 * @property string $link_text
 * @property string $metadata
 *
 * @property Collection<Collaborator> $collaborators
 *
 * @method static self find($id, $columns = ['*'])
 * @method static self findOrFail($id, $columns = ['*'])
 */
class Link extends Content implements VersionableObject
{
    use HasFactory;
    use HasUuids;

    public function givesScore(): int
    {
        return 0;
    }

    /**
     * @return HasMany<ArticleCollaborator>
     */
    public function collaborators(): HasMany
    {
        return $this->hasMany(ArticleCollaborator::class, 'article_id');
    }

    public function getContentOwnerId(): string
    {
        return $this->getOwnerId();
    }

    public function getISO6393Language(): string
    {
        return Iso639p3::code3letters('eng');
    }

    public function makeCopy($owner = null): static
    {
        $newLink = $this->replicate();
        //$newLink->id = Uuid::uuid4()->toString();
        if (!is_null($owner)) {
            $newLink->owner_id = $owner;
        }
        $newLink->save();

        return $newLink;
    }

    protected function getRequestContent(Request $request)
    {
        return $request->input('linkUrl');
    }

    protected function getContentContent()
    {
        return $this->link_url;
    }

    public function getContentType(bool $withSubType = false): string
    {
        return Content::TYPE_LINK;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwnerId(): string
    {
        return $this->owner_id;
    }

    public function setParentVersionId(string $parentVersionId): bool
    {
        return false; // Not stored
    }

    public function setVersionId(string $versionId): void
    {
        $this->version_id = $versionId;
    }

    public function getIsPrivateAttribute(): false
    {
        return false; // Defaults to public / listed
    }
}
