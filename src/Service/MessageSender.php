<?php
namespace App\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessageSender {
    private string $host;
    private int $port = 5672;
    private string $user = 'guest';
    private string $pass = 'guest';
    private string $queue = 'habit_queue';

    public function __construct(string $host) {
        $this->host = $host;
    }

    public function send(string $message): void {
        $connection = new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->pass
        );
        $channel = $connection->channel();

        $channel->queue_declare($this->queue, false, true, false, false);

        $msg = new AMQPMessage(
            $message,
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );
        $channel->basic_publish($msg, '', $this->queue);

        $channel->close();
        $connection->close();
    }
}
