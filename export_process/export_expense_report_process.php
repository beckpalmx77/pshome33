<?php
include('../config/connect_db.php');

date_default_timezone_set('Asia/Bangkok');

$months = $_POST["month"] ?? [];
$year = $_POST["year"] ?? '';

if (empty($year)) {
    die("กรุณาเลือกปี");
}

if (!is_array($months) || count($months) == 0) {
    die("กรุณาเลือกเดือนอย่างน้อยหนึ่งเดือน");
}

$filename = "expense-" . implode('-', $months) . "-" . $year . "_" . date('Ymd_His') . ".csv";

// ตั้ง header สำหรับไฟล์ CSV
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Encoding: UTF-8');
header("Content-Disposition: attachment; filename=" . $filename);

$select_query_daily = "SELECT * FROM v_ims_expenses ";

// กรณีเลือก "ทุกเดือน"
if (in_array('all', $months)) {
    $select_where_daily = " WHERE exp_year = :year ";
    $params = [':year' => intval($year)];
} else {
    // กรณีเลือกหลายเดือน
    // สร้าง placeholder ? ตามจำนวนเดือน
    $placeholders = implode(',', array_fill(0, count($months), '?'));
    $select_where_daily = " WHERE exp_month IN ($placeholders) AND exp_year = ?";
    // รวมพารามิเตอร์เดือน และปี
    $params = array_map('intval', $months);
    $params[] = intval($year);
}

$select_group_order = " ORDER BY exp_year, exp_month";

$sql = $select_query_daily . $select_where_daily . $select_group_order;

try {
    $query = $conn->prepare($sql);
    $query->execute($params);
    $results = $query->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    die("Error query: " . $e->getMessage());
}

// สร้าง header CSV
$data = "วันที่ทำรายการ,เดือน,ปี,รายละเอียด,ประเภท,จำนวน,หน่วยนับ,จำนวนเงิน(บาท),สถานะการอนุมัติ\n";

if ($results) {
    foreach ($results as $result) {
        $approve_status_desc = $result->approve_status === "Y" ? "อนุมัติ" : "รอการอนุมัติ";

        $data .= "{$result->expense_date},";
        $data .= "{$result->month_name},";
        $data .= "{$result->exp_year},";
        $data .= "{$result->description},";
        $data .= "{$result->category_name},";
        $data .= "{$result->qty},";
        $data .= "{$result->unit_name},";
        $data .= "{$result->amount},";
        $data .= "{$approve_status_desc}\n";
    }
} else {
    $data .= "ไม่มีข้อมูลตามเงื่อนไขที่เลือก\n";
}

// แปลง encoding เป็น TIS-620 สำหรับ Excel ไทย
echo iconv("UTF-8", "TIS-620", $data);

exit();
?>
