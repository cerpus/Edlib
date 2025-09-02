<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Models\ContentVersion;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

final class ContentVersionTransformer extends TransformerAbstract
{
    /** @var string[] */
    protected array $defaultIncludes = [
        'tags',
    ];

    /** @var string[] */
    protected array $availableIncludes = [
        'lti_tool',
    ];

    public function __construct(
        private readonly LtiToolTransformer $ltiToolTransformer,
        private readonly TagTransformer $tagTransformer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function transform(ContentVersion $version): array
    {
        return [
            'id' => $version->id,
            'content_id' => $version->content_id,
            'lti_tool_id' => $version->lti_tool_id,
            'edited_by' => $version->edited_by,
            'created_at' => $version->created_at?->format('c'),
            'lti_launch_url' => $version->lti_launch_url,
            'title' => $version->title,
            'license' => $version->license,
            'language_iso_639_3' => $version->language_iso_639_3,
            'published' => $version->published,
            'min_score' => $version->min_score,
            'max_score' => $version->max_score,
            'displayed_content_type' => $version->displayed_content_type,
            'links' => [
                'self' => route('api.contents.versions.show', [$version->content_id, $version->id]),
                'content' => route('api.contents.show', [$version->content_id]),
                'lti_tool' => route('api.lti-tools.show', [$version->lti_tool_id]),
            ],
            'tags' => $version->getSerializedTags(),
        ];
    }

    public function includeLtiTool(ContentVersion $contentVersion): Item
    {
        return $this->item(
            $contentVersion->tool()->firstOrFail(),
            $this->ltiToolTransformer,
        );
    }

    public function includeTags(ContentVersion $contentVersion): Collection
    {
        return $this->collection(
            $contentVersion->tags,
            $this->tagTransformer,
        );
    }
}
