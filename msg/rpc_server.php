<?php

require_once '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

$connection = null;
$channel = null;

try {
    $connection = new AMQPStreamConnection('192.168.88.241', 5672, 'admin', 'admin');
    $channel = $connection->channel();
    $channel->queue_declare('rpc_queue', false, false, false, false);

    /**
     * @param int $n
     * @return int
     */
    function fib(int $n): int
    {
        if ($n === 0) {
            return 0;
        }
        if ($n === 1) {
            return 1;
        }

        return fib($n - 1) + fib($n - 2);
    }

    echo " [x] Awaiting RPC requests\n";

    $callback = function (AMQPMessage $req) use ($channel) {
        $n = (int) $req->getBody();
        echo ' [.] fib(', $n, ")\n";

        $responseMessage = new AMQPMessage(
            (string) fib($n),
            ['correlation_id' => $req->get('correlation_id')]
        );

        $req->getChannel()->basic_publish(
            $responseMessage,
            '',
            $req->get('reply_to')
        );

        $req->ack();
    };

    $channel->basic_qos(0, 1, false);
    $channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

    $channel->consume();
} catch (\Throwable $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
} finally {
    if ($channel instanceof AMQPChannel) {
        $channel->close();
    }
    if ($connection instanceof AMQPStreamConnection) {
        $connection->close();
    }
}