<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Models\LtiTool;
use League\Fractal\TransformerAbstract;

final class LtiToolTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(LtiTool $tool): array
    {
        return [
            'id' => $tool->id,
            'consumer_key' => $tool->consumer_key,
            'deep_linking_url' => $tool->creator_launch_url,
            'edit_mode' => $tool->edit_mode->value,
            'proxies_lti_launches' => $tool->proxy_launch,
            'send_email' => $tool->send_email,
            'send_name' => $tool->send_name,
            'links' => [
                'self' => route('api.lti-tools.show', [$tool]),
            ],
        ];
    }
}