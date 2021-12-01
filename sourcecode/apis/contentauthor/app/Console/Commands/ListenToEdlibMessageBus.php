<?php

namespace App\Console\Commands;

use Anik\Amqp\ConsumableMessage;
use Anik\Amqp\Exchanges\Fanout;
use Anik\Amqp\Queues\Queue;
use Anik\Laravel\Amqp\Facades\Amqp;
use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;

class ListenToEdlibMessageBus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message-bus:listen {queueName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen to Edlib message bus';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queueName = $this->argument('queueName');

        $handle = function (ConsumableMessage $message, AMQPMessage $original)
        {
            $this->output->text(
                sprintf('[%s][Message]: %s', now()->toDateTimeString(), $message->getMessageBody())
            );
            $this->info(json_decode($message->getMessageBody()));

            $message->ack();
        };

        $exchange = new Fanout($this->argument('queueName'));
        $exchange->setDeclare(true);
        $queue = new Queue('edlib_gdpr_delete_request-contentauthor');
        $queue->setDeclare(true);

        $this->info('a');
        Amqp::consume($handle, '', $exchange, $queue, null, [
            'consume' => [
                'non_blocking' => true
            ]
        ]);
    }
}
