<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');



if ($_POST["action"] === 'GET_HOUSE_PAYMENT_DETAIL') {

    ## Read value
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value
    $searchArray = array();

    $payment_date = $_POST['payment_date'];

## Search
    $searchQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND (detail LIKE :detail or
        house_number LIKE :house_number ) ";
        $searchArray = array(
            'detail' => "%$searchValue%",
            'house_number' => "%$searchValue%",
        );
    }

## Total number of records without filtering
    $sql_get_all = "SELECT COUNT(*) AS allcount FROM v_ims_house_payment WHERE payment_date = '" . $payment_date . "'";
    $stmt = $conn->prepare($sql_get_all);
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

## Total number of records with filtering
    $sql_get_filter = "SELECT COUNT(*) AS allcount FROM v_ims_house_payment WHERE payment_date = '" . $payment_date . "' " . $searchQuery;
    $stmt = $conn->prepare($sql_get_filter);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

## Fetch records
    $sql_get_load = "SELECT * FROM v_ims_house_payment WHERE payment_date = '" . $payment_date . "' " . $searchQuery
        . " LIMIT :limit,:offset";

    $stmt = $conn->prepare($sql_get_load);

// Bind values
    foreach ($searchArray as $key => $search) {
        $stmt->bindValue(':' . $key, $search, PDO::PARAM_STR);
    }

    $stmt->bindValue(':limit', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();
    $data = array();

    $isUser = $_SESSION['account_type'] === "user";
    $isManager = $_SESSION['account_type'] === "manager";
    $isMaster = $_POST['sub_action'] === "GET_MASTER";

    $statusMeta = [
        'Y' => ['desc' => "ชำระเรียบร้อยแล้ว", 'color' => 'green', 'can_print' => true],
        'N' => ['desc' => "ยังไม่ยืนยันการชำระ", 'color' => 'gray', 'can_print' => false],
    ];

    foreach ($empRecords as $row) {
        if ($isMaster) {
            $status = $row['payment_status'];
            $meta = $statusMeta[$status] ?? ['desc' => '-', 'color' => 'gray', 'can_print' => false];

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
                "line_picture_profile" => "<img src='" . ($row['line_picture_profile_show'] ?: 'img/icon/none_img.png') . "' alt='image' style='width: 50px; height: auto;'>",
                "payment_status_desc" => "<span style='color: {$meta['color']}'>{$meta['desc']}</span>",
                "print" => "<button type='button' name='print' id='{$row['id']}' class='btn btn-outline-success btn-xs print' " . ($meta['can_print'] ? "" : "disabled") . ">Print</button>",
                "slip" => "<button type='button' name='slip' id='{$row['id']}' class='btn btn-info btn-xs slip'>Slip</button>",
                "update" => $isUser ? "<button type='button' class='btn btn-info btn-xs update' disabled>Update</button>"
                    : "<button type='button' name='update' id='{$row['id']}' class='btn btn-info btn-xs update'>Update</button>",
                "delete" => $isUser || $isManager ? "<button type='button' class='btn btn-danger btn-xs delete' disabled>Delete</button>"
                    : "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete'>Delete</button>",
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

    ## Response Return Value for DataTable
    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
}

