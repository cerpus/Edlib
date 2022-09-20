<?php

namespace App\Providers;

use App\Libraries\HTMLPurify\Config\MathMLConfig;
use Illuminate\Support\ServiceProvider;
use HTMLPurifier_HTML5Config;
use HTMLPurifier;
use HTMLPurifier_Config;

class HTMLPurifierServiceProvider extends ServiceProvider
{
    protected $defer = true;
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(HTMLPurifier_HTML5Config::class, function ($app, $configSettings = null) {
            $config = HTMLPurifier_HTML5Config::createDefault();
            $config->set('Cache.SerializerPath', config('html.cacheDirectory'));
            if (!empty($configSettings) && is_array($configSettings)) {
                $config->loadArray($configSettings);
            }
            return $config;
        });

        $this->app->bind(MathMLConfig::class, function ($app, $configSettings = null) {
            $config = MathMLConfig::createDefault();
            if (!empty($configSettings) && is_array($configSettings)) {
                $config->loadArray($configSettings);
            }
            $config->set('Cache.SerializerPath', config('html.cacheDirectory'));
            return $config;
        });

        $this->app->bind(HTMLPurifier::class, function ($app, $parameters = null) {
            $config = !empty($parameters) && $parameters[0] instanceof HTMLPurifier_Config ? $parameters[0] : $this->app->make(MathMLConfig::class);
            return new HTMLPurifier($config);
        });
    }

    public function provides()
    {
        return [HTMLPurifier_HTML5Config::class, HTMLPurifier::class, MathMLConfig::class];
    }
}
