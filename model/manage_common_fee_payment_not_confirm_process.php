<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');

if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"];
    $return_arr = [];

    $sql_get = "SELECT * FROM v_ims_house_payment WHERE id = :id";
    $stmt = $conn->prepare($sql_get);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $return_arr[] = [
            "id" => $result['id'],
            "doc_id" => $result['doc_id'],
            "house_number" => $result['house_number'],
            "payment_date" => $result['payment_date'],
            "amount" => $result['amount'],
            "period_month_start" => $result['period_month_start'],
            "period_month_to" => $result['period_month_to'],
            "month_name_start" => $result['month_name_start'],
            "month_name_to" => $result['month_name_to'],
            "period_year" => $result['period_year'],
            "detail" => $result['detail'],
            "contact_name" => $result['contact_name'],
            "house_name" => $result['house_name'],
            "phone_number" => $result['phone_number'],
            "picture_payment" => $result['picture_payment'],
            "payment_status" => $result['payment_status'],
            "payment_type" => $result['payment_type'],
            "remark" => $result['remark'],
            "created_at" => $result['created_at'],
            "updated_at" => $result['updated_at'],
            "alley" => $result['alley']
        ];
    }

    echo json_encode($return_arr);
    exit;
}

// อัปเดตสถานะการชำระเงิน
if ($_POST["action"] === 'UPDATE') {

    if (!empty($_POST["house_number"])) {
        $id = $_POST["id"];
        $payment_status = ($_POST["payment_status"] === "Y") ? "Y" : "N";

        $sql_find = "SELECT COUNT(*) FROM ims_house_payment WHERE id = :id";
        $stmt = $conn->prepare($sql_find);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $nRows = $stmt->fetchColumn();

        if ($nRows > 0) {
            $sql_update = "UPDATE ims_house_payment SET payment_status = :payment_status WHERE id = :id";
            $query = $conn->prepare($sql_update);
            $query->bindParam(':payment_status', $payment_status, PDO::PARAM_STR);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            echo $save_success;
        }
    }

    exit;
}


// ลบข้อมูล
if ($_POST["action"] === 'DELETE') {
    $id = $_POST["id"];
    $sql_find = "SELECT COUNT(*) FROM ims_house_payment WHERE id = :id";
    $stmt = $conn->prepare($sql_find);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $nRows = $stmt->fetchColumn();

    if ($nRows > 0) {
        try {
            $sql = "DELETE FROM ims_house_payment WHERE id = :id";
            $query = $conn->prepare($sql);
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();
            echo $del_success;
        } catch (Exception $e) {
            echo 'Message: ' . $e->getMessage();
        }
    }

    exit;
}


if ($_POST["action"] === 'GET_COMMON_FEE') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    //$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $columnSortOrder = 'desc'; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    $searchArray = array();

## Search
    $searchQuery = " ";

    if ($searchValue != '') {
        $searchQuery = " AND (house_number LIKE :house_number AND payment_status_desc LIKE :payment_status_desc) ";
        $searchArray = array(
            'house_number' => "%$searchValue%",
            'payment_status_desc' => "%$searchValue%"
        );

        // รวมข้อมูลทั้ง searchQuery และ searchArray
/*
        $txt = "Search Query:\n" . $searchQuery . "\n\nSearch Array:\n" . print_r($searchArray, true);
        // เขียนลงไฟล์
        $my_file = fopen("device_0.txt", "w") or die("Unable to open file!");
        fwrite($my_file, $txt);
        fclose($my_file);
*/

    }


    $where_house_number = " ";
    if ($_SESSION['account_type'] === "user") {
        $where_house_number = " AND house_number = '" . $_SESSION['house_number'] . "'";
    }

/*
    $txt = $where_house_number;
    $my_file = fopen("device_1.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/

## Total number of records without filtering
    $sql_getdata = "SELECT COUNT(*) AS allcount FROM v_ims_house_payment WHERE payment_status = 'N' " . $where_house_number;
    $stmt = $conn->prepare($sql_getdata);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

/*
    $txt = $sql_getdata;
    $my_file = fopen("device_a.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/

## Total number of records with filtering

    $sql_getdata = "SELECT COUNT(*) AS allcount FROM v_ims_house_payment WHERE payment_status = 'N' " . $searchQuery . $where_house_number;

    $stmt = $conn->prepare($sql_getdata);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

/*
    $txt = $sql_getdata;
    $my_file = fopen("device_b.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/

## Fetch records
    $sql_getdata = "SELECT * FROM v_ims_house_payment WHERE payment_status = 'N' " . $searchQuery . $where_house_number
        . " ORDER BY id DESC " . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_getdata);

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

/*
    $txt = $sql_getdata . "  " . $row . "," . $rowperpage;
    $my_file = fopen("device_c.txt", "w") or die("Unable to open file!");
    fwrite($my_file, $txt);
    fclose($my_file);
*/

    foreach ($empRecords as $row) {

        if ($_POST['sub_action'] === "GET_MASTER") {
            $status = $row['payment_status'];
            $payment_status_desc = ($status === 'Y') ? "ชำระเรียบร้อยแล้ว" : "ยังไม่ยืนยันการชำระ";
            $color = ($status === 'Y') ? "green" : "gray";
            $print_disabled = ($status === 'Y') ? "" : "disabled";

            // ปุ่ม Update
            $update_button = ($_SESSION['account_type'] === "user")
                ? "<button type='button' class='btn btn-info btn-xs update' disabled>Update</button>"
                : "<button type='button' name='update' id='{$row['id']}' class='btn btn-info btn-xs update'>Update</button>";

            // ปุ่ม Delete
            $delete_button = ($_SESSION['account_type'] === "user")
                ? "<button type='button' class='btn btn-danger btn-xs delete' disabled>Delete</button>"
                : "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete'>Delete</button>";

            $data[] = [
                "id" => $row['id'],
                "doc_id" => $row['doc_id'],
                "payment_date" => $row['payment_date'],
                "detail" => $row['detail'],
                "house_number" => $row['house_number'],
                "alley" => $row['alley'],
                "contact_name" => $row['contact_name'],
                "phone_number" => $row['phone_number'],
                "payment_type" => $row['payment_type'],
                "period_month_start" => $row['period_month_start'],
                "period_month_to" => $row['period_month_to'],
                "month_name_start" => $row['month_name_start'],
                "month_name_to" => $row['month_name_to'],
                "month_name_period" => $row['month_name_start'] . " - " . $row['month_name_to'],
                "period_year" => $row['period_year'],
                "area_size" => $row['area_size'],
                "garbage_collection_fee" => $row['garbage_collection_fee'],
                "common_fee" => $row['common_fee'],
                "amount" => $row['amount'],
                "payment_status" => $row['payment_status'],
                "line_picture_profile" => $row['line_picture_profile_show'],
                "payment_status_desc" => "<span style='color: $color;'>$payment_status_desc</span>",
                "print" => "<button type='button' name='print' id='{$row['id']}' class='btn btn-outline-success btn-xs print' $print_disabled>Print</button>",
                "slip" => "<button type='button' name='slip' id='{$row['id']}' class='btn btn-info btn-xs slip'>Slip</button>",
                "update" => $update_button,
                "delete" => $delete_button,
                "remark" => $row['remark']
            ];
        } else {
            $data[] = [
                "id" => $row['id'],
                "house_number" => $row['house_number'],
                "contact_name" => $row['contact_name'],
                "select" => "<button type='button' name='select' id='{$row['house_number']}@{$row['contact_name']}' class='btn btn-outline-success btn-xs select'>select <i class='fa fa-check'></i></button>"
            ];
        }
    }


## Response Return Value
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);

}

