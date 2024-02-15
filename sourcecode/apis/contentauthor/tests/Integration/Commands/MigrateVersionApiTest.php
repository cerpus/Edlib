<?php

namespace Tests\Integration\Commands;

use App\Article;
use App\Content;
use App\ContentVersion;
use App\Game;
use App\H5PContent;
use App\Link;
use Carbon\Carbon;
use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\VersionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MigrateVersionApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testMigration_articles(): void
    {
        $unversioned = Article::factory()->create(['version_id' => null]);
        $created = Carbon::now()->sub('1d');
        $parentcreated = Carbon::now()->sub('2d');
        $versioned = Article::factory()->create([
            'created_at' => $created,
        ]);
        $missingVersion = Article::factory()->create();

        $rootVersionData = (object)[
            'id' => $this->faker->uuid,
            'externalReference' => $this->faker->uuid,
            'userId' => $versioned->owner_id,
            'versionPurpose' => ContentVersion::PURPOSE_CREATE,
            'createdAt' => $parentcreated->getPreciseTimestamp(3),
        ];

        $parentVersionData = (object)[
            'id' => $this->faker->uuid,
            'externalReference' => $this->faker->uuid,
            'userId' => $versioned->owner_id,
            'versionPurpose' => ContentVersion::PURPOSE_UPGRADE,
            'createdAt' => $parentcreated->getPreciseTimestamp(3),
            'parent' => $rootVersionData,
        ];

        $versionData = (object)[
            'id' => $versioned->version_id,
            'externalReference' => $versioned->id,
            'userId' => $versioned->owner_id,
            'versionPurpose' => ContentVersion::PURPOSE_UPDATE,
            'createdAt' => $created->getPreciseTimestamp(3),
            'parent' => $parentVersionData,
        ];

        $vc = $this->createMock(VersionClient::class);
        $this->instance(VersionClient::class, $vc);
        $vc->expects($this->exactly(2))
            ->method('getVersion')
            ->willReturnCallback(function ($value) use ($versioned, $versionData) {
                return match ($value) {
                    $versioned->version_id => (new VersionData())->populate($versionData),
                    default => false,
                };
            })
        ;

        $this->artisan('edlib:migrate-version-api --debug')
            ->expectsOutput('Debug enabled')
            ->expectsOutput('Migrating data for articles')
            ->expectsOutput('Chunk with 2 row(s)')
            ->expectsOutputToContain(sprintf('Creating version "%s" for content id "%s"', $versioned->version_id, $versioned->id))
            ->expectsOutput(sprintf('Creating missing parent version "%s" for content id "%s"', $parentVersionData->id, $parentVersionData->externalReference))
            ->expectsOutput(sprintf('Creating missing parent version "%s" for content id "%s"', $rootVersionData->id, $rootVersionData->externalReference))
            ->expectsOutputToContain(sprintf('Unknown error from Version API for version id "%s" and content id "%s"', $missingVersion->version_id, $missingVersion->id))
            ->expectsOutput('Committing changes...')
            ->expectsOutput('Versions for articles committed')
            ->expectsOutput('No records to process for games')
            ->expectsOutput('No records to process for links')
            ->expectsOutput('No records to process for h5p_contents')
        ;

        $this->assertDatabaseCount('content_versions', 3);
        $this->assertDatabaseHas('content_versions', [
            'id' => $rootVersionData->id,
            'content_id' => $rootVersionData->externalReference,
            'content_type' => Content::TYPE_ARTICLE,
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $parentVersionData->id,
            'content_id' => $parentVersionData->externalReference,
            'content_type' => Content::TYPE_ARTICLE,
            'created_at' => Carbon::createFromTimestampMs($parentcreated->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versionData->id,
            'content_id' => $versionData->externalReference,
            'content_type' => Content::TYPE_ARTICLE,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versioned->version_id,
            'content_id' => $versioned->id,
            'content_type' => Content::TYPE_ARTICLE,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);

        $this->assertDatabaseMissing('content_versions', [
            'content_id' => $unversioned->id,
        ]);
    }

    public function testMigration_games(): void
    {
        $unversioned = Game::factory()->create();
        $parentcreated = Carbon::now()->sub('2d');
        $created = Carbon::now()->sub('1d');
        $versioned = Game::factory()->create([
            'created_at' => $created,
            'version_id' => $this->faker->uuid,
        ]);
        $missingVersion = Game::factory()->create([
            'version_id' => $this->faker->uuid,
            'created_at' => Carbon::now(),
        ]);

        $parentVersionData = (object)[
            'id' => $this->faker->uuid,
            'externalReference' => $this->faker->uuid,
            'userId' => $versioned->owner,
            'versionPurpose' => ContentVersion::PURPOSE_CREATE,
            'createdAt' => $parentcreated->getPreciseTimestamp(3),
        ];

        $versionData = (object)[
            'id' => $versioned->version_id,
            'externalReference' => $versioned->id,
            'userId' => $versioned->owner,
            'versionPurpose' => ContentVersion::PURPOSE_UPDATE,
            'createdAt' => $created->getPreciseTimestamp(3),
            'parent' => $parentVersionData,
        ];

        $vc = $this->createMock(VersionClient::class);
        $this->instance(VersionClient::class, $vc);
        $vc->expects($this->exactly(2))
            ->method('getVersion')
            ->willReturnCallback(function ($value) use ($versioned, $versionData) {
                return match ($value) {
                    $versioned->version_id => (new VersionData())->populate($versionData),
                    default => false,
                };
            })
        ;

        $this->artisan('edlib:migrate-version-api --debug')
            ->expectsOutput('Debug enabled')
            ->expectsOutput('No records to process for articles')
            ->expectsOutput('Migrating data for games')
            ->expectsOutput('Chunk with 2 row(s)')
            ->expectsOutputToContain(sprintf('Creating version "%s" for content id "%s"', $versioned->version_id, $versioned->id))
            ->expectsOutput(sprintf('Creating missing parent version "%s" for content id "%s"', $parentVersionData->id, $parentVersionData->externalReference))
            ->expectsOutputToContain(sprintf('Unknown error from Version API for version id "%s" and content id "%s"', $missingVersion->version_id, $missingVersion->id))
            ->expectsOutput('Committing changes...')
            ->expectsOutput('Versions for games committed')
            ->expectsOutput('No records to process for links')
            ->expectsOutput('No records to process for h5p_contents')
        ;

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $parentVersionData->id,
            'content_id' => $parentVersionData->externalReference,
            'content_type' => Content::TYPE_GAME,
            'created_at' => Carbon::createFromTimestampMs($parentcreated->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versionData->id,
            'content_id' => $versionData->externalReference,
            'content_type' => Content::TYPE_GAME,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versioned->version_id,
            'content_id' => $versioned->id,
            'content_type' => Content::TYPE_GAME,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);

        $this->assertDatabaseMissing('content_versions', [
            'content_id' => $unversioned->id,
        ]);
    }

    public function testMigration_links(): void
    {
        $unversioned = Link::factory()->create();
        $parentcreated = Carbon::now()->sub('2d');
        $created = Carbon::now()->sub('1d');
        $versioned = Link::factory()->create([
            'created_at' => $created,
            'version_id' => $this->faker->uuid,
        ]);
        $missingVersion = Link::factory()->create([
            'version_id' => $this->faker->uuid,
            'created_at' => Carbon::now(),
        ]);

        $parentVersionData = (object)[
            'id' => $this->faker->uuid,
            'externalReference' => $this->faker->uuid,
            'userId' => $versioned->owner_id,
            'versionPurpose' => ContentVersion::PURPOSE_CREATE,
            'createdAt' => $parentcreated->getPreciseTimestamp(3),
        ];

        $versionData = (object)[
            'id' => $versioned->version_id,
            'externalReference' => $versioned->id,
            'userId' => $versioned->owner_id,
            'versionPurpose' => ContentVersion::PURPOSE_UPDATE,
            'createdAt' => $created->getPreciseTimestamp(3),
            'parent' => $parentVersionData,
        ];

        $vc = $this->createMock(VersionClient::class);
        $this->instance(VersionClient::class, $vc);
        $vc->expects($this->exactly(2))
            ->method('getVersion')
            ->willReturnCallback(function ($value) use ($versioned, $versionData) {
                return match ($value) {
                    $versioned->version_id => (new VersionData())->populate($versionData),
                    default => false,
                };
            })
        ;

        $this->artisan('edlib:migrate-version-api --debug')
            ->expectsOutput('Debug enabled')
            ->expectsOutput('No records to process for articles')
            ->expectsOutput('No records to process for games')
            ->expectsOutput('Migrating data for links')
            ->expectsOutput('Chunk with 2 row(s)')
            ->expectsOutputToContain(sprintf('Creating version "%s" for content id "%s"', $versioned->version_id, $versioned->id))
            ->expectsOutput(sprintf('Creating missing parent version "%s" for content id "%s"', $parentVersionData->id, $parentVersionData->externalReference))
            ->expectsOutputToContain(sprintf('Unknown error from Version API for version id "%s" and content id "%s"', $missingVersion->version_id, $missingVersion->id))
            ->expectsOutput('Committing changes...')
            ->expectsOutput('Versions for links committed')
            ->expectsOutput('No records to process for h5p_contents')
        ;

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $parentVersionData->id,
            'content_id' => $parentVersionData->externalReference,
            'content_type' => Content::TYPE_LINK,
            'created_at' => Carbon::createFromTimestampMs($parentcreated->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versionData->id,
            'content_id' => $versionData->externalReference,
            'content_type' => Content::TYPE_LINK,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versioned->version_id,
            'content_id' => $versioned->id,
            'content_type' => Content::TYPE_LINK,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);

        $this->assertDatabaseMissing('content_versions', [
            'content_id' => $unversioned->id,
        ]);
    }

    public function testMigration_h5p(): void
    {
        $unversioned = H5PContent::factory()->create();
        $parentcreated = Carbon::now()->sub('2d');
        $created = Carbon::now()->sub('1d');
        $versioned = H5PContent::factory()->create([
            'created_at' => $created,
            'version_id' => $this->faker->uuid,
        ]);
        $missingVersion = H5PContent::factory()->create([
            'version_id' => $this->faker->uuid,
            'created_at' => Carbon::now(),
        ]);

        $parentVersionData = (object)[
            'id' => $this->faker->uuid,
            'externalReference' => $this->faker->uuid,
            'userId' => $versioned->user_id,
            'versionPurpose' => ContentVersion::PURPOSE_CREATE,
            'createdAt' => $parentcreated->getPreciseTimestamp(3),
        ];

        $versionData = (object)[
            'id' => $versioned->version_id,
            'externalReference' => $versioned->id,
            'userId' => $versioned->user_id,
            'versionPurpose' => ContentVersion::PURPOSE_UPDATE,
            'createdAt' => $created->getPreciseTimestamp(3),
            'parent' => $parentVersionData,
        ];

        $vc = $this->createMock(VersionClient::class);
        $this->instance(VersionClient::class, $vc);
        $vc->expects($this->exactly(2))
            ->method('getVersion')
            ->willReturnOnConsecutiveCalls(
                (new VersionData())->populate($versionData),
                false
            );

        $this->artisan('edlib:migrate-version-api --debug')
            ->expectsOutput('Debug enabled')
            ->expectsOutput('No records to process for articles')
            ->expectsOutput('No records to process for games')
            ->expectsOutput('No records to process for links')
            ->expectsOutput('Migrating data for h5p_contents')
            ->expectsOutput('Chunk with 2 row(s)')
            ->expectsOutput(sprintf('Creating version "%s" for content id "%s"', $versioned->version_id, $versioned->id))
            ->expectsOutput(sprintf('Creating missing parent version "%s" for content id "%s"', $parentVersionData->id, $parentVersionData->externalReference))
            ->expectsOutput(sprintf('Unknown error from Version API for version id "%s" and content id "%s"', $missingVersion->version_id, $missingVersion->id))
            ->expectsOutput('Committing changes...')
            ->expectsOutput('Versions for h5p_contents committed')
        ;

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $parentVersionData->id,
            'content_id' => $parentVersionData->externalReference,
            'content_type' => Content::TYPE_H5P,
            'created_at' => Carbon::createFromTimestampMs($parentcreated->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versionData->id,
            'content_id' => $versionData->externalReference,
            'content_type' => Content::TYPE_H5P,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versioned->version_id,
            'content_id' => $versioned->id,
            'content_type' => Content::TYPE_H5P,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);

        $this->assertDatabaseMissing('content_versions', [
            'content_id' => $unversioned->id,
        ]);
    }

    public function testMigration_progress(): void
    {
        $created = Carbon::now()->sub('1d');
        $versioned = H5PContent::factory()->create([
            'created_at' => $created,
            'version_id' => $this->faker->uuid,
        ]);

        $versionData = (object)[
            'id' => $versioned->version_id,
            'externalReference' => $versioned->id,
            'userId' => $versioned->user_id,
            'versionPurpose' => ContentVersion::PURPOSE_UPDATE,
            'createdAt' => $created->getPreciseTimestamp(3),
            'parent' => null,
        ];

        $vc = $this->createMock(VersionClient::class);
        $this->instance(VersionClient::class, $vc);
        $vc->expects($this->exactly(1))
            ->method('getVersion')
            ->willReturn(
                (new VersionData())->populate($versionData)
            );

        $this->artisan('edlib:migrate-version-api')
            ->expectsOutput('No records to process for articles')
            ->expectsOutput('No records to process for games')
            ->expectsOutput('No records to process for links')
            ->expectsOutputToContain('Migrating data for h5p_contents:')
            ->expectsOutput('Versions for h5p_contents committed')
        ;

        $this->assertDatabaseCount('content_versions', 1);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versionData->id,
            'content_id' => $versionData->externalReference,
            'content_type' => Content::TYPE_H5P,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versioned->version_id,
            'content_id' => $versioned->id,
            'content_type' => Content::TYPE_H5P,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
    }

    public function testMigration_dryrun(): void
    {
        $created = Carbon::now()->sub('1d');
        $versioned = H5PContent::factory()->create([
            'created_at' => $created,
            'version_id' => $this->faker->uuid,
        ]);

        $versionData = (object)[
            'id' => $versioned->version_id,
            'externalReference' => $versioned->id,
            'userId' => $versioned->user_id,
            'versionPurpose' => ContentVersion::PURPOSE_UPDATE,
            'createdAt' => $created->getPreciseTimestamp(3),
            'parent' => null,
        ];

        $vc = $this->createMock(VersionClient::class);
        $this->instance(VersionClient::class, $vc);
        $vc->expects($this->exactly(1))
            ->method('getVersion')
            ->willReturn(
                (new VersionData())->populate($versionData)
            );

        $this->artisan('edlib:migrate-version-api --dry-run')
            ->expectsOutput('Dry-run mode enabled')
            ->expectsOutput('No records to process for articles')
            ->expectsOutput('No records to process for games')
            ->expectsOutput('No records to process for links')
            ->expectsOutputToContain('Migrating data for h5p_contents:')
            ->expectsOutput('Dry-run enabled, rolling back changes')
            ->expectsOutput('Changes was not committed')
        ;

        $this->assertDatabaseEmpty('content_versions');
    }

    public function testMigration_timetravel(): void
    {
        $childDate = Carbon::now()->sub('2d');
        $child = H5PContent::factory()->create([
            'created_at' => $childDate,
            'version_id' => $this->faker->uuid,
        ]);

        $parentDate = Carbon::now()->sub('1d');
        $parent = H5PContent::factory()->create([
            'created_at' => $parentDate,
            'version_id' => $this->faker->uuid,
        ]);

        $parentVersionData = (object)[
            'id' => $parent->version_id,
            'externalReference' => $parent->id,
            'userId' => $parent->user_id,
            'versionPurpose' => ContentVersion::PURPOSE_CREATE,
            'createdAt' => $parentDate->getPreciseTimestamp(3),
        ];

        $versionData = (object)[
            'id' => $child->version_id,
            'externalReference' => $child->id,
            'userId' => $child->user_id,
            'versionPurpose' => ContentVersion::PURPOSE_UPDATE,
            'createdAt' => $childDate->getPreciseTimestamp(3),
            'parent' => $parentVersionData,
        ];

        $vc = $this->createMock(VersionClient::class);
        $this->instance(VersionClient::class, $vc);
        $vc->expects($this->exactly(2))
            ->method('getVersion')
            ->willReturnOnConsecutiveCalls(
                (new VersionData())->populate($versionData),
                (new VersionData())->populate($parentVersionData),
            );

        $this->artisan('edlib:migrate-version-api --debug')
            ->expectsOutput('Debug enabled')
            ->expectsOutput('No records to process for articles')
            ->expectsOutput('No records to process for games')
            ->expectsOutput('No records to process for links')
            ->expectsOutput('Migrating data for h5p_contents')
            ->expectsOutput('Chunk with 2 row(s)')
            ->expectsOutput(sprintf('Creating version "%s" for content id "%s"', $child->version_id, $child->id))
            ->expectsOutput(sprintf('Creating missing parent version "%s" for content id "%s"', $parent->version_id, $parent->id))
            ->expectsOutput(sprintf('Creating version "%s" for content id "%s"', $parent->version_id, $parent->id))
            ->expectsOutput(sprintf('Version "%s" already exists', $parent->version_id))
            ->expectsOutput('Committing changes...')
            ->expectsOutput('Versions for h5p_contents committed')
        ;

        $this->assertDatabaseCount('content_versions', 2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $parentVersionData->id,
            'content_id' => $parentVersionData->externalReference,
            'content_type' => Content::TYPE_H5P,
            'created_at' => Carbon::createFromTimestampMs($parentDate->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versionData->id,
            'content_id' => $versionData->externalReference,
            'content_type' => Content::TYPE_H5P,
            'created_at' => Carbon::createFromTimestampMs($childDate->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
    }

    public function testMigration_h5p_error(): void
    {
        $unversioned = H5PContent::factory()->create();
        $created = Carbon::now();
        $versioned = H5PContent::factory()->create([
            'created_at' => $created,
            'version_id' => $this->faker->uuid,
        ]);
        $errorVersion = H5PContent::factory()->create([
            'version_id' => $this->faker->uuid,
            'created_at' => Carbon::now(),
        ]);

        $versionData = (object)[
            'id' => $versioned->version_id,
            'externalReference' => $versioned->id,
            'userId' => $versioned->user_id,
            'versionPurpose' => ContentVersion::PURPOSE_UPDATE,
            'createdAt' => $created->getPreciseTimestamp(3),
        ];

        $exception = new \Exception('Just testing', 403);

        $vc = $this->createMock(VersionClient::class);
        $this->instance(VersionClient::class, $vc);
        $vc->expects($this->any())->method('getErrorCode')->willReturn($exception->getCode());
        $vc->expects($this->any())->method('getError')->willReturn($exception->getMessage());
        $vc->expects($this->exactly(2))
            ->method('getVersion')
            ->willReturnCallback(function ($value) use ($versioned, $versionData, $errorVersion, $exception) {
                if ($value === $errorVersion->version_id) {
                    $this->throwException($exception);
                } elseif ($value === $versioned->version_id) {
                    return (new VersionData())->populate($versionData);
                }
                return false;
            })
        ;

        $this->artisan('edlib:migrate-version-api --debug')
            ->expectsOutput('Debug enabled')
            ->expectsOutput('No records to process for articles')
            ->expectsOutput('No records to process for games')
            ->expectsOutput('No records to process for links')
            ->expectsOutput('Migrating data for h5p_contents')
            ->expectsOutput('Chunk with 2 row(s)')
            ->expectsOutput(sprintf('Creating version "%s" for content id "%s"', $versioned->version_id, $versioned->id))
            ->expectsOutput(sprintf('Error "%s" from Version API for version id "%s" and content id "%s": %s', $exception->getCode(), $errorVersion->version_id, $errorVersion->id, $exception->getMessage()))
            ->expectsOutput('Committing changes...')
            ->expectsOutput('Versions for h5p_contents committed')
        ;

        $this->assertDatabaseCount('content_versions', 1);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versionData->id,
            'content_id' => $versionData->externalReference,
            'content_type' => Content::TYPE_H5P,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);
        $this->assertDatabaseHas('content_versions', [
            'id' => $versioned->version_id,
            'content_id' => $versioned->id,
            'content_type' => Content::TYPE_H5P,
            'created_at' => Carbon::createFromTimestampMs($created->getPreciseTimestamp(3))->format('Y-m-d H:i:s.u'),
        ]);

        $this->assertDatabaseMissing('content_versions', [
            'content_id' => $unversioned->id,
        ]);
    }
}
