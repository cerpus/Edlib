<?php

namespace App\Libraries\H5P\Packages;

use LogicException;

class Video extends H5PBase
{
    public static string $machineName = "H5P.Video";
    public static int $majorVersion = 1;
    public static int $minorVersion = 3;

    public function getPackageSemantics()
    {
        return json_decode('{"visuals":{"fit":true,"controls":true},"playback":{"autoplay":false,"loop":false},"l10n":{"name":"Video","loading":"Video player loading...","noPlayers":"Found no video players that supports the given video format.","noSources":"Video is missing sources.","aborted":"Media playback has been aborted.","networkFailure":"Network failure.","cannotDecode":"Unable to decode media.","formatNotSupported":"Video format not supported.","mediaEncrypted":"Media encrypted.","unknownError":"Unknown error.","invalidYtId":"Invalid YouTube ID.","unknownYtId":"Unable to find video with the given YouTube ID.","restrictedYt":"The owner of this video does not allow it to be embedded."},"sources":[]}');
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

    public function alterSource($sourceFile, array $newSource)
    {
        $files = $this->getSources();
        if (empty($files) && !empty($sourceFile) && !empty($newSource)) {
            $files = [(object) ["path" => $sourceFile . "#tmp"]];
        }

        if (empty($files)) {
            return false;
        }

        foreach ($files as $index => $file) {
            if ($file->path === $sourceFile . "#tmp") {
                list($source, $mimetype) = $newSource;
                $files[$index]->path = $source;
                $files[$index]->mime = $mimetype;
            }
        }
        $this->packageStructure->sources = $files;
        return true;
    }

    public function getSources()
    {
        return $this->packageStructure->sources ?? [];
    }

    public function getPackageAnswers($data)
    {
        // TODO: Implement getPackageAnswers() method.
    }
}
