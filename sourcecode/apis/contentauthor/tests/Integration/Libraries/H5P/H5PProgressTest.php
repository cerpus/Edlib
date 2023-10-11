<?php

namespace Tests\Integration\Libraries\H5P;

use App\Libraries\H5P\H5PProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;
use Tests\Helpers\TestHelpers;
use Tests\Seeds\TestProgressSeeder;
use Tests\TestCase;
use TypeError;

class H5PProgressTest extends TestCase
{
    use RefreshDatabase;
    use TestHelpers;

    public function assertPreConditions(): void
    {
        $this->seed(TestProgressSeeder::class);
    }

    private function getH5PProgress($userId = null)
    {
        return new H5PProgress(DB::connection()->getPdo(), is_null($userId) ? rand(1, 100) : $userId);
    }

    public function testTrackProgress()
    {
        $this->get('api/progress', [])
            ->assertJson(['success' => true]);
    }

    public function testStoreUserContentInvalidData()
    {
        $h5pprogress = $this->getH5PProgress();
        $response = self::callmethod($h5pprogress, "storeusercontentdata", [[]]);
        $this->assertJson(json_encode($response), json_encode(["success" => false, "message" => "Missing parameters"]));
    }

    public function testStoreProgressWithInvalidAction()
    {
        $this->expectException(TypeError::class);
        $h5pprogress = $this->getH5PProgress();
        /** @noinspection PhpParamsInspection */
        $h5pprogress->storeProgress("InvalidAction");
    }

    public function testStoreProgressWithActionButNoData()
    {
        $expectedResult = new stdClass();
        $expectedResult->success = false;
        $expectedResult->message = "Missing parameters";
        $h5pprogress = $this->getH5PProgress();
        $this->assertEquals(
            $expectedResult,
            $h5pprogress->storeProgress(Request::create('', parameters: ['action' => "h5p_contents_user_data"]))
        );
    }

    public function testShouldUpdate()
    {
        $h5pprogress = $this->getH5PProgress(1);
        $shouldUpdate = self::callmethod($h5pprogress, "shouldUpdate", [
            1,
            'state',
            0,
            null
        ]);
        $this->assertTrue($shouldUpdate);

        $shouldUpdate = self::callmethod($h5pprogress, "shouldUpdate", [
            1,
            'state',
            0,
            'myContext'
        ]);
        $this->assertFalse($shouldUpdate);

        $shouldUpdate = self::callmethod($h5pprogress, "shouldUpdate", [
            2,
            'state',
            0,
            'context_1'
        ]);
        $this->assertTrue($shouldUpdate);

        $shouldUpdate = self::callmethod($h5pprogress, "shouldUpdate", [
            2,
            'state',
            0,
            'context_different'
        ]);
        $this->assertFalse($shouldUpdate);

        $shouldUpdate = self::callmethod($h5pprogress, "shouldUpdate", [
            3,
            'state',
            0,
            null
        ]);
        $this->assertFalse($shouldUpdate);

        $shouldUpdate = self::callmethod($h5pprogress, "shouldUpdate", [
            3,
            'state',
            0,
            'Context'
        ]);
        $this->assertFalse($shouldUpdate);
    }

    public function testStoreUserContentData()
    {
        $courseid1 = rand(100, 200);
        $user1 = rand(1, 100);
        $user2 = rand(101, 200);

        $h5pprogressuser1 = $this->getH5PProgress($user1);

        $this->assertequals(0, $h5pprogressuser1->countprogresses($courseid1));
        $response = self::callmethod($h5pprogressuser1, "storeusercontentdata", [
            [
                'content_id' => $courseid1,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => json_encode([
                    "test" => "ok",
                    "all" => "good"
                ]),
                'preload' => '1',
                'invalidate' => '0'
            ]
        ]);

        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Inserting"]));
        $this->assertequals(1, $h5pprogressuser1->countprogresses($courseid1));
        $this->assertDatabaseHas('h5p_contents_user_data', [
            'content_id' => $courseid1,
            'user_id' => $user1,
            'data' => json_encode([
                "test" => "ok",
                "all" => "good",
            ]),
        ]);

        $response = self::callmethod($h5pprogressuser1, "storeusercontentdata", [
            [
                'content_id' => $courseid1,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => json_encode([
                    "test" => "ok",
                    "all" => "good",
                    "second" => "run"
                ]),
                'preload' => '1',
                'invalidate' => '0'
            ]
        ]);

        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Updating"]));
        $this->assertequals(1, $h5pprogressuser1->countprogresses($courseid1));
        $this->assertDatabaseHas('h5p_contents_user_data', [
            'content_id' => $courseid1,
            'user_id' => $user1,
            'data_id' => 'state',
            'sub_content_id' => '0',
            'data' => json_encode([
                "test" => "ok",
                "all" => "good",
                "second" => "run"
            ]),
            'preload' => '1',
            'invalidate' => '0',
            'context' => null,
        ]);

        $h5pprogressuser2 = $this->getH5PProgress($user2);

        $response = self::callmethod($h5pprogressuser2, "storeusercontentdata", [
            [
                'content_id' => $courseid1,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => json_encode([
                    "second_user" => "GO",
                    "arrived" => "soon"
                ]),
                'preload' => '1',
                'invalidate' => '0'
            ]
        ]);

        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Inserting"]));
        $this->assertequals(2, $h5pprogressuser2->countprogresses($courseid1));
        $this->assertDatabaseHas('h5p_contents_user_data', [
            'content_id' => $courseid1,
            'user_id' => $user2,
            'data' => json_encode([
                "second_user" => "GO",
                "arrived" => "soon"
            ]),
        ]);

        $response = self::callmethod($h5pprogressuser2, "storeusercontentdata", [
            [
                'content_id' => $courseid1,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => json_encode([
                    "second_user" => "GO",
                    "arrived" => "safely"
                ]),
                'preload' => '1',
                'invalidate' => '0'
            ]
        ]);

        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Updating"]));
        $this->assertequals(2, $h5pprogressuser2->countprogresses($courseid1));
        $this->assertDatabaseHas('h5p_contents_user_data', [
            'content_id' => $courseid1,
            'user_id' => $user2,
            'data' => json_encode([
                "second_user" => "GO",
                "arrived" => "safely"
            ])
        ]);


        $response = self::callmethod($h5pprogressuser1, "storeusercontentdata", [
            [
                'content_id' => $courseid1,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => '0',
                'preload' => '1',
                'invalidate' => '0'
            ]
        ]);

        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Deleting."]));
        $this->assertequals(1, $h5pprogressuser1->countprogresses($courseid1));
        $this->assertDatabaseMissing('h5p_contents_user_data', [
            'content_id' => $courseid1,
            'user_id' => $user1,
        ]);

        $response = self::callmethod($h5pprogressuser2, "storeusercontentdata", [
            [
                'content_id' => $courseid1,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => '0',
                'preload' => '1',
                'invalidate' => '0'
            ]
        ]);
        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Deleting."]));
        $this->assertequals(0, $h5pprogressuser2->countprogresses($courseid1));
        $this->assertDatabaseMissing('h5p_contents_user_data', [
            'content_id' => $courseid1,
            'user_id' => $user2,
        ]);
    }

    public function testStoreUserContentDataWithContext()
    {
        $h5pprogressuser = new H5PProgress(DB::connection()->getPdo(), 1);
        $response = self::callmethod($h5pprogressuser, "storeusercontentdata", [
            [
                'content_id' => 1,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => json_encode([
                    "new" => "row in db",
                ]),
                'preload' => '1',
                'invalidate' => '0',
                'context' => 'context_1'
            ]
        ]);

        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Inserting"]));
        $this->assertDatabaseHas("h5p_contents_user_data", [
            'content_id' => 1,
            'user_id' => 1,
            'data_id' => 'state',
            'sub_content_id' => 0,
            'data' => json_encode([
                "new" => "row in db",
            ]),
            'preload' => 1,
            'invalidate' => 0,
            'context' => 'context_1'
        ]);

        $response = self::callmethod($h5pprogressuser, "storeusercontentdata", [
            [
                'content_id' => 1,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => json_encode([
                    "new" => "set context to null",
                ]),
                'preload' => '1',
                'invalidate' => '0',
                'context' => null
            ]
        ]);

        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Updating"]));
        $this->assertDatabaseHas("h5p_contents_user_data", [
            'content_id' => 1,
            'user_id' => 1,
            'data_id' => 'state',
            'sub_content_id' => 0,
            'data' => json_encode([
                "new" => "set context to null",
            ]),
            'preload' => 1,
            'invalidate' => 0,
            'context' => null
        ]);

        $response = self::callmethod($h5pprogressuser, "storeusercontentdata", [
            [
                'content_id' => 2,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => json_encode([
                    "update" => "course",
                ]),
                'preload' => '1',
                'invalidate' => '0',
                'context' => 'context_1'
            ]
        ]);
        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Updating"]));
        $this->assertDatabaseHas("h5p_contents_user_data", [
            'content_id' => 2,
            'user_id' => 1,
            'data_id' => 'state',
            'sub_content_id' => 0,
            'data' => json_encode([
                "update" => "course",
            ]),
            'preload' => 1,
            'invalidate' => 0,
            'context' => 'context_1',
        ]);

        $response = self::callmethod($h5pprogressuser, "storeusercontentdata", [
            [
                'content_id' => 2,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => json_encode([
                    "update" => "course",
                ]),
                'preload' => '1',
                'invalidate' => '0',
                'context' => null
            ]
        ]);
        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Inserting"]));
        $this->assertDatabaseHas("h5p_contents_user_data", [
            'content_id' => 2,
            'user_id' => 1,
            'data_id' => 'state',
            'sub_content_id' => 0,
            'data' => json_encode([
                "update" => "course",
            ]),
            'preload' => 1,
            'invalidate' => 0,
            'context' => null,
        ]);
    }

    public function testDeleteUserContentWithContext()
    {
        $contentId = 2;
        $userId = 1;

        $h5pprogressuser = new H5PProgress(DB::connection()->getPdo(), $userId);
        $response = self::callmethod($h5pprogressuser, "storeusercontentdata", [
            [
                'content_id' => $contentId,
                'data_type' => 'state',
                'sub_content_id' => 0,
                'data' => '0',
                'preload' => '1',
                'invalidate' => '0',
                'context' => 'context_1'
            ]
        ]);

        $this->assertJson(json_encode($response), json_encode(["success" => true, "message" => "Deleting"]));
        $this->assertEquals(2, $h5pprogressuser->countProgresses($contentId));
        $this->assertDatabaseMissing("h5p_contents_user_data", [
            'content_id' => $contentId,
            'user_id' => $userId,
            'data_id' => 'state',
            'context' => 'context_1'
        ]);
        $this->assertDatabaseHas("h5p_contents_user_data", [
            'content_id' => $contentId,
            'user_id' => $userId,
            'data_id' => 'state',
            'context' => null
        ]);
    }

    public function testState()
    {
        $progress = $this->getH5PProgress(1);

        $invalidContentId = $progress->getState(100000, null);
        $this->assertFalse($invalidContentId);

        $validContentIdInvalidContext = $progress->getState(2, 'invalidContent');
        $this->assertFalse($validContentIdInvalidContext);

        $validContentValidContext = $progress->getState(1, null);
        $this->assertEquals("array", gettype($validContentValidContext));

        $validContentValidContext = $progress->getState(2, 'context_1');
        $this->assertEquals("array", gettype($validContentValidContext));
    }

    public function testMissingRequestWithProgress()
    {
        $this->expectException(TypeError::class);

        $progress = $this->getH5PProgress(1);
        /** @noinspection PhpParamsInspection */
        $progress->getProgress();
    }

    public function testProgress()
    {
        $progress = $this->getH5PProgress(1);
        $this->assertNull($progress->getProgress(new Request()));
        $this->assertNull($progress->getProgress(Request::create('', parameters: [
            'content_id' => 2000000000,
            'sub_content_id' => 0,
            'data_type' => 'state',
            'context' => null
        ])));

        $this->assertEquals("['hello', 'everyone']", $progress->getProgress(Request::create('', parameters: [
            'content_id' => 2,
            'sub_content_id' => 0,
            'data_type' => 'state',
            'context' => null
        ])));

        $this->assertEquals("['hello', 'there']", $progress->getProgress(Request::create('', parameters: [
            'content_id' => 2,
            'sub_content_id' => 0,
            'data_type' => 'state',
            'context' => 'context_1'
        ])));
    }

    public function testResult()
    {
        $progress = $this->getH5PProgress(1);

        $response = self::callmethod($progress, "processFinished", [[]]);
        $this->assertFalse($response);

        $response = self::callmethod($progress, "processFinished", [
            [
                'contentId' => 1,
                'score' => 2,
                'maxScore' => 4,
                'opened' => 11111,
                'finished' => 2222,
            ]
        ]);
        $this->assertTrue($response);
        $this->assertDatabaseHas("h5p_results", [
            'content_id' => 1,
            'score' => 2,
            'max_score' => 4,
            'context' => null
        ]);

        $response = self::callmethod($progress, "processFinished", [
            [
                'contentId' => 1,
                'score' => 3,
                'maxScore' => 4,
                'opened' => 11111,
                'finished' => 2222,
            ]
        ]);
        $this->assertTrue($response);
        $this->assertDatabaseHas("h5p_results", [
            'content_id' => 1,
            'score' => 3,
            'max_score' => 4,
            'context' => null
        ]);

        $response = self::callmethod($progress, "processFinished", [
            [
                'contentId' => 1,
                'score' => 4,
                'maxScore' => 4,
                'opened' => 11111,
                'finished' => 2222,
                'context' => "myContext_1"
            ]
        ]);
        $this->assertTrue($response);
        $this->assertDatabaseHas("h5p_results", [
            'content_id' => 1,
            'score' => 4,
            'max_score' => 4,
            'context' => "myContext_1"
        ]);
        $this->assertDatabaseHas("h5p_results", [
            'content_id' => 1,
            'score' => 3,
            'max_score' => 4,
            'context' => null
        ]);

        $response = self::callmethod($progress, "processFinished", [
            [
                'contentId' => 2,
                'score' => 1,
                'maxScore' => 2,
                'opened' => 11111,
                'finished' => 2222,
                'context' => "myContext_2"
            ]
        ]);
        $this->assertTrue($response);
        $this->assertDatabaseHas("h5p_results", [
            'content_id' => 2,
            'score' => 1,
            'max_score' => 2,
            'context' => "myContext_2"
        ]);

        $response = self::callmethod($progress, "processFinished", [
            [
                'contentId' => 2,
                'score' => 2,
                'maxScore' => 2,
                'opened' => 11111,
                'finished' => 2222,
                'context' => "myContext_2"
            ]
        ]);
        $this->assertTrue($response);
        $this->assertDatabaseHas("h5p_results", [
            'content_id' => 2,
            'score' => 2,
            'max_score' => 2,
            'context' => "myContext_2"
        ]);
        $this->assertDatabaseMissing("h5p_results", [
            'content_id' => 2,
            'score' => 1,
            'max_score' => 2,
            'context' => "myContext_2"
        ]);
    }
}
