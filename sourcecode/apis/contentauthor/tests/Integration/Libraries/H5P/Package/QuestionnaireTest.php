<?php

namespace Tests\Integration\Libraries\H5P\Package;

use App\Libraries\H5P\Packages\Questionnaire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuestionnaireTest extends TestCase
{
    private static array $elements = [
        'twoTextAndOneRadio' => '{"questionnaireElements":[{"library":{"params":{"placeholderText":"Skriv din ærlige mening her.","inputRows":"3","question":"Hva er ditt syn på fiske?"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"5f1cab82-66cf-4df5-897d-a26ad3346eee"},"requiredField":false},{"library":{"params":{"placeholderText":"Skriver her...","inputRows":"10","question":"Hva er ditt beste minne fra friluftslivtur?"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"111ee02d-ba7f-4cec-b19e-47134209c80f"},"requiredField":false},{"library":{"params":{"inputType":"radio","alternatives":[{"feedback":{"chosenFeedback":"<div>Helt rett!</div>\n","notChosenFeedback":""},"text":"&gt;= 3"},{"feedback":{"chosenFeedback":"<div>Er du verrûckt?</div>\n","notChosenFeedback":"<div>Nu må du skjærpe deg!</div>\n"},"text":"0"},{"feedback":{"chosenFeedback":"<div>En god start.</div>\n","notChosenFeedback":""},"text":"1"},{"feedback":{"chosenFeedback":"<div>Tampen brenner!</div>\n","notChosenFeedback":""},"text":"2"}],"question":"Hvor mange fisketurer er passende hvert år"},"library":"H5P.SimpleMultiChoice 1.1","subContentId":"98f39cf3-24fd-480b-b7bd-0a8e233c46b7"},"requiredField":true}],"successScreenOptions":{"enableSuccessScreen":false,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"de165056-273c-4b8c-a9ed-4ee240d70136"},"successMessage":"Du er nå ferdig med spørreskjemaet."},"uiElements":{"buttonLabels":{"prevLabel":"Forrige","continueLabel":"Fortsett","nextLabel":"Neste","submitLabel":"Send inn"},"accessibility":{"requiredTextExitLabel":"Lukk feilmelding","progressBarText":"Spørsmål %current av %max"},"requiredMessage":"Dette spørsmålet krever et svar","requiredText":"nødvendig","submitScreenTitle":"Du er nå ferdig med å svare på spørsmålene.","submitScreenSubtitle":"Trykk under for å sende inn dine svar"}}',
        'oneText' => '{"questionnaireElements":[{"library":{"params":{"placeholderText":"Skriv din ærlige mening her.","inputRows":"3","question":"Hva er ditt syn på fiske?"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"5f1cab82-66cf-4df5-897d-a26ad3346eee"},"requiredField":false}],"successScreenOptions":{"enableSuccessScreen":false,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"de165056-273c-4b8c-a9ed-4ee240d70136"},"successMessage":"Du er nå ferdig med spørreskjemaet."},"uiElements":{"buttonLabels":{"prevLabel":"Forrige","continueLabel":"Fortsett","nextLabel":"Neste","submitLabel":"Send inn"},"accessibility":{"requiredTextExitLabel":"Lukk feilmelding","progressBarText":"Spørsmål %current av %max"},"requiredMessage":"Dette spørsmålet krever et svar","requiredText":"nødvendig","submitScreenTitle":"Du er nå ferdig med å svare på spørsmålene.","submitScreenSubtitle":"Trykk under for å sende inn dine svar"}}',
    ];

    #[Test]
    public function validateStructure()
    {
        $questionnaire = new Questionnaire("InvalidJson");
        $this->assertFalse($questionnaire->validate());

        $questionnaire = new Questionnaire('{}');
        $this->assertFalse($questionnaire->validate());

        $questionnaire = new Questionnaire(json_encode([
            'questionnaireElements' => "snafu",
        ]));
        $this->assertTrue($questionnaire->validate());

        $questionnaire = new Questionnaire(json_encode([
            'questionnaireElements' => [
                'foo' => 'bar',
            ],
        ]));
        $this->assertTrue($questionnaire->validate());
    }

    #[Test]
    public function getQuestions()
    {
        $questionnaire = new Questionnaire(json_encode([
            'questionnaireElements' => null,
        ]));
        $this->assertEmpty($questionnaire->getElements());

        $questionnaire = new Questionnaire(self::$elements['twoTextAndOneRadio']);
        $elements = $questionnaire->getElements();
        $this->assertNotEmpty($elements);
        $componentElements = $elements['elements'];
        $this->assertCount(3, $componentElements);
        $this->assertEquals("Hva er ditt syn på fiske?", $componentElements[0]['question']);
        $this->assertEquals("Hva er ditt beste minne fra friluftslivtur?", $componentElements[1]['question']);
        $this->assertEquals("Hvor mange fisketurer er passende hvert år", $componentElements[2]['question']);

        $questionnaire = new Questionnaire(self::$elements['oneText']);
        $elements = $questionnaire->getElements();
        $this->assertNotEmpty($elements);
        $this->assertCount(2, $elements);
        $this->assertEquals("Hva er ditt syn på fiske?", $elements['elements'][0]['question']);
    }
}
