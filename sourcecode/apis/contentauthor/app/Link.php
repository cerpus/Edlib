<?php

namespace App;

use App\Libraries\Versioning\VersionableObject;
use App\Traits\Versionable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Iso639p3;

use function route;

/**
 * @property string $id
 * @property string $link_url
 * @property string $link_type
 * @property string $owner_id
 * @property int $deleted_at
 * @property string $link_text
 * @property ?string $metadata
 *
 * @method static self|Collection<self> find(string|array $id, string|array $columns = ['*'])
 * @method static self|Collection|Builder|Builder[] findOrFail(mixed $id, array|string $columns = ['*'])
 */
class Link extends Content implements VersionableObject
{
    use HasFactory;
    use HasUuids;
    use Versionable;

    public string $editRouteName = 'link.edit'; // note: doesn't work anymore

    public function givesScore(): int
    {
        return 0;
    }

    public function getContentOwnerId(): string
    {
        return $this->getOwnerId();
    }

    public function getISO6393Language(): string
    {
        return Iso639p3::code3letters('eng');
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

    public function getUrl(): string
    {
        return route('link.show', [$this->id]);
    }

    public function getMachineName(): string
    {
        return 'Link';
    }

    protected function getTags(): array
    {
        return [
            'h5p:' . $this->getMachineName(),
        ];
    }
}
