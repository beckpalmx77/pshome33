<?php
include('../config/connect_db.php');

if($_POST['action'] == "UPDATE_SLIP") {
    $id = $_POST['payment_id'];

    if(isset($_FILES['new_slip']['name']) && $_FILES['new_slip']['name'] != "") {
        $file_name = time() . "_" . $_FILES['new_slip']['name'];

        $upload_dir = __DIR__ . '/../uploads/slips/'; // ใช้ __DIR__ เพื่อให้ Path ถูกต้องเสมอ
        $target = $upload_dir . $file_name;

        if(move_uploaded_file($_FILES['new_slip']['tmp_name'], $target)) {
            $sql = "UPDATE ims_house_payment SET picture_payment = :slip WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':slip', $file_name);
            $stmt->bindParam(':id', $id);
            if($stmt->execute()){
                echo "success";
            } else {
                echo "db_fail";
            }
        } else {
            echo "upload_fail";
        }
    }
}