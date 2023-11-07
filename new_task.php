<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('login_queue', false, true, false, false);

$data = implode('', array_slice($argv, 1));
if (empty($data)) {
	$data = "Hello User!";
}

$msg = new AMQPMessage(
	$data,
	array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
)
;
$channel->basic_publish($msg, '', 'login_queue');

echo ' [x] Sent ', $data, "\n";

$channel->close();
$connection->close();
?>
