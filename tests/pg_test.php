<?php
include '../config_pg/connect_db.php';
$stmt = $conn->prepare(
    "SELECT * FROM sac_orders order by date desc limit 1");
$stmt->execute();
$orders = $stmt->fetchAll();
foreach($orders as $order)
{
        echo $order['date'] . "\n\r";
        $msg_data = "Date = " . $order['date'];
}


require_once '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('192.168.88.8', 5672, 'admin', 'admin');
$channel = $connection->channel();

$channel->queue_declare('sac_msg', false, false, false, false);

$msg = new AMQPMessage($msg_data);
$channel->basic_publish($msg, '', 'sac_msg');

echo " [x] Sent 'Send Data'\n";

$channel->close();
$connection->close();
