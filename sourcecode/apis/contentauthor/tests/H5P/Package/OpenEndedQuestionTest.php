<?php

namespace Tests\H5P\Package;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Libraries\H5P\Packages\OpenEndedQuestion;

class OpenEndedQuestionTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function validateStructure()
    {
        $openEndedQuesiton = new OpenEndedQuestion('');
        $this->assertFalse($openEndedQuesiton->validate());

        $params = [
            'library' => null,
        ];
        $openEndedQuesiton = new OpenEndedQuestion(json_encode($params));
        $this->assertFalse($openEndedQuesiton->validate());

        $params['library'] = null;
        $openEndedQuesiton = new OpenEndedQuestion(json_encode($params));
        $this->assertFalse($openEndedQuesiton->validate());

        $params['library'] = "H5P.InvalidMachingName";
        $openEndedQuesiton = new OpenEndedQuestion(json_encode($params));
        $this->assertFalse($openEndedQuesiton->validate());

        $params['library'] = "H5P.OpenEndedQuestion";
        $params['params']['question'] = "FOO";
        $openEndedQuesiton = new OpenEndedQuestion(json_encode($params));
        $this->assertTrue($openEndedQuesiton->validate());
    }

    /**
     * @test
     */
    public function getElements()
    {
        $params['params'] = [
            'question' => $this->faker->sentence
        ];
        $openEndedQuesiton = new OpenEndedQuestion(json_encode($params));
        $elements = $openEndedQuesiton->getElements();
        $this->assertNotEmpty($elements);
        $this->assertCount(6, $elements);
        $this->assertArrayHasKey("question", $elements);
        $this->assertArrayHasKey("type", $elements);
        $this->assertEquals(OpenEndedQuestion::class, $elements['type']);
        $this->assertEquals("text", $elements['short_type']);
        $this->assertFalse($elements['composedComponent']);
    }
}
