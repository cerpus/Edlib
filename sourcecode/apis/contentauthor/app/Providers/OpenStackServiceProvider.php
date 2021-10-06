<?php

namespace App\Providers;

use Biigle\CachedOpenStack\OpenStack;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Nimbusoft\Flysystem\OpenStack\SwiftAdapter;

class OpenStackServiceProvider extends ServiceProvider
{
    const OpenStackService = 'OpenStackService';
    const OpenStackStoreContainer = 'OpenStackStoreContainer';

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(self::OpenStackService, function ($app) {
            $config = config('services.openstack');

            $options = [
                'authUrl' => $config['authUrl'],
                'region' => $config['region'],
                'user' => [
                    'name' => $config['username'],
                    'password' => $config['password'],
                    'domain' => ['name' => $config['domain']],
                ],
            ];
            if (!empty($config['projectId'])) {
                $options['scope'] = ['project' => ['id' => $config['projectId']]];
            }
            return new OpenStack($app->has('cache') ? $app->cache : new CacheManager($app), $options);
        });

        $this->app->singletonIf(self::OpenStackStoreContainer, function ($app){
            /** @var OpenStack $openStackService */
            $openStackService = $app->make(self::OpenStackService);

            $store = $openStackService->objectStoreV1();
            $container = config('filesystems.disks.openstack.container');
            if( !$store->containerExists($container)){
                $store->createContainer(['name' => $container]);
            }
            return $store->getContainer($container);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        \Storage::extend('openstack', function ($app) {
            $container = $app->make(self::OpenStackStoreContainer);
            return new Filesystem(new SwiftAdapter($container));
        });
    }
}
