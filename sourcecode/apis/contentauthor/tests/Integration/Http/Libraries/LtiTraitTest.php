<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Libraries;

use App\H5pLti;
use App\Http\Requests\LTIRequest;
use Exception;
use Illuminate\Http\Request;
use Tests\Integration\Http\Libraries\Stubs\LtiTraitStubClass;
use Tests\TestCase;

class LtiTraitTest extends TestCase
{
    public function setupLti(): void
    {
        $ltiRequest = $this->createMock(LTIRequest::class);

        $this->instance(LTIRequest::class, $ltiRequest);

        $h5pLti = $this->createMock(H5pLti::class);
        $h5pLti->expects($this->once())
            ->method('getValidatedLtiRequest')
            ->willReturn($ltiRequest);
        $this->instance(H5pLti::class, $h5pLti);
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
        $this->assertSame('create', $testClass->ltiCreate(Request::create('')));
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
        $this->assertSame('edit', $testClass->ltiEdit(Request::create(''), 42));
    }
}
