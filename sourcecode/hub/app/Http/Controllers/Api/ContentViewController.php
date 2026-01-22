<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ContentViewSource;
use App\Http\Requests\Api\AccumulatedViewsRequest;
use App\Http\Requests\Api\MultipleAccumulatedViewsRequest;
use App\Models\Content;
use App\Models\ContentViewsAccumulated;
use App\Transformers\ContentViewsAccumulatedTransformer;
use App\Transformers\ContentViewTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

final readonly class ContentViewController
{
    public function __construct(
        private ContentViewTransformer $transformer,
        private ContentViewsAccumulatedTransformer $viewsAccumulatedTransformer,
    ) {}

    public function index(Content $apiContent): JsonResponse
    {
        $views = $apiContent->views()->paginate();

        return fractal($views)
            ->transformWith($this->transformer)
            ->paginateWith(new IlluminatePaginatorAdapter($views))
            ->respond();
    }

    public function storeAccumulatedViews(Content $apiContent, AccumulatedViewsRequest $request): JsonResponse
    {
        $data = $request->safe();

        $entry = DB::transaction(function () use ($apiContent, $data) {
            $entry = $apiContent->viewsAccumulated()->firstOrNew([
                'source' => $data->enum('source', ContentViewSource::class),
                'lti_platform_id' => $data['lti_platform_id'],
                'date' => $data->string('date'),
                'hour' => $data->integer('hour'),
            ]);
            assert($entry instanceof ContentViewsAccumulated);

            $entry->view_count += $data->integer('view_count');
            $entry->save();

            return $entry;
        });

        return fractal($entry)
            ->transformWith($this->viewsAccumulatedTransformer)
            ->respond();
    }

    public function storeMultipleAccumulatedViews(
        Content $apiContent,
        MultipleAccumulatedViewsRequest $request,
    ): JsonResponse {
        $data = $request->safe();

        $views = DB::transaction(function () use ($apiContent, $data) {
            $views = [];
            foreach ($data->array('views') as $viewData) {
                $entry = $apiContent->viewsAccumulated()->firstOrNew([
                    'source' => ContentViewSource::from($viewData['source']),
                    'lti_platform_id' => $viewData['lti_platform_id'] ?? null,
                    'date' => $viewData['date'],
                    'hour' => (int) $viewData['hour'],
                ]);
                assert($entry instanceof ContentViewsAccumulated);

                $entry->view_count += $viewData['view_count'];
                $entry->save();

                $views[] = $entry;
            }

            return $views;
        });

        return fractal()
            ->collection($views)
            ->transformWith($this->viewsAccumulatedTransformer)
            ->respond();
    }
}
