<?php
session_start();
include '../config/connect_db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['alogin']) || strlen($_SESSION['alogin']) == "") {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$house_number = $_POST['house_number'] ?? '';

if (empty($house_number)) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบข้อมูลบ้านเลขที่']);
    exit;
}

$car_no1 = $_POST['car_no1'] ?? '';
$car_no1_province = $_POST['car_no1_province'] ?? '';
$car_no1_type = $_POST['car_no1_type'] ?? '';
$car_no1_brand = $_POST['car_no1_brand'] ?? '';
$car_no1_color = $_POST['car_no1_color'] ?? '';

$car_no2 = $_POST['car_no2'] ?? '';
$car_no2_province = $_POST['car_no2_province'] ?? '';
$car_no2_type = $_POST['car_no2_type'] ?? '';
$car_no2_brand = $_POST['car_no2_brand'] ?? '';
$car_no2_color = $_POST['car_no2_color'] ?? '';

$car_no3 = $_POST['car_no3'] ?? '';
$car_no3_province = $_POST['car_no3_province'] ?? '';
$car_no3_type = $_POST['car_no3_type'] ?? '';
$car_no3_brand = $_POST['car_no3_brand'] ?? '';
$car_no3_color = $_POST['car_no3_color'] ?? '';

$car_no4 = $_POST['car_no4'] ?? '';
$car_no4_province = $_POST['car_no4_province'] ?? '';
$car_no4_type = $_POST['car_no4_type'] ?? '';
$car_no4_brand = $_POST['car_no4_brand'] ?? '';
$car_no4_color = $_POST['car_no4_color'] ?? '';

$car_no5 = $_POST['car_no5'] ?? '';
$car_no5_province = $_POST['car_no5_province'] ?? '';
$car_no5_type = $_POST['car_no5_type'] ?? '';
$car_no5_brand = $_POST['car_no5_brand'] ?? '';
$car_no5_color = $_POST['car_no5_color'] ?? '';

$car_no6 = $_POST['car_no6'] ?? '';
$car_no6_province = $_POST['car_no6_province'] ?? '';
$car_no6_type = $_POST['car_no6_type'] ?? '';
$car_no6_brand = $_POST['car_no6_brand'] ?? '';
$car_no6_color = $_POST['car_no6_color'] ?? '';

$car_no7 = $_POST['car_no7'] ?? '';
$car_no7_province = $_POST['car_no7_province'] ?? '';
$car_no7_type = $_POST['car_no7_type'] ?? '';
$car_no7_brand = $_POST['car_no7_brand'] ?? '';
$car_no7_color = $_POST['car_no7_color'] ?? '';

$sql = "UPDATE ims_house SET 
    car_no1 = :car_no1,
    car_no1_province = :car_no1_province,
    car_no1_type = :car_no1_type,
    car_no1_brand = :car_no1_brand,
    car_no1_color = :car_no1_color,
    car_no2 = :car_no2,
    car_no2_province = :car_no2_province,
    car_no2_type = :car_no2_type,
    car_no2_brand = :car_no2_brand,
    car_no2_color = :car_no2_color,
    car_no3 = :car_no3,
    car_no3_province = :car_no3_province,
    car_no3_type = :car_no3_type,
    car_no3_brand = :car_no3_brand,
    car_no3_color = :car_no3_color,
    car_no4 = :car_no4,
    car_no4_province = :car_no4_province,
    car_no4_type = :car_no4_type,
    car_no4_brand = :car_no4_brand,
    car_no4_color = :car_no4_color,
    car_no5 = :car_no5,
    car_no5_province = :car_no5_province,
    car_no5_type = :car_no5_type,
    car_no5_brand = :car_no5_brand,
    car_no5_color = :car_no5_color,
    car_no6 = :car_no6,
    car_no6_province = :car_no6_province,
    car_no6_type = :car_no6_type,
    car_no6_brand = :car_no6_brand,
    car_no6_color = :car_no6_color,
    car_no7 = :car_no7,
    car_no7_province = :car_no7_province,
    car_no7_type = :car_no7_type,
    car_no7_brand = :car_no7_brand,
    car_no7_color = :car_no7_color
WHERE house_number = :house_number";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':house_number', $house_number, PDO::PARAM_STR);
    $stmt->bindParam(':car_no1', $car_no1, PDO::PARAM_STR);
    $stmt->bindParam(':car_no1_province', $car_no1_province, PDO::PARAM_STR);
    $stmt->bindParam(':car_no1_type', $car_no1_type, PDO::PARAM_STR);
    $stmt->bindParam(':car_no1_brand', $car_no1_brand, PDO::PARAM_STR);
    $stmt->bindParam(':car_no1_color', $car_no1_color, PDO::PARAM_STR);
    $stmt->bindParam(':car_no2', $car_no2, PDO::PARAM_STR);
    $stmt->bindParam(':car_no2_province', $car_no2_province, PDO::PARAM_STR);
    $stmt->bindParam(':car_no2_type', $car_no2_type, PDO::PARAM_STR);
    $stmt->bindParam(':car_no2_brand', $car_no2_brand, PDO::PARAM_STR);
    $stmt->bindParam(':car_no2_color', $car_no2_color, PDO::PARAM_STR);
    $stmt->bindParam(':car_no3', $car_no3, PDO::PARAM_STR);
    $stmt->bindParam(':car_no3_province', $car_no3_province, PDO::PARAM_STR);
    $stmt->bindParam(':car_no3_type', $car_no3_type, PDO::PARAM_STR);
    $stmt->bindParam(':car_no3_brand', $car_no3_brand, PDO::PARAM_STR);
    $stmt->bindParam(':car_no3_color', $car_no3_color, PDO::PARAM_STR);
    $stmt->bindParam(':car_no4', $car_no4, PDO::PARAM_STR);
    $stmt->bindParam(':car_no4_province', $car_no4_province, PDO::PARAM_STR);
    $stmt->bindParam(':car_no4_type', $car_no4_type, PDO::PARAM_STR);
    $stmt->bindParam(':car_no4_brand', $car_no4_brand, PDO::PARAM_STR);
    $stmt->bindParam(':car_no4_color', $car_no4_color, PDO::PARAM_STR);
    $stmt->bindParam(':car_no5', $car_no5, PDO::PARAM_STR);
    $stmt->bindParam(':car_no5_province', $car_no5_province, PDO::PARAM_STR);
    $stmt->bindParam(':car_no5_type', $car_no5_type, PDO::PARAM_STR);
    $stmt->bindParam(':car_no5_brand', $car_no5_brand, PDO::PARAM_STR);
    $stmt->bindParam(':car_no5_color', $car_no5_color, PDO::PARAM_STR);
    $stmt->bindParam(':car_no6', $car_no6, PDO::PARAM_STR);
    $stmt->bindParam(':car_no6_province', $car_no6_province, PDO::PARAM_STR);
    $stmt->bindParam(':car_no6_type', $car_no6_type, PDO::PARAM_STR);
    $stmt->bindParam(':car_no6_brand', $car_no6_brand, PDO::PARAM_STR);
    $stmt->bindParam(':car_no6_color', $car_no6_color, PDO::PARAM_STR);
    $stmt->bindParam(':car_no7', $car_no7, PDO::PARAM_STR);
    $stmt->bindParam(':car_no7_province', $car_no7_province, PDO::PARAM_STR);
    $stmt->bindParam(':car_no7_type', $car_no7_type, PDO::PARAM_STR);
    $stmt->bindParam(':car_no7_brand', $car_no7_brand, PDO::PARAM_STR);
    $stmt->bindParam(':car_no7_color', $car_no7_color, PDO::PARAM_STR);

    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'บันทึกสำเร็จ']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}