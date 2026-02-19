<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Context\DeepLinkingProps as Prop;
use App\EdlibResourceKit\Lti\Message\DeepLinking\LineItem;

final readonly class LineItemSerializer implements LineItemSerializerInterface
{
    public function __construct(
        private ScoreConstraintsSerializerInterface $scoreConstraintsSerializer = new ScoreConstraintsSerializer(),
    ) {
    }

    public function serialize(LineItem $lineItem): array
    {
        $serialized = [
            '@type' => 'LineItem',
        ];

        if ($lineItem->getScoreConstraints() !== null) {
            $serialized[Prop::SCORE_CONSTRAINTS] = $this
                ->scoreConstraintsSerializer
                ->serialize($lineItem->getScoreConstraints());
        }

        return $serialized;
    }
}
