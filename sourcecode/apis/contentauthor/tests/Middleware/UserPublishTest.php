<?php

namespace Tests\Middleware;

use App\Http\Middleware\UserPublishAction;
use App\Http\Requests\LTIRequest;
use App\SessionKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class UserPublishTest extends TestCase
{

    private function buildLtiMock($userPublishReturn): LTIRequest|MockObject
    {
        $ltiRequest = $this
            ->getMockBuilder(LTIRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ltiRequest->method('getExtEnableUserPublish')->willReturn($userPublishReturn);
        $ltiRequest->method('getToolConsumerInfoProductFamilyCode')->willReturn("TestTool");

        return $ltiRequest;
    }

    public function testNotValidLti()
    {
        $request = new Request();
        $request->setLaravelSession(session());
        $draftMiddleware = $this->createPartialMock(UserPublishAction::class, ['getLtiRequest']);
        $draftMiddleware->method('getLtiRequest')->willReturn(null);
        $draftMiddleware->handle($request, function ($req) use ($request) {
            $this->assertEquals($request, $req);
            $this->assertNull(
                $req->session()->get(sprintf(SessionKeys::EXT_USER_PUBLISH_SETTING, $req->request->get('redirectToken')))
            );
        });
    }

    public function testValidLtiWithDraftSetting()
    {
        $ltiRequest = $this->buildLtiMock('true');

        $draftMiddleware = $this->createPartialMock(UserPublishAction::class, ['getLtiRequest']);
        $draftMiddleware->method('getLtiRequest')->willReturn($ltiRequest);

        $request = new Request();
        $request->setLaravelSession(session());
        $draftMiddleware->handle($request, function (Request $req) use ($request) {
            $this->assertArrayNotHasKey('redirectToken', $request->all());
            $this->assertArrayHasKey('redirectToken', $req->request->all());
            $this->assertTrue(
                $req->session()->get(sprintf(SessionKeys::EXT_USER_PUBLISH_SETTING, $req->request->get('redirectToken')))
            );
        });
    }

    public function testValidLtiWithInvalidDraftSetting()
    {
        $ltiRequest = $this->buildLtiMock('notValid');

        $draftMiddleware = $this->createPartialMock(UserPublishAction::class, ['getLtiRequest']);
        $draftMiddleware->method('getLtiRequest')->willReturn($ltiRequest);

        $request = new Request();
        $request->setLaravelSession(session());
        $draftMiddleware->handle($request, function (Request $req) use ($request) {
            $this->assertArrayNotHasKey('redirectToken', $request->all());
            $this->assertArrayHasKey('redirectToken', $req->request->all());
            $this->assertFalse(
                $req->session()->get(sprintf(SessionKeys::EXT_USER_PUBLISH_SETTING, $req->request->get('redirectToken')))
            );
        });
    }

    public function testValidLtiWithRedirectToken()
    {
        $ltiRequest = $this->buildLtiMock('notValid');

        $draftMiddleware = $this->createPartialMock(UserPublishAction::class, ['getLtiRequest']);
        $draftMiddleware->method('getLtiRequest')->willReturn($ltiRequest);

        $redirectToken = Str::uuid();
        $request = new Request(['redirectToken' => $redirectToken]);
        $request->setLaravelSession(session());
        $draftMiddleware->handle($request, function (Request $req) use ($request, $redirectToken) {
            $this->assertArrayHasKey('redirectToken', $request->all());
            $this->assertArrayNotHasKey('redirectToken', $req->request->all());
            $this->assertEquals($redirectToken, $req->get('redirectToken'));
        });
    }

    public function testValidLtiWithRedirectTokenAndValidUserPublish()
    {
        $ltiRequest = $this->buildLtiMock('false');

        $draftMiddleware = $this->createPartialMock(UserPublishAction::class, ['getLtiRequest']);
        $draftMiddleware->method('getLtiRequest')->willReturn($ltiRequest);

        $redirectToken = Str::uuid();
        $request = new Request(['redirectToken' => $redirectToken]);
        $request->setLaravelSession(session());

        $draftMiddleware->handle($request, function (Request $req) use ($request, $redirectToken) {
            $this->assertArrayHasKey('redirectToken', $request->all());
            $this->assertArrayNotHasKey('redirectToken', $req->request->all());
            $this->assertEquals($redirectToken, $req->get('redirectToken'));
            $this->assertFalse($req->session()->get(sprintf(SessionKeys::EXT_USER_PUBLISH_SETTING, $redirectToken)));
        });
    }
}
