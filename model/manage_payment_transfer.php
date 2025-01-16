<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    $field = "runno";
    $table = "ims_house_payment";
    $cond = " WHERE house_number = '" . $house_number . "' AND period_year = '" . $period_year . "'";

    $runno = LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond);

    $doc_id = "P-" . $house_number . "-" . $period_year . "-" . sprintf('%03s', $runno);


        $txt = $doc_id . " | " . $detail . " | "  . $house_number . " | Period Number = " . $payment_type . " | " .  $period_month_start . " | " . $period_month_to . " | "
            . $period_year . " | " . $amount . " | " . $remark . " | "
            . $runno ;

/*
        $my_file = fopen("doc_p.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);
*/

    // หากมีการอัปโหลดไฟล์
    if ($picture_payment['error'] == 0) {
        $upload_dir = '../uploads/slips/';
        $file_name = time() . "_" . basename($picture_payment['name']);
        $file_path = $upload_dir . $file_name;

        // อัปโหลดไฟล์
        if (move_uploaded_file($picture_payment['tmp_name'], $file_path)) {

            $ins_str = "INSERT INTO ims_house_payment (doc_id, payment_date, house_number, detail,runno,period_month_start,period_month_to,period_year,amount,picture_payment,remark,payment_type) 
            VALUES (:doc_id, :payment_date, :house_number,:detail, :runno,:period_month_start,:period_month_to,:period_year,:amount,:picture_payment,:remark,:payment_type)";
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

            if ($stmt->execute()) {
                echo 1; // สำเร็จ
            } else {
                echo 0; // เกิดข้อผิดพลาดในฐานข้อมูล
            }
        } else {
            echo 0; // เกิดข้อผิดพลาดในการอัปโหลดไฟล์
        }
    } else {
        // กรณีไม่มีไฟล์อัปโหลด
        $ins_str = "INSERT INTO ims_house_payment (doc_id, payment_date, house_number, detail, period_month_start, period_month_to, period_year, amount, remark, runno) 
                                    VALUES (:doc_id, :payment_date, :house_number, :detail, :period_month_start, :period_month_to, :period_year, :amount, :remark, :runno)";

        // บันทึกข้อมูลลงในฐานข้อมูล
        $stmt = $conn->prepare($ins_str);
        // Bind ข้อมูล
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

        if ($stmt->execute()) {
            echo 1; // สำเร็จ
        } else {
            echo 0; // เกิดข้อผิดพลาดในฐานข้อมูล
        }
    }
}

