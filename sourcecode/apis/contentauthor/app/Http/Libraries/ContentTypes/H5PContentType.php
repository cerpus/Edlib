<?php

namespace App\Http\Libraries\ContentTypes;

use App\LibraryDescription;
use App\H5PLibrary;
use Illuminate\Support\Facades\Lang;

class H5PContentType implements ContentTypeInterface
{
    public function getContentTypes($redirectToken)
    {
        $locale = Lang::getLocale();
        $contentTypes = [];
        /** @var \H5peditor $editor */
        $editor = resolve(\H5peditor::class);

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
        switch (strtolower($h5pType)) {
            case '':
                return 'fa fa-exclamation';
                break;
            case 'h5p.accordion':
                return 'h5p-icon-Accordion';
                break;
            case 'h5p.appearin':
                return 'h5p-icon-AppearIn';
                break;
            case 'h5p.arithmeticquiz':
                return 'h5p-icon-ArithmeticQuiz';
                break;
            case 'h5p.audio':
                return 'fa fa-volume-up';
                break;
            case 'h5p.boardgame':
                return 'h5p-icon-BoardGame';
                break;
            case 'h5p.chart':
                return 'h5p-icon-Chart';
                break;
            case 'h5p.collage':
                return 'h5p-icon-Collage';
                break;
            case 'h5p.coursepresentation':
                return 'h5p-icon-CoursePresentation';
                break;
            case 'h5p.dialogcards':
                return 'h5p-icon-DialogueCards';
                break;
            case 'h5p.documentationtool':
                return 'h5p-icon-DocumentationTool';
                break;
            case 'h5p.dragndrop':
                return 'h5p-icon-DragandDrop';
                break;
            case 'h5p.dragquestion':
                return 'h5p-icon-DragandDrop';
                break;
            case 'h5p.dragtext':
                return 'h5p-icon-DragtheWords';
                break;
            case 'h5p.facebookpagefeed':
                return 'h5p-icon-FacebookFeed';
                break;
            case 'h5p.blanks':
                return 'h5p-icon-FillintheBlanks';
                break;
            case 'h5p.impresspresentation':
                return 'h5p-icon-ImpressivePresentation';
                break;
            case 'h5p.imagehotspotquestion':
                return 'h5p-icon-FindtheHotspot';
                break;
            case 'h5p.flashcards':
                return 'h5p-icon-Flashcards';
                break;
            case 'h5p.greetingcard':
                return 'fa fa-space-shuttle';
                break;
            case 'h5p.guesstheanswer':
                return 'h5p-icon-GuesstheAnswer';
                break;
            case 'h5p.iframeembed':
                return 'h5p-icon-IframeEmbedder';
                break;
            case 'h5p.imagehotspots':
                return 'h5p-icon-ImageHotspots';
                break;
            case 'h5p.interactivevideo':
                return 'h5p-icon-InteractiveVideo';
                break;
            case 'h5p.markthewords':
                return 'h5p-icon-MarktheWords';
                break;
            case 'h5p.memorygame':
                return 'h5p-icon-MemoryGame';
                break;
            case 'h5p.multichoice':
                return 'h5p-icon-MultipleChoice';
                break;
            case 'h5p.questionset':
                return 'h5p-icon-Quiz';
                break;
            case 'h5p.singlechoiceset':
                return 'h5p-icon-SingleChoice';
                break;
            case 'h5p.summary':
                return 'h5p-icon-Summary';
                break;
            case 'h5p.timeline':
                return 'h5p-icon-Timeline';
                break;
            case 'h5p.twitteruserfeed':
                return 'h5p-icon-TwitterUserFeed';
                break;
            case 'h5p.temparticle':
                return 'fa fa-file-text-o';
                break;
        }

        return 'fa fa-list';
    }
}