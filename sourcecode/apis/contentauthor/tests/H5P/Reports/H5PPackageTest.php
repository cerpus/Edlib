<?php

namespace Tests\H5P\Reports;

use App\H5PContent;
use App\H5PContentsUserData;
use App\Libraries\H5P\Reports\H5PPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\db\TestH5PSeeder;
use Tests\TestCase;
use Tests\Traits\WithFaker;

class H5PPackageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @test
     */
    public function getQuestionsAndAnswers_validContext_thenSuccess()
    {
        app()->instance('requestId', 123);

        $this->seed(TestH5PSeeder::class);

        $context = $this->faker->unique()->uuid;
        $userId = $this->faker->unique()->uuid;
        $answers = json_decode('{"questions":["Test","0[,]3",""],"progress":2,"finished":true,"version":1}');

        $content = factory(H5PContent::class)->create([
            'user_id' => $userId,
            'parameters' => '{"questionnaireElements":[{"library":{"params":{"placeholderText":"Start writing...","inputRows":"1","question":"Vanlig spørsmål"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"e9fc6a28-3d63-4f3c-91b0-6a5e93ea440a"},"requiredField":false},{"library":{"params":{"inputType":"checkbox","alternatives":[{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Torsk"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Ørret"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Røye"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Sei"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Steinbit"}],"question":"Fisk i havet"},"library":"H5P.SimpleMultiChoice 1.1","subContentId":"62f2b267-31fd-41bb-84b3-914ee06e41e9"},"requiredField":false},{"library":{"params":{"inputType":"radio","alternatives":[{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Høst"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Sommer"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Vinter"}],"question":"Din favoritt fiskesesong"},"library":"H5P.SimpleMultiChoice 1.1","subContentId":"b3a1cc71-8228-4f09-882a-3648e686c311"},"requiredField":false}],"successScreenOptions":{"enableSuccessScreen":true,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"6b3a6665-fa9d-43fb-82d7-30ba29ca5363"},"successMessage":"You have completed the questionnaire."},"uiElements":{"buttonLabels":{"prevLabel":"Back","continueLabel":"Continue","nextLabel":"Next","submitLabel":"Submit"},"accessibility":{"requiredTextExitLabel":"Close error message","progressBarText":"Question %current of %max"},"requiredMessage":"This question requires an answer","requiredText":"required","submitScreenTitle":"You successfully answered all of the questions","submitScreenSubtitle":"Click below to submit your answers"}}',
            'filtered' => '{"questionnaireElements":[{"library":{"params":{"placeholderText":"Start writing...","inputRows":"1","question":"Vanlig spørsmål"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"e9fc6a28-3d63-4f3c-91b0-6a5e93ea440a"},"requiredField":false},{"library":{"params":{"inputType":"checkbox","alternatives":[{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Torsk"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Ørret"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Røye"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Sei"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Steinbit"}],"question":"Fisk i havet"},"library":"H5P.SimpleMultiChoice 1.1","subContentId":"62f2b267-31fd-41bb-84b3-914ee06e41e9"},"requiredField":false},{"library":{"params":{"inputType":"radio","alternatives":[{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Høst"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Sommer"},{"feedback":{"chosenFeedback":"","notChosenFeedback":""},"text":"Vinter"}],"question":"Din favoritt fiskesesong"},"library":"H5P.SimpleMultiChoice 1.1","subContentId":"b3a1cc71-8228-4f09-882a-3648e686c311"},"requiredField":false}],"successScreenOptions":{"enableSuccessScreen":true,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"6b3a6665-fa9d-43fb-82d7-30ba29ca5363"},"successMessage":"You have completed the questionnaire."},"uiElements":{"buttonLabels":{"prevLabel":"Back","continueLabel":"Continue","nextLabel":"Next","submitLabel":"Submit"},"accessibility":{"requiredTextExitLabel":"Close error message","progressBarText":"Question %current of %max"},"requiredMessage":"This question requires an answer","requiredText":"required","submitScreenTitle":"You successfully answered all of the questions","submitScreenSubtitle":"Click below to submit your answers"}}',
            'library_id' => 207,
        ]);

        factory(H5PContentsUserData::class)->create([
            'data' => json_encode($answers),
            'context' => $context,
            'user_id' => $userId,
            'content_id' => $content->id,
        ]);

        $h5pPackage = new H5PPackage();
        $questionsAndAnswers = $h5pPackage->questionsAndAnswers([$context], $userId);
        $this->assertIsArray($questionsAndAnswers);
        $this->assertCount(1, $questionsAndAnswers);
        $components = $questionsAndAnswers[0];
        $this->assertEquals($context, $components['context']);
        $this->assertCount(2, $components);
        $this->assertTrue($components['elements']['composedComponent']);
        $this->assertIsArray($components['elements']);
        $this->assertCount(2, $components['elements']);
        $componentsElements = $components['elements'];
        $this->assertEquals("Vanlig spørsmål", $componentsElements['elements'][0]['question']);
        $this->assertEquals("Test", $componentsElements['elements'][0]['answer']);
        $this->assertFalse($componentsElements['elements'][0]['composedComponent']);
    }

    /**
     * @test
     */
    public function oneQuestionWithAnswer_validContext_thenSuccess()
    {

        $this->seed(TestH5PSeeder::class);

        $context = $this->faker->unique()->uuid;
        $userId = $this->faker->unique()->uuid;
        $answers = json_decode('{"questions":["FooBar"],"progress":0,"finished":true,"version":1}');

        $content = factory(H5PContent::class)->create([
            'user_id' => $userId,
            'parameters' => '{"questionnaireElements":[{"library":{"params":{"placeholderText":"Start writing...","inputRows":"1","question":"Status?"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"e9fc6a28-3d63-4f3c-91b0-6a5e93ea440a"},"requiredField":false}],"successScreenOptions":{"enableSuccessScreen":true,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"6b3a6665-fa9d-43fb-82d7-30ba29ca5363"},"successMessage":"You have completed the questionnaire."},"uiElements":{"buttonLabels":{"prevLabel":"Back","continueLabel":"Continue","nextLabel":"Next","submitLabel":"Submit"},"accessibility":{"requiredTextExitLabel":"Close error message","progressBarText":"Question %current of %max"},"requiredMessage":"This question requires an answer","requiredText":"required","submitScreenTitle":"You successfully answered all of the questions","submitScreenSubtitle":"Click below to submit your answers"}}',
            'filtered' => '{"questionnaireElements":[{"library":{"params":{"placeholderText":"Start writing...","inputRows":"1","question":"Status?"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"e9fc6a28-3d63-4f3c-91b0-6a5e93ea440a"},"requiredField":false}],"successScreenOptions":{"enableSuccessScreen":true,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"6b3a6665-fa9d-43fb-82d7-30ba29ca5363"},"successMessage":"You have completed the questionnaire."},"uiElements":{"buttonLabels":{"prevLabel":"Back","continueLabel":"Continue","nextLabel":"Next","submitLabel":"Submit"},"accessibility":{"requiredTextExitLabel":"Close error message","progressBarText":"Question %current of %max"},"requiredMessage":"This question requires an answer","requiredText":"required","submitScreenTitle":"You successfully answered all of the questions","submitScreenSubtitle":"Click below to submit your answers"}}',
            'library_id' => 207,
        ]);

        factory(H5PContentsUserData::class)->create([
            'data' => json_encode($answers),
            'context' => $context,
            'user_id' => $userId,
            'content_id' => $content->id,
        ]);

        $h5pPackage = new H5PPackage();
        $questionsAndAnswers = $h5pPackage->questionsAndAnswers([$context], $userId);
        $this->assertIsArray($questionsAndAnswers);
        $this->assertCount(1, $questionsAndAnswers);
        $components = $questionsAndAnswers[0];
        $this->assertEquals($context, $components['context']);
        $this->assertCount(2, $components);
        $this->assertTrue($components['elements']['composedComponent']);
        $this->assertIsArray($components['elements']);
        $this->assertCount(2, $components['elements']);
        $componentsElements = $components['elements'];
        $this->assertTrue($componentsElements['composedComponent']);
        $this->assertEquals("Status?", $componentsElements['elements'][0]['question']);
        $this->assertEquals("FooBar", $componentsElements['elements'][0]['answer']);
        $this->assertFalse($componentsElements['elements'][0]['composedComponent']);
    }

    /**
     * @test
     */
    public function getInteractiveVideo_validContext_thenSuccess()
    {
        $this->seed(TestH5PSeeder::class);

        $context = $this->faker->unique()->uuid;
        $userId = $this->faker->unique()->uuid;
        $answers = json_decode('{"progress":85.976378,"answers":[[],{"questions":["Fordi de spiller god rock!"],"progress":0,"version":1},{"answers":[]}]}');

        $content = factory(H5PContent::class)->create([
            'user_id' => $userId,
            'parameters' => '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interaktiv video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}],"files":[{"path":"https://www.youtube.com/watch?v=yw6kT7jvlx0","mime":"video/YouTube","copyright":{"license":"U"}}]},"assets":{"interactions":[{"x":82.4634655532359,"y":38.961038961038966,"width":10,"height":10,"duration":{"from":52,"to":62},"libraryTitle":"Mark the Words","action":{"library":"H5P.MarkTheWords 1.7","params":{"overallFeedback":[{"from":0,"to":100}],"checkAnswerButton":"Sjekk","tryAgainButton":"Prøv igjen","showSolutionButton":"Fasit","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"showScorePoints":true},"correctAnswer":"Riktig!","incorrectAnswer":"Feil!","missedAnswer":"Mangler!","displaySolutionDescription":"Oppgaven er oppdatert til å inneholde fasiten.","taskDescription":"<p>Klikk på bandene.</p>\n","textField":"<p>Last *kiss* is not a song by the *eagles*. And *queen* as not done it either.</p>\n"},"subContentId":"db680b64-1427-46b3-a18b-f90a7d4f005c"},"pause":false,"displayType":"button","buttonOnMobile":false,"adaptivity":{"correct":{"allowOptOut":false,"message":""},"wrong":{"allowOptOut":false,"message":""},"requireCompletion":false},"label":""},{"x":14.613778705636744,"y":27.82931354359926,"width":10,"height":10,"duration":{"from":24,"to":34},"libraryTitle":"Questionnaire","action":{"library":"H5P.Questionnaire 1.2","params":{"questionnaireElements":[{"library":{"params":{"placeholderText":"Skriver her...","inputRows":"1","question":"Hvorfor Kiss eller AC/DC?"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"197c884f-f78b-4a4a-9339-6552eb3037ac"},"requiredField":false}],"successScreenOptions":{"enableSuccessScreen":true,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"b24ec926-7f66-4c75-b5ea-40bde34b03a2"},"successMessage":"Du er nå ferdig med spørreskjemaet."},"uiElements":{"buttonLabels":{"prevLabel":"Forrige","continueLabel":"Fortsett","nextLabel":"Neste","submitLabel":"Send inn"},"accessibility":{"requiredTextExitLabel":"Lukk feilmelding","progressBarText":"Spørsmål %current av %max"},"requiredMessage":"Dette spørsmålet krever et svar","requiredText":"nødvendig","submitScreenTitle":"Du er nå ferdig med å svare på spørsmålene.","submitScreenSubtitle":"Trykk under for å sende inn dine svar"}},"subContentId":"3567da7c-ba46-40e0-9c9b-d332d9970a7a"},"pause":false,"displayType":"button","buttonOnMobile":false,"label":""},{"x":55.323590814196244,"y":76.06679035250464,"width":10,"height":10,"duration":{"from":79,"to":89},"libraryTitle":"Multiple Choice","action":{"library":"H5P.MultiChoice 1.10","params":{"media":{"params":{}},"answers":[{"correct":true,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>Ibanez</div>\n"},{"correct":true,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>Fender</div>\n"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>Stentor</div>\n"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>ChickenWings</div>\n"}],"overallFeedback":[{"from":0,"to":100}],"UI":{"checkAnswerButton":"Check","showSolutionButton":"Show solution","tryAgainButton":"Retry","tipsLabel":"Show tip","scoreBarLabel":"Score","tipAvailable":"Tip available","feedbackAvailable":"Feedback available","readFeedback":"Read feedback","wrongAnswer":"Wrong answer","correctAnswer":"Correct answer","shouldCheck":"Should have been checked","shouldNotCheck":"Should not have been checked","noInput":"Please answer before viewing the solution"},"behaviour":{"enableRetry":true,"enableSolutionsButton":true,"type":"auto","singlePoint":false,"randomAnswers":true,"showSolutionsRequiresInput":true,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"autoCheck":false,"passPercentage":100,"showScorePoints":true},"confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Avbryt","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"question":"<p>Guitar brands</p>\n"},"subContentId":"efaf0755-d016-49b6-adcf-b4b4b11b754a"},"pause":false,"displayType":"button","buttonOnMobile":false,"adaptivity":{"correct":{"allowOptOut":false,"message":""},"wrong":{"allowOptOut":false,"message":""},"requireCompletion":false},"label":""}],"bookmarks":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Velg riktig påstand.","summaries":[{"subContentId":"35ef94f1-e57f-4647-a53e-071c68924d32","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Løst:","scoreLabel":"Antall feil:","resultLabel":"Ditt resultat:","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers."},"subContentId":"d13ad6ba-656c-4742-b8b8-1a3754a70bcd"},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaksjon","play":"Spill av","pause":"Pause","mute":"Lyd av","unmute":"Lyd på","quality":"Videokvalitet","captions":"Captions","close":"Lukk","fullscreen":"Fullskjerm","exitFullscreen":"Avslutt fullskjerm","summary":"Oppsummering","bookmarks":"Bokmerker","defaultAdaptivitySeekLabel":"Fortsett","continueWithVideo":"Fortsett video","playbackRate":"Avspillingshastighet","rewind10":"Spol tilbake 10 sekund","navDisabled":"Navigasjon er ikke tillatt","sndDisabled":"Sound is disabled","requiresCompletionWarning":"Du må svare korrekt på alle spørsmålene før du kan fortsette.","back":"Forrige","hours":"Timer","minutes":"Minutter","seconds":"Sekunder","currentTime":"Current time:","totalTime":"Full tid:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused"}}',
            'filtered' => '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interaktiv video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}],"files":[{"path":"https://www.youtube.com/watch?v=yw6kT7jvlx0","mime":"video/YouTube","copyright":{"license":"U"}}]},"assets":{"interactions":[{"x":82.4634655532359,"y":38.961038961038966,"width":10,"height":10,"duration":{"from":52,"to":62},"libraryTitle":"Mark the Words","action":{"library":"H5P.MarkTheWords 1.7","params":{"overallFeedback":[{"from":0,"to":100}],"checkAnswerButton":"Sjekk","tryAgainButton":"Prøv igjen","showSolutionButton":"Fasit","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"showScorePoints":true},"correctAnswer":"Riktig!","incorrectAnswer":"Feil!","missedAnswer":"Mangler!","displaySolutionDescription":"Oppgaven er oppdatert til å inneholde fasiten.","taskDescription":"<p>Klikk på bandene.</p>\n","textField":"<p>Last *kiss* is not a song by the *eagles*. And *queen* as not done it either.</p>\n"},"subContentId":"db680b64-1427-46b3-a18b-f90a7d4f005c"},"pause":false,"displayType":"button","buttonOnMobile":false,"adaptivity":{"correct":{"allowOptOut":false,"message":""},"wrong":{"allowOptOut":false,"message":""},"requireCompletion":false},"label":""},{"x":14.613778705636744,"y":27.82931354359926,"width":10,"height":10,"duration":{"from":24,"to":34},"libraryTitle":"Questionnaire","action":{"library":"H5P.Questionnaire 1.2","params":{"questionnaireElements":[{"library":{"params":{"placeholderText":"Skriver her...","inputRows":"1","question":"Hvorfor Kiss eller AC/DC?"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"197c884f-f78b-4a4a-9339-6552eb3037ac"},"requiredField":false}],"successScreenOptions":{"enableSuccessScreen":true,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"b24ec926-7f66-4c75-b5ea-40bde34b03a2"},"successMessage":"Du er nå ferdig med spørreskjemaet."},"uiElements":{"buttonLabels":{"prevLabel":"Forrige","continueLabel":"Fortsett","nextLabel":"Neste","submitLabel":"Send inn"},"accessibility":{"requiredTextExitLabel":"Lukk feilmelding","progressBarText":"Spørsmål %current av %max"},"requiredMessage":"Dette spørsmålet krever et svar","requiredText":"nødvendig","submitScreenTitle":"Du er nå ferdig med å svare på spørsmålene.","submitScreenSubtitle":"Trykk under for å sende inn dine svar"}},"subContentId":"3567da7c-ba46-40e0-9c9b-d332d9970a7a"},"pause":false,"displayType":"button","buttonOnMobile":false,"label":""},{"x":55.323590814196244,"y":76.06679035250464,"width":10,"height":10,"duration":{"from":79,"to":89},"libraryTitle":"Multiple Choice","action":{"library":"H5P.MultiChoice 1.10","params":{"media":{"params":{}},"answers":[{"correct":true,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>Ibanez</div>\n"},{"correct":true,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>Fender</div>\n"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>Stentor</div>\n"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>ChickenWings</div>\n"}],"overallFeedback":[{"from":0,"to":100}],"UI":{"checkAnswerButton":"Check","showSolutionButton":"Show solution","tryAgainButton":"Retry","tipsLabel":"Show tip","scoreBarLabel":"Score","tipAvailable":"Tip available","feedbackAvailable":"Feedback available","readFeedback":"Read feedback","wrongAnswer":"Wrong answer","correctAnswer":"Correct answer","shouldCheck":"Should have been checked","shouldNotCheck":"Should not have been checked","noInput":"Please answer before viewing the solution"},"behaviour":{"enableRetry":true,"enableSolutionsButton":true,"type":"auto","singlePoint":false,"randomAnswers":true,"showSolutionsRequiresInput":true,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"autoCheck":false,"passPercentage":100,"showScorePoints":true},"confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Avbryt","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"question":"<p>Guitar brands</p>\n"},"subContentId":"efaf0755-d016-49b6-adcf-b4b4b11b754a"},"pause":false,"displayType":"button","buttonOnMobile":false,"adaptivity":{"correct":{"allowOptOut":false,"message":""},"wrong":{"allowOptOut":false,"message":""},"requireCompletion":false},"label":""}],"bookmarks":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Velg riktig påstand.","summaries":[{"subContentId":"35ef94f1-e57f-4647-a53e-071c68924d32","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Løst:","scoreLabel":"Antall feil:","resultLabel":"Ditt resultat:","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers."},"subContentId":"d13ad6ba-656c-4742-b8b8-1a3754a70bcd"},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaksjon","play":"Spill av","pause":"Pause","mute":"Lyd av","unmute":"Lyd på","quality":"Videokvalitet","captions":"Captions","close":"Lukk","fullscreen":"Fullskjerm","exitFullscreen":"Avslutt fullskjerm","summary":"Oppsummering","bookmarks":"Bokmerker","defaultAdaptivitySeekLabel":"Fortsett","continueWithVideo":"Fortsett video","playbackRate":"Avspillingshastighet","rewind10":"Spol tilbake 10 sekund","navDisabled":"Navigasjon er ikke tillatt","sndDisabled":"Sound is disabled","requiresCompletionWarning":"Du må svare korrekt på alle spørsmålene før du kan fortsette.","back":"Forrige","hours":"Timer","minutes":"Minutter","seconds":"Sekunder","currentTime":"Current time:","totalTime":"Full tid:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused"}}',
            'library_id' => 202,
        ]);

        factory(H5PContentsUserData::class)->create([
            'data' => json_encode($answers),
            'context' => $context,
            'user_id' => $userId,
            'content_id' => $content->id,
        ]);

        $h5pPackage = new H5PPackage();
        $questionsAndAnswers = $h5pPackage->questionsAndAnswers([$context], $userId);
        $this->assertIsArray($questionsAndAnswers);
        $this->assertCount(1, $questionsAndAnswers);
        $components = $questionsAndAnswers[0];
        $this->assertCount(2, $components['elements']);
        $this->assertEquals($context, $components['context']);
        $interactiveVideoElements = $components['elements'];
        $this->assertTrue($interactiveVideoElements['composedComponent']);
        $this->assertIsArray($interactiveVideoElements['elements']);
        $this->assertCount(1, $interactiveVideoElements['elements']);
        $questionnaireElements = $interactiveVideoElements['elements'][1];
        $this->assertIsArray($questionnaireElements['elements']);
        $this->assertTrue($questionnaireElements['composedComponent']);
        $this->assertCount(1, $questionnaireElements['elements']);
        $this->assertEquals("Hvorfor Kiss eller AC/DC?", $questionnaireElements['elements'][0]['question']);
        $this->assertEquals("Fordi de spiller god rock!", $questionnaireElements['elements'][0]['answer']);
    }
}
