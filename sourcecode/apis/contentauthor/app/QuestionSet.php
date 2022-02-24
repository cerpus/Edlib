<?php

namespace App;

use App\Libraries\DataObjects\ContentTypeDataObject;
use App\Libraries\DataObjects\ResourceDataObject;
use App\Traits\Collaboratable;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Iso639p3;

/**
 * Class QuestionSet
 * @package App
 *
 * @property string language_code
 * @property string owner
 * @property string external_reference
 * @property string tags
 * @property Collection<QuestionSetQuestion> questions
 *
 * @method static self find($id, $columns = ['*'])
 * @method static self findOrFail($id, $columns = ['*'])
 */
class QuestionSet extends Content
{
    use Collaboratable;
    use HasFactory;
    use UuidForKey;

    public string $editRouteName = 'questionset.edit';

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

    public static function getContentTypeInfo(string $contentType): ?ContentTypeDataObject
    {
        return new ContentTypeDataObject('QuestionSet', $contentType, 'Question set', "mui:DoneAll");
    }
}
