<?php

namespace Tests\Integration\Libraries\H5P\Reports;

use App\H5PContent;
use App\H5PContentsUserData;
use App\H5PLibrary;
use App\Libraries\H5P\Packages\SimpleMultiChoice;
use App\Libraries\H5P\Reports\H5PPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class H5PPackageTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_questionsAndAnswers(): void
    {
        $mock = $this->createMock(SimpleMultiChoice::class);
        app()->bind(SimpleMultiChoice::class, fn () => $mock);

        $mock->expects($this->exactly(2))
            ->method('validate')
            ->willReturnOnConsecutiveCalls(true, false);

        $mock->expects($this->exactly(2))
            ->method('canExtractAnswers')
            ->willReturn(true);

        $mock->expects($this->once())
            ->method('getPackageAnswers');

        $mock->expects($this->once())
            ->method('setAnswers');

        $mock->expects($this->once())
            ->method('getElements')
            ->willReturn(['test' => 'it is']);

        $contexts = [
            $this->faker->uuid,
            $this->faker->uuid,
            $this->faker->uuid,
        ];
        $userId = $this->faker->uuid;

        $libs = [
            H5PLibrary::factory()->create(),
            H5PLibrary::factory()->create([
                'name' => "H5P.SimpleMultiChoice",
            ]),
        ];
        $contents = [
            H5PContent::factory()->create(['library_id' => $libs[0]->id]),
            H5PContent::factory()->create(['library_id' => $libs[1]->id]),
            H5PContent::factory()->create(['library_id' => $libs[1]->id]),
        ];
        H5PContentsUserData::factory()->create([
            'content_id' => $contents[0]->id,
            'user_id' => $userId,
            'context' => $contexts[0],
            'data' => 'Rejected since there is no package for this library',
        ]);
        H5PContentsUserData::factory()->create([
            'content_id' => $contents[1]->id,
            'user_id' => $userId,
            'context' => $contexts[1],
            'data' => 'Mock validate() will pass this',
        ]);
        H5PContentsUserData::factory()->create([
            'content_id' => $contents[2]->id,
            'user_id' => $userId,
            'context' => $contexts[2],
            'data' => 'Mock validate() will reject this',
        ]);

        $result = app(H5PPackage::class)->questionsAndAnswers($contexts, $userId);

        $this->assertCount(1, $result);
        $this->assertSame('it is', $result[1]['elements']['test']);
        $this->assertSame($contexts[1], $result[1]['context']);
    }
}
