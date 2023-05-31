<?php

namespace App\Libraries\H5P\Helper;

use App\Libraries\H5P\Packages\Video;
use App\Libraries\H5P\Packages\Column;
use App\Libraries\H5P\Packages\DragText;
use App\Libraries\H5P\Packages\ImagePair;
use App\Libraries\H5P\Packages\MemoryGame;
use App\Libraries\H5P\Packages\MultiChoice;
use App\Libraries\H5P\Packages\QuestionSet;
use App\Libraries\H5P\Packages\DragQuestion;
use App\Libraries\H5P\Packages\Questionnaire;
use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Packages\InteractiveVideo;
use App\Libraries\H5P\Packages\OpenEndedQuestion;
use App\Libraries\H5P\Packages\SimpleMultiChoice;
use App\Libraries\H5P\Interfaces\PackageInterface;
use App\Libraries\H5P\Packages\CoursePresentation;

class H5PPackageProvider
{
    /**
     * @param string $structure
     * @return PackageInterface
     * @throws UnknownH5PPackageException
     */
    public static function make($machineName, $structure = '')
    {
        switch (self::getMachineName($machineName)) {
            case OpenEndedQuestion::$machineName:
                $className = OpenEndedQuestion::class;
                break;
            case SimpleMultiChoice::$machineName:
                $className = SimpleMultiChoice::class;
                break;
            case Questionnaire::$machineName:
                $className = Questionnaire::class;
                break;
            case InteractiveVideo::$machineName:
                $className = InteractiveVideo::class;
                break;
            case QuestionSet::$machineName:
                $className = QuestionSet::class;
                break;
            case MultiChoice::$machineName:
                $className = MultiChoice::class;
                break;
            case DragQuestion::$machineName:
                $className = DragQuestion::class;
                break;
            case Video::$machineName:
                $className = Video::class;
                break;
            case CoursePresentation::$machineName:
                $className = CoursePresentation::class;
                break;
            case Column::$machineName:
                $className = Column::class;
                break;
            case MemoryGame::$machineName:
                $className = MemoryGame::class;
                break;
            case DragText::$machineName:
                $className = DragText::class;
                break;
            case ImagePair::$machineName:
                $className = ImagePair::class;
                break;
            default:
                $message = sprintf("Missing adapter for %s", $machineName);

                throw new UnknownH5PPackageException($message);
                break;
        }

        if (!is_string($structure)) {
            $structure = json_encode($structure);
        }
        return resolve($className, ["packageStructure" => $structure]);
    }

    public static function getMachineName($machineName)
    {
        $split = explode(" ", $machineName);
        return array_shift($split);
    }
}
