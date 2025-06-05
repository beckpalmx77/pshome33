<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจาก POST
    $payment_date = $_POST['payment_date'];
    $house_number = $_POST['house_number'];
    $detail = $_POST['detail'];
    $payment_type = $_POST['payment_type'];
    $period_month_start = $_POST['period_month_start'];
    $period_month_to = $_POST['period_month_to'];
    $period_year = $_POST['period_year'];
    $amount = $_POST['amount'];
    $remark = $_POST['remark'];
    $picture_payment = $_FILES['picture_payment'];
    $payment_method = $_POST['payment_method'];

    //$create_by = $_SESSION['username'];

    $create_by = $_SESSION['first_name'] . " " . $_SESSION['last_name'];

    // ตรวจสอบค่า amount ว่าเป็นตัวเลขหรือไม่
    if (!is_numeric($amount) || $amount <= 0) {
        echo 0; // ข้อมูล amount ไม่ถูกต้อง
        exit;
    }

    // สร้าง runno และ doc_id
    $field = "runno";
    $table = "ims_house_payment";
    $cond = " WHERE house_number = '" . $house_number . "' AND period_year = '" . $period_year . "'";
    $runno = LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond);
    $doc_id = "P-" . $house_number . "-" . $period_year . "-" . sprintf('%03s', $runno);

    // บันทึก log ไฟล์ text
    $txt = $doc_id . " | " . $detail . " | " . $house_number . " | payment_type = " . $payment_type . " | "
        . $period_month_start . " | " . $period_month_to . " | "
        . $period_year . " | " . $amount . " | " . $remark . " | "
        . $runno;

    /*
        $my_file = fopen("doc_p.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);
    */
    // ฟังก์ชันตรวจสอบนามสกุลไฟล์ภาพที่อนุญาต
    function isAllowedFileType($filename)
    {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $allowed);
    }

    if ($picture_payment['error'] == 0) {
        // ตรวจสอบชนิดไฟล์
        if (!isAllowedFileType($picture_payment['name'])) {
            echo 0; // ไฟล์ประเภทไม่ถูกต้อง
            exit;
        }
        // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
        if ($picture_payment['size'] > 5 * 1024 * 1024) {
            echo 0; // ไฟล์ใหญ่เกินไป
            exit;
        }

        $upload_dir = '../uploads/slips/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name = time() . "_" . basename($picture_payment['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($picture_payment['tmp_name'], $file_path)) {
            // Insert พร้อมไฟล์ภาพและ payment_type
            $ins_str = "INSERT INTO ims_house_payment (doc_id, payment_date, house_number, detail, runno, period_month_start, period_month_to, period_year, amount, picture_payment, remark, payment_type, payment_method,create_by) 
                VALUES (:doc_id, :payment_date, :house_number, :detail, :runno, :period_month_start, :period_month_to, :period_year, :amount, :picture_payment, :remark, :payment_type, :payment_method,:create_by)";
            $stmt = $conn->prepare($ins_str);

            $stmt->bindParam(':doc_id', $doc_id);
            $stmt->bindParam(':payment_date', $payment_date);
            $stmt->bindParam(':house_number', $house_number);
            $stmt->bindParam(':detail', $detail);
            $stmt->bindParam(':runno', $runno);
            $stmt->bindParam(':period_month_start', $period_month_start);
            $stmt->bindParam(':period_month_to', $period_month_to);
            $stmt->bindParam(':period_year', $period_year);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':picture_payment', $file_name);
            $stmt->bindParam(':remark', $remark);
            $stmt->bindParam(':payment_type', $payment_type);
            $stmt->bindParam(':payment_method', $payment_method);
            $stmt->bindParam(':create_by', $create_by);
            echo $stmt->execute() ? 1 : 0;
        } else {
            echo 0; // อัปโหลดไฟล์ล้มเหลว
        }
    } else {
        // ไม่มีไฟล์อัปโหลด - insert พร้อม payment_type
        $ins_str = "INSERT INTO ims_house_payment (doc_id, payment_date, house_number, detail, period_month_start, period_month_to, period_year, amount, remark, runno, payment_type, payment_method, create_by) 
                    VALUES (:doc_id, :payment_date, :house_number, :detail, :period_month_start, :period_month_to, :period_year, :amount, :remark, :runno, :payment_type, :payment_method, :create_by)";
        $stmt = $conn->prepare($ins_str);

        $stmt->bindParam(':doc_id', $doc_id);
        $stmt->bindParam(':payment_date', $payment_date);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->bindParam(':detail', $detail);
        $stmt->bindParam(':period_month_start', $period_month_start);
        $stmt->bindParam(':period_month_to', $period_month_to);
        $stmt->bindParam(':period_year', $period_year);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':remark', $remark);
        $stmt->bindParam(':runno', $runno);
        $stmt->bindParam(':payment_type', $payment_type);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':create_by', $create_by);

        echo $stmt->execute() ? 1 : 0;
    }
}
