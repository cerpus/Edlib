<?php

namespace Tests\Middleware;

use App\Http\Middleware\DraftAction;
use App\Http\Requests\LTIRequest;
use App\SessionKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class DraftActionTest extends TestCase
{

    private function buildLtiMock($draftAction)
    {
        $ltiRequest = $this
            ->getMockBuilder(LTIRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $ltiRequest->method('getExtUseDraftLogic')->willReturn($draftAction);
        $ltiRequest->method('getToolConsumerInfoProductFamilyCode')->willReturn("TestTool");
        return $ltiRequest;
    }
    
    /**
     * @test
     */
    public function notValidLti()
    {
        $request = new Request();
        $request->setLaravelSession(session());
        $draftMiddleware = $this->createPartialMock(DraftAction::class, ['getLtiRequest']);
        $draftMiddleware->method('getLtiRequest')->willReturn(null);
        $draftMiddleware->handle($request, function ($req) use ($request){
            $this->assertEquals($request, $req);
            $this->assertNull($req->session()->get(sprintf(SessionKeys::EXT_DRAFT_SETTING, $req->request->get('redirectToken'))));
        });
    }

    /**
     * @test
     */
    public function validLtiWithDraftSetting()
    {
        $ltiRequest = $this->buildLtiMock('true');

        $draftMiddleware = $this->createPartialMock(DraftAction::class, ['getLtiRequest']);
        $draftMiddleware->method('getLtiRequest')->willReturn($ltiRequest);

        $request = new Request();
        $request->setLaravelSession(session());
        $draftMiddleware->handle($request, function (Request $req) use ($request){
            $this->assertArrayNotHasKey('redirectToken', $request->all());
            $this->assertArrayHasKey('redirectToken', $req->request->all());
            $this->assertTrue($req->session()->get(sprintf(SessionKeys::EXT_DRAFT_SETTING, $req->request->get('redirectToken'))));
        });
    }

    /**
     * @test
     */
    public function validLtiWithInvalidDraftSetting()
    {
        $ltiRequest = $this->buildLtiMock('notValid');

        $draftMiddleware = $this->createPartialMock(DraftAction::class, ['getLtiRequest']);
        $draftMiddleware->method('getLtiRequest')->willReturn($ltiRequest);

        $request = new Request();
        $request->setLaravelSession(session());
        $draftMiddleware->handle($request, function (Request $req) use ($request){
            $this->assertArrayNotHasKey('redirectToken', $request->all());
            $this->assertArrayHasKey('redirectToken', $req->request->all());
            $this->assertFalse($req->session()->get(sprintf(SessionKeys::EXT_DRAFT_SETTING, $req->request->get('redirectToken'))));
        });
    }

    /**
     * @test
     */
    public function validLtiWithRedirectToken()
    {
        $ltiRequest = $this->buildLtiMock('notValid');

        $draftMiddleware = $this->createPartialMock(DraftAction::class, ['getLtiRequest']);
        $draftMiddleware->method('getLtiRequest')->willReturn($ltiRequest);

        $redirectToken = Str::uuid();
        $request = new Request(['redirectToken' => $redirectToken]);
        $request->setLaravelSession(session());
        $draftMiddleware->handle($request, function (Request $req) use ($request, $redirectToken){
            $this->assertArrayHasKey('redirectToken', $request->all());
            $this->assertArrayNotHasKey('redirectToken', $req->request->all());
            $this->assertEquals($redirectToken, $req->get('redirectToken'));
        });
    }

    /**
     * @test
     */
    public function validLtiWithRedirectTokenAndValidDraftAction()
    {
        $ltiRequest = $this->buildLtiMock('false');

        $draftMiddleware = $this->createPartialMock(DraftAction::class, ['getLtiRequest']);
        $draftMiddleware->method('getLtiRequest')->willReturn($ltiRequest);

        $redirectToken = Str::uuid();
        $request = new Request(['redirectToken' => $redirectToken]);
        $request->setLaravelSession(session());

        $draftMiddleware->handle($request, function (Request $req) use ($request, $redirectToken){
            $this->assertArrayHasKey('redirectToken', $request->all());
            $this->assertArrayNotHasKey('redirectToken', $req->request->all());
            $this->assertEquals($redirectToken, $req->get('redirectToken'));
            $this->assertFalse($req->session()->get(sprintf(SessionKeys::EXT_DRAFT_SETTING, $redirectToken)));
        });
    }
}
