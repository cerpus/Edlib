<?php

namespace App\Http\Libraries\ContentTypes;

use App\LibraryDescription;
use App\H5PLibrary;
use H5peditor;
use Illuminate\Support\Facades\Lang;

class H5PContentType implements ContentTypeInterface
{
    public function getContentTypes($redirectToken)
    {
        $locale = Lang::getLocale();
        $contentTypes = [];
        /** @var H5peditor $editor */
        $editor = resolve(H5peditor::class);

        $libraries = $editor->getLibraries();
        if (is_string($libraries)) {
            $libraries = json_decode($libraries);
        }
        foreach ($libraries as $library) {
            if (config('h5p.developmentMode') !== true || !empty($library->id)) {
                $realLibrary = H5PLibrary::find($library->id);
                if (empty($realLibrary->capability) || $realLibrary->capability->enabled) {
                    if (property_exists($library, 'isOld') === false || $library->isOld !== true) {
                        $title = LibraryDescription::getTranslatedName($realLibrary->id, $locale);
                        $contentTypes[] = new ContentType(
                            $title,
                            route("create.h5pContenttype", [
                                'contenttype' => rawurlencode($library->uberName),
                                'redirectToken' => $redirectToken,
                            ]),
                            $library->uberName,
                            (!empty($realLibrary->description) ? $realLibrary->description->description : ''),
                            $this->getH5PIcon($library->name)
                        );
                    }
                }
            } else {
                $contentTypes[] = new ContentType(
                    $library->title . ' (DEV)',
                    route("create.h5pContenttype", [
                        'contenttype' => rawurlencode($library->uberName),
                        'redirectToken' => $redirectToken,
                    ]),
                    $library->uberName,
                    null,
                    $this->getH5PIcon($library->name)
                );
            }
        }

        return $contentTypes;
    }

    private function getH5PIcon($h5pType)
    {
        return match (strtolower($h5pType)) {
            '' => 'fa fa-exclamation',
            'h5p.accordion' => 'h5p-icon-Accordion',
            'h5p.appearin' => 'h5p-icon-AppearIn',
            'h5p.arithmeticquiz' => 'h5p-icon-ArithmeticQuiz',
            'h5p.audio' => 'fa fa-volume-up',
            'h5p.boardgame' => 'h5p-icon-BoardGame',
            'h5p.chart' => 'h5p-icon-Chart',
            'h5p.collage' => 'h5p-icon-Collage',
            'h5p.coursepresentation' => 'h5p-icon-CoursePresentation',
            'h5p.dialogcards' => 'h5p-icon-DialogueCards',
            'h5p.documentationtool' => 'h5p-icon-DocumentationTool',
            'h5p.dragndrop' => 'h5p-icon-DragandDrop',
            'h5p.dragquestion' => 'h5p-icon-DragandDrop',
            'h5p.dragtext' => 'h5p-icon-DragtheWords',
            'h5p.facebookpagefeed' => 'h5p-icon-FacebookFeed',
            'h5p.blanks' => 'h5p-icon-FillintheBlanks',
            'h5p.impresspresentation' => 'h5p-icon-ImpressivePresentation',
            'h5p.imagehotspotquestion' => 'h5p-icon-FindtheHotspot',
            'h5p.flashcards' => 'h5p-icon-Flashcards',
            'h5p.greetingcard' => 'fa fa-space-shuttle',
            'h5p.guesstheanswer' => 'h5p-icon-GuesstheAnswer',
            'h5p.iframeembed' => 'h5p-icon-IframeEmbedder',
            'h5p.imagehotspots' => 'h5p-icon-ImageHotspots',
            'h5p.interactivevideo' => 'h5p-icon-InteractiveVideo',
            'h5p.markthewords' => 'h5p-icon-MarktheWords',
            'h5p.memorygame' => 'h5p-icon-MemoryGame',
            'h5p.multichoice' => 'h5p-icon-MultipleChoice',
            'h5p.questionset' => 'h5p-icon-Quiz',
            'h5p.singlechoiceset' => 'h5p-icon-SingleChoice',
            'h5p.summary' => 'h5p-icon-Summary',
            'h5p.timeline' => 'h5p-icon-Timeline',
            'h5p.twitteruserfeed' => 'h5p-icon-TwitterUserFeed',
            'h5p.temparticle' => 'fa fa-file-text-o',
            default => 'fa fa-list',
        };

    }
}
