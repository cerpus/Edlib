<?php

namespace App\Providers;

use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\Adapters\CerpusH5PAdapter;
use App\Libraries\H5P\Adapters\NDLAH5PAdapter;
use App\Libraries\H5P\Audio\NDLAAudioBrowser;
use App\Libraries\H5P\EditorAjax;
use App\Libraries\H5P\EditorStorage;
use App\Libraries\H5P\Framework;
use App\Libraries\H5P\H5PLibraryAdmin;
use App\Libraries\H5P\H5pPresave;
use App\Libraries\H5P\Image\NDLAContentBrowser;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PAudioInterface;
use App\Libraries\H5P\Interfaces\H5PImageAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use App\Libraries\H5P\Storage\H5PCerpusStorage;
use App\Libraries\H5P\TranslationServices\NynorobotAdapter;
use App\Libraries\H5P\TranslationServices\NynorskrobotenAdapter;
use App\Libraries\H5P\Video\NDLAVideoAdapter;
use App\Libraries\H5P\Video\NullVideoAdapter;
use Cerpus\Helper\Clients\Auth0Client;
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
    public function boot()
    {
    }

    public function provides()
    {
        return [
            H5PFileStorage::class,
            H5PAdapterInterface::class,
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
            ->give(fn () => Storage::disk('h5p-presave'));

        $this->app->bind(H5PVideoInterface::class, function () {
            $adapter = $this->app->make(H5PAdapterInterface::class)->getAdapterName();

            return match (strtolower($adapter)) {
                'ndla' => $this->app->make(NDLAVideoAdapter::class),
                default => $this->app->make(NullVideoAdapter::class),
            };
        });

        $this->app->when(NDLAVideoAdapter::class)
            ->needs(Client::class)
            ->give(fn () => Oauth2Client::getClient(OauthSetup::create([
                'authUrl' => config('h5p.video.authUrl'),
                'coreUrl' => config('h5p.video.url'),
                'key' => config('h5p.video.key'),
                'secret' => config('h5p.video.secret'),
            ])));

        $this->app->when(NDLAVideoAdapter::class)
            ->needs('$accountId')
            ->giveConfig('h5p.video.accountId');

        $this->app->when(NDLAContentBrowser::class)
            ->needs(Client::class)
            ->give(fn () => Auth0Client::getClient(OauthSetup::create([
                'key' => config('h5p.image.key'),
                'secret' => config('h5p.image.secret'),
                'authUrl' => config('h5p.image.authDomain'),
                'coreUrl' => config('h5p.image.url'),
                'audience' => config('h5p.image.audience'),
            ])));

        $this->app->bind(H5PImageAdapterInterface::class, function () {
            $adapter = $this->app->make(H5PAdapterInterface::class);

            return match (strtolower($adapter->getAdapterName())) {
                'ndla' => $this->app->make(NDLAContentBrowser::class),
                default => null,
            };
        });

        $this->app->when(NDLAAudioBrowser::class)
            ->needs(Client::class)
            ->give(function () {
                if (!is_null(config('h5p.audio.url'))) {
                    $authSetup = OauthSetup::create([
                        'key' => config('h5p.audio.key'),
                        'secret' => config('h5p.audio.secret'),
                        'authUrl' => config('h5p.audio.authDomain'),
                        'coreUrl' => config('h5p.audio.url'),
                        'audience' => config('h5p.audio.audience'),
                    ]);
                } else {
                    $authSetup = OauthSetup::create([
                        'key' => config('h5p.image.key'),
                        'secret' => config('h5p.image.secret'),
                        'authUrl' => config('h5p.image.authDomain'),
                        'coreUrl' => config('h5p.image.url'),
                        'audience' => config('h5p.image.audience'),
                    ]);
                }

                return Auth0Client::getClient($authSetup);
            });

        $this->app->bind(H5PAudioInterface::class, function () {
            $adapter = $this->app->make(H5PAdapterInterface::class);

            return match (strtolower($adapter->getAdapterName())) {
                'ndla' => $this->app->make(NDLAAudioBrowser::class),
                default => null, // none supported at the moment
            };
        });

        $this->app->bind(H5PCerpusStorage::class);
        $this->app->bind(H5PFileStorage::class, H5PCerpusStorage::class);
        $this->app->bind(CerpusStorageInterface::class, H5PCerpusStorage::class);

        $this->app->singletonIf('H5PFilesystem', fn () => Storage::disk());

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

        $this->app->bind(TranslationServiceInterface::class, match (config('h5p.nynorskAdapter')) {
            'nynorskroboten' => NynorskrobotenAdapter::class,
            'nynorobot' => NynorobotAdapter::class,
            default => throw new \Exception('Unknown nynorsk adapter'),
        });

        $this->app->bind(H5PAdapterInterface::class, function () {
            $adapterTarget = strtolower(Session::get('adapterMode', config('h5p.h5pAdapter')));
            switch ($adapterTarget) {
                case 'ndla':
                    $adapter = new NDLAH5PAdapter();
                    break;
                case 'cerpus':
                default:
                    $adapter = new CerpusH5PAdapter();
                    break;
            }
            if (Session::has('adapterMode')) {
                $adapter->overrideAdapterSettings();
            }
            return $adapter;
        });

        $this->app->singletonIf(H5peditorStorage::class, function ($app) {
            /** @var ContentAuthorStorage $contentAuthorStorage */
            $contentAuthorStorage = $app->make(ContentAuthorStorage::class);
            return new EditorStorage(resolve(H5PCore::class), $contentAuthorStorage);
        });

        $this->app->singletonIf(H5PFrameworkInterface::class, function () {
            $pdoConnection = DB::connection()->getPdo();
            /** @var ContentAuthorStorage $contentAuthorStorage */
            $contentAuthorStorage = $this->app->make(ContentAuthorStorage::class);

            return new Framework(
                new Client(),
                $pdoConnection,
                $contentAuthorStorage->getH5pTmpDisk(),
            );
        });

        $this->app->singleton(H5PCore::class, function ($app) {
            /** @var App $app */
            /** @var CerpusStorageInterface|H5PFileStorage $fileStorage */
            $fileStorage = $app->make(H5PFileStorage::class);
            $contentAuthorStorage = $app->make(ContentAuthorStorage::class);
            $core = new H5PCore($app->make(H5PFrameworkInterface::class), $fileStorage, $contentAuthorStorage->getAssetsBaseUrl());
            $core->aggregateAssets = true;

            $app->instance(H5PCore::class, $core);
            return $core;
        });

        $this->app->when(\App\Libraries\H5P\H5PExport::class)
            ->needs('$convertMediaToLocal')
            ->giveConfig('feature.export_h5p_with_local_files');

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
