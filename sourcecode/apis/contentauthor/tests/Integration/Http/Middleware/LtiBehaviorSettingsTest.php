<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Middleware;

use App\Http\Middleware\LtiBehaviorSettings;
use App\Libraries\DataObjects\BehaviorSettingsDataObject;
use App\Libraries\DataObjects\EditorBehaviorSettingsDataObject;
use App\SessionKeys;
use Cerpus\EdlibResourceKit\Oauth1\ValidatorInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LtiBehaviorSettingsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->expects($this->once())->method('validate');
        $this->instance(ValidatorInterface::class, $validator);
    }

    public function test_handle_ViewContext(): void
    {
        $request = Request::create('', 'POST', [
            'lti_message_type' => 'basic-lti-launch-request',
            'ext_behavior_settings' => '{"enableRetry":true,"showSolution":false,"includeAnswers":null}',
        ]);

        $middleware = app(LtiBehaviorSettings::class);
        $middleware->handle($request, fn() => null, 'view');

        $settings = Session::get(SessionKeys::EXT_BEHAVIOR_SETTINGS);

        $this->assertInstanceOf(BehaviorSettingsDataObject::class, $settings);
        $this->assertTrue($settings->enableRetry);
        $this->assertFalse($settings->showSolution);
        $this->assertNull($settings->includeAnswers);
    }

    public function test_handle_EditorContext(): void
    {
        $request = Request::create('', 'POST', [
            'lti_message_type' => 'basic-lti-launch-request',
            'ext_behavior_settings' => '{"hideTextAndTranslations":true,"behaviorSettings":{"enableRetry":true,"showSolution":false,"includeAnswers":null}}',
        ]);

        $middleware = app(LtiBehaviorSettings::class);
        $middleware->handle($request, fn() => null, 'editor');

        $editorSettings = Session::get(sprintf(SessionKeys::EXT_EDITOR_BEHAVIOR_SETTINGS, $request->get('redirectToken')));

        $this->assertInstanceOf(EditorBehaviorSettingsDataObject::class, $editorSettings);
        $this->assertTrue($editorSettings->hideTextAndTranslations);

        $settings = Session::get(SessionKeys::EXT_BEHAVIOR_SETTINGS);

        $this->assertInstanceOf(BehaviorSettingsDataObject::class, $settings);
        $this->assertTrue($settings->enableRetry);
        $this->assertFalse($settings->showSolution);
        $this->assertNull($settings->includeAnswers);
    }
}
