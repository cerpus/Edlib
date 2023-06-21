<?php

namespace App\Models;

use App\Lti\Oauth1\Oauth1Request;
use App\Lti\Oauth1\Oauth1SignerInterface;
use BadMethodCallException;
use Cerpus\EdlibResourceKit\Lti\ContentItem\ContentItemPlacement;
use Cerpus\EdlibResourceKit\Lti\ContentItem\ContentItems;
use Cerpus\EdlibResourceKit\Lti\ContentItem\LtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\ContentItem\PresentationDocumentTarget;
use Cerpus\EdlibResourceKit\Lti\ContentItem\Serializer\ContentItemsSerializerInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Builder as ScoutBuilder;
use Laravel\Scout\Searchable;

use function is_string;
use function session;

use const JSON_THROW_ON_ERROR;

class Content extends Model
{
    use HasFactory;
    use HasUlids;
    use Searchable;

    protected $perPage = 48;

    public function toItemSelectionRequest(): Oauth1Request
    {
        $returnUrl = session()->get('lti.content_item_return_url')
            ?? throw new BadMethodCallException('Not in LTI selection context');
        assert(is_string($returnUrl));

        $version = $this->latestPublishedVersion
            ?? throw new BadMethodCallException('Calling the thing on content without published version');
        assert($version->resource !== null);

        $contentItems = new ContentItems([
            new LtiLinkItem(
                mediaType: 'application/vnd.ims.lti.v1.ltilink',
                title: $version->resource->title,
                url: url()->route('content.preview', [$this->id]),
                placementAdvice: new ContentItemPlacement(
                    presentationDocumentTarget: PresentationDocumentTarget::Iframe,
                ),
            ),
        ]);

        $serializer = app()->make(ContentItemsSerializerInterface::class);
        $oauth1Signer = app()->make(Oauth1SignerInterface::class);

        $credentials = LtiPlatform::where('key', session()->get('lti.oauth_consumer_key'))
            ->firstOrFail()
            ->getOauth1Credentials();

        return $oauth1Signer->sign(new Oauth1Request('POST', $returnUrl, [
            'content_items' => json_encode(
                $serializer->serialize($contentItems),
                flags: JSON_THROW_ON_ERROR,
            ),
            'lti_message_type' => 'ContentItemSelection',
        ]), $credentials);
    }

    public function createCopyBelongingTo(User $user): self
    {
        return DB::transaction(function () use ($user) {
            $version = $this->latestPublishedVersion
                ?? throw new Exception('No published version');

            // TODO: title for resource copies
            // TODO: somehow denote content is copied
            $copy = new Content();
            $copy->save();
            $copy->versions()->save($version->replicate());
            $copy->users()->save($user, ['role' => ContentUserRole::Owner]);

            return $copy;
        });
    }

    /**
     * @return HasOne<ContentVersion>
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class)
            ->has('resource')
            ->latestOfMany();
    }

    /**
     * @return HasOne<ContentVersion>
     */
    public function latestPublishedVersion(): HasOne
    {
        return $this->hasOne(ContentVersion::class)
            ->has('resource')
            ->ofMany(['id' => 'max'], function (Builder $query) {
                /** @var Builder<ContentVersion> $query */
                $query->published();
            });
    }

    /**
     * @return HasMany<ContentVersion>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ContentVersion::class)->orderBy('id', 'DESC');
    }

    /**
     * @return BelongsToMany<User>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withCasts([
                'role' => ContentUserRole::class,
            ])
            ->withTimestamps();
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $version = $this->latestPublishedVersion ?? $this->latestVersion;
        assert($version !== null);

        $title = $version->resource?->title ?? null;
        assert($title !== null);

        return [
            'id' => $this->id,
            'has_draft' => $this->latestVersion !== $this->latestPublishedVersion,
            'published' => $this->latestPublishedVersion !== null,
            'title' => $title,
            'user_ids' => $this->users()->allRelatedIds()->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->versions()->has('resource')->exists();
    }

    public static function findShared(string $query = ''): ScoutBuilder
    {
        return Content::search($query)
            ->where('published', true)
            ->orderBy('updated_at', 'desc')
            ->query(fn (Builder $query) => $query->with([
                'latestPublishedVersion',
            ]));
    }

    public static function findForUser(User $user, string $query = ''): ScoutBuilder
    {
        return Content::search($query)
            ->where('user_ids', $user->id)
            ->orderBy('updated_at', 'desc')
            ->query(fn (Builder $query) => $query->with([
                'latestVersion',
                'latestVersion.resource'
            ]));
    }
}
