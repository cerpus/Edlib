<?php


namespace App\Libraries\NDLA\Traits;


use Illuminate\Support\Str;

trait FiltersArticles
{
    public function filterArticlesAndDeduplicate(array $resources): array
    {
        if (empty($resources)) {
            return $resources;
        }

        $deduplicatedArticles = collect($resources)
            ->filter(function ($resource) {
                return Str::contains($resource->contentUri ?? '', 'urn:article:');
            })
            ->unique('contentUri')
            ->values()
            ->toArray();

        return $deduplicatedArticles;
    }
}
