<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use App\Models\Content;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $oldId = $this->config->extractEdlib2IdFromUrl($request->input('url', ''));
        $original = Content::ofTag('edlib2_usage_id:' . $oldId)
            ->limit(1)
            ->firstOrFail();

        $newId = DB::transaction(function () use ($original, $user) {
            $copy = $original->createCopyBelongingTo($user);
            $tag = Tag::findOrCreateFromString('edlib2_usage_id:' . Str::uuid());
            $copy->tags()->attach($tag);

            return $tag->name;
        });

        return response()->json([
            'url' => route('ndla-legacy.resource', [$newId]),
        ]);
    }
}
