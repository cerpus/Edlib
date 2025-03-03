<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Models\ContentView;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

final class ContentViewTransformer extends TransformerAbstract
{
    /**
     * @var string[]
     */
    protected array $availableIncludes = [
        'lti_platform',
    ];

    public function __construct(
        private readonly LtiPlatformTransformer $ltiPlatformTransformer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function transform(ContentView $view): array
    {
        return [
            'id' => $view->id,
            'source' => $view->source->value,
            'ip' => $view->ip,
            'viewed_on' => $view->created_at?->format('c'),
        ];
    }

    public function includeLtiPlatform(ContentView $view): Item|NullResource
    {
        $platform = $view->ltiPlatform;

        if ($platform === null) {
            return $this->null();
        }

        return $this->item($view->ltiPlatform, $this->ltiPlatformTransformer);
    }
}
