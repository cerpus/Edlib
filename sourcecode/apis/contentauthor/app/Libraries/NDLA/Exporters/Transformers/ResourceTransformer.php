<?php

namespace App\Libraries\NDLA\Exporters\Transformers;

use League\Fractal\TransformerAbstract;

class ResourceTransformer extends TransformerAbstract
{
    public function transform($resource)
    {
        $title = $resource->name ?? "Resource: If you see this there is a bug in CA.";
        if ($modifiedTitle = $resource->prefix ?? null) {
            $title = $resource->prefix . $title;
        }

        return [
            'title' => $title,
            'url' => $resource->launch_url ?? 'If you see this there is a bug in CA.',
        ];
    }
}
