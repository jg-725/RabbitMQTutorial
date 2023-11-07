<?php

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('database_exchange', 'direct', false, false, false);

$binding_key = "frontend";

$username = "John";

$send = array();

if (empty($send)) {

        $send['type'] = "verified_user";
        $send['username'] = $username;
	$send['condition'] = "user exists";
}

$login_data = implode($send);

$msg = new AMQPMessage(
        $login_data,
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);

$channel->basic_publish($msg, 'database_exchange', $binding_key);

echo ' [x] Database Task: Sent Logged In User to Messenger -> Frontend', "\n";
print_r($send);
echo "\n\n";

$channel->close();
$connection->close();
?>
