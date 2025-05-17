<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('habit_queue', false, true, false, false);
echo "[*] Oczekiwanie na wiadomości. Naciśnij CTRL+C, aby zakończyć\n";

$callback = function ($msg) {
    echo "[x] Odebrano: ", $msg->body, "\n";

    // symulacja przetwarzania
    sleep(substr_count($msg->body, '.'));
    echo "[v] Przetworzono: ", $msg->body, "\n";

    $msg->ack();
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('habit_queue', '', false, false, false, false, $callback);

while (true) {
    $channel->wait();
}
