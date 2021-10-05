<?php


namespace App\Libraries\NDLA\Importers\Handlers\Helpers;


trait ClassNames
{
    protected function iframeClassNames(\DOMElement $embedNode)
    {
        $classNames = ['edlib_resource'];
        $size = str_replace('fullbredde', 'full', $embedNode->getAttribute('data-size'));
        $align = $embedNode->getAttribute('data-align');
        if (!empty($align)) {
            if (!empty($size)) {
                $classNames[] = sprintf('u-float-%s-%s', $size, $align);
            } else {
                $classNames[] = sprintf('u-float-%s', $align);
            }
        }

        return $classNames;
    }
}
