<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\Content;
use App\ContentVersion;
use App\H5PContent;
use Carbon\Carbon;
use Generator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ContentVersionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_previousVersion(): void
    {
        $v1 = ContentVersion::factory()->create();
        $v2 = ContentVersion::factory()->create([
            'parent_id' => $v1->id,
        ]);
        ContentVersion::factory()->create();
        ContentVersion::factory()->create([
            'parent_id' => $v1->id,
        ]);

        $previous = $v2->previousVersion;
        $this->assertInstanceOf(ContentVersion::class, $previous);
        $this->assertSame($v1->id, $previous->id);
    }

    public function test_previousVersion_noPrevious(): void
    {
        $v1 = ContentVersion::factory()->create();

        $this->assertNull($v1->previousVersion);
    }

    public function test_nextVersions(): void
    {
        $v1 = ContentVersion::factory()->create();
        $v2_1 = ContentVersion::factory()->create([
            'parent_id' => $v1->id,
        ]);
        $v2_2 = ContentVersion::factory()->create([
            'parent_id' => $v1->id,
        ]);
        ContentVersion::factory()->create();

        $next = $v1->nextVersions;
        $this->assertCount(2, $next);
        $this->assertSame($v2_1->id, $next[0]->id);
        $this->assertSame($v2_2->id, $next[1]->id);
    }

    public function test_latestLeaf_linear(): void
    {
        $v1 = ContentVersion::factory()->create([
            'linear_versioning' => true,
        ]);
        $v2 = ContentVersion::factory()->create([
            'parent_id' => $v1->id,
            'linear_versioning' => true,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);
        $v3 = ContentVersion::factory()->create([
            'parent_id' => $v2->id,
            'linear_versioning' => true,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $latest = $v1->latestLeafVersion();
        $this->assertNotNull($latest);
        $this->assertSame($v3->id, $latest->id);

        $latest = ContentVersion::latestLeaf($latest->id);
        $this->assertNotNull($latest);
        $this->assertSame($v3->id, $latest->id);
    }

    public function test_latestLeaf_branched(): void
    {
        $v1 = ContentVersion::factory()->create([
            'created_at' => Carbon::now()->sub('1d'),
        ]);
        $v1_1 = ContentVersion::factory()->create([
            'parent_id' => $v1->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'created_at' => Carbon::now()->sub('15h'),
        ]);
        $v1_2 = ContentVersion::factory()->create([
            'parent_id' => $v1->id,
            'version_purpose' => ContentVersion::PURPOSE_TRANSLATION,
            'created_at' => Carbon::now()->sub('10m'),
        ]);

        $v1_1_1 = ContentVersion::factory()->create([
            'parent_id' => $v1_1->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'created_at' => Carbon::now()->sub('1m'),
        ]);
        $v1_1_2 = ContentVersion::factory()->create([
            'parent_id' => $v1_1->id,
            'version_purpose' => ContentVersion::PURPOSE_TRANSLATION,
            'created_at' => Carbon::now()->sub('5s'),
        ]);

        $v1_2_1 = ContentVersion::factory()->create([
            'parent_id' => $v1_2->id,
            'version_purpose' => ContentVersion::PURPOSE_TRANSLATION,
            'created_at' => Carbon::now()->sub('5m'),
        ]);
        $v1_2_2 = ContentVersion::factory()->create([
            'parent_id' => $v1_2->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'created_at' => Carbon::now()->sub('1m'),
        ]);
        $v1_2_1_1 = ContentVersion::factory()->create([
            'parent_id' => $v1_2_1->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'created_at' => Carbon::now()->sub('3m'),
        ]);

        $this->assertSame($v1_1_1->id, $v1->latestLeafVersion()->id);
        $this->assertSame($v1_2_2->id, $v1_2->latestLeafVersion()->id);

        $this->assertSame($v1_1_1->id, ContentVersion::latestLeaf($v1->id)->id);
        $this->assertSame($v1_2_2->id, ContentVersion::latestLeaf($v1_2->id)->id);
    }

    public function test_latestLeaf_nonExisting(): void
    {
        $this->expectException(ModelNotFoundException::class);

        ContentVersion::latestLeaf('123');
    }

    #[DataProvider('providerLinearVersioning')]
    public function testLinearVersioning(bool $parentLinear, bool $newLinear): void
    {
        $v1 = ContentVersion::factory()->create([
            'linear_versioning' => $parentLinear,
        ]);
        $v2 = ContentVersion::factory()->create([
            'parent_id' => $v1->id,
            'linear_versioning' => $parentLinear,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $v3 = ContentVersion::create([
            'content_id' => $this->faker->uuid,
            'content_type' => 'linearTest',
            'parent_id' => $v1->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'user_id' => 1,
            'linear_versioning' => $newLinear,
        ]);

        $this->assertDatabaseHas('content_versions', [
            'id' => $v3->id,
            'content_id' => $v3->content_id,
        ]);

        $this->assertSame($v2->id, $v3->parent_id);
    }

    public static function providerLinearVersioning(): Generator
    {
        yield 'linear' => [true, true];
        yield 'nonLinearParent' => [false, true];
        yield 'nonLinearChild' => [true, false];
    }

    public function test_findLatestLeaf_multiple(): void
    {
        $root = ContentVersion::factory()->create([
            'linear_versioning' => false,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);
        $child_INITIAL = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_INITIAL,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('1d'),
        ]);
        $child_CREATE = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('2d'),
        ]);
        $child_IMPORT = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_IMPORT,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('3d'),
        ]);
        $child_COPY = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_COPY,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('4d'),
        ]);
        $child_TRANSLATION = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_TRANSLATION,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('5d'),
        ]);
        $child_UPDATE = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('6d'),
        ]);
        $child_UPGRADE = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_UPGRADE,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('7d'),
        ]);

        $this->assertDatabaseCount('content_versions', 8);
        $this->assertCount(7, $root->nextVersions);
        $this->assertCount(2, $root->leafs);
        $this->assertSame($child_UPDATE->id, $root->latestLeafVersion()->id);

        // This version is newer that $child_UPDATE, and since it's connected to a leaf node
        // it is now the latest version of $root
        $child_UPGRADE_UPDATE = ContentVersion::factory()->create([
            'parent_id' => $child_UPGRADE->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('3d'),
        ]);

        $this->assertDatabaseCount('content_versions', 9);
        $this->assertSame($child_UPGRADE_UPDATE->id, $root->latestLeafVersion()->id);

        // This is the newest node, but it's not connected to a leaf node of $root and
        // is therefore not the latest version of $root
        $child_TRANSLATION_UPDATE = ContentVersion::factory()->create([
            'parent_id' => $child_TRANSLATION->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now(),
        ]);

        $this->assertDatabaseCount('content_versions', 10);
        $this->assertSame($child_UPGRADE_UPDATE->id, $root->latestLeafVersion()->id);

        // but it is the latest version of $child_TRANSLATION
        $this->assertSame($child_TRANSLATION_UPDATE->id, $child_TRANSLATION->latestLeafVersion()->id);
    }

    public function test_findLatestLeaf_linear_multiple(): void
    {
        $root = ContentVersion::factory()->create([
            'linear_versioning' => true,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);
        $child_INITIAL = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_INITIAL,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('1d'),
        ]);
        $child_CREATE = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('2d'),
        ]);
        $child_IMPORT = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_IMPORT,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('3d'),
        ]);
        $child_COPY = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_COPY,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('4d'),
        ]);
        $child_TRANSLATION = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_TRANSLATION,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('5d'),
        ]);
        $child_UPDATE = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('6d'),
        ]);

        // This will not be connected to the $root, but to $child_UPDATE since that is the latest leaf node
        // And even if the created_at date is illogical, it was created before its parent, it still
        // considered the latest version
        $child_UPGRADE = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_UPGRADE,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('7d'),
        ]);

        $this->assertDatabaseCount('content_versions', 8);
        $this->assertCount(6, $root->nextVersions);
        $this->assertCount(1, $root->leafs);
        $this->assertSame($child_UPGRADE->id, $root->latestLeafVersion()->id);
        $this->assertDatabaseHas('content_versions', [
            'id' => $child_UPGRADE->id,
            'parent_id' => $child_UPDATE->id,
        ]);

        // This will be conected to $child_UPGRADE since that is the latest leaf node of $root
        $child_UPGRADE_UPDATE = ContentVersion::factory()->create([
            'parent_id' => $root->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now()->sub('3d'),
        ]);

        $this->assertDatabaseCount('content_versions', 9);
        $this->assertSame($child_UPGRADE_UPDATE->id, $root->latestLeafVersion()->id);
        $this->assertDatabaseHas('content_versions', [
            'id' => $child_UPGRADE_UPDATE->id,
            'parent_id' => $child_UPGRADE->id,
        ]);

        // This is the newest node, but it's not connected to a leaf node of $root and
        // is therefore not the latest version of $root
        $child_TRANSLATION_UPDATE = ContentVersion::factory()->create([
            'parent_id' => $child_TRANSLATION->id,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
            'linear_versioning' => $root->linear_versioning,
            'created_at' => Carbon::now(),
        ]);

        $this->assertDatabaseCount('content_versions', 10);
        $this->assertSame($child_UPGRADE_UPDATE->id, $root->latestLeafVersion()->id);

        // but it is the latest version of $child_TRANSLATION
        $this->assertSame($child_TRANSLATION_UPDATE->id, $child_TRANSLATION->latestLeafVersion()->id);
    }

    #[DataProvider('provider_isLeaf')]
    public function test_isLeaf(string $purpose, bool $parentLeaf, bool $childLeaf): void
    {
        $first = ContentVersion::factory()->create();
        $second = ContentVersion::factory()->create([
            'parent_id' => $first->id,
            'version_purpose' => $purpose,
        ]);

        $this->assertSame($parentLeaf, $first->isLeaf());
        $this->assertSame($childLeaf, $second->isLeaf());
    }

    public static function provider_isLeaf(): Generator
    {
        // Parent is no longer a leaf node
        yield 'update' => [ContentVersion::PURPOSE_UPDATE, false, true];
        yield 'upgrade' => [ContentVersion::PURPOSE_UPGRADE, false, true];

        // Parent is still considered a leaf node
        yield 'copy' => [ContentVersion::PURPOSE_COPY, true, true];
        yield 'translation' => [ContentVersion::PURPOSE_TRANSLATION, true, true];
    }

    public function test_getContent(): void
    {
        $content = H5PContent::factory()->create([
            'version_id' => $this->faker->uuid,
        ]);
        $version = ContentVersion::factory()->create([
            'id' => $content->version_id,
            'content_id' => $content->id,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
            'content_type' => Content::TYPE_H5P,
            'linear_versioning' => true,
        ]);

        $result = $version->getContent();
        $this->assertSame($content->id, $result->id);
    }
}
