<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('login_queue', false, true, false, false);

echo " [*] Waiting for Login information. To exit press CTRL+C\n";

$callback = function ($msg) {
	echo ' [x] Login Received ', $msg->body, "\n";
	sleep(substr_count($msg->getBody(), '.'));
	echo "[x] Done\n";
	$msg->ack();
};

$channel->basic_qos(null, 1, false);
$channel->basic_consume('login_queue', '', false, false, false, false, $callback);

try {
	$channel->consume();
} catch (\Throwable $exception) {
	echo $exception->getMessage();
}

$channel->close();
$connection->close();
?>
