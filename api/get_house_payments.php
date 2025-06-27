<?php
// กำหนด Content-Type เป็น JSON
header('Content-Type: application/json');

// ตั้งค่าการแสดงผลข้อผิดพลาด (สำหรับ Development เท่านั้น)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// *** ส่วนที่แก้ไข: นำเข้าไฟล์ connect_db.php ที่มี $conn อยู่แล้ว ***
// ตรวจสอบ Path ของไฟล์ connect_db.php ให้ถูกต้อง
include('../config/connect_db.php');

// ในกรณีที่คุณต้องการให้ PDO ทำงานในโหมดที่เหมาะสมกับการดึงข้อมูลและการเตรียมคำสั่ง
// และคุณแน่ใจว่า connect_db.php ยังไม่ได้ตั้งค่าเหล่านี้
// คุณอาจเพิ่มโค้ดนี้หลัง include connect_db.php
// แต่โดยทั่วไปแล้ว ควรตั้งค่าเหล่านี้ใน connect_db.php โดยตรง
if (isset($conn)) {
    // ตัวอย่างการตั้งค่าเพิ่มเติม (ถ้ายังไม่ได้ตั้งค่าใน connect_db.php)
    // $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} else {
    // กรณีที่ connect_db.php ไม่ได้สร้าง $conn ขึ้นมา หรือมีปัญหา
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: $conn object not found after including connect_db.php'
    ]);
    exit();
}
// *** สิ้นสุดส่วนที่แก้ไข ***

// ตัวแปรสำหรับรับค่าจาก Frontend
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20; // จำนวนรายการต่อหน้า
$searchHouseNo = isset($_GET['searchHouseNo']) ? trim($_GET['searchHouseNo']) : '';
$paymentStatus = isset($_GET['paymentStatus']) ? trim($_GET['paymentStatus']) : '';
$sortColumn = isset($_GET['sortColumn']) ? $_GET['sortColumn'] : 'created_at';
$sortOrder = isset($_GET['sortOrder']) ? strtoupper($_GET['sortOrder']) : 'DESC';

// ตรวจสอบค่าที่ไม่ถูกต้อง
if ($page < 1) $page = 1;
if ($limit < 1 || $limit > 100) $limit = 20; // จำกัดไม่ให้ดึงข้อมูลมากเกินไป เพื่อป้องกันการโหลดที่หนักเกินไป
if (!in_array($sortOrder, ['ASC', 'DESC'])) $sortOrder = 'DESC';

// White-list สำหรับคอลัมน์ที่สามารถเรียงลำดับได้ เพื่อป้องกัน SQL Injection
$allowedSortColumns = [
    'id', 'doc_id', 'payment_date', 'house_number', 'amount',
    'payment_status', 'created_at', 'period_year' , 'common_fee' , 'alley' , 'payment_status_desc' , 'month_name_start', 'month_name_start'
];
if (!in_array($sortColumn, $allowedSortColumns)) {
    $sortColumn = 'created_at'; // Default sort column
}

$offset = ($page - 1) * $limit;

$data = [];
$totalRecords = 0;

try {
    // ใช้ $conn ที่เชื่อมต่อแล้วจากไฟล์ connect_db.php
    // --- 1. ดึง Total Records สำหรับ Pagination ---
    $countSql = "SELECT COUNT(id) FROM v_ims_house_payment WHERE 1=1";
    $params = []; // Array สำหรับเก็บค่า Parameter ที่จะ bind

    if (!empty($searchHouseNo)) {
        $countSql .= " AND house_number LIKE ?";
        $params[] = '%' . $searchHouseNo . '%';
    }
    if (!empty($paymentStatus)) {
        $countSql .= " AND payment_status = ?";
        $params[] = $paymentStatus;
    }

    $stmt = $conn->prepare($countSql); // เปลี่ยนจาก $pdo->prepare เป็น $conn->prepare
    $stmt->execute($params);
    $totalRecords = $stmt->fetchColumn();

    // --- 2. ดึงข้อมูลรายการ ---
    $selectColumns = "id, doc_id, payment_date, house_number, amount, payment_status, picture_payment , created_at , area_size , common_fee , alley , payment_status_desc , month_name_start , month_name_to , period_year ,detail ";
    $sql = "SELECT $selectColumns FROM v_ims_house_payment WHERE 1=1";

    $dataParams = [];
    if (!empty($searchHouseNo)) {
        $sql .= " AND house_number LIKE ?";
        $dataParams[] = '%' . $searchHouseNo . '%';
    }
    if (!empty($paymentStatus)) {
        $sql .= " AND payment_status = ?";
        $dataParams[] = $paymentStatus;
    }

    $sql .= " ORDER BY " . $sortColumn . " " . $sortOrder;
    $sql .= " LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql); // เปลี่ยนจาก $pdo->prepare เป็น $conn->prepare

    $paramIndex = 0;
    foreach ($dataParams as $value) {
        $stmt->bindValue(++$paramIndex, $value, PDO::PARAM_STR);
    }
    $stmt->bindValue(++$paramIndex, $limit, PDO::PARAM_INT);
    $stmt->bindValue(++$paramIndex, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $data = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $data,
        'totalRecords' => $totalRecords,
        'currentPage' => $page,
        'totalPages' => ceil($totalRecords / $limit)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Query error: ' . $e->getMessage()
    ]);
}
?>