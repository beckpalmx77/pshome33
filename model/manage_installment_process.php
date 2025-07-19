<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php'); // ตรวจสอบว่ามีฟังก์ชัน generateNextInstallmentID() ในไฟล์นี้หรือไม่
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
            "detail" => $result['detail'] ?? null, // เพิ่ม detail
            "principal_amount" => $result['principal_amount'],
            "down_payment" => $result['down_payment'],
            "principal_amount_balance" => $result['principal_amount_balance'],
            "num_installments" => $result['num_installments'],
            "interest_rate" => $result['interest_rate'] ?? null, // เพิ่ม interest_rate
            "installment_per_period" => $result['installment_per_period'],
            "start_date" => $result['start_date'] ?? null, // เพิ่ม start_date
            "status" => $result['status'],
            "installment_img" => $result['installment_img'] ?? '' // เพิ่ม installment_img
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
    $installment_id = $payload['installment_id'] ?? null;
    $id = $payload['id'] ?? null; // ID ของ record หลัก (สำหรับ UPDATE)

    try {
        $conn->beginTransaction(); // เริ่มต้น transaction

        if ($action === 'ADD') {
            // --- ส่วนของการเพิ่มข้อมูลใหม่ (ADD) ---
            // 1. สร้าง installment_id ใหม่ ถ้ายังไม่มี
            // ตรวจสอบว่าฟังก์ชัน generateNextInstallmentID มีอยู่จริงหรือไม่
            if (function_exists('generateNextInstallmentID')) {
                $installment_id = generateNextInstallmentID($conn);
            } else {
                // หากไม่มีฟังก์ชัน generateNextInstallmentID ให้ใช้ logic เดิม (หรือสร้าง ID ด้วยตัวเอง)
                $stmt_count = $conn->prepare("SELECT COUNT(*) FROM ims_installment WHERE house_number = ?");
                $stmt_count->execute([$payload['house_number']]);
                $count = $stmt_count->fetchColumn();
                $new_count = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
                $installment_id = "INST-" . $payload['house_number'] . "-" . date("Ymd") . "-" . $new_count;
            }

            // รับค่า installment_img จาก payload
            $installment_img = $payload['picture_payment'] ?? '';

            // 2. บันทึกข้อมูลหลักลงใน ims_installment
            $stmt_master = $conn->prepare("
                INSERT INTO ims_installment (
                    installment_id, house_number, debtor, doc_date, down_payment,
                    principal_amount, principal_amount_balance, num_installments, installment_per_period,
                    detail, interest_rate, start_date, status, installment_img, create_date, update_date
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                )
            ");
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
                $payload['detail'] ?? '',
                $payload['interest_rate'] ?? 0,
                $payload['start_date'] ?? '',
                $payload['status'],
                $installment_img
            ]);
            $id = $conn->lastInsertId(); // รับ ID ที่สร้างขึ้นมาใหม่

            // 3. บันทึกข้อมูลรายละเอียดลงใน ims_installment_detail (ยังคงใช้ logic เดิมของคุณ)
            if (!empty($payload['details']) && is_array($payload['details'])) {
                $stmt_detail = $conn->prepare("INSERT INTO ims_installment_detail (installment_id, line_no, installment_number, amount_due, principal_per_installment
                , amount_paid, payment_method, payment_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
            echo json_encode(['status' => 'success', 'message' => 'เพิ่มข้อมูลการผ่อนชำระสำเร็จ', 'id' => $id, 'installment_id' => $installment_id]);

        } elseif ($action === 'UPDATE') {
            // --- ส่วนของการแก้ไขข้อมูล (UPDATE) ---
            if (!$installment_id) {
                echo json_encode(['status' => 'error', 'message' => 'ไม่พบ Installment ID สำหรับการอัปเดต.']);
                exit();
            }

            // จัดการรูปภาพ (ดึงค่าเดิม, รวมค่าใหม่, ลบค่าที่ถูกลบ)
            $installment_img_new_upload = $payload['picture_payment'] ?? '';

            // ดึงชื่อไฟล์รูปภาพเดิมจากฐานข้อมูล
            $stmt_current_img = $conn->prepare("SELECT installment_img FROM ims_installment WHERE installment_id = :installment_id");
            $stmt_current_img->bindParam(':installment_id', $installment_id, PDO::PARAM_STR);
            $stmt_current_img->execute();
            $current_img_data = $stmt_current_img->fetch(PDO::FETCH_ASSOC);
            $existing_images = explode(',', $current_img_data['installment_img'] ?? '');

            // จัดการรูปภาพที่ถูกลบ
            $deleted_images = json_decode($payload['deleted_images'] ?? '[]', true);
            $final_images = array_diff($existing_images, $deleted_images);

            // รวมกับรูปภาพที่อัปโหลดใหม่
            if (!empty($installment_img_new_upload)) {
                $new_images_array = explode(',', $installment_img_new_upload);
                $final_images = array_unique(array_merge($final_images, $new_images_array));
            }
            // กรองค่าว่างและรวมเป็น string คั่นด้วยคอมมา
            $final_images_str = implode(',', array_filter($final_images));


            // 1. อัปเดตข้อมูลหลักใน ims_installment
            $stmt_master = $conn->prepare("
                UPDATE ims_installment SET
                    house_number = ?,
                    debtor = ?,
                    doc_date = ?,
                    down_payment = ?,
                    principal_amount = ?,
                    principal_amount_balance = ?,
                    num_installments = ?,
                    installment_per_period = ?,
                    status = ?,
                    detail = ?,                 -- เพิ่ม detail
                    interest_rate = ?,          -- เพิ่ม interest_rate
                    start_date = ?,             -- เพิ่ม start_date
                    installment_img = ?,        -- เพิ่ม installment_img
                    update_date = NOW()
                WHERE installment_id = ?
            ");
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
                $payload['detail'] ?? '',
                $payload['interest_rate'] ?? 0,
                $payload['start_date'] ?? '',
                $final_images_str, // ใช้ string รูปภาพที่ประมวลผลแล้ว
                $installment_id
            ]);
            $stmt_master->closeCursor();

            // 2. จัดการข้อมูลรายละเอียดใน ims_installment_detail (ใช้ logic เดิมของคุณ)
            $stmt_delete_details = $conn->prepare("DELETE FROM ims_installment_detail WHERE installment_id = ?");
            $stmt_delete_details->execute([$installment_id]);
            $stmt_delete_details->closeCursor();

            if (!empty($payload['details']) && is_array($payload['details'])) {
                $stmt_detail = $conn->prepare("INSERT INTO ims_installment_detail (installment_id, line_no, installment_number, amount_due, principal_per_installment
                , amount_paid, payment_method, payment_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
            echo json_encode(['status' => 'success', 'message' => 'อัปเดตข้อมูลการผ่อนชำระสำเร็จ', 'id' => $id, 'installment_id' => $installment_id]);

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
        // ใช้ debtor สำหรับการค้นหา แทน payer เพราะ ims_installment ไม่มี payer
        $searchQuery = " AND (debtor LIKE :debtor OR house_number LIKE :house_number OR installment_id LIKE :installment_id) ";
        $searchArray = [
            'debtor' => "%$searchValue%",
            'house_number' => "%$searchValue%",
            'installment_id' => "%$searchValue%"
        ];
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

    $isUser = $_SESSION['account_type'] !== "user"; // ตรวจสอบจาก session

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


function get_total_all_records($conn) // ฟังก์ชันนี้ซ้ำกับในโค้ดเก่า, แนะนำให้ใช้แค่ get_total_all_records หรือเปลี่ยนชื่อให้ไม่ซ้ำ
{
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_installment");
    $stmt->execute();
    return $stmt->fetchColumn();
}


// --------------------------------------------------------------------------
// GENERATE INSTALLMENT DETAILS (ฟังก์ชันนี้อาจต้องย้ายไป util/record_util.php)
// --------------------------------------------------------------------------
/*
function generateInstallmentDetails($conn, $master_id, $installment_id, $principal_amount_balance, $num_installments, $installment_per_period, $start_date, $interest_rate)
{
    try {
        $current_principal = $principal_amount_balance;
        $start_timestamp = strtotime($start_date);

        for ($i = 1; $i <= $num_installments; $i++) {
            $line_no = $i;
            $installment_number = $i;
            $doc_date = date('Y-m-d'); // วันที่ปัจจุบันที่สร้างรายการรายละเอียด

            // คำนวณดอกเบี้ยต่อเดือน (ถ้ามี)
            $interest_per_installment = 0;
            if ($interest_rate > 0) {
                $interest_per_installment = ($current_principal * $interest_rate / 100) / 12; // ดอกเบี้ยต่อเดือน
            }

            $principal_to_pay = $installment_per_period; // ส่วนของเงินต้นที่จ่ายในงวดนี้
            $amount_due = $principal_to_pay + $interest_per_installment; // ยอดรวมที่ต้องชำระงวดนี้

            $payment_date = date('Y-m-d', strtotime("+" . ($i - 1) . " months", $start_timestamp)); // วันที่ครบกำหนดชำระ

            // อัปเดตเงินต้นคงเหลือ
            $current_principal -= $principal_to_pay;

            $stmt = $conn->prepare("
                INSERT INTO ims_installment_detail (
                    installment_id, line_no, installment_number, doc_date,
                    amount_due, principal_per_installment, interest_per_installment,
                    payment_date, status, create_date
                ) VALUES (
                    :installment_id, :line_no, :installment_number, :doc_date,
                    :amount_due, :principal_per_installment, :interest_per_installment,
                    :payment_date, :status, NOW()
                )
            ");

            $stmt->bindParam(':installment_id', $installment_id);
            $stmt->bindParam(':line_no', $line_no);
            $stmt->bindParam(':installment_number', $installment_number);
            $stmt->bindParam(':doc_date', $doc_date);
            $stmt->bindParam(':amount_due', $amount_due);
            $stmt->bindParam(':principal_per_installment', $principal_to_pay);
            $stmt->bindParam(':interest_per_installment', $interest_per_installment);
            $stmt->bindParam(':payment_date', $payment_date);
            $status = 'due'; // ตั้งค่าเริ่มต้นเป็น 'due'
            $stmt->bindParam(':status', $status);

            $stmt->execute();
        }
    } catch (PDOException $e) {
        error_log('Error generating installment details: ' . $e->getMessage());
        // ในสถานการณ์จริง คุณอาจต้องการแจ้งเตือนผู้ใช้หรือบันทึกข้อผิดพลาดที่ละเอียดยิ่งขึ้น
    }
}
*/

// ----------------------------------------------------
// GENERATE NEXT INSTALLMENT ID (ฟังก์ชันนี้อาจต้องย้ายไป util/record_util.php)
// ----------------------------------------------------
/*
// หากฟังก์ชันนี้อยู่ใน record_util.php อยู่แล้ว ไม่ต้อง include ซ้ำ
function generateNextInstallmentID($conn)
{
    // รูปแบบ INS-YYYYMMDD-XXX
    $prefix = "INS-" . date("Ymd");
    $stmt = $conn->prepare("SELECT MAX(installment_id) AS max_id FROM ims_installment WHERE installment_id LIKE :prefix");
    $stmt->bindValue(':prefix', $prefix . '%');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $max_id = $result['max_id'];
    $new_suffix_num = 1;

    if ($max_id) {
        $parts = explode('-', $max_id);
        $last_suffix = end($parts);
        if (is_numeric($last_suffix)) {
            $new_suffix_num = (int)$last_suffix + 1;
        }
    }
    return $prefix . "-" . str_pad($new_suffix_num, 3, '0', STR_PAD_LEFT);
}
*/

// ----------------------------------------------------
// SELECT MASTER RECORD (ใช้ $_POST)
// ----------------------------------------------------
if (isset($_POST["action"]) && $_POST["action"] === 'SELECT_MASTER') {
    $response = ['status' => 'error', 'message' => ''];
    try {
        $installment_id_for_select = $_POST['installment_id_for_select'] ?? ''; // ใช้ $_POST แทน $payload

        $stmt = $conn->prepare("SELECT * FROM ims_installment WHERE installment_id = :installment_id");
        $stmt->bindParam(':installment_id', $installment_id_for_select, PDO::PARAM_STR);
        $stmt->execute();
        $master_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($master_data) {
            $response = ['status' => 'success', 'data' => $master_data];
        } else {
            $response['message'] = 'ไม่พบข้อมูล Master record สำหรับ installment_id นี้';
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    echo json_encode($response);
    exit();
}

// ----------------------------------------------------
// FETCH DETAIL RECORDS (ใช้ $_POST)
// ----------------------------------------------------
if (isset($_POST["action"]) && $_POST["action"] === 'FETCH_DETAIL') {
    $response = ['status' => 'error', 'message' => ''];
    try {
        $installment_id_for_detail = $_POST['installment_id_for_detail'] ?? '';

        $query = "SELECT * FROM ims_installment_detail WHERE installment_id = :installment_id ";

        if (isset($_POST["search"]["value"])) {
            $query .= 'AND (line_no LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR doc_date LIKE "%' . $_POST["search"]["value"] . '%" ';
            $query .= 'OR amount_due LIKE "%' . $_POST["search"]["value"] . '%") ';
        }

        if (isset($_POST["order"])) {
            $query .= 'ORDER BY ' . $_POST['order']['0']['column'] . ' ' . $_POST['order']['0']['dir'] . ' ';
        } else {
            $query .= 'ORDER BY line_no ASC ';
        }

        if ($_POST["length"] != -1) {
            $query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':installment_id', $installment_id_for_detail, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = array();
        $filtered_rows = $stmt->rowCount();

        foreach ($result as $row) {
            $data[] = array(
                "id" => $row['id'],
                "installment_id" => $row['installment_id'],
                "line_no" => $row['line_no'],
                "installment_number" => $row['installment_number'],
                "doc_date" => $row['doc_date'],
                "amount_due" => $row['amount_due'],
                "principal_per_installment" => $row['principal_per_installment'],
                "interest_per_installment" => $row['interest_per_installment'],
                "payment_method" => $row['payment_method'],
                "amount_paid" => $row['amount_paid'],
                "payment_date" => $row['payment_date'],
                "status" => $row['status'],
                "notes" => $row['notes'],
                "update" => "<button type='button' name='update' id='{$row['id']}' class='btn btn-info btn-xs update'>Update</button>",
                "delete" => "<button type='button' name='delete' id='{$row['id']}' class='btn btn-danger btn-xs delete'>Delete</button>"
            );
        }

        $total_rows_query = $conn->prepare("SELECT COUNT(*) FROM ims_installment_detail WHERE installment_id = :installment_id");
        $total_rows_query->bindParam(':installment_id', $installment_id_for_detail, PDO::PARAM_STR);
        $total_rows_query->execute();
        $total_rows = $total_rows_query->fetchColumn();


        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data" => $data
        );

        echo json_encode($output);
        exit();

    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        echo json_encode($response);
        exit();
    }
}


// -----------------------------
// DELETE MASTER RECORD (ใช้ $_POST)
// -----------------------------
if (isset($_POST["action"]) && $_POST["action"] === 'DELETE') {
    $response = ['status' => 'error', 'message' => ''];
    try {
        $id = $_POST["id"] ?? 0;

        // ดึง installment_id ก่อนลบ ims_installment
        $stmt_get_installment_id = $conn->prepare("SELECT installment_id FROM ims_installment WHERE id = :id");
        $stmt_get_installment_id->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_get_installment_id->execute();
        $installment_id_to_delete = $stmt_get_installment_id->fetchColumn();

        if ($installment_id_to_delete) {
            // เริ่มต้น Transaction
            $conn->beginTransaction();

            // ลบข้อมูลใน ims_installment_detail ก่อน
            $stmt_detail = $conn->prepare("DELETE FROM ims_installment_detail WHERE installment_id = :installment_id");
            $stmt_detail->bindParam(':installment_id', $installment_id_to_delete, PDO::PARAM_STR);
            $stmt_detail->execute();

            // ลบข้อมูลใน ims_installment
            $stmt_master = $conn->prepare("DELETE FROM ims_installment WHERE id = :id");
            $stmt_master->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt_master->execute()) {
                $conn->commit(); // Commit Transaction
                $response = ['status' => 'success', 'message' => 'ลบข้อมูลการผ่อนชำระและรายละเอียดที่เกี่ยวข้องสำเร็จ'];
            } else {
                $conn->rollBack(); // Rollback Transaction
                $response['message'] = 'ไม่สามารถลบข้อมูลการผ่อนชำระหลักได้: ' . implode(" ", $stmt_master->errorInfo());
            }
        } else {
            $response['message'] = 'ไม่พบข้อมูลการผ่อนชำระที่ต้องการลบ';
        }

    } catch (PDOException $e) {
        $conn->rollBack(); // Rollback Transaction in case of exception
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
    echo json_encode($response);
    exit();
}

?>