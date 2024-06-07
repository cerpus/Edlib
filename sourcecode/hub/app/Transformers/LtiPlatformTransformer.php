<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Models\LtiPlatform;
use League\Fractal\TransformerAbstract;

final class LtiPlatformTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(LtiPlatform $platform): array
    {
        return [
            'id' => $platform->id,
            'name' => $platform->name,
            'key' => $platform->key,
            'enable_sso' => $platform->enable_sso,
            'created_at' => $platform->created_at,
            'updated_at' => $platform->updated_at,
        ];
    }
}
