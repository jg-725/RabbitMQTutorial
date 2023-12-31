<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class LoginRpcClient
{
	private $connection;		// Holds AMQP connection
	private $channel;		// Channel instance holder for connection
	private $callback_queue;	// Queue Name
	private $response;		// Holds response received from server
	private $corr_id;		// Holds correlation ID from request

	public function _construct()
	{
		$this->connection = new AMQPStreamConnection(
			'localhost',
			5672,
			'guest',
			'guest',
		);
		// 
		$this->channel = $this->connection->channel();
		list($this->callback_queue, ,) = $this->channel->queue_declare(
			"",
			false,
			false,
			true,
			false
		);
		$this->channel->basic_consume(
			$this->callback_queue,
			'',
			false,
			true,
			false,
			false,
			array(
				$this,
				'onReponse'
			)
		);
	}

	public function onResponse($rep)
	{
		if ($rep->get('correlation_id') == $this->corr_id) {
			$this->response = $rep->body;
		}
	}

	public function call($n)
	{
		$this->response = null;
		$this->corr_id = uniqid();

		$msg = new AMQPMessage(
			(string) $n,
			array(
				'correlation_id' => $this->corr_id,
				'reply_to' => $this->callback_queue
			)
		);

		$this->channel->basic_publish($msg, '', 'rpc_queue');

		while (!$this->response) {
			$this->channel->wait();
		}

		return intval($this->response);
	}
}

$fibonacci_rpc = new FibonacciRpcClient();
$response = $fibonacci_rpc->call(30);
echo ' [.] Got ', $response, "\n";
?>
