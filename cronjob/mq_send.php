<?php
include '../config_pg/connect_pg_db.php';
include '../config/connect_db.php';
include '../config/config_rabbit.inc';
require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

echo "Time in Bangkok\n";
$date2 = new DateTime();
$date2->setTimezone(new DateTimeZone('Asia/Bangkok'));
echo $date2->format(DateTime::RFC1123) . "\n";

$current_date = date("Y-m-d");
//$current_date = "2023-07-04";

echo "Date = " . $current_date . "\n\r";

$sql_pg = "SELECT sac_orders.*,sac_customers.code,sac_customers.name,sac_customers.owner FROM sac_orders
    LEFT JOIN sac_customers ON sac_customers.id = sac_orders.customer_id  
    WHERE date >= '" . $current_date . "'";

echo $sql_pg . "\n\r";

$stmt = $conn_pg->prepare($sql_pg);
$stmt->execute();
$orders = $stmt->fetchAll();
foreach ($orders as $order) {
    $sql_find = " SELECT code_id FROM ims_sac_orders WHERE code_id = " . $order['id'];
    echo $sql_find . "\n\r";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        echo "Dup id = " . $order['id'] . "\n\r";
        $data = "";
    } else {
        $data = $order['id'];
        echo "Insert id = " . $data . "\n\r";
        $sql = " INSERT INTO ims_sac_orders (code_id,date,customer_id,customer_code,customer_name,owner,address,contract_name,contract_phone) 
            VALUE (:code_id,:date,:customer_id,:customer_code,:customer_name,:owner,:address,:contract_name,:contract_phone) ";
        $query = $conn->prepare($sql);
        $query->bindParam(':code_id', $order["id"], PDO::PARAM_STR);
        $query->bindParam(':date', $order["date"], PDO::PARAM_STR);
        $query->bindParam(':customer_id', $order["customer_id"], PDO::PARAM_STR);
        $query->bindParam(':customer_code', $order["code"], PDO::PARAM_STR);
        $query->bindParam(':customer_name', $order["name"], PDO::PARAM_STR);
        $query->bindParam(':owner', $order["owner"], PDO::PARAM_STR);
        $query->bindParam(':address', $order["address"], PDO::PARAM_STR);
        $query->bindParam(':contract_name', $order["contract_name"], PDO::PARAM_STR);
        $query->bindParam(':contract_phone', $order["contract_phone"], PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            $connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPass);
            $channel = $connection->channel();

            $channel->queue_declare($rabbitmqQueue, false, false, false, false);

            $msg = new AMQPMessage($data);
            $channel->basic_publish($msg, '', $rabbitmqQueue);

            echo " [x] Sent 'Send Data'\n\r";

            $channel->close();
            $connection->close();

        }
    }
}


