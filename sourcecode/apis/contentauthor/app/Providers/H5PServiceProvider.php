<?php

namespace App\Providers;

use App\Console\Libraries\CliH5pFramework;
use App\Libraries\H5P\Adapters\CerpusH5PAdapter;
use App\Libraries\H5P\Adapters\NDLAH5PAdapter;
use App\Libraries\H5P\Audio\NdlaAudioAdapter;
use App\Libraries\H5P\Audio\NdlaAudioClient;
use App\Libraries\H5P\Audio\NullAudioAdapter;
use App\Libraries\H5P\EditorAjax;
use App\Libraries\H5P\EditorStorage;
use App\Libraries\H5P\Framework;
use App\Libraries\H5P\H5PLibraryAdmin;
use App\Libraries\H5P\H5pPresave;
use App\Libraries\H5P\Image\NdlaImageAdapter;
use App\Libraries\H5P\Image\NdlaImageClient;
use App\Libraries\H5P\Image\NullImageAdapter;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PAudioInterface;
use App\Libraries\H5P\Interfaces\H5PImageInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use App\Libraries\H5P\Storage\H5PCerpusStorage;
use App\Libraries\H5P\TranslationServices\NullTranslationAdapter;
use App\Libraries\H5P\TranslationServices\NynorobotAdapter;
use App\Libraries\H5P\TranslationServices\NynorskrobotenAdapter;
use App\Libraries\H5P\Video\NDLAVideoAdapter;
use App\Libraries\H5P\Video\NullVideoAdapter;
use Cerpus\Helper\Clients\Oauth2Client;
use Cerpus\Helper\DataObjects\OauthSetup;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use H5PContentValidator;
use H5PCore;
use H5peditor;
use H5PEditorAjax;
use H5PEditorAjaxInterface;
use H5peditorStorage;
use H5PExport;
use H5PFileStorage;
use H5PFrameworkInterface;
use H5PValidator;
use Illuminate\Contracts\Filesystem\Cloud;
use Illuminate\Foundation\Application as App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class H5PServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {}

    public function provides()
    {
        return [
            H5PFileStorage::class,
            H5PAdapterInterface::class,
            H5PAudioInterface::class,
            H5PImageInterface::class,
            H5PVideoInterface::class,
            H5PLibraryAdmin::class,
            H5peditorStorage::class,
        ];
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app
            ->when(H5pPresave::class)
            ->needs(Cloud::class)
            ->give(fn() => Storage::disk('h5p-presave'));

        $this->app->bind(H5PVideoInterface::class, match (config('h5p.video.adapter')) {
            'ndla' => NDLAVideoAdapter::class,
            default => NullVideoAdapter::class,
        });

        $this->app->when(NDLAVideoAdapter::class)
            ->needs(Client::class)
            ->give(fn() => Oauth2Client::getClient(OauthSetup::create([
                'authUrl' => config('ndla.video.authUrl'),
                'coreUrl' => config('ndla.video.url'),
                'key' => config('ndla.video.key'),
                'secret' => config('ndla.video.secret'),
            ])));

        $this->app->when(NDLAVideoAdapter::class)
            ->needs('$accountId')
            ->giveConfig('ndla.video.accountId');

        $this->app->bind(H5PImageInterface::class, match (config('h5p.image.adapter')) {
            'ndla' => NdlaImageAdapter::class,
            default => NullImageAdapter::class,
        });

        $this->app->when(NdlaImageAdapter::class)
            ->needs('$url')
            ->giveConfig('ndla.image.url');

        $this->app->bind(NdlaImageClient::class, fn() => new NdlaImageClient([
            'base_uri' => config('ndla.image.url'),
        ]));

        $this->app->bind(H5PAudioInterface::class, match (config('h5p.audio.adapter')) {
            'ndla' => NdlaAudioAdapter::class,
            default => NullAudioAdapter::class,
        });

        $this->app->when(NdlaAudioAdapter::class)
            ->needs('$url')
            ->giveConfig('ndla.audio.url');

        $this->app->bind(NdlaAudioClient::class, fn() => new NdlaAudioClient([
            'base_uri' => config('ndla.audio.url'),
        ]));

        $this->app->bind(H5PCerpusStorage::class);
        $this->app->bind(H5PFileStorage::class, H5PCerpusStorage::class);
        $this->app->bind(CerpusStorageInterface::class, H5PCerpusStorage::class);

        $this->app->singletonIf('H5PFilesystem', fn() => Storage::disk());

        $this->app->bind(NynorskrobotenAdapter::class, function () {
            $client = new Client([
                'base_uri' => config('services.nynorskroboten.domain'),
            ]);
            return new NynorskrobotenAdapter($client, config('services.nynorskroboten.token'));
        });

        $this->app->when(NynorobotAdapter::class)
            ->needs(ClientInterface::class)
            ->give(function () {
                return new Client([
                    'base_uri' => config('services.nynorobot.base_uri'),
                    'headers' => [
                        'x-user' => config('services.nynorobot.key'),
                        // Unbelievably, this is a thing we have to do.
                        'x-api-key' => iconv('UTF-8', 'ISO-8859-1', config('services.nynorobot.secret')),
                    ],
                ]);
            });

        $this->app->when(NynorobotAdapter::class)
            ->needs('$style')
            ->giveConfig('services.nynorobot.style');

        $this->app->bind(TranslationServiceInterface::class, match (config('h5p.translator')) {
            'nynorskroboten' => NynorskrobotenAdapter::class,
            'nynorobot' => NynorobotAdapter::class,
            default => NullTranslationAdapter::class,
        });

        $this->app->bind(H5PAdapterInterface::class, function () {
            $adapterTarget = strtolower(Session::get('adapterMode', config('h5p.h5pAdapter')));
            $adapter = match ($adapterTarget) {
                'ndla' => $this->app->make(NDLAH5PAdapter::class),
                default => $this->app->make(CerpusH5PAdapter::class),
            };
            if (Session::has('adapterMode')) {
                $adapter->overrideAdapterSettings();
            }
            return $adapter;
        });

        $this->app->singletonIf(H5peditorStorage::class, EditorStorage::class);

        $this->app->singletonIf(H5PFrameworkInterface::class, function () {
            $pdoConnection = DB::connection()->getPdo();

            // H5P Editor preforms permission checks when installing/updating libraries, so we override
            // the permission functions in a CLI version of the Framework
            if ($this->app->runningInConsole() && !$this->app->runningUnitTests()) {
                return new CliH5pFramework(
                    new Client(),
                    $pdoConnection,
                    Storage::disk('h5pTmp'),
                );
            }

            return new Framework(
                new Client(),
                $pdoConnection,
                Storage::disk('h5pTmp'),
            );
        });

        $this->app->singleton(H5PCore::class, function ($app) {
            /** @var App $app */
            /** @var CerpusStorageInterface|H5PFileStorage $fileStorage */
            $fileStorage = $app->make(H5PFileStorage::class);
            $core = new H5PCore($app->make(H5PFrameworkInterface::class), $fileStorage, Storage::disk()->url(''));
            $core->aggregateAssets = true;

            $app->instance(H5PCore::class, $core);
            return $core;
        });

        $this->app->when(\App\Libraries\H5P\H5PExport::class)
            ->needs('$convertMediaToLocal')
            ->giveConfig('feature.export_h5p_with_local_files');

        $this->app->tag([
            H5PAudioInterface::class,
            H5PImageInterface::class,
            H5PVideoInterface::class,
        ], 'external-providers');

        $this->app->when(\App\Libraries\H5P\H5PExport::class)
            ->needs('$externalProviders')
            ->giveTagged('external-providers');

        $this->app->bind(H5PExport::class, function ($app) {
            /** @var App $app */
            return new H5PExport($app->make(H5PFrameworkInterface::class), $app->make(H5PCore::class));
        });

        $this->app->bind(H5PContentValidator::class, function ($app) {
            /** @var App $app */
            return new H5PContentValidator($app->make(H5PFrameworkInterface::class), $app->make(H5PCore::class));
        });

        $this->app->bind(H5PEditorAjaxInterface::class, function () {
            return new EditorAjax();
        });

        $this->app->singleton(H5PValidator::class, function ($app) {
            /** @var App $app */
            return new H5PValidator($app->make(H5PFrameworkInterface::class), $app->make(H5PCore::class));
        });

        $this->app->bind(H5peditor::class, function ($app) {
            /** @var App $app */
            return new H5peditor($app->make(H5PCore::class), $app->make(EditorStorage::class), $app->make(EditorAjax::class));
        });

        $this->app->bind(H5PEditorAjax::class, function ($app) {
            /** @var App $app */
            return new H5PEditorAjax($app->make(H5PCore::class), $app->make(H5peditor::class), $app->make(H5peditorStorage::class));
        });
    }
}
