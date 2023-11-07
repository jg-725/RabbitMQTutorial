<?php

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;


$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declaring an EXCHANGE to receive messages from frontend
$channel->exchange_declare('backend_exchange', 'direct', false, false, false);

// Declaring a DURABLE QUEUE to send messages to backend
$channel->queue_declare('backend_queue', false, true, false, false);

// Creating the binding key
$binding_key = 'frontend';

// Binding the exchange and queue together using the binding key
$channel->queue_bind('backend_queue', 'backend_exchange', $binding_key);

echo " [*] Waiting for frontend to send a message. To exit press CTRL+C\n";

$callback = function ($msg) {
	echo ' [x] ', $msg->getRoutingKey(), ':', $msg->getBody(), "\n";
	//$msg->ack();
	echo  " [x] Message Received\n";
};

$channel->basic_qos(null, 1, false);
$channel->basic_consume('backend_queue', '', false, true, false, false, $callback);

try {
	$channel->consume();
} catch (\Throwable $exception) {
	echo $exception->getMessage();
}

echo "Messenger Received: ", "\n";

$channel->close();
$connection->close();
?>
