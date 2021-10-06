<?php


namespace App\Http\Controllers\Admin;

use App\Libraries\NDLA\Importers\ImporterInterface;
use Exception;
use App\Http\Controllers\Controller;
use App\Libraries\Storage\LogStorage;
use App\NdlaIdMapper;
use Carbon\Carbon;
use Cerpus\Helper\Clients\Client;
use Cerpus\Helper\DataObjects\OauthSetup;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NDLAMetadataImportController extends Controller
{
    const H5PExport = '/node/%s/export';
    const chunkSize = 30;
    const sessionKey = 'NDLAMetadataImport';
    const logFile = 'metadataMigration.log';
    const METADATA_INPROGRESS = 1;
    const METADATA_FAIL = 2;
    const METADATA_NO_DATA = 4;
    const METADATA_DATA_SET = 8;

    private $log;

    public function __construct(LogStorage $logStorage)
    {
        $this->log = $logStorage::disk();
    }

    public function index()
    {
        $numMissingMetadata = NdlaIdMapper::withH5PMetadata()->count();

        return view('admin.metadata.index', [
            'numMissingMetadata' => $numMissingMetadata,
            'updateRoute' => route('admin.metadata.migrate'),
            'numRowsToTraverse' => self::chunkSize,
            'downloadLink' => route('admin.metadata.download')
        ]);

    }

    public function migrate(Request $request)
    {
        /** @var \GuzzleHttp\Client $client */
        $client = Client::getClient(OauthSetup::create(['coreUrl' => config('ndla.baseUrl')]));

        $builder = NdlaIdMapper::withH5PMetadata();
        $idList = $request->input('idList');
        $builder->when($idList, function ($query) use ($idList) {
            /** @var Builder $query */
            $list = collect(explode(',', $idList))
                ->filter(function ($id) {
                    return filter_var(trim($id), FILTER_SANITIZE_NUMBER_INT);
                });
            $query->whereIn('ndla_id', $list->toArray());
        });

        $batch = collect();
        $requests = function ($targets) use ($client) {
            /** @var Collection $targets */
            $currentTargets = $targets->toArray();
            foreach ($currentTargets as $currentTarget) {
                $url = sprintf(self::H5PExport, $currentTarget['ndla_id']);
                yield function () use ($client, $url) {
                    return $client->getAsync($url);
                };
            }
        };

        $targets = $builder->take(self::chunkSize)->get();
        $targets->each(function ($target){
            /** @var NdlaIdMapper $target */
            $target->metadata_fetch = self::METADATA_INPROGRESS;
            $target->save();
        });
        $pool = new Pool($client, $requests($targets), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) use (&$batch, $targets) {
                /** @var Response $response */
                $currentTarget = $targets->get($index);
                $log = [
                    'id' => $currentTarget->id,
                    'ndla_id' => $currentTarget->ndla_id,
                    'ca_id' => $currentTarget->ca_id,
                    'success' => true,
                ];
                try {
                    $contents = \GuzzleHttp\json_decode($response->getBody()->getContents());
                    if (empty($contents->nodeId)) {
                        throw new Exception("Response not as expected. Aborting...", 800);
                    }
                    $h5pImport = resolve(ImporterInterface::class);
                    $h5pImport->addMetadata($contents, $currentTarget->ca_id);
                    $currentTarget->metadata_fetch = self::METADATA_DATA_SET;

                    $batch->push($log);
                    unset($log['success']);
                    $this->log("SUCCESS", $log);
                } catch (Exception $exception) {
                    $currentTarget->metadata_fetch = self::METADATA_FAIL;
                    $log['success'] = false;
                    $log['errorMessage'] = $exception->getMessage();
                    $log['errorCode'] = $exception->getCode();
                    $batch->push($log);
                    unset($log['success']);
                    $this->log("ERROR", $log);
                } finally {
                    $currentTarget->save();
                }
            },
            'rejected' => function ($reason, $index) use (&$batch, $targets) {
                $currentTarget = $targets->get($index);
                $currentTarget->metadata_fetch = self::METADATA_FAIL;
                $currentTarget->save();
                /** @var Exception $reason */
                $batch->push([
                    'id' => $currentTarget->id,
                    'ndla_id' => $currentTarget->ndla_id,
                    'ca_id' => $currentTarget->ca_id,
                    'success' => false,
                    'errorMessage' => $reason->getMessage(),
                    'errorCode' => $reason->getCode(),
                ]);

                $this->log("ERROR", [
                    'id' => $currentTarget->id,
                    'ndla_id' => $currentTarget->ndla_id,
                    'ca_id' => $currentTarget->ca_id,
                    'code' => $reason->getCode(),
                    'message' => $reason->getMessage(),
                ]);
            }
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        return response()->json([
            'outstanding' => $builder->count(),
            'batch' => $batch,
        ]);
    }

    private function log($message, $context = null)
    {
        $lines = [
            '[' . Carbon::now()->format('Y-m-d H:i:s') . ']',
            $message,
        ];

        if (is_scalar($context)) {
            $lines[] = (string)$context;
        } else {
            if (is_array($context) && count($context) > 0) {
                $lines[] = json_encode($context);
            }
        }

        $this->log->append(self::logFile, implode(" ", $lines));
    }

    public function download()
    {
        $file = $this->log->path(self::logFile);
        return response()->download($file, self::logFile);
    }
}