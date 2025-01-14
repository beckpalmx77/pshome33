<?php
header('Content-Type: application/json');

$host = '192.168.88.7';
$port = '3307';
$db = 'sac_emp';
$user = 'myadmin';
$pass = 'myadmin';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $stmt = $pdo->query('SELECT id, total_tires as title, job_date_calendar as start, job_date_calendar as end FROM job_payment_daily_total');
    $events = $stmt->fetchAll();

    echo json_encode($events);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
