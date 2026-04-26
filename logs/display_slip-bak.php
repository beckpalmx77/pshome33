<?php
include('config/connect_db.php');

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql_load = "SELECT picture_payment FROM ims_house_payment WHERE id = :id";

    $stmt = $conn->prepare($sql_load);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['picture_payment']) {
        $base_url = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
        $image_url = $base_url . dirname($_SERVER['PHP_SELF']) . '/../uploads/slips/' . $row['picture_payment'];
        echo json_encode(['status' => 1, 'image_url' => $image_url]);
    } else {
        echo json_encode(['status' => 0]);
    }
} else {
    echo json_encode(['status' => 0]);
}
