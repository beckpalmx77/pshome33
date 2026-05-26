<?php
session_start();
error_reporting(0);
include('../config/connect_db.php');

if ($_POST["action"] === 'GET_MAPPINGS') {
    $type = $_POST['type']; // 'expense' or 'income'
    $data = [];

    if ($type === 'expense') {
        $sql = "SELECT pgroup_id as id, pgroup_name as name, acc_code FROM ims_pgroup ORDER BY pgroup_id ASC";
    } else {
        $sql = "SELECT category_id as id, category_name as name, acc_code FROM ims_category ORDER BY category_id ASC";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($records as $r) {
        $data[] = [
            "id" => $r['id'],
            "name" => $r['name'],
            "acc_code" => $r['acc_code'] ?? '',
            "action" => "<button type='button' class='btn btn-primary btn-xs edit-mapping' data-id='".$r['id']."' data-name='".$r['name']."' data-acc='".$r['acc_code']."'>ผูกบัญชี</button>"
        ];
    }

    echo json_encode(["aaData" => $data]);
    exit;
}

if ($_POST["action"] === 'UPDATE_MAPPING') {
    $type = $_POST['type'];
    $id = $_POST['id'];
    $acc_code = $_POST['acc_code'];

    if ($type === 'expense') {
        $sql = "UPDATE ims_pgroup SET acc_code = :acc WHERE pgroup_id = :id";
    } else {
        $sql = "UPDATE ims_category SET acc_code = :acc WHERE category_id = :id";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':acc', $acc_code);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo "บันทึกการผูกบัญชีสำเร็จ";
    exit;
}
