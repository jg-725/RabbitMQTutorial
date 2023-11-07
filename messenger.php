<?php

require_once __DIR__ .'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declaring an EXCHANGE to receive messages from frontend
$channel->exchange_declare('frontend_exchange', 'direct', false, false, false);

$channel->exchange_declare('backend_exchange', 'direct', false, false, false);

$channel->exchange_declare('database_exchange', 'direct', false, false, false);


// Declaring a DURABLE QUEUE to send messages to backend
$channel->queue_declare('backend_queue', false, true, false, false);

$channel->queue_declare('database_queue', false, true, false, false);

$channel->queue_declare('frontend_queue', false, true, false, false);


// Creating the binding keys for each server
$binding_key_backend = 'backend';
$binding_key_database = 'database';
$binding_key_frontend = 'frontend';


// Binding the exchange and queue together using the binding key
$channel->queue_bind('backend_queue', 'frontend_exchange', $binding_key_backend);

$channel->queue_bind('database_queue', 'backend_exchange', $binding_key_database);

$channel->queue_bind('frontend_queue', 'database_exchange', $binding_key_frontend);


echo " [*] Messenger Server INITIATED\n";
echo " [*] Waiting for Senders to send a message to RabbitMQ. To exit press CTRL+C\n\n";


$callback = function ($msg) {
	echo ' [x] ', 'Routing Key->', $msg->getRoutingKey(), "\n";
	echo ' [x] ', 'Msg->', $msg->getBody(), "\n";

	//$msg->ack();
	echo  " [x] Message Received\n\n";
};

$channel->basic_qos(null, 1, false);
$channel->basic_consume('backend_queue', '', false, true, false, false, $callback);

$channel->basic_consume('database_queue', '', false, true, false, false, $callback);

$channel->basic_consume('frontend_queue', '', false, true, false, false, $callback);

try {
	$channel->consume();
} catch (\Throwable $exception) {
	echo $exception->getMessage();
}

$channel->close();
$connection->close();

//	TESTING CODE
/*
function requestProcessor($message)
{
        var_dump($message);
        if (!isset($message['type'])) {
                return "ERROR: UNSUPPORTED MESSAGE TYPE IN ARRAY";
        }

        $request = $message['type'];

        switch ($request) {
                case "login":
                        return sendToBackend($message);
                case "register":
                        return sendToBackend($message);
                case "loginDB":
                        return sendToDatabase($message);
                case "registerDB":
                        return sendToDatabase($message);
                case "verified_user"
                        return sendToFrontend($message);
        }
        return array("returnCode" => '0', "message" =>"Messenger received the message and processed");
}
$server = new rabbitMQServer("", "");

echo "Messenger Server Initated";

$server->process_requests('requestProcessor');

echo "Messenger Server TERMINATED";
*/
?>
