<?php

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('backend_exchange', 'direct', false, false, false);

// The key/adress of where message is going
$binding_key = "database";

//Array Variables
$username = "John";
$password = "1234";
$auth = "authenticated";

$send = array();

if (empty($send)) {

        $send['type'] = "loginDB";
        $send['username'] = $username;
	$send['password'] = $password;
	$send['auth'] = $auth;
}

$login_data = implode($send);

$msg = new AMQPMessage(
        $login_data,
        array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
);

$channel->basic_publish($msg, 'backend_exchange', $binding_key);

echo ' [x] Backend Task: Sent Authenticated User to Messenger -> Database', "\n";
print_r($send);
echo "\n\n";

$channel->close();
$connection->close();
?>
