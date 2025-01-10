<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Models\Content;
use App\Models\User;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class ContentTransformer extends TransformerAbstract
{
    /** @var string[] */
    protected array $availableIncludes = [
        'roles',
        'versions',
    ];

    /** @var string[] */
    protected array $defaultIncludes = [
        'roles',
        'versions',
    ];

    public function __construct(
        private readonly ContentVersionTransformer $contentVersionTransformer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function transform(Content $content): array
    {
        return [
            'id' => $content->id,
            'created_at' => $content->created_at?->format('c'),
            'updated_at' => $content->updated_at?->format('c'),
            'deleted_at' => $content->deleted_at?->format('c'),
            'shared' => $content->shared,
            'links' => [
                'self' => route('api.contents.show', [$content]),
                'views' => route('api.contents.views.index', [$content]),
            ],
        ];
    }

    public function includeRoles(Content $content): Collection
    {
        return $this->collection($content->users, function (User $user) {
            assert(isset($user->pivot->role));

            return [
                'user_id' => $user->id,
                'role' => $user->pivot->role,
            ];
        });
    }

    public function includeVersions(Content $content): Collection
    {
        return $this->collection(
            $content->versions->all(),
            $this->contentVersionTransformer,
        );
    }
}
