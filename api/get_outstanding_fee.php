<?php
/**
 * API สำหรับตรวจสอบยอดค้างชำระค่าส่วนกลาง (Query Outstanding Common Fee Balance API)
 * สำหรับให้ Application ภายนอกเรียกใช้งานผ่าน Parameter
 */

header('Content-Type: application/json; charset=utf-8');

// 1. นำเข้าไฟล์ตั้งค่าฐานข้อมูล
include(__DIR__ . '/../config/connect_db.php');

// 2. กำหนดรหัส API Key สำหรับความปลอดภัย (สามารถปรับเปลี่ยนคีย์นี้ตามที่ต้องการ)
define('API_KEY', 'PSHOME33_SECURE_API_KEY_2026');

// 3. ตรวจสอบสิทธิ์การเข้าใช้งาน (API Key Authentication)
$received_key = '';
if (isset($_GET['api_key'])) {
    $received_key = $_GET['api_key'];
} elseif (isset($_SERVER['HTTP_X_API_KEY'])) {
    $received_key = $_SERVER['HTTP_X_API_KEY'];
}

if (empty($received_key) || $received_key !== API_KEY) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized: Invalid or missing API Key.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 4. รับค่า Parameter ภายนอก
$house_number = isset($_GET['house_number']) ? trim($_GET['house_number']) : '';
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

if (empty($house_number)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Bad Request: Missing required parameter "house_number".'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 5. ค้นหาข้อมูลบ้านและอัตราค่าส่วนกลางรายเดือน
    $sql_house = "SELECT m.house_number, m.common_fee, h.contact_name, h.phone_number 
                  FROM ims_house_master AS m 
                  LEFT JOIN ims_house AS h ON m.house_number = h.house_number
                  WHERE m.house_number = :house_number AND m.status = 'Y'";
    $stmt_house = $conn->prepare($sql_house);
    $stmt_house->bindParam(':house_number', $house_number, PDO::PARAM_STR);
    $stmt_house->execute();
    $house_info = $stmt_house->fetch(PDO::FETCH_ASSOC);

    if (!$house_info) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Not Found: House number not found or inactive.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $monthly_rate = (float)$house_info['common_fee'];

    // 6. ดึงข้อมูลการชำระเงินที่ได้รับอนุมัติแล้วของปีที่เลือก
    $sql_pay = "SELECT period_month_start, period_month_to 
                FROM ims_house_payment 
                WHERE house_number = :house_number 
                  AND period_year = :year 
                  AND payment_status = 'Y'";
    $stmt_pay = $conn->prepare($sql_pay);
    $stmt_pay->bindParam(':house_number', $house_number, PDO::PARAM_STR);
    $stmt_pay->bindParam(':year', $year, PDO::PARAM_INT);
    $stmt_pay->execute();
    $payments = $stmt_pay->fetchAll(PDO::FETCH_ASSOC);

    // 7. ตรวจสอบหาเดือนที่ค้างชำระ
    $thai_months = [
        1 => "มกราคม", 2 => "กุมภาพันธ์", 3 => "มีนาคม", 4 => "เมษายน", 
        5 => "พฤษภาคม", 6 => "มิถุนายน", 7 => "กรกฎาคม", 8 => "สิงหาคม", 
        9 => "กันยายน", 10 => "ตุลาคม", 11 => "พฤศจิกายน", 12 => "ธันวาคม"
    ];

    $unpaid_months_all = [];
    $unpaid_months_up_to_now = [];
    $current_month = (int)date('n');
    $current_year = (int)date('Y');

    for ($m = 1; $m <= 12; $m++) {
        $is_paid = false;
        foreach ($payments as $p) {
            if ($m >= (int)$p['period_month_start'] && $m <= (int)$p['period_month_to']) {
                $is_paid = true;
                break;
            }
        }

        if (!$is_paid) {
            $unpaid_months_all[] = [
                'month_no' => $m,
                'month_name' => $thai_months[$m]
            ];
            
            // กรองเฉพาะเดือนที่ผ่านมาแล้วหรือเดือนปัจจุบันในปีนี้
            if ($year < $current_year || ($year === $current_year && $m <= $current_month)) {
                $unpaid_months_up_to_now[] = [
                    'month_no' => $m,
                    'month_name' => $thai_months[$m]
                ];
            }
        }
    }

    // 8. สรุปยอดค้างชำระ
    $outstanding_months_up_to_now_count = count($unpaid_months_up_to_now);
    $outstanding_amount_up_to_now = $outstanding_months_up_to_now_count * $monthly_rate;

    $outstanding_months_full_year_count = count($unpaid_months_all);
    $outstanding_amount_full_year = $outstanding_months_full_year_count * $monthly_rate;

    // 9. ส่งข้อมูลกลับแบบ JSON
    echo json_encode([
        'status' => 'success',
        'data' => [
            'house_number' => $house_info['house_number'],
            'owner_name' => $house_info['contact_name'] ?? 'ไม่ระบุชื่อ',
            'phone_number' => $house_info['phone_number'] ?? 'ไม่ระบุเบอร์โทร',
            'monthly_rate' => $monthly_rate,
            'query_year' => $year,
            'current_date' => date('Y-m-d'),
            
            // ยอดค้างชำระ ณ ปัจจุบัน (ไม่รวมเดือนในอนาคตของปีนี้)
            'outstanding_current' => [
                'unpaid_count' => $outstanding_months_up_to_now_count,
                'unpaid_amount' => $outstanding_amount_up_to_now,
                'unpaid_months' => $unpaid_months_up_to_now
            ],
            
            // ยอดค้างชำระทั้งหมดของปีที่เลือก (รวมทุกเดือนที่ค้างใน 1 ปี)
            'outstanding_full_year' => [
                'unpaid_count' => $outstanding_months_full_year_count,
                'unpaid_amount' => $outstanding_amount_full_year,
                'unpaid_months' => $unpaid_months_all
            ]
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal Server Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
