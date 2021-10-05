<?php

namespace App;

use App\Libraries\DataObjects\ResourceDataObject;
use App\Traits\UuidForKey;
use Illuminate\Http\Request;
use App\Traits\Collaboratable;
use Iso639p3;

/**
 * Class QuestionSet
 * @package App
 *
 * @property string language_code
 * @property string owner
 * @property string external_reference
 * @property string tags
 */
class QuestionSet extends Content
{
    use UuidForKey, Collaboratable;

    public $editRouteName = 'questionset.edit';

    public function questions()
    {
        return $this->hasMany(QuestionSetQuestion::class)->ordered();
    }

    protected function getRequestContent(Request $request)
    {
        // TODO: Implement getRequestContent() method.
        return true;
    }

    protected function getContentContent()
    {
        // TODO: Implement getContentContent() method.
        return true;
    }

    public function getContentOwnerId(): string
    {
        return $this->owner;
    }

    public function getISO6393Language(): string
    {
        return Iso639p3::code3letters('eng');
    }

    public function getContentType($withSubType = false): string
    {
        return ResourceDataObject::QUESTIONSET;
    }

    function getId(): string
    {
        return $this->id;
    }
}
