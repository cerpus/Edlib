<?php
/**
 * Created by PhpStorm.
 * User: oddaj
 * Date: 7/11/16
 * Time: 9:39 AM
 */

namespace App\Libraries\NDLA\Importers\Handlers\H5P;

use Illuminate\Support\Facades\Log;

class DownloadFiles
{
    protected $h5p;

    public function handle($params, $h5p)
    {
        $this->h5p = (object)$h5p;
        if (!$this->hasImages($params)) {
            return $params;
        }

        $res = $this->processParams($params);
        return $res;


    }

    private function hasImages($haystack)
    {
        $re = '/(H5P\.Image [0-9]\.[0-9])|("mime":"image\\\\\/.+?")/m';
        return preg_match($re, $haystack);
    }

    private function processParams($params)
    {
        $re = "/(?<=\"path\":\")(.*?)(?=\",)/";
        $matches = [];
        preg_match_all($re, $params, $matches);
        $response = preg_replace_callback($re, function ($m) {
            $url = $m[0];

            if (strstr($url, "api.ndla.no\/image-api")) { // Images from import will be referenced, not downloaded.
                return $url;
            }

            if (!strstr($url, 'ndla.no')) {
                return $url;
            }

            return $this->download($url);
        }, $params);
        return $response;
    }

    private function download($url)
    {
        $fromUrl = $this->makeNiceUrl($url);
        Log::debug('Getting file: ' . $fromUrl);
        $file = file_get_contents($fromUrl);
        if ($file === false) {
            return $url;
        }
        $fileName = strstr($fromUrl, "/files/");
        $fileName = str_replace('/files/', '', $fileName);
        $fileName = str_replace('%20', '_', $fileName);
        $toDirRelative = '/h5pstorage/content/' . $this->h5p->id;
        $toUrl = $toDirRelative . '/' . $fileName;
        $toPath = public_path() . $toUrl;
        $dirPath = dirname($toPath);
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }
        $assetFile = fopen($toPath, 'w');
        if ($assetFile == false) {
            return $url;
        }
        if (fwrite($assetFile, $file) === false) {
            return $url;
        }
        if (fclose($assetFile) === false) {
            return $url;
        }
        return addslashes($fileName);
    }

    private function makeNiceUrl($url)
    {
        $url = stripslashes($url);
        $url = str_replace('red.', 'www.', $url);
        $url = str_replace('https://', 'http://', $url);
        $url = str_replace('http://www.ndla.no', config('ndla.baseUrl'), $url);
        $url = str_replace(' ', '%20', $url);
        $url = str_replace('u00c6', 'Æ', $url); // Æ
        $url = str_replace('u00e6', 'æ', $url); // æ
        $url = str_replace('u00d8', 'Ø', $url); // Ø
        $url = str_replace('u00f8', 'ø', $url); // ø
        $url = str_replace('u00c5', 'Å', $url); // Å
        $url = str_replace('u00e5', 'å', $url); // å

        return $url;
    }

}
