<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Models\ContentViewsAccumulated;
use League\Fractal\Resource\ResourceAbstract;
use League\Fractal\TransformerAbstract;

final class ContentViewsAccumulatedTransformer extends TransformerAbstract
{
    /**
     * @var string[]
     */
    protected array $availableIncludes = ['content', 'lti_platform'];

    public function __construct(
        private readonly ContentTransformer $contentTransformer,
        private readonly LtiPlatformTransformer $ltiPlatformTransformer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function transform(ContentViewsAccumulated $viewsAccumulated): array
    {
        return [
            'id' => $viewsAccumulated->id,
            'source' => $viewsAccumulated->source->value,
            'view_count' => $viewsAccumulated->view_count,
            'date' => $viewsAccumulated->date,
            'hour' => $viewsAccumulated->hour,
        ];
    }

    public function includeContent(ContentViewsAccumulated $viewsAccumulated): ResourceAbstract
    {
        return $this->item($viewsAccumulated->content, $this->contentTransformer);
    }

    public function includeLtiPlatformTransformer(ContentViewsAccumulated $viewsAccumulated): ResourceAbstract
    {
        $platform = $viewsAccumulated->ltiPlatform;

        if ($platform === null) {
            return $this->null();
        }

        return $this->item($platform, $this->ltiPlatformTransformer);
    }
}
