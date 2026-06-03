<?php
include('config/connect_db.php');
try {
    $stmt = $conn->query("
        SELECT trx_mysql_thread_id, TIME_TO_SEC(TIMEDIFF(NOW(), trx_started)) as duration 
        FROM information_schema.innodb_trx 
        WHERE TIME_TO_SEC(TIMEDIFF(NOW(), trx_started)) > 10
    ");
    $trxs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $killed = 0;
    foreach ($trxs as $trx) {
        $id = $trx['trx_mysql_thread_id'];
        echo "Killing transaction thread ID: $id (Duration: " . $trx['duration'] . "s)\n";
        $conn->query("KILL $id");
        $killed++;
    }
    echo "Total transaction threads killed: $killed\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
