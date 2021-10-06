<?php

namespace App;

use App\Libraries\DataObjects\ResourceDataObject;
use App\Libraries\Versioning\VersionableObject;
use App\Traits\UuidForKey;
use Illuminate\Http\Request;
use Iso639p3;

/**
 * Class Link
 * @package App
 *
 * @property string link_url
 * @property string link_type
 * @property string owner_id
 * @property int deleted_at
 * @property string link_text
 * @property string metadata
 *
 * @method Link replicate(array $except = null)
 */
class Link extends Content implements VersionableObject
{
    use UuidForKey;

    private $parentId;

    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    public function givesScore()
    {
        return 0;
    }

    public function collaborators()
    {
        return $this->hasMany('App\ArticleCollaborator', 'article_id');
    }

    public function getContentOwnerId(): string
    {
        return $this->getOwnerId();
    }

    public function getISO6393Language(): string
    {
        return Iso639p3::code3letters('eng');
    }

    public function makeCopy($owner = null)
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

    public function getContentType($withSubType = false): string
    {
        return ResourceDataObject::LINK;
    }

    function getId(): string
    {
        return $this->id;
    }

    function getOwnerId(): string
    {
        return $this->owner_id;
    }

    function setParentVersionId(string $parentVersionId): bool
    {
        return false; // Not stored
    }

    function setVersionId(string $versionId)
    {
        $this->version_id = $versionId;
    }

    public function getIsPrivateAttribute()
    {
        return false; // Defaults to public / listed
    }
}
