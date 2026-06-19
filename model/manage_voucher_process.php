<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');

header('Content-Type: application/json');

// -----------------------------
// GET MASTER RECORD BY ID
// -----------------------------
if ($_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM ims_payment_voucher WHERE id = ?");
    $stmt->execute([$id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $return_arr = [];

    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'],
            "doc_no" => $result['doc_no'],
            "doc_date" => $result['doc_date'],
            "requester" => $result['requester'],
            "supplier_id" => $result['supplier_id'],
            "supplier_name" => $result['supplier_name'],
            "address" => $result['address'],
            "purpose" => $result['purpose'],
            "total_amount" => $result['total_amount'],
            "picture_doc" => $result['picture_doc'],
            "payment_method" => $result['payment_method'],
            "bank_no" => $result['bank_no'],
            "create_name" => $result['create_name'],
            "checker_name" => $result['checker_name'],
            "approve_name" => $result['approve_name'],
            "receipt_name" => $result['receipt_name'],
            "approve_status" => $result['approve_status'],
            "status" => $result['status']
        );
    }

// 🔽 เพิ่มส่วนนี้เพื่อ push ลงไฟล์ JSON
    // file_put_contents('voucher_data_log.json', json_encode($return_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// 🔁 ส่งกลับให้ AJAX
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
            SELECT product_id, product_name, quantity, inv, price, unit_id, unit_name , remark
            FROM ims_payment_voucher_items
            WHERE doc_no = ?
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
if (isset($_POST["action"]) && $_POST["action"] === 'GET_PURCHASE') {

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
        $searchQuery = " AND (supplier_name LIKE :supplier_name OR purpose LIKE :purpose) ";
        $searchArray = [
            'supplier_name' => "%$searchValue%",
            'purpose' => "%$searchValue%"
        ];
    }

    // Total records (no filter)
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_payment_voucher");
    $stmt->execute();
    $records = $stmt->fetch();
    $totalRecords = $records['allcount'];

    // Total records (with filter)
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_payment_voucher WHERE 1 " . $searchQuery);
    $stmt->execute($searchArray);
    $records = $stmt->fetch();
    $totalRecordwithFilter = $records['allcount'];

    // Fetch data
    $sql = "SELECT * FROM v_ims_payment_voucher WHERE 1 " . $searchQuery .
        " ORDER BY doc_year DESC,CAST(doc_month AS UNSIGNED) DESC,CAST(doc_day AS UNSIGNED) DESC LIMIT :offset, :limit";
    $stmt = $conn->prepare($sql);

    foreach ($searchArray as $key => $val) {
        $stmt->bindValue(':' . $key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll();

    $data = [];

    $isUser = $_SESSION['account_type'] !== "user";

    $statusMeta = [
        'Y' => ['desc' => "พิมพ์เอกสารแล้ว", 'color' => 'green', 'can_print' => true],
        'N' => ['desc' => "ยังไม่ได้พิมพ์เอกสาร", 'color' => 'gray', 'can_print' => false],
    ];

    foreach ($empRecords as $row) {
        if ($_POST['sub_action'] === "GET_MASTER") {
            $approve_status = $row['approve_status'] ?? 'N';
            $meta = $statusMeta[$approve_status] ?? ['desc' => 'ไม่ทราบสถานะ', 'color' => 'gray', 'can_print' => false];

            $data[] = array(
                "doc_no" => $row['doc_no'],
                "supplier_id" => $row['supplier_id'],
                "supplier_name" => $row['supplier_name'],
                "purpose" => $row['purpose'],
                "doc_date" => $row['doc_date'],
                "total_amount" => number_format($row['total_amount'], 2),
                "approve_status" => $row['approve_status'],
                "approve_status_desc" => "<span style='color: {$meta['color']}'>{$meta['desc']}</span>",
                "status" => $row['status'] === 'Active'
                    ? "<div class='text-success'>{$row['status']}</div>"
                    : "<div class='text-muted'>{$row['status']}</div>",
                "update" => "<button type='button' name='update' id='{$row['id']}' class='btn btn-info btn-xs update'>Update</button>",
                "print" => "<button type='button' name='print' id='{$row['id']}' class='btn btn-outline-success btn-xs print'>Print</button>",
                "delete" => "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete'>Delete</button>"
            );
        }
        else {
            $data[] = array(
                "id" => $row['id'],
                "doc_no" => $row['doc_no'],
                "supplier_id" => $row['supplier_id'],
                "select" => "<button type='button' name='select' id='{$row['doc_no']}@{$row['supplier_id']}' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='Select'>Select <i class='fa fa-check' aria-hidden='true'></i></button>"
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
    include('../util/month_util.php');
    $sql = "SELECT doc_month, doc_year, SUM(total_amount) as total_sum 
            FROM ims_payment_voucher 
            GROUP BY doc_year, doc_month 
            ORDER BY doc_year DESC, CAST(doc_month AS UNSIGNED) DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($results as $row) {
        $month_num = (int)$row['doc_month'];
        $data[] = array(
            "month_num" => $month_num,
            "month_name" => $month_arr[$month_num],
            "doc_year" => $row['doc_year'],
            "total_sum" => number_format($row['total_sum'], 2)
        );
    }

    echo json_encode(["aaData" => $data]);
    exit;
}
