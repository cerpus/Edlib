<?php

namespace Tests\Integration\Libraries\H5P\Package;

use App\Libraries\H5P\Packages\InteractiveVideo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InteractiveVideoTest extends TestCase
{
    private static array $elements = [
        'interactiveWithOneQuestionnaireAndTwoOthers' => '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interaktiv video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}],"files":[{"path":"https://www.youtube.com/watch?v=yw6kT7jvlx0","mime":"video/YouTube","copyright":{"license":"U"}}]},"assets":{"interactions":[{"x":14.613778705636744,"y":27.82931354359926,"width":10,"height":10,"duration":{"from":24,"to":34},"libraryTitle":"Questionnaire","action":{"library":"H5P.Questionnaire 1.2","params":{"questionnaireElements":[{"library":{"params":{"placeholderText":"Skriver her...","inputRows":"1","question":"Hvorfor Kiss eller AC/DC?"},"library":"H5P.OpenEndedQuestion 1.0","subContentId":"197c884f-f78b-4a4a-9339-6552eb3037ac"},"requiredField":false}],"successScreenOptions":{"enableSuccessScreen":true,"successScreenImage":{"params":{"contentName":"Image"},"library":"H5P.Image 1.0","subContentId":"b24ec926-7f66-4c75-b5ea-40bde34b03a2"},"successMessage":"Du er nå ferdig med spørreskjemaet."},"uiElements":{"buttonLabels":{"prevLabel":"Forrige","continueLabel":"Fortsett","nextLabel":"Neste","submitLabel":"Send inn"},"accessibility":{"requiredTextExitLabel":"Lukk feilmelding","progressBarText":"Spørsmål %current av %max"},"requiredMessage":"Dette spørsmålet krever et svar","requiredText":"nødvendig","submitScreenTitle":"Du er nå ferdig med å svare på spørsmålene.","submitScreenSubtitle":"Trykk under for å sende inn dine svar"}},"subContentId":"3567da7c-ba46-40e0-9c9b-d332d9970a7a"},"pause":false,"displayType":"button","buttonOnMobile":false,"label":""},{"x":82.4634655532359,"y":38.961038961038966,"width":10,"height":10,"duration":{"from":52,"to":62},"libraryTitle":"Mark the Words","action":{"library":"H5P.MarkTheWords 1.7","params":{"overallFeedback":[{"from":0,"to":100}],"checkAnswerButton":"Sjekk","tryAgainButton":"Prøv igjen","showSolutionButton":"Fasit","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"showScorePoints":true},"correctAnswer":"Riktig!","incorrectAnswer":"Feil!","missedAnswer":"Mangler!","displaySolutionDescription":"Oppgaven er oppdatert til å inneholde fasiten.","taskDescription":"<p>Klikk på bandene.</p>\n","textField":"<p>Last *kiss* is not a song by the *eagles*. And *queen* as not done it either.</p>\n"},"subContentId":"db680b64-1427-46b3-a18b-f90a7d4f005c"},"pause":false,"displayType":"button","buttonOnMobile":false,"adaptivity":{"correct":{"allowOptOut":false,"message":""},"wrong":{"allowOptOut":false,"message":""},"requireCompletion":false},"label":""},{"x":55.323590814196244,"y":76.06679035250464,"width":10,"height":10,"duration":{"from":79,"to":89},"libraryTitle":"Multiple Choice","action":{"library":"H5P.MultiChoice 1.10","params":{"media":{"params":{}},"answers":[{"correct":true,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>Ibanez</div>\n"},{"correct":true,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>Fender</div>\n"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>Stentor</div>\n"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<div>ChickenWings</div>\n"}],"overallFeedback":[{"from":0,"to":100}],"UI":{"checkAnswerButton":"Check","showSolutionButton":"Show solution","tryAgainButton":"Retry","tipsLabel":"Show tip","scoreBarLabel":"Score","tipAvailable":"Tip available","feedbackAvailable":"Feedback available","readFeedback":"Read feedback","wrongAnswer":"Wrong answer","correctAnswer":"Correct answer","shouldCheck":"Should have been checked","shouldNotCheck":"Should not have been checked","noInput":"Please answer before viewing the solution"},"behaviour":{"enableRetry":true,"enableSolutionsButton":true,"type":"auto","singlePoint":false,"randomAnswers":true,"showSolutionsRequiresInput":true,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"autoCheck":false,"passPercentage":100,"showScorePoints":true},"confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Avbryt","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"question":"<p>Guitar brands</p>\n"},"subContentId":"efaf0755-d016-49b6-adcf-b4b4b11b754a"},"pause":false,"displayType":"button","buttonOnMobile":false,"adaptivity":{"correct":{"allowOptOut":false,"message":""},"wrong":{"allowOptOut":false,"message":""},"requireCompletion":false},"label":""}],"bookmarks":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Velg riktig påstand.","summaries":[{"subContentId":"35ef94f1-e57f-4647-a53e-071c68924d32","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Løst:","scoreLabel":"Antall feil:","resultLabel":"Ditt resultat:","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers."},"subContentId":"d13ad6ba-656c-4742-b8b8-1a3754a70bcd"},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaksjon","play":"Spill av","pause":"Pause","mute":"Lyd av","unmute":"Lyd på","quality":"Videokvalitet","captions":"Captions","close":"Lukk","fullscreen":"Fullskjerm","exitFullscreen":"Avslutt fullskjerm","summary":"Oppsummering","bookmarks":"Bokmerker","defaultAdaptivitySeekLabel":"Fortsett","continueWithVideo":"Fortsett video","playbackRate":"Avspillingshastighet","rewind10":"Spol tilbake 10 sekund","navDisabled":"Navigasjon er ikke tillatt","sndDisabled":"Sound is disabled","requiresCompletionWarning":"Du må svare korrekt på alle spørsmålene før du kan fortsette.","back":"Forrige","hours":"Timer","minutes":"Minutter","seconds":"Sekunder","currentTime":"Current time:","totalTime":"Full tid:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused"}}',
        'interactiveWithOneVideo' => '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interactive Video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}],"files":[{"path":"videos/files-5a2f98d3cf06c.mp4#tmp","mime":"video/mp4","copyright":{"license":"U"}}]},"assets":{"interactions":[],"bookmarks":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Choose the correct statement.","summaries":[{"subContentId":"2715e2a9-93cb-467a-a807-82d0623e93aa","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Progress:","scoreLabel":"Wrong answers:","resultLabel":"Your result","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers."},"subContentId":"6d04aa0d-5317-442b-8981-bd8cb4bc195e"},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaction","play":"Play","pause":"Pause","mute":"Mute","unmute":"Unmute","quality":"Video Quality","captions":"Captions","close":"Close","fullscreen":"Fullscreen","exitFullscreen":"Exit Fullscreen","summary":"Summary","bookmarks":"Bookmarks","defaultAdaptivitySeekLabel":"Continue","continueWithVideo":"Continue with video","playbackRate":"Playback Rate","rewind10":"Rewind 10 Seconds","navDisabled":"Navigation is disabled","sndDisabled":"Sound is disabled","requiresCompletionWarning":"You need to answer all the questions correctly before continuing.","back":"Back","hours":"Hours","minutes":"Minutes","seconds":"Seconds","currentTime":"Current time:","totalTime":"Total time:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused"}}',
        'interactiveWithNoVideo' => '{"interactiveVideo":{"video":{"startScreenOptions":{"title":"Interactive Video","hideStartTitle":false,"copyright":""},"textTracks":[{"label":"Subtitles","kind":"subtitles","srcLang":"en"}]},"assets":{"interactions":[],"bookmarks":[]},"summary":{"task":{"library":"H5P.Summary 1.8","params":{"intro":"Choose the correct statement.","summaries":[{"subContentId":"2715e2a9-93cb-467a-a807-82d0623e93aa","tip":""}],"overallFeedback":[{"from":0,"to":100}],"solvedLabel":"Progress:","scoreLabel":"Wrong answers:","resultLabel":"Your result","labelCorrect":"Correct.","labelIncorrect":"Incorrect! Please try again.","labelCorrectAnswers":"Correct answers."},"subContentId":"6d04aa0d-5317-442b-8981-bd8cb4bc195e"},"displayAt":3}},"override":{"autoplay":false,"loop":false,"showBookmarksmenuOnLoad":false,"showRewind10":false,"preventSkipping":false,"deactivateSound":false},"l10n":{"interaction":"Interaction","play":"Play","pause":"Pause","mute":"Mute","unmute":"Unmute","quality":"Video Quality","captions":"Captions","close":"Close","fullscreen":"Fullscreen","exitFullscreen":"Exit Fullscreen","summary":"Summary","bookmarks":"Bookmarks","defaultAdaptivitySeekLabel":"Continue","continueWithVideo":"Continue with video","playbackRate":"Playback Rate","rewind10":"Rewind 10 Seconds","navDisabled":"Navigation is disabled","sndDisabled":"Sound is disabled","requiresCompletionWarning":"You need to answer all the questions correctly before continuing.","back":"Back","hours":"Hours","minutes":"Minutes","seconds":"Seconds","currentTime":"Current time:","totalTime":"Total time:","navigationHotkeyInstructions":"Use key k for starting and stopping video at any time","singleInteractionAnnouncement":"Interaction appeared:","multipleInteractionsAnnouncement":"Multiple interactions appeared.","videoPausedAnnouncement":"Video is paused"}}',
    ];

    #[Test]
    public function validateStructure()
    {
        $interactiveVideo = new InteractiveVideo("InvalidJson");
        $this->assertFalse($interactiveVideo->validate());

        $interactiveVideo = new InteractiveVideo('{}');
        $this->assertFalse($interactiveVideo->validate());

        $interactiveVideo = new InteractiveVideo(json_encode([
            'interactiveVideo' => "NotValid",
        ]));
        $this->assertFalse($interactiveVideo->validate());

        $interactiveVideo = new InteractiveVideo(json_encode([
            'interactiveVideo' => [
                'assets' => [
                    'interactions' => "FOO",
                ],
            ],
        ]));
        $this->assertTrue($interactiveVideo->validate());
    }

    #[Test]
    public function getQuestions()
    {
        $interactiveVideo = new InteractiveVideo(json_encode([
            'interactiveVideo' => [
                'assets' => [
                    'interactions' => null,
                ],
            ],
        ]));
        $this->assertEmpty($interactiveVideo->getElements());

        $interactiveVideo = new InteractiveVideo(self::$elements['interactiveWithOneQuestionnaireAndTwoOthers']);
        $elements = $interactiveVideo->getElements();
        $this->assertNotEmpty($elements);
        $this->assertCount(2, $elements);
        $compontentElements = $elements['elements'];
        $this->assertEquals("Hvorfor Kiss eller AC/DC?", $compontentElements[0]['elements'][0]['question']);
    }

    #[Test]
    public function alterSources()
    {
        $interactiveVideo = new InteractiveVideo(self::$elements['interactiveWithOneVideo']);
        $interactiveVideo->alterSource('videos/files-5a2f98d3cf06c.mp4', [
            'https://example.com/stream',
            'video/streamps',
        ]);
        $existingStructure = json_decode(self::$elements['interactiveWithOneVideo']);
        $existingStructure->interactiveVideo->video->files[0]->path = 'https://example.com/stream';
        $existingStructure->interactiveVideo->video->files[0]->mime = 'video/streamps';
        $structure = $interactiveVideo->getPackageStructure();
        $this->assertJsonStringEqualsJsonString(json_encode($existingStructure), json_encode($structure));
    }

    #[Test]
    public function alterSourcesWithNoVideos()
    {
        $interactiveVideo = new InteractiveVideo(self::$elements['interactiveWithNoVideo']);
        $interactiveVideo->alterSource('videos/files-5a2f98d3cf06c.mp4', [
            'https://example.com/stream',
            'video/streamps',
        ]);
        $existingStructure = json_decode(self::$elements['interactiveWithNoVideo']);
        $existingStructure->interactiveVideo->video->files = [(object) ["path" => 'https://example.com/stream', 'mime' => 'video/streamps']];
        $structure = $interactiveVideo->getPackageStructure();
        $this->assertJsonStringEqualsJsonString(json_encode($existingStructure), json_encode($structure));
    }
}
