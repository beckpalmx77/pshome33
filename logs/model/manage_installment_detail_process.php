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

    // ค้นหา installment_id จาก id ก่อน
    $sql_find = "SELECT installment_id FROM ims_installment WHERE id = :id";
    $stmt = $conn->prepare($sql_find);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $installment_id = $stmt->fetchColumn(); // เก็บ installment_id

    if ($installment_id) {
        try {
            // ลบจาก ims_installment
            $sql = "DELETE FROM ims_installment WHERE installment_id = :installment_id";
            $query = $conn->prepare($sql);
            $query->bindParam(':installment_id', $installment_id, PDO::PARAM_STR);
            $query->execute();

            // ลบจาก ims_installment_detail
            $sql = "DELETE FROM ims_installment_detail WHERE installment_id = :installment_id";
            $query = $conn->prepare($sql);
            $query->bindParam(':installment_id', $installment_id, PDO::PARAM_STR);
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
