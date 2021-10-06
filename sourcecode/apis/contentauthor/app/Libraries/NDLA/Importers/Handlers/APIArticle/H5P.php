<?php

namespace App\Libraries\NDLA\Importers\Handlers\APIArticle;

use Cache;
use App\Article;
use Carbon\Carbon;
use App\NdlaIdMapper;
use Cerpus\Helper\Clients\Client;
use Cerpus\Helper\DataObjects\OauthSetup;
use GuzzleHttp\Exception\ServerException;
use App\Libraries\NDLA\API\H5PIdMapperApi;
use App\Libraries\NDLA\Importers\ImporterInterface;
use App\Http\Controllers\Admin\NDLAMetadataImportController;


class H5P extends BaseHandler
{
    protected $client;

    public function process(Article $article, $jsonArticle): Article
    {
        $this->article = $article;
        $this->jsonArticle = $jsonArticle;

        $this->debug("Processing H5Ps");

        $this->client = Client::getClient(OauthSetup::create(['coreUrl' => config('ndla.baseUrl')]));

        $document = $this->getDom();

        $embedNodes = $document->getElementsByTagName('embed');
        $processedH5PCount = 0;
        foreach ($embedNodes as $node) {
            if ($this->isH5PEmbeddedNode($node)) {
                if ($h5pId = $this->getH5PId($node->getAttribute('data-url'))) {
                    // PS! getEdLibUrlFromH5PId will try to import missing H5Ps!
                    if ($edLibH5PUrl = $this->getEdLibUrlFromH5PId($h5pId)) {
                        $h5pIframe = $document->createElement('iframe');
                        $h5pIframe->setAttribute('src', $edLibH5PUrl);
                        $h5pIframe->setAttribute('class', 'oerlearningorg_resource');
                        $h5pIframe->setAttribute('allow', 'fullscreen');
                        $h5pIframe->setAttribute('allowfullscreen', 'allowfullscreen');
                        $h5pIframe->setAttribute('frameborder', '0');
                        $node->parentNode->insertBefore($h5pIframe, $node);
                        $processedH5PCount++;
                    }
                }
            }
        }

        if($processedH5PCount > 0){
            $this->saveContent($document);
        }

        $this->debug('H5P: Inserted ' . $processedH5PCount . ' H5Ps.');

        return $this->article;
    }

    private function getH5PJSON($nodeId)
    {
        $cacheKey = "getH5PJson|" . $nodeId;
        $cacheTime = Carbon::now()->addMinutes(10);
        $result = null;

        if (!$result = Cache::get($cacheKey)) {
            try {
                $url = sprintf(NDLAMetadataImportController::H5PExport, $nodeId);
                $response = $this->client->get($url);
                $result = \GuzzleHttp\json_decode($response->getBody());
                Cache::put($cacheKey, $result, $cacheTime);
            } catch (\Throwable $t) {

            }
        }
        return $result;
    }

    protected function getEdLibUrlFromH5PId($h5pId)
    {
        $edLibUrl = false;

        try {
            $h5pMapperApi = new H5PIdMapperApi;
            if ($ndlaId = $h5pMapperApi->fetchOldH5PNodeId($h5pId)) {
                if (!$idMap = NdlaIdMapper::h5pByNdlaId($ndlaId)) {
                    $startTime = microtime(true);
                    $import = app(ImporterInterface::class);
                    $jsonStructure = $this->getH5PJSON($ndlaId);
                    $this->debug("Importing H5P($ndlaId) on demand.");
                    $importResult = $import->setImportId($this->importId)->import($jsonStructure);
                    $endTime = sprintf("%0.2f", microtime(true) - $startTime);

                    if ($importResult->status ?? null === 201) {
                        $this->debug("Imported H5P in $endTime seconds.");
                    } else {
                        $this->error("Failed H5P import after $endTime seconds.");
                    }
                }

                // Try one last time, the H5P should be imported now.
                if ($idMap = NdlaIdMapper::h5pByNdlaId($ndlaId)) {
                    if ($idMap->launch_url) {
                        $edLibUrl = $idMap->getOerLink();
                    }
                }
            }
        } catch (ServerException $e) {
            $this->error("H5P: failed mapping resource $h5pId: ({$e->getCode()}) {$e->getMessage()}");
            $this->error($e->getTraceAsString());
        }

        return $edLibUrl;
    }

    protected function getH5PId($uri)
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $parts = explode('/', $path);
        $h5pId = false;
        if (is_array($parts)) {
            $h5pId = last($parts);
        }

        return $h5pId;
    }

    protected function getH5PResourceId($node)
    {
        $nodeUri = $node->getAttribute('data-url');
        $path = parse_url($nodeUri, PHP_URL_PATH);
        $parts = explode('/', $path);
        $resourceId = false;
        if (is_array($parts)) {
            $resourceId = last($parts);
        }

        return $resourceId;
    }

    protected function isH5PEmbeddedNode($node)
    {
        $isExternal = $this->isExternal($node);
        $linksToNdla = $this->linksToNdla($node);
        $hasAResourceId = !empty($this->getH5PResourceId($node));

        return $isExternal && $linksToNdla && $hasAResourceId;
    }

    protected function getH5PResourceUrl($node)
    {
        $resourceUri = false;
        if ($this->isH5PEmbeddedNode($node)) {
            $resourceUri = $node->getAttribute('data-url');
        }

        return $resourceUri;
    }

    protected function linksToNdla($node)
    {
        $linksToNdla = false;
        $nodeUri = $node->getAttribute('data-url');
        $host = parse_url($nodeUri, PHP_URL_HOST);

        if ($host) {
            $linksToNdla = (mb_strtolower($host) === "h5p.ndla.no");
        }

        return $linksToNdla;
    }

    protected function isExternal($node): bool
    {
        return (mb_strtolower($node->getAttribute('data-resource')) === "external");
    }
}
