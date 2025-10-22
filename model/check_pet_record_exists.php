<?php
header('Content-Type: application/json');
include('../config/connect_db.php');

$response = ['exists' => false];

if (isset($_POST["house_number"])) {
    $house_number = $_POST["house_number"];

    $sql = "SELECT COUNT(*) FROM ims_house_pet WHERE house_number = :house_number";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':house_number', $house_number, PDO::PARAM_STR);
    $stmt->execute();

    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $response['exists'] = true;
    }
}

echo json_encode($response);