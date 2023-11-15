<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');

$channel = $connection->channel();

$channel->queue_declare('rpc_queue', false, false, false, false);

function fib($n)
{
	if ($n == 0) {
		return 0;
	}
	if ($n == 1) {
		return 1;
	}
	return fib($n-1) + fib($n-2);
}

echo " [x] Awaiting RPC requests\n";

$callback = function($req) {

	$n = intval($req->getBody());
	
	echo ' [.] fib(', $n, ")\n";

	$result = fib($n);	
	
	$msg = new AMQPMessage(
		(string) $result,
		array('correlation_id' => $req->get('correlation_id'))
	);
	
	// Sending result back to client
	$req->getChannel()->basic_publish(
		$msg,
		'',
		$req->get('reply_to')
	);
	$req->ack();
};

// Channel handles one request at a time
$channel->basic_qos(null, 1, null;

// Create channel that will consume messages from queue 
$channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

// While condition that waits for all incoming messages to be received and consumed while channel is open 
while ($channel->is_open()) {
	$channel->wait;
}

$channel->close();
$connection->close();
?>
