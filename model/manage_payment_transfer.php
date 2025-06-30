<?php
session_start();
// --- เปิดใช้งาน Error Reporting เต็มรูปแบบเพื่อการ Debug (ควรปิดเมื่อขึ้น Production) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

// --- ฟังก์ชันสำหรับเขียน Log ลงไฟล์ที่กำหนดเอง ---
function writeToCustomLog($message) {
    $log_file = __DIR__ . '/../logs/payment_activity.log'; // Path ไปยังไฟล์ Log
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}" . PHP_EOL; // เพิ่มเวลาและขึ้นบรรทัดใหม่

    // ตรวจสอบและสร้างโฟลเดอร์ logs ถ้ายังไม่มี
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        // พยายามสร้างโฟลเดอร์ด้วยสิทธิ์ 0755
        if (!mkdir($log_dir, 0755, true)) {
            // หากสร้างโฟลเดอร์ไม่ได้ ให้ใช้ error_log แทน
            error_log("Failed to create log directory: {$log_dir}");
            return; // หยุดการทำงานของฟังก์ชัน log
        }
    }

    // เขียน Log ลงไฟล์
    // FILE_APPEND: เพิ่มข้อมูลท้ายไฟล์
    // LOCK_EX: ล็อกไฟล์เพื่อป้องกันการเขียนทับพร้อมกัน
    if (file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX) === false) {
        // หากเขียนไฟล์ไม่ได้ ให้ใช้ error_log แทน
        error_log("Failed to write to custom log file: {$log_file} - Message: {$message}");
    }
}

writeToCustomLog("Script save_payment_data.php started.");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    writeToCustomLog("Received POST request.");

    // รับค่าจาก POST
    $payment_date = $_POST['payment_date'] ?? null;
    $house_number = $_POST['house_number'] ?? null;
    $detail = $_POST['detail'] ?? null;
    $payment_type = $_POST['payment_type'] ?? null;
    $period_month_start = $_POST['period_month_start'] ?? null;
    $period_month_to = $_POST['period_month_to'] ?? null;
    $period_year = $_POST['period_year'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $remark = $_POST['remark'] ?? null;
    $picture_payment = $_FILES['picture_payment'] ?? null;
    $payment_method = $_POST['payment_method'] ?? null;

    $create_by = $_SESSION['first_name'] . " " . $_SESSION['last_name'];
    if (empty(trim($create_by))) {
        $create_by = 'System';
        writeToCustomLog("create_by session empty, set to 'System'.");
    }

    // --- ส่วนที่ 1: ตรวจสอบความถูกต้องของข้อมูลพื้นฐาน ---
    if (!is_numeric($amount) || $amount <= 0) {
        writeToCustomLog("Error: Invalid amount provided. Amount: " . $amount);
        echo "Error: Invalid amount provided. Amount must be a positive number.";
        exit;
    }
    if (empty($house_number)) {
        writeToCustomLog("Error: House number is empty.");
        echo "Error: House number cannot be empty.";
        exit;
    }
    if (empty($period_year) || !is_numeric($period_year)) {
        writeToCustomLog("Error: Period year is invalid. Year: " . $period_year);
        echo "Error: Period year is invalid.";
        exit;
    }
    if (empty($period_month_start) || empty($period_month_to) || !is_numeric($period_month_start) || !is_numeric($period_month_to)) {
        writeToCustomLog("Error: Period month start/to is invalid. Start: {$period_month_start}, To: {$period_month_to}");
        echo "Error: Period month start/to is invalid.";
        exit;
    }
    writeToCustomLog("Basic POST data validation passed.");

    // --- ส่วนที่ 2: ตรวจสอบและ INSERT เข้า ims_house ---
    $house_alley = '';

    try {
        writeToCustomLog("Checking if house_number '{$house_number}' exists in ims_house.");
        $stmt_check_house = $conn->prepare("SELECT alley FROM ims_house WHERE house_number = :house_number");
        $stmt_check_house->bindParam(':house_number', $house_number);
        $stmt_check_house->execute();
        $result_house = $stmt_check_house->fetch(PDO::FETCH_ASSOC);

        if ($result_house) {
            $house_alley = $result_house['alley'];
            writeToCustomLog("House '{$house_number}' found in ims_house. Alley: '{$house_alley}'.");
        } else {
            writeToCustomLog("House '{$house_number}' not found in ims_house. Attempting to insert.");
            // 2. ค้นหา alley จาก ims_house_master
            $stmt_get_alley = $conn->prepare("SELECT alley FROM ims_house_master WHERE house_number = :house_number");
            $stmt_get_alley->bindParam(':house_number', $house_number);
            $stmt_get_alley->execute();
            $result_master = $stmt_get_alley->fetch(PDO::FETCH_ASSOC);

            if ($result_master) {
                $house_alley = $result_master['alley'];
                writeToCustomLog("Alley '{$house_alley}' found for '{$house_number}' in ims_house_master.");
            } else {
                writeToCustomLog("Warning: House number '{$house_number}' not found in ims_house_master. Alley set to empty.");
                $house_alley = '';
            }

            // 3. Insert ข้อมูลใหม่เข้าตาราง ims_house
            $ins_house_sql = "INSERT INTO ims_house (
                                house_number,
                                contact_name,
                                alley,
                                car_no1, car_no2, car_no3, car_no4, car_no5,
                                create_by, update_by)
                              VALUES (
                                :house_number,
                                :contact_name,
                                :alley,
                                '', '', '', '', '',
                                :create_by, :update_by)";
            $stmt_insert_house = $conn->prepare($ins_house_sql);
            $stmt_insert_house->bindParam(':house_number', $house_number);
            $stmt_insert_house->bindParam(':contact_name', $detail);
            $stmt_insert_house->bindParam(':alley', $house_alley);
            $stmt_insert_house->bindParam(':create_by', $create_by);
            $stmt_insert_house->bindParam(':update_by', $create_by);

            $stmt_insert_house->execute();
            writeToCustomLog("Successfully inserted new house '{$house_number}' into ims_house.");
        }
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            writeToCustomLog("Duplicate house_number '{$house_number}' during ims_house insert. This is expected if another process added it. Continuing.");
        } else {
            writeToCustomLog("Critical Error inserting into ims_house: " . $e->getMessage());
            echo "Error: Database error during house registration. Please check logs. " . $e->getMessage();
            exit;
        }
    }

    // --- ส่วนที่ 3: สร้าง runno และ doc_id สำหรับ ims_house_payment ---
    if (!isset($conn) || !is_object($conn)) {
        writeToCustomLog("Error: Database connection object is not valid before LAST_DOCUMENT_NUMBER.");
        echo "Error: Database connection object is not valid.";
        exit;
    }

    $field = "runno";
    $table = "ims_house_payment";
    $cond = " WHERE house_number = '" . $house_number . "' AND period_year = '" . $period_year . "'";
    $runno = LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond);
    $doc_id = "P-" . $house_number . "-" . $period_year . "-" . sprintf('%03s', $runno);
    writeToCustomLog("Generated doc_id: {$doc_id} with runno: {$runno}.");

    // --- ส่วนที่ 4: จัดการการอัปโหลดไฟล์ภาพ ---
    $file_name = null;

    if ($picture_payment && $picture_payment['error'] == UPLOAD_ERR_OK) {
        function isAllowedFileType($filename)
        {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            return in_array($ext, $allowed);
        }

        writeToCustomLog("Attempting file upload for " . $picture_payment['name'] . ".");
        if (!isAllowedFileType($picture_payment['name'])) {
            writeToCustomLog("Error: Invalid file type for " . $picture_payment['name'] . ".");
            echo "Error: Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.";
            exit;
        }
        if ($picture_payment['size'] > 5 * 1024 * 1024) {
            writeToCustomLog("Error: File " . $picture_payment['name'] . " is too large (" . $picture_payment['size'] . " bytes).");
            echo "Error: File too large. Maximum size is 5MB.";
            exit;
        }

        $upload_dir = __DIR__ . '/../uploads/slips/'; // ใช้ __DIR__ เพื่อให้ Path ถูกต้องเสมอ
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                writeToCustomLog("Critical Error: Failed to create upload directory: {$upload_dir}. Check permissions.");
                echo "Error: Could not create upload directory. Check permissions for " . $upload_dir;
                exit;
            }
            writeToCustomLog("Upload directory created: {$upload_dir}.");
        }

        $file_name = time() . "_" . basename($picture_payment['name']);
        $file_path = $upload_dir . $file_name;

        if (!move_uploaded_file($picture_payment['tmp_name'], $file_path)) {
            writeToCustomLog("Critical Error: Failed to move uploaded file from " . $picture_payment['tmp_name'] . " to " . $file_path . ".");
            echo "Error: Failed to upload file. Check permissions for '{$upload_dir}' or disk space.";
            exit;
        }
        writeToCustomLog("File successfully uploaded to: {$file_path}.");
    } else if ($picture_payment && $picture_payment['error'] != UPLOAD_ERR_NO_FILE) {
        writeToCustomLog("Error: File upload error code " . $picture_payment['error'] . " for " . ($picture_payment['name'] ?? 'unknown file') . ".");
        echo "Error: File upload error code " . $picture_payment['error'] . ". Please try again.";
        exit;
    } else {
        writeToCustomLog("No file uploaded or no file input provided.");
    }

    // --- ส่วนที่ 5: Insert ข้อมูลลง ims_house_payment ---
    try {
        writeToCustomLog("Attempting to insert data into ims_house_payment.");
        if ($file_name) {
            $ins_str = "INSERT INTO ims_house_payment (
                            doc_id, payment_date, house_number, detail, runno,
                            period_month_start, period_month_to, period_year, amount,
                            picture_payment, remark, payment_type, payment_method, create_by
                        )
                        VALUES (
                            :doc_id, :payment_date, :house_number, :detail, :runno,
                            :period_month_start, :period_month_to, :period_year, :amount,
                            :picture_payment, :remark, :payment_type, :payment_method, :create_by
                        )";
            $stmt = $conn->prepare($ins_str);
            $stmt->bindParam(':picture_payment', $file_name);
        } else {
            $ins_str = "INSERT INTO ims_house_payment (
                            doc_id, payment_date, house_number, detail,
                            period_month_start, period_month_to, period_year, amount,
                            remark, runno, payment_type, payment_method, create_by
                        )
                        VALUES (
                            :doc_id, :payment_date, :house_number, :detail,
                            :period_month_start, :period_month_to, :period_year, :amount,
                            :remark, :runno, :payment_type, :payment_method, :create_by
                        )";
            $stmt = $conn->prepare($ins_str);
        }

        $stmt->bindParam(':doc_id', $doc_id);
        $stmt->bindParam(':payment_date', $payment_date);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->bindParam(':detail', $detail);
        $stmt->bindParam(':runno', $runno);
        $stmt->bindParam(':period_month_start', $period_month_start);
        $stmt->bindParam(':period_month_to', $period_month_to);
        $stmt->bindParam(':period_year', $period_year);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':remark', $remark);
        $stmt->bindParam(':payment_type', $payment_type);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':create_by', $create_by);

        if ($stmt->execute()) {
            writeToCustomLog("Successfully inserted payment record for doc_id: {$doc_id}.");
            echo 1; // ส่ง 1 กลับไปเมื่อสำเร็จ
        } else {
            $errorInfo = $stmt->errorInfo();
            $errorMessage = isset($errorInfo[2]) ? $errorInfo[2] : "Unknown PDO error.";
            writeToCustomLog("Error inserting into ims_house_payment: {$errorMessage}");
            echo "Error inserting into ims_house_payment: {$errorMessage}";
        }
    } catch (PDOException $e) {
        writeToCustomLog("Exception during ims_house_payment insert: " . $e->getMessage());
        echo "Exception during ims_house_payment insert: " . $e->getMessage();
    }
} else {
    writeToCustomLog("Error: Invalid request method. Not a POST request.");
    echo "Error: Invalid request method. Only POST allowed.";
}
writeToCustomLog("Script save_payment_data.php finished.");

?>