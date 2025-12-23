<?php
// ไฟล์: export_meeting_excel_process.php
session_start();

// 1. เชื่อมต่อฐานข้อมูล (ไฟล์นี้ต้องสร้างตัวแปร $conn ที่เป็น PDO Object)
include("../config/connect_db.php");

// 2. ตรวจสอบสิทธิ์การใช้งาน
if (empty($_SESSION['alogin'])) {
    exit("Access Denied: กรุณาเข้าสู่ระบบก่อนใช้งาน");
}

// 3. รับค่าปีที่ส่งมาจาก Frontend
$meeting_year = isset($_GET['meeting_year']) ? $_GET['meeting_year'] : '';

// 4. ตั้งชื่อไฟล์
$filename = "meeting_report_" . date('Ymd') . ".csv";

// 5. ส่ง Header
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 6. เปิด Output Stream
$output = fopen('php://output', 'w');

// *** ใส่ BOM เพื่อให้ Excel เปิดภาษาไทยได้ถูกต้อง ***
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 7. เขียนหัวตาราง
fputcsv($output, array('บ้านเลขที่', 'ซอย', 'ปี', 'วันที่ประชุม', 'ชื่อการประชุม', 'ผู้เข้าร่วม', 'สถานะ'));

try {
    // 8. สร้าง SQL Query
    // หมายเหตุ: เปลี่ยนชื่อตาราง 'v_ims_house_meeting' ให้ตรงกับ DB ของคุณ
    $sql = "SELECT house_number, alley, meeting_year, meeting_date, meeting_name, attendance_name, meeting_status 
            FROM v_ims_house_meeting 
            WHERE 1=1 ";

    // ตัวแปรเก็บ Parameter สำหรับผูกค่า (Binding)
    $params = array();

    // ถ้ามีการเลือกปี ให้เติม SQL และเก็บค่าลง Array
    if (!empty($meeting_year)) {
        $sql .= " AND meeting_year = :meeting_year ";
        $params[':meeting_year'] = $meeting_year;
    }

    // เรียงลำดับข้อมูล
    $sql .= " ORDER BY house_number ASC, meeting_date DESC";

    // 9. Prepare และ Execute (PDO)
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // 10. วนลูปดึงข้อมูล
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        // แปลงสถานะ Y/N
        $status_text = ($row['meeting_status'] == 'Y') ? 'เข้าร่วม' : 'ไม่เข้าร่วม';

        // เตรียมข้อมูลแต่ละบรรทัด
        $lineData = array(
            $row['house_number'],
            $row['alley'],
            $row['meeting_year'],
            $row['meeting_date'],
            $row['meeting_name'],
            $row['attendance_name'],
            $status_text
        );

        // เขียนลงไฟล์
        fputcsv($output, $lineData);
    }

} catch (PDOException $e) {
    // กรณี Error ให้เขียนลงไฟล์ CSV
    fputcsv($output, array('Error executing query: ' . $e->getMessage()));
}

// 11. ปิดการเชื่อมต่อ (PDO จะปิดเองเมื่อ script จบ แต่สั่ง null เพื่อความชัดเจนได้)
$conn = null;
fclose($output);
exit();
?>