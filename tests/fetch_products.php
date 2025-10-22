<?php

$host = '127.0.0.1';
$db = 'mydatabase';
$user = 'myadmin';
$pass = 'myadmin';
$charset = 'utf8mb4';
$port = 3307;

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$stmt = $pdo->query('SELECT id,name,description,price FROM products ORDER BY id ASC');
$data = $stmt->fetchAll();

echo json_encode(['data' => $data]);