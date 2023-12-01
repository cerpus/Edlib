<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Libraries;

use App\Lti\LtiRequest;
use Cerpus\EdlibResourceKit\Oauth1\ValidatorInterface;
use Exception;
use Illuminate\Http\Request;
use Tests\Integration\Http\Libraries\Stubs\LtiTraitStubClass;
use Tests\TestCase;

class LtiTraitTest extends TestCase
{
    public function setupLti(): void
    {
        $ltiRequest = $this->createMock(LtiRequest::class);
        $this->instance(LTIRequest::class, $ltiRequest);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())->method('validate');
        $this->instance(ValidatorInterface::class, $validator);
    }

    public function test_ltiShow_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No valid LTI request');

        $class = app(LtiTraitStubClass::class);
        $class->ltiShow(1);
    }

    public function test_ltiShow(): void
    {
        $this->setupLti();

        $request = Request::create('', 'POST', [
            'lti_message_type' => 'basic-lti-launch-request',
        ]);
        $this->instance('request', $request);

        $testClass = app(LtiTraitStubClass::class);
        $this->assertSame('doShow', $testClass->ltiShow(42));
    }

    public function test_ltiCreate_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No valid LTI request');

        $class = app(LtiTraitStubClass::class);
        $class->ltiCreate(Request::create(''));
    }

    public function test_ltiCreate(): void
    {
        $this->setupLti();
        $testClass = app(LtiTraitStubClass::class);
        $this->assertSame('create', $testClass->ltiCreate(
            Request::create('', 'POST', ['lti_message_type' => 'basic-lti-launch-request'])
        ));
    }

    public function test_ltiEdit_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No valid LTI request');

        $class = app(LtiTraitStubClass::class);
        $class->ltiEdit(Request::create(''), 1);
    }

    public function test_ltiEdit(): void
    {
        $this->setupLti();
        $testClass = app(LtiTraitStubClass::class);
        $this->assertSame('edit', $testClass->ltiEdit(
            Request::create('', 'POST', ['lti_message_type' => 'basic-lti-launch-request']),
            42
        ));
    }
}
