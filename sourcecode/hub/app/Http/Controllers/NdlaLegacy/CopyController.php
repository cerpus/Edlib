<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use App\Models\Content;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function assert;
use function response;

/**
 * @deprecated This exists for compatibility with old integrations. New
 *     integrations with Edlib should not use this.
 */
final readonly class CopyController
{
    public function __construct(private NdlaLegacyConfig $config) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        $oldUsageId = $this->config->extractEdlib2IdFromUrl($request->input('url', ''))
            ?? abort(404, 'Could not extract Edlib 2 URL');
        $original = Content::firstWithEdlib2UsageIdOrFail($oldUsageId);

        $newUsageId = DB::transaction(function () use ($original, $user) {
            $copy = $original->createCopyBelongingTo($user);
            $usage = $copy->edlib2Usages()->create();
            $copy->save();

            $copy->contexts()->syncWithoutDetaching($original->contexts);

            return $usage->edlib2_usage_id;
        });

        return response()->json([
            'url' => route('ndla-legacy.resource', [$newUsageId]),
        ]);
    }
}
