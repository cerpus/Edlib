<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use App\Libraries\NDLA\API\AudioApiClient;
use App\Libraries\NDLA\API\ImageApiClient;
use App\Libraries\NDLA\API\ArticleApiClient;
use App\Libraries\NDLA\API\LearningPathApiClient;

class NdlaApiClientProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ArticleApiClient::class, function ($app, $args) {
            $client = null;
            if (array_key_exists('client', $args) && !empty($args['client'])) {
                $client = $args['client'];
            } else {
                $client = new Client([
                    'base_uri' => config('ndla.api.uri'),
                    'headers' => [
                        'User-Agent' => config('ndla.api.userAgent', 'Cerpus NDLA Api client'),
                    ]
                ]);
            }
            if (array_key_exists('pageSize', $args) && !empty($args['pageSize'])) {
                $pageSize = $args['pageSize'];
            } else {
                $pageSize = config('ndla.api.pageSize');
            }

            return new ArticleApiClient($client, $pageSize);
        });

        $this->app->bind(ImageApiClient::class, function () {
            $client = new Client([
                'base_uri' => config('ndla.api.uri'),
                'headers' => [
                    'User-Agent' => config('ndla.api.userAgent'),
                ]
            ]);

            return new ImageApiClient($client);
        });

        $this->app->bind(AudioApiClient::class, function () {
            $client = new Client([
                'base_uri' => config('ndla.api.uri'),
                'headers' => [
                    'User-Agent' => config('ndla.api.userAgent'),
                ]
            ]);

            return new AudioApiClient($client);
        });

        $this->app->bind(LearningPathApiClient::class, function ($app, $args) {
            $client = null;
            if (array_key_exists('client', $args) && !empty($args['client'])) {
                $client = $args['client'];
            } else {
                $client = new Client([
                    'base_uri' => config('ndla.api.uri'),
                    'headers' => [
                        'User-Agent' => config('ndla.api.userAgent', 'Cerpus NDLA Api client'),
                    ]
                ]);
            }

            if (array_key_exists('pageSize', $args) && !empty($args['pageSize'])) {
                $pageSize = $args['pageSize'];
            } else {
                $pageSize = config('ndla.api.pageSize');
            }

            return new LearningPathApiClient($client, $pageSize);
        });
    }
}
