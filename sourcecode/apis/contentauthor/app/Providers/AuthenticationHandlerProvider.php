<?php

namespace App\Providers;


use App\Libraries\Auth\ContentAuthorAuthenticationHandler;
use Illuminate\Support\ServiceProvider;

class AuthenticationHandlerProvider extends ServiceProvider {
    public function boot() {
    }

    public function register() {
        $this->app->bind(\Cerpus\AuthCore\AuthenticationHandler::class, function ($app) {
            return new \App\Libraries\Auth\AuthenticationHandler();
        });
        $this->app->bind(ContentAuthorAuthenticationHandler::class, function ($app) {
            return new \App\Libraries\Auth\AuthenticationHandler();
        });
    }
}