<?php

namespace App;

use App\Traits\Collaboratable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Iso639p3;

use function route;

/**
 * @property string $language_code
 * @property string $owner
 * @property string $external_reference
 * @property string $tags
 * @property Collection<int, QuestionSetQuestion> $questions
 *
 * @method static self|Collection<self> find(string|array $id, string|array $columns = ['*'])
 * @method static self|Collection|Builder|Builder[] findOrFail(mixed $id, array|string $columns = ['*'])
 */
class QuestionSet extends Content
{
    use Collaboratable;
    use HasFactory;
    use HasUuids;

    protected $guarded = [
        'id',
        'owner',
    ];

    public string $editRouteName = 'questionset.edit';

    /**
     * @return HasMany<QuestionSetQuestion, $this>
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuestionSetQuestion::class)->ordered();
    }

    protected function getRequestContent(Request $request): true
    {
        // TODO: Implement getRequestContent() method.
        return true;
    }

    protected function getContentContent(): true
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

    public function getContentType(bool $withSubType = false): string
    {
        return Content::TYPE_QUESTIONSET;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return route('questionset.show', [$this->id]);
    }

    public function getMachineName(): string
    {
        return 'QuestionSet';
    }
}
