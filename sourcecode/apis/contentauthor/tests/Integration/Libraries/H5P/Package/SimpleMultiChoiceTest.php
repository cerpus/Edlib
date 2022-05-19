<?php

namespace Tests\Integration\Libraries\H5P\Package;


use App\Libraries\H5P\Packages\SimpleMultiChoice;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SimpleMultiChoiceTest extends TestCase
{
    use WithFaker;

    /**
     * @test
     */
    public function validate()
    {
        $simpleMultiChoice = new SimpleMultiChoice('');
        $this->assertFalse($simpleMultiChoice->validate());

        $params = ['library' => null];
        $simpleMultiChoice = new SimpleMultiChoice(json_encode($params));
        $this->assertFalse($simpleMultiChoice->validate());

        $params['library'] = "H5P.NotCorrectMachineName";
        $simpleMultiChoice = new SimpleMultiChoice(json_encode($params));
        $this->assertFalse($simpleMultiChoice->validate());

        $params['library'] = "H5P.SimpleMultiChoice";
        $simpleMultiChoice = new SimpleMultiChoice(json_encode($params));
        $this->assertFalse($simpleMultiChoice->validate());

        $params['params']['question'] = "FOO";
        $simpleMultiChoice = new SimpleMultiChoice(json_encode($params));
        $this->assertTrue($simpleMultiChoice->validate());
    }

    /**
     * @test
     */
    public function getElements()
    {
        $alternatives = [
            (object) [
                'text' => $this->faker->word,
            ],
            (object) [
                'text' => $this->faker->word,
            ],
            (object) [
                'text' => $this->faker->word,
            ],
            (object) [
                'text' => $this->faker->word,
            ],
            (object) [
                'text' => $this->faker->word,
            ],
        ];
        $params['params'] = [
            'question' => $this->faker->sentence,
            'inputType' => "checkbox",
            'alternatives' => $alternatives,
        ];
        $simpleMultiChoice = new SimpleMultiChoice(json_encode($params));
        $answers = implode('[,]', [0, 1, 3]);
        $simpleMultiChoice->setAnswers($answers);
        $elements = $simpleMultiChoice->getElements();
        $this->assertNotEmpty($elements);
        $this->assertCount(6, $elements);
        $this->assertArrayHasKey("question", $elements);
        $this->assertArrayHasKey("type", $elements);
        $this->assertEquals(SimpleMultiChoice::class, $elements['type']);
        $this->assertEquals("options", $elements['short_type']);
        $this->assertFalse($elements['composedComponent']);
        $this->assertEquals(implode(", ", [$alternatives[0]->text, $alternatives[1]->text, $alternatives[3]->text]), $elements['answer']);
    }
}
