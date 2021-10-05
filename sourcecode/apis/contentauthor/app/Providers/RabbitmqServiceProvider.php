<?php

namespace App\Providers;

use App\Libraries\Rabbitmq\RabbitmqConnection;
use Illuminate\Support\ServiceProvider;
use Vinelab\Bowler\Connection;

class RabbitmqServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Connection::class, function () {
            $secure = config('bowler.rabbitmq.secure');
            $rbmqHost = config('bowler.rabbitmq.host');
            $rbmqPort = config('bowler.rabbitmq.port');
            $rbmqUsername = config('bowler.rabbitmq.username');
            $rbmqPassword = config('bowler.rabbitmq.password');
            $rbmqConnectionTimeout = config('bowler.rabbitmq.connection_timeout');
            $rbmqReadWriteTimeout = config('bowler.rabbitmq.read_write_timeout');
            $rbmqHeartbeat = config('bowler.rabbitmq.heartbeat');
            $rbmqVhost = config('bowler.rabbitmq.vhost', '/');

            return new RabbitmqConnection($secure, $rbmqHost, $rbmqPort, $rbmqUsername, $rbmqPassword, $rbmqConnectionTimeout, $rbmqReadWriteTimeout, $rbmqHeartbeat, $rbmqVhost);
        });
    }
}
