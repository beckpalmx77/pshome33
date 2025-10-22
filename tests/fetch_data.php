<?php
$host = '127.0.0.1';
$db = 'sac_emp';
$user = 'myadmin';
$pass = 'myadmin';
$charset = 'utf8mb4';
$port = 3307;

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$sql = "SELECT * FROM ims_user";
$stmt = $pdo->query($sql);
$data = $stmt->fetchAll();


echo json_encode(["data" => $data]);

/*

// Encode array to JSON with formatting
$json = json_encode($data, JSON_PRETTY_PRINT);

// Write JSON to file and handle errors
$file = "myfile.json";
if (file_put_contents($file, $json) !== false) {
    echo "Data has been written to $file.";
} else {
    echo "Error occurred while writing to $file.";
}
*/


