<?php
require "../config/connect_db_sac_data2.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// รับ raw JSON input
$raw = file_get_contents("php://input");

// บันทึก log เช็คว่าได้รับข้อมูลไหม
file_put_contents("debug.log", $raw . PHP_EOL, FILE_APPEND);

$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "JSON decode error: " . json_last_error_msg(),
        "raw" => $raw
    ]);
    exit;
}

if (isset($data['user_id'], $data['latitude'], $data['longitude'])) {
    $userId = $data['user_id'];
    $lat = $data['latitude'];
    $lon = $data['longitude'];
    $timestamp = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO checkins (user_id, latitude, longitude, checkin_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $lat, $lon, $timestamp]);

    echo json_encode([
        "status" => "success",
        "message" => "Check-in OK",
        "user_id" => $userId,
        "latitude" => $lat,
        "longitude" => $lon,
        "timestamp" => $timestamp
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Missing fields",
        "data" => $data
    ]);
}
