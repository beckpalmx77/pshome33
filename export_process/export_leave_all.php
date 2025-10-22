<?php
include('../config/connect_db.php');
date_default_timezone_set('Asia/Bangkok');

// ตั้งชื่อไฟล์ CSV
$filename = "leave_and_holiday_data-" . date('Ymd-His') . ".csv";

// ตั้งค่า Header สำหรับการดาวน์โหลดไฟล์ CSV
@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

// รับค่าจากแบบฟอร์ม
$doc_date_start = $_POST["doc_date_start"];
$doc_date_to = $_POST["doc_date_to"];
$employeeSelect = $_POST["employeeSelect"];

// แปลงวันที่จาก DD-MM-YYYY เป็น YYYY-MM-DD
$doc_date_start = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
$doc_date_to = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

// สร้างเงื่อนไขการกรองข้อมูล
$search_Query = " WHERE STR_TO_DATE(doc_date, '%d-%m-%Y') BETWEEN '$doc_date_start' AND '$doc_date_to'";
if (!empty($employeeSelect) && $employeeSelect !== '-') {
    $search_Query .= " AND emp_id = '$employeeSelect'";
}

// คำสั่ง SQL สำหรับข้อมูลการลา
$select_leave_query = "SELECT * FROM v_dleave_event" . $search_Query . " ORDER BY STR_TO_DATE(doc_date, '%d-%m-%Y') ASC";

// คำสั่ง SQL สำหรับข้อมูลวันหยุด
$select_holiday_query = "SELECT * FROM vdholiday_event" . $search_Query . " ORDER BY STR_TO_DATE(doc_date, '%d-%m-%Y') ASC";

// เตรียมหัวตาราง CSV
$data = "ข้อมูลการลา พนักงาน\n";
$data .= "#,วันที่เอกสาร,รหัสพนักงาน,ชื่อพนักงาน,หน่วยงาน,ประเภทการลา,วันที่ลาเริ่มต้น,วันที่ลาสิ้นสุด,จำนวนวัน,จำนวนชั่วโมง,สถานะ,หมายเหตุ\n";

/*
$my_file = fopen("exp_leave.txt", "w") or die("Unable to open file!");
fwrite($my_file, $select_leave_query);
fclose($my_file);
*/

// ดึงข้อมูลการลา
$query_leave = $conn->prepare($select_leave_query);
$query_leave->execute();
$results_leave = $query_leave->fetchAll(PDO::FETCH_OBJ);

$i = 1;

if ($query_leave->rowCount() >= 1) {
    foreach ($results_leave as $result) {

        switch ($result->status) {
            case 'A':
                $status_desc = "อนุมัติ";
                break;
            case 'R':
                $status_desc = "ไม่อนุมัติ";
                break;
            case 'N':
                $status_desc = "รอพิจารณา";
        }
        $data .= $i++ . ",";
        $data .= $result->doc_date . ",";
        $data .= $result->emp_id . ",";
        $data .= $result->f_name . " " . $result->l_name . ",";
        $data .= $result->department_id . ",";
        $data .= $result->leave_type_detail . ",";
        $data .= $result->date_leave_start . ",";
        $data .= $result->date_leave_to . ",";
        $data .= $result->leave_day . ",";
        $data .= $result->leave_hour . ",";
        $data .= $status_desc . ",";
        $data .= $result->remark . "\n";
    }
}

// เตรียมหัวตาราง CSV
$data .= "\n";
$data .= "แสดงข้อมูลใช้วันหยุด พนักงาน\n";
$data .= "#,วันที่เอกสาร,รหัสพนักงาน,ชื่อพนักงาน,หน่วยงาน,ประเภทการลา,วันที่ลาเริ่มต้น,วันที่ลาสิ้นสุด,จำนวนวัน,จำนวนชั่วโมง,สถานะ,หมายเหตุ\n";

// ดึงข้อมูลการใช้วันหยุดนักขัตฤกษ์
$query_leave = $conn->prepare($select_holiday_query);
$query_leave->execute();
$results_leave = $query_leave->fetchAll(PDO::FETCH_OBJ);

$i = 1;

if ($query_leave->rowCount() >= 1) {
    foreach ($results_leave as $result) {

        switch ($result->status) {
            case 'A':
                $status_desc = "อนุมัติ";
                break;
            case 'R':
                $status_desc = "ไม่อนุมัติ";
                break;
            case 'N':
                $status_desc = "รอพิจารณา";
        }
        $data .= $i++ . ",";
        $data .= $result->doc_date . ",";
        $data .= $result->emp_id . ",";
        $data .= $result->f_name . " " . $result->l_name . ",";
        $data .= $result->department_id . ",";
        $data .= $result->leave_type_detail . ",";
        $data .= $result->date_leave_start . ",";
        $data .= $result->date_leave_to . ",";
        $data .= $result->leave_day . ",";
        $data .= $result->leave_hour . ",";
        $data .= $status_desc . ",";
        $data .= $result->remark . "\n";
    }
}


// แปลง encoding เป็น TIS-620 สำหรับภาษาไทย
$data = iconv("utf-8", "tis-620", $data);

// ส่งข้อมูลออกไปยังไฟล์ CSV
echo $data;
exit();

