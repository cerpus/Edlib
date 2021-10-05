<?php

namespace App\Libraries\Rabbitmq;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Vinelab\Bowler\Connection;

class RabbitmqConnection extends Connection
{
    /**
     * $connection var.
     *
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * $channel var.
     *
     * @var AMQPChannel
     */
    private $channel;

    public function __construct($secure = false, $host = 'localhost', $port = 5672, $username = 'guest', $password = 'guest', $connectionTimeout = 30, $readWriteTimeout = 30, $heartbeat = 15, $vhost = '/')
    {
        try {
            parent::__construct($host, $port, $username, $password, $connectionTimeout, $readWriteTimeout, $heartbeat, $vhost);
        } catch (\Exception $e) {}

        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->connectionTimeout = $connectionTimeout;
        $this->readWriteTimeout = $readWriteTimeout;
        $this->heartbeat = $heartbeat;
        $this->vhost = $vhost;

        $insist = false;
        $login_method = 'AMQPLAIN';
        $login_response = null;
        $locale = 'en_US';
        $context = null;
        $keepalive = false;

        if (!$secure) {
            $this->connection = app()->makeWith(AMQPStreamConnection::class, [
                'host' => $host,
                'port' => $port,
                'user' => $username,
                'password' => $password,
                'vhost' => $vhost,
                'insist' => $insist,
                'login_method' => $login_method,
                'login_response' => $login_response,
                'locale' => $locale,
                'connection_timeout' => $connectionTimeout,
                'read_write_timeout' => $readWriteTimeout,
                'context' => $context,
                'keepalive' => $keepalive,
                'heartbeat' => $heartbeat,
            ]);

            $this->channel = $this->connection->channel();
        } else {
            $this->connection = app()->makeWith(AMQPSSLConnection::class, [
                'host' => $host,
                'port' => $port,
                'user' => $username,
                'password' => $password,
                'vhost' => $vhost,
                'ssl_options' => [
                    'capath' => '/etc/ssl/certs',
                    'fail_if_no_peer_cert' => false,
                    'verify_peer' => false
                ],
                'options' => [
                    'insist' => $insist,
                    'login_method' => $login_method,
                    'login_response' => $login_response,
                    'locale' => $locale,
                    'connection_timeout' => $connectionTimeout,
                    'read_write_timeout' => $readWriteTimeout,
                    'context' => $context,
                    'keepalive' => $keepalive,
                    'heartbeat' => $heartbeat,
                ]
            ]);

            $this->channel = $this->connection->channel();
        }
    }

    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }
}
