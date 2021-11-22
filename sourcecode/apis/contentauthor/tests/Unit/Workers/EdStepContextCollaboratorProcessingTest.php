<?php

namespace Tests\Unit\Workers;

use Carbon\Carbon;
use Tests\TestCase;
use App\CollaboratorContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Libraries\Workers\EdStepContextCollaboratorProcessor;
use App\Libraries\Workers\Exceptions\CottontailBadMessageException;
use App\Libraries\Workers\Exceptions\CottontailMissingMessageException;

class EdStepContextCollaboratorProcessingTest extends TestCase
{
    use RefreshDatabase;

    protected $invalidMessage = null;
    protected $invalidMessage2 = null;
    protected $validMessage = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->invalidMessage = json_decode('{"timestamp":1494847405,"type":"collaboratorChange","systemId":"EdStep","courseId":3,"contextId":"EdStep|3","collaborators":[],"resources":[{"courseId":3,"moduleId":8,"activityId":39,"contentAuthorId":"2d09c8a1-bdb4-4de0-ac88-dcc9ba18b48e","coreId":"2a095535-215f-4ee3-a331-b21539675d99"}]}');
        $this->invalidMessage2 = json_decode('{"version":"v1","timestamp":1494847405,"type":"invalidMessageType","systemId":"EdStep","courseId":3,"contextId":"EdStep|3","collaborators":[],"resources":[{"courseId":3,"moduleId":8,"activityId":39,"contentAuthorId":"2d09c8a1-bdb4-4de0-ac88-dcc9ba18b48e","coreId":"2a095535-215f-4ee3-a331-b21539675d99"}]}');
        $this->validMessage = json_decode('{"version":"v1","timestamp":1494921014,"type":"collaboratorChange","systemId":"EdStep","courseId":3,"contextId":"EdStep|3","collaborators":[{"type":"user","authId":"6928b0b0-23cd-4dd2-a03a-c1cf02d66b12"}],"resources":[{"courseId":3,"moduleId":8,"activityId":39,"contentAuthorId":"2d09c8a1-bdb4-4de0-ac88-dcc9ba18b48e","coreId":"2a095535-215f-4ee3-a331-b21539675d99"},{"courseId":3,"moduleId":8,"activityId":40,"contentAuthorId":"4dd0989a-4c2f-4c88-9b06-6c0bd93a93d0","coreId":"1a7b023a-6e05-4cc3-9aec-9d92c606c395"}]}');
        $this->validMessage->timestamp = Carbon::now()->timestamp;
    }

    public function testThrowsMissingMessageException()
    {
        $this->expectException(CottontailMissingMessageException::class);

        $processor = new EdStepContextCollaboratorProcessor();
        $processor->process();
    }

    public function testValidatesMessageFormatMustBeObject()
    {
        $this->expectException(CottontailBadMessageException::class);

        $processor = new EdStepContextCollaboratorProcessor();
        $processor->setMessage('just a string');
    }

    public function testValidatesMessageFormatHasAllFields()
    {
        $this->expectException(CottontailBadMessageException::class);

        $processor = new EdStepContextCollaboratorProcessor();
        $processor->setMessage($this->invalidMessage);
    }

    public function testValidatesMessageFormatInvalidMessageType()
    {
        $this->expectException(CottontailBadMessageException::class);

        $processor = new EdStepContextCollaboratorProcessor();
        $processor->setMessage($this->invalidMessage2);
    }

    public function testWillUpdateOldContext()
    {
        CollaboratorContext::factory()->create([
            'system_id' => 'EdStep',
            'context_id' => 'EdStep|3',
            'timestamp' => 1494847400
        ]);
        $this->assertCount(1, CollaboratorContext::all());

        $processor = new EdStepContextCollaboratorProcessor($this->validMessage);
        $processor->process();
        $this->assertCount(2, CollaboratorContext::all());
    }
}
