<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/reorder_record.php');

header('Content-Type: application/json');

// ตรวจสอบว่า $conn เป็น PDO object ที่พร้อมใช้งานหรือไม่
if (!isset($conn) || !$conn instanceof PDO) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed. Please check connect_db.php.']);
    exit();
}

// -----------------------------------------------------
// อ่าน JSON Payload (ใช้สำหรับ ADD/UPDATE โดยเฉพาะ)
// ต้องอ่านก่อน action อื่นๆ ที่อาจจะใช้ $_POST ปกติ
// -----------------------------------------------------
$payload = [];
$input = file_get_contents('php://input');
if ($input) {
    $payload = json_decode($input, true);
}

// -----------------------------
// GET MASTER RECORD BY ID (ใช้ $_POST)
// -----------------------------
if (isset($_POST["action"]) && $_POST["action"] === 'GET_DATA') {

    $id = $_POST["id"] ?? 0;

    $stmt = $conn->prepare("SELECT * FROM ims_installment WHERE id = ?");
    $stmt->execute([$id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $return_arr = [];

    foreach ($results as $result) {
        $return_arr[] = array(
            "id" => $result['id'],
            "installment_id" => $result['installment_id'],
            "doc_date" => $result['doc_date'],
            "house_number" => $result['house_number'],
            "debtor" => $result['debtor'],
            "detail" => $result['detail'],
            "total_amount" => $result['total_amount'],
            "down_payment" => $result['down_payment'],
            "principal_amount" => $result['principal_amount'],
            "principal_amount_balance" => $result['principal_amount_balance'],
            "num_installments" => $result['num_installments'],
            "interest_rate" => $result['interest_rate'],
            "installment_per_period" => $result['installment_per_period'],
            "start_date" => $result['start_date'],
            // เพิ่มคอลัมน์เหล่านี้ถ้ามีในฐานข้อมูลและต้องการส่งกลับ
            "payment_schedule_type" => $result['payment_schedule_type'] ?? null,
            "due_date_first_installment" => $result['due_date_first_installment'] ?? null,
            "status" => $result['status']
        );
    }

    echo json_encode($return_arr);
    exit;
}

// --- ส่วนที่แก้ไขสำหรับจัดการ ADD/UPDATE ---
// ------------------------------------------
// จัดการการเพิ่ม/แก้ไขข้อมูลการผ่อนชำระ (Master & Details)
// ตรวจสอบ action จาก $payload ที่อ่านมาจาก JSON input
// ------------------------------------------
if (isset($payload['action']) && ($payload['action'] === 'ADD' || $payload['action'] === 'UPDATE')) {

    $action = $payload['action'];
    //$id = $payload['id'] ?? null;
    $installment_id = $payload['installment_id'] ?? null;

    try {
        $conn->beginTransaction(); // เริ่มต้น transaction

        if ($action === 'ADD') {
            // --- ส่วนของการเพิ่มข้อมูลใหม่ (ADD) ---
            // 1. สร้าง installment_id ใหม่ ถ้ายังไม่มี
            if (empty($installment_id)) {
                $stmt_count = $conn->prepare("SELECT COUNT(*) FROM ims_installment WHERE house_number = '" . $payload['house_number'] . "'");
                $stmt_count->execute();
                $count = $stmt_count->fetchColumn();
                $stmt_count->closeCursor();
                $new_count = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
                $installment_id = "INST-" . $payload['house_number'] . "-" . date("Ymd") . "-" . $new_count;
            }

            // 2. บันทึกข้อมูลหลักลงใน ims_installment
            $stmt_master = $conn->prepare("INSERT INTO ims_installment (installment_id, house_number, debtor, doc_date, down_payment
            , principal_amount, principal_amount_balance, num_installments, installment_per_period, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"); // Corrected: Added closing parenthesis
            $stmt_master->execute([
                $installment_id,
                $payload['house_number'],
                $payload['debtor'],
                $payload['doc_date'],
                $payload['down_payment'],
                $payload['principal_amount'],
                $payload['principal_amount_balance'],
                $payload['num_installments'],
                $payload['installment_per_period'],
                $payload['status']
            ]);
            $id = $conn->lastInsertId();

            // 3. บันทึกข้อมูลรายละเอียดลงใน ims_installment_detail
            if (!empty($payload['details']) && is_array($payload['details'])) {
                $stmt_detail = $conn->prepare("INSERT INTO ims_installment_detail (installment_id, line_no, installment_number ,amount_due , principal_per_installment, amount_paid
                , payment_method, payment_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"); // Corrected: Added closing parenthesis
                foreach ($payload['details'] as $detail_row) {
                    $stmt_detail->execute([
                        $installment_id,
                        $detail_row['line_no'] ?? 0,
                        $detail_row['installment_number'] ?? 0,
                        $detail_row['amount_due'] ?? 0,
                        $detail_row['principal_per_installment'] ?? 0,
                        $detail_row['amount_paid'] ?? 0,
                        $detail_row['payment_method'] ?? 0,
                        $detail_row['payment_date'] ?? '',
                        $detail_row['status'] ?? 0
                    ]);
                }
                $stmt_detail->closeCursor();
            }
            $stmt_master->closeCursor();

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Data added successfully.', 'id' => $id, 'installment_id' => $installment_id]);

        } elseif ($action === 'UPDATE') {
            // --- ส่วนของการแก้ไขข้อมูล (UPDATE) ---
            if (!$installment_id) {
                echo json_encode(['status' => 'error', 'message' => 'Missing ID or Installment ID for UPDATE action.']);
                exit();
            }

            // 1. อัปเดตข้อมูลหลักใน ims_installment
            $stmt_master = $conn->prepare("UPDATE ims_installment SET house_number=?, debtor=?, doc_date=?, down_payment=?
            , principal_amount=?, principal_amount_balance=?, num_installments=?, installment_per_period=?, status=?
            WHERE installment_id=? ");
            $stmt_master->execute([
                $payload['house_number'],
                $payload['debtor'],
                $payload['doc_date'],
                $payload['down_payment'],
                $payload['principal_amount'],
                $payload['principal_amount_balance'],
                $payload['num_installments'],
                $payload['installment_per_period'],
                $payload['status'],
                $installment_id
            ]);
            $stmt_master->closeCursor();

            // 2. จัดการข้อมูลรายละเอียดใน ims_installment_detail
            $stmt_delete_details = $conn->prepare("DELETE FROM ims_installment_detail WHERE installment_id = ?");
            $stmt_delete_details->execute([$installment_id]);
            $stmt_delete_details->closeCursor();

            if (!empty($payload['details']) && is_array($payload['details'])) {
                $stmt_detail = $conn->prepare("INSERT INTO ims_installment_detail (installment_id, line_no, installment_number, amount_due, principal_per_installment
                , amount_paid, payment_method, payment_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"); // Corrected: Added closing parenthesis
                foreach ($payload['details'] as $detail_row) {
                    $stmt_detail->execute([
                        $installment_id,
                        $detail_row['line_no'] ?? 0,
                        $detail_row['installment_number'] ?? 0,
                        $detail_row['amount_due'] ?? 0,
                        $detail_row['principal_per_installment'] ?? 0,
                        $detail_row['amount_paid'] ?? 0,
                        $detail_row['payment_method'] ?? 0,
                        $detail_row['payment_date'] ?? '',
                        $detail_row['status'] ?? 0
                    ]);
                }
                $stmt_detail->closeCursor();
            }

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Data updated successfully.', 'id' => $id, 'installment_id' => $installment_id]);

        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
        }

    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("PDO Error in manage_installment_process.php (ADD/UPDATE): " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// -----------------------------
// GET DETAIL RECORD BY installment_id (ใช้ $_GET)
// -----------------------------
if (isset($_GET["action"]) && $_GET["action"] === 'GET_DATA_DETAIL') {

    $installment_id = $_GET['installment_id'] ?? '';

    if (empty($installment_id)) {
        echo json_encode([]);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT *
            FROM ims_installment_detail
            WHERE installment_id = ?
            ORDER BY id ASC -- หรือ line_no ASC ถ้ามีคอลัมน์ line_no
        ");
        $stmt->execute([$installment_id]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($results);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }

    exit;
}

// -----------------------------
// DATATABLE SERVER-SIDE LOAD (ใช้ $_POST)
// -----------------------------
if (isset($_POST["action"]) && $_POST["action"] === 'GET_INSTALLMENT') {

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
        $searchQuery = " AND (payer LIKE :payer) ";
        $searchArray = ['payer' => "%$searchValue%"];
    }

    // Total records (no filter)
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_installment");
    $stmt->execute();
    $totalRecords = $stmt->fetchColumn();

    // Total records (with filter)
    $stmt = $conn->prepare("SELECT COUNT(*) AS allcount FROM ims_installment WHERE 1 " . $searchQuery);
    foreach ($searchArray as $key => $val) {
        $stmt->bindValue(':' . $key, $val, PDO::PARAM_STR);
    }
    $stmt->execute();
    $totalRecordwithFilter = $stmt->fetchColumn();

    // Fetch data
    $sql = "SELECT * FROM ims_installment WHERE 1 " . $searchQuery .
        " ORDER BY " . $columnName . " " . $columnSortOrder . " LIMIT :offset, :limit";
    $stmt = $conn->prepare($sql);

    foreach ($searchArray as $key => $val) {
        $stmt->bindValue(':' . $key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':offset', (int)$row, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$rowperpage, PDO::PARAM_INT);
    $stmt->execute();
    $empRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];

    $isUser = $_SESSION['account_type'] !== "user";

    foreach ($empRecords as $row) {
        if (isset($_POST['sub_action']) && $_POST['sub_action'] === "GET_MASTER") {
            $data[] = array(
                "id" => $row['id'],
                "installment_id" => $row['installment_id'],
                "house_number" => $row['house_number'],
                "detail" => $row['detail'],
                "debtor" => $row['debtor'],
                "interest_rate" => $row['interest_rate'],
                "principal_amount" => $row['principal_amount'],
                "total_amount" => $row['total_amount'],
                "down_payment" => $row['down_payment'],
                "num_installments" => $row['num_installments'],
                "installment_per_period" => $row['installment_per_period'],
                "start_date" => $row['start_date'],
                "status" => $row['status'],
                "update" => "<button type='button' name='update' id='{$row['id']}' class='btn btn-info btn-xs update'>Update</button>",
                "print" => "<button type='button' name='print' id='{$row['id']}' class='btn btn-outline-success btn-xs print'>Print</button>",
                "delete" => "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete'>Delete</button>"
            );
        } else {
            $data[] = array(
                "id" => $row['id'],
                "installment_id" => $row['installment_id'],
                "house_number" => $row['house_number'],
                "select" => "<button type='button' name='select' id='{$row['installment_id']}@{$row['house_number']}' class='btn btn-outline-success btn-xs select' data-toggle='tooltip' title='Select'>Select <i class='fa fa-check' aria-hidden='true'></i></button>"
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

?>