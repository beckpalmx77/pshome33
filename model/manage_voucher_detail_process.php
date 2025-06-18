<?php
session_start();
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');


// ลบข้อมูล
if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];

    // ค้นหา doc_no จาก id ก่อน
    $sql_find = "SELECT doc_no FROM ims_payment_voucher WHERE id = :id";
    $stmt = $conn->prepare($sql_find);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $doc_no = $stmt->fetchColumn(); // เก็บ doc_no

    if ($doc_no) {
        try {
            // ลบจาก ims_payment_voucher
            $sql = "DELETE FROM ims_payment_voucher WHERE doc_no = :doc_no";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            $query->execute();

            // ลบจาก ims_payment_voucher_items
            $sql = "DELETE FROM ims_payment_voucher_items WHERE doc_no = :doc_no";
            $query = $conn->prepare($sql);
            $query->bindParam(':doc_no', $doc_no, PDO::PARAM_STR);
            $query->execute();

            echo $del_success; // ควรแน่ใจว่า $del_success ถูกกำหนดไว้แล้วก่อนหน้านี้
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    } else {
        echo "ไม่พบข้อมูล";
    }

    exit;
}
