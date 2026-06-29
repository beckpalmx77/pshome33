<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');
include('../util/month_util.php');

header('Content-Type: application/json');

// -----------------------------
// GET MASTER RECORD BY ID
// -----------------------------
if (isset($_POST["action"]) && $_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM v_ims_payroll WHERE id = ?");
    $stmt->execute([$id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $return_arr = [];
    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'],
            "doc_no" => $result['doc_no'],
            "doc_date" => $result['doc_date'],
            "emp_id" => $result['emp_id'],
            "f_name" => $result['f_name'],
            "l_name" => $result['l_name'],
            "payroll_month" => $result['payroll_month'],
            "payroll_year" => $result['payroll_year'],
            "salary_type" => $result['salary_type'],
            "salary" => $result['salary'],
            "payment_method" => $result['payment_method'],
            "bank_no" => $result['bank_no'],
            "employee_fullname" => $result['employee_fullname'],
            "total_amount" => $result['total_amount'],
            "status" => $result['status']
        );
    }

    echo json_encode($return_arr);
    exit;
}

// -----------------------------
// GET DETAIL RECORD BY DOC_NO
// -----------------------------
if ($_GET["action"] === 'GET_DATA_DETAIL') {

    $doc_no = $_GET['doc_no'] ?? '';

    // เขียน log doc_no ลงไฟล์
    //file_put_contents('debug_log.txt', "doc_no: " . $doc_no . "\n", FILE_APPEND);

    if (empty($doc_no)) {
        // เขียน log กรณี doc_no ว่าง
        //file_put_contents('debug_log.txt', "Empty doc_no\n", FILE_APPEND);
        echo json_encode([]);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT *
            FROM ims_payroll_detail
            WHERE doc_no = ?
            ORDER BY CASE icd_type_sign WHEN '+' THEN 1 WHEN '-' THEN 2 ELSE 3 END ASC, icd_type_id ASC
        ");
        $stmt->execute([$doc_no]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // เขียน log ผลลัพธ์ที่ได้ (json)
        //file_put_contents('debug_log.txt', "Results: " . json_encode($results) . "\n", FILE_APPEND);

        echo json_encode($results);
    } catch (PDOException $e) {
        // เขียน log error
        //file_put_contents('debug_log.txt', "PDO Error: " . $e->getMessage() . "\n", FILE_APPEND);

        echo json_encode(['error' => $e->getMessage()]);
    }

    exit;
}


// -----------------------------
// DATATABLE SERVER-SIDE LOAD
// -----------------------------
if (isset($_POST["action"]) && $_POST["action"] === 'GET_PAYROLL') {

    $draw = $_POST['draw'] ?? 1;
    $row = $_POST['start'] ?? 0;
    $rowperpage = $_POST['length'] ?? 10;
    $columnIndex = $_POST['order'][0]['column'] ?? 0;
    $columnName = $_POST['columns'][$columnIndex]['data'] ?? 'id';
    $columnSortOrder = $_POST['order'][0]['dir'] ?? 'DESC';
    $searchValue = $_POST['search']['value'] ?? '';

    $searchArray = [];
    $searchQuery = "";
    if (!empty($searchValue)) {
        $searchQuery = " AND (employee_fullname LIKE :employee_fullname) ";
        $searchArray = ['employee_fullname' => "%$searchValue%"];
    }

    // Total records (no filter)
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_payroll");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    // Total records (with filter)
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM v_ims_payroll WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    // Fetch data
    $sql = "SELECT * FROM v_ims_payroll WHERE 1 " . $searchQuery .
        " ORDER BY id desc LIMIT :offset, :limit";
    $stmt = $conn->prepare($sql);

    foreach ($searchArray as $key => $val) {
        $stmt->bindValue(':' . $key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();

    $data = [];

    $statusMeta = [
        'Y' => ['desc' => "อนุมัติ", 'color' => 'green', 'can_print' => true],
        'N' => ['desc' => "รอการอนุมัติ", 'color' => 'gray', 'can_print' => false],
    ];

    foreach ($empRecords as $row) {
        if ($_POST['sub_action'] === "GET_MASTER") {
            $approve_status = $row['approve_status'] ?? 'N';
            $meta = $statusMeta[$approve_status] ?? ['desc' => 'ไม่ทราบสถานะ', 'color' => 'gray', 'can_print' => false];

            $payroll_month = $month_arr[$row['payroll_month']];

            $data[] = array(
                "doc_no" => $row['doc_no'],
                "emp_id" => $row['emp_id'],
                "employee_fullname" => $row['employee_fullname'],
                "doc_date" => $row['doc_date'],
                "payroll_month" => $payroll_month,
                "payroll_year" => $row['payroll_year'],
                "total_amount" => number_format($row['total_amount'], 2),
                "payment_method" => $row['payment_method'],
                "bank_no" => $row['bank_no'],
                "update" => "<button type='button' name='update' id='{$row['id']}' class='btn btn-info btn-xs update'>Update</button>",
                "info" => "<button type='button' name='info' id='{$row['id']}' class='btn btn-info btn-xs info'>Info</button>",
                "delete" => "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "emp_id" => $row['emp_id'],
                "select" => "<button type='button' name='select' id='{$row['doc_no']}@{$row['emp_id']}' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='Select'>Select <i class='fa fa-check' aria-hidden='true'></i></button>"
            );
        }
    }

    $response = array(
        "draw" => intval($draw),
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data
    );

    echo json_encode($response);
    exit;
}

if (isset($_POST["action"]) && $_POST["action"] === 'GET_SUMMARY') {
    $sql = "SELECT payroll_month, payroll_year, SUM(total_amount) as total_sum 
            FROM ims_payroll 
            GROUP BY payroll_year, payroll_month 
            ORDER BY payroll_year DESC, payroll_month DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($results as $row) {
        $data[] = array(
            "month_num" => (int)$row['payroll_month'],
            "payroll_month" => $month_arr[$row['payroll_month']],
            "payroll_year" => $row['payroll_year'],
            "total_sum" => number_format($row['total_sum'], 2)
        );
    }

    echo json_encode(["aaData" => $data]);
    exit;
}
