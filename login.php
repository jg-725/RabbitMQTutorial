<?php

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('frontend_exchange', 'direct', false, false, false);

$binding_key = "backend";

$username = "John";
$password = "1234";

$send = array();

if (empty($send)) {

	$send['type'] = "Login";
	$send['username'] = $username;
	$send['password'] = $password;
}

$login_data = implode($send);


$msg = new AMQPMessage(
	$login_data,
	array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);

$channel->basic_publish($msg, 'frontend_exchange', $binding_key);

echo ' [x] Frontend Task: Sent Login to Messenger -> Backend', "\n";
print_r($send);
echo "\n\n";

$channel->close();
$connection->close();

?>
