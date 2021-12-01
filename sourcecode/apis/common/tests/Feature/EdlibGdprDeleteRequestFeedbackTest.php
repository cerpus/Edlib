<?php

namespace Tests\Feature;

use App\Messaging\Handlers\EdlibGdprDeleteRequestFeedback;
use App\Models\Application;
use App\Models\GdprRequest;
use App\Models\GdprRequestCompletedStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EdlibGdprDeleteRequestFeedbackTest extends TestCase
{
    use RefreshDatabase;


    public function invalidMessage(): array
    {
        return [
            'Empty' => [
                'message' => json_encode([]),
            ],
            'Missing stepName' => [
                'message' => json_encode([
                    'serviceName' => 'sa',
                    'requestId' => '1'
                ]),
            ],
            'Missing requestId' => [
                'message' => json_encode([
                    'serviceName' => 'sa',
                    'stepName' => 'sa'
                ]),
            ],
            'Missing serviceName' => [
                'message' => json_encode([
                    'requestId' => '1',
                    'stepName' => 'sa'
                ]),
            ],
            'Unknown request' => [
                'message' => json_encode([
                    'requestId' => '2',
                    'serviceName' => 'sa',
                    'stepName' => 'sa'
                ]),
            ],
        ];
    }

    /**
     * @dataProvider invalidMessage
     */
    public function testMissingDataOrInvalid(string $message): void
    {
        GdprRequest::factory([
            'id' => '1'
        ])->for(Application::factory())->create();

        Log::shouldReceive('error')
            ->once();

        $handler = new EdlibGdprDeleteRequestFeedback();
        $handler->consume($message);
    }

    public function testValidRequestBehavesCorrectly(): void
    {
        GdprRequest::factory([
            'id' => '1'
        ])->for(Application::factory())->create();

        Log::shouldReceive('error')
            ->times(0);

        $handler = new EdlibGdprDeleteRequestFeedback();
        $handler->consume(json_encode([
            'requestId' => '1',
            'serviceName' => 'sa',
            'stepName' => 'sa',
            'message' => 'test',
        ]));

        $this->assertDatabaseCount(GdprRequestCompletedStep::class, 1);
        $gdprRequestCompletedStep = GdprRequestCompletedStep::whereGdprRequestId('1')->first();

        $this->assertEquals('sa', $gdprRequestCompletedStep->step_name);
        $this->assertEquals('sa', $gdprRequestCompletedStep->service_name);
        $this->assertEquals('1', $gdprRequestCompletedStep->gdpr_request_id);
        $this->assertEquals('test', $gdprRequestCompletedStep->message);
    }
}
