<?php

declare(strict_types=1);

namespace Tests\Integration\Models;

use App\ContentVersions;
use App\Exceptions\NotFoundException;
use Carbon\Carbon;
use Generator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContentVersionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_previousVersion(): void
    {
        $v1 = ContentVersions::factory()->create();
        $v2 = ContentVersions::factory()->create([
            'parent_id' => $v1->id,
        ]);
        ContentVersions::factory()->create();
        ContentVersions::factory()->create([
            'parent_id' => $v1->id,
        ]);

        /** @var Collection<ContentVersions> $previous */
        $previous = $v2->getPreviousVersion();
        $this->assertInstanceOf(ContentVersions::class, $previous);
        $this->assertSame($v1->id, $previous->id);
    }

    public function test_previousVersion_noPrevious(): void
    {
        $v1 = ContentVersions::factory()->create();

        /** @var Collection<ContentVersions> $previous */
        $previous = $v1->getPreviousVersion();
        $this->assertNull($previous);
    }

    public function test_nextVersions(): void
    {
        $v1 = ContentVersions::factory()->create();
        $v2_1 = ContentVersions::factory()->create([
            'parent_id' => $v1->id,
        ]);
        $v2_2 = ContentVersions::factory()->create([
            'parent_id' => $v1->id,
        ]);
        ContentVersions::factory()->create();

        /** @var Collection<ContentVersions> $next */
        $next = $v1->getNextVersions();
        $this->assertCount(2, $next);
        $this->assertSame($v2_1->id, $next[0]->id);
        $this->assertSame($v2_2->id, $next[1]->id);
    }

    public function test_latestVersion_linear(): void
    {
        $v1 = ContentVersions::factory()->create([
            'linear_versioning' => true,
        ]);
        $v2 = ContentVersions::factory()->create([
            'parent_id' => $v1->id,
            'linear_versioning' => true,
        ]);
        $v3 = ContentVersions::factory()->create([
            'parent_id' => $v2->id,
            'linear_versioning' => true,
        ]);

        $latest = $v1->latestVersion();

        $this->assertNotNull($latest);
        $this->assertSame($v3->id, $latest->id);
    }

    public function test_latestVersion_branched(): void
    {
        $v1 = ContentVersions::factory()->create([
            'version_purpose' => 'v1',
            'created_at' => Carbon::now()->sub('1d'),
        ]);
        $v1_1 = ContentVersions::factory()->create([
            'parent_id' => $v1->id,
            'version_purpose' => 'v1_1',
            'created_at' => Carbon::now()->sub('15h'),
        ]);
        $v1_2 = ContentVersions::factory()->create([
            'parent_id' => $v1->id,
            'version_purpose' => 'v1_2',
            'created_at' => Carbon::now()->sub('10m'),
        ]);

        $v1_1_1 = ContentVersions::factory()->create([
            'parent_id' => $v1_1->id,
            'version_purpose' => 'v1_1_1',
            'created_at' => Carbon::now()->sub('1m'),
        ]);
        $v1_1_2 = ContentVersions::factory()->create([
            'parent_id' => $v1_1->id,
            'version_purpose' => 'v1_1_2',
            'created_at' => Carbon::now()->sub('5s'),
        ]);

        $v1_2_1 = ContentVersions::factory()->create([
            'parent_id' => $v1_2->id,
            'version_purpose' => 'v1_2_1',
            'created_at' => Carbon::now()->sub('5m'),
        ]);
        $v1_2_2 = ContentVersions::factory()->create([
            'parent_id' => $v1_2->id,
            'version_purpose' => 'v1_2_1',
            'created_at' => Carbon::now()->sub('1m'),
        ]);
        $v1_2_1_1 = ContentVersions::factory()->create([
            'parent_id' => $v1_2_1->id,
            'version_purpose' => 'v1_2_1_1',
            'created_at' => Carbon::now()->sub('3m'),
        ]);

        $this->assertSame($v1_1_2->id, $v1->latestVersion()->id);

        $this->assertSame($v1_2_2->id, $v1_2->latestVersion()->id);
    }

    public function test_latestVersion_nonExisting(): void
    {
        $v1 = ContentVersions::factory()->create();
        ContentVersions::factory()->create([
            'parent_id' => $v1->id,
        ]);

        $this->expectException(ModelNotFoundException::class);

        ContentVersions::latest('123');
    }

    /** @dataProvider providerLinearVersioning */
    public function testLinearVersioning(bool $parentLinear, bool $newLinear): void
    {
        $v1 = ContentVersions::factory()->create([
            'linear_versioning' => $parentLinear,
        ]);
        $v2 = ContentVersions::factory()->create([
            'parent_id' => $v1->id,
            'linear_versioning' => $parentLinear,
        ]);

        $v3 = ContentVersions::create([
            'content_id' => $this->faker->uuid,
            'content_type' => 'linearTest',
            'parent_id' => $v1->id,
            'version_purpose' => ContentVersions::PURPOSE_CREATE,
            'user_id' => 1,
            'linear_versioning' => $newLinear,
        ]);

        $this->assertDatabaseHas('content_versions', [
            'id' => $v3->id,
            'content_id' => $v3->content_id,
        ]);

        $this->assertSame($v2->id, $v3->parent_id);
    }

    public function providerLinearVersioning(): Generator
    {
        yield 'linear' => [true, true];
        yield 'nonLinearParent' => [false, true];
        yield 'nonLinearChild' => [true, false];
    }
}
