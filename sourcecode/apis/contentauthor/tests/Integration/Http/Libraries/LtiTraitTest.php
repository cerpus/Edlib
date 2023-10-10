<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Libraries;

use App\H5pLti;
use App\Http\Libraries\LtiTrait;
use App\Http\Requests\LTIRequest;
use Exception;
use Illuminate\Http\Request;
use Tests\TestCase;

class LtiTraitTestClass
{
    use LtiTrait;

    public function __construct(private readonly H5pLti $lti)
    {
    }

    public function create(Request $request): string
    {
        return 'create';
    }

    public function doShow($id, $context, $preview = false): string
    {
        return 'doShow';
    }

    public function edit(Request $request, $id): string
    {
        return 'edit';
    }
}

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

        $class = app(LtiTraitTestClass::class);
        $class->ltiShow(1);
    }

    public function test_ltiShow(): void
    {
        $this->setupLti();
        $testClass = app(LtiTraitTestClass::class);
        $this->assertSame('doShow', $testClass->ltiShow(42));
    }

    public function test_ltiCreate_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No valid LTI request');

        $class = app(LtiTraitTestClass::class);
        $class->ltiCreate(Request::create(''));
    }

    public function test_ltiCreate(): void
    {
        $this->setupLti();
        $testClass = app(LtiTraitTestClass::class);
        $this->assertSame('create', $testClass->ltiCreate(Request::create('')));
    }

    public function test_ltiEdit_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No valid LTI request');

        $class = app(LtiTraitTestClass::class);
        $class->ltiEdit(Request::create(''), 1);
    }

    public function test_ltiEdit(): void
    {
        $this->setupLti();
        $testClass = app(LtiTraitTestClass::class);
        $this->assertSame('edit', $testClass->ltiEdit(Request::create(''), 42));
    }
}
