<?php
namespace App;

// Modified version, original from https://medium.com/@bauernfeind.dominik/using-logstash-with-laravel-509c65065d52

use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\SocketHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class LogstashLogger
{

    /**
     * @param array $config
     * @return LoggerInterface
     */
    public function __invoke(array $config): LoggerInterface
    {
        $handler = new SocketHandler("udp://{$config['host']}:{$config['port']}");
        $handler->setChunkSize(65530);
        $handler->setFormatter(new LogstashFormatter($config['appName']));

        return new Logger('logstash.main', [$handler]);
    }
}
