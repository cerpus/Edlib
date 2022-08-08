<?php

/**
 * Created by PhpStorm.
 * User: tsivert
 * Date: 5/11/18
 * Time: 9:20 AM
 */

namespace App\Libraries\H5P\Packages;


use LogicException;

class Dialogcards extends H5PBase
{
    public static string $machineName = "H5P.Dialogcards";
    public static int $majorVersion = 1;
    public static int $minorVersion = 6;
    protected bool $canExtractAnswers = false;

    public function getPackageSemantics()
    {
        // TODO: Implement getPackageSemantics() method.
    }

    public function populateSemanticsFromData($data)
    {
        // TODO: Implement populateSemanticsFromData() method.
    }

    public function getElements(): array
    {
        // TODO: Implement getElements() method.
        throw new LogicException('This method is not implemented');
    }

    public function getAnswers($index = null)
    {
        // TODO: Implement getAnswers() method.
    }

    public function getPackageAnswers($data)
    {
        // TODO: Implement getPackageAnswers() method.
    }

    public function alterSemantics(&$semantics)
    {
        if (config('h5p.H5P_Dialogcards.useRichText') === true) {
            $this->addRichTextEditor($semantics);
        }
    }

    private function addRichTextEditor(&$semantics)
    {
        $text = $semantics[2]->field->fields[0];
        $answer = $semantics[2]->field->fields[1];

        // Remove maxlength
        unset($text->maxLength, $answer->maxLength);
        // Add wysiwyg - support
        $answer->widget = $text->widget = 'html';
    }
}
