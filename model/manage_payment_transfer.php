<?php
session_start();
// --- เปิดใช้งาน Error Reporting เต็มรูปแบบเพื่อการ Debug (ควรปิดเมื่อขึ้น Production) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php'); // ตรวจสอบให้แน่ใจว่า LAST_DOCUMENT_NUMBER อยู่ในไฟล์นี้

// --- ฟังก์ชันสำหรับเขียน Log ลงไฟล์ที่กำหนดเอง ---
function writeToCustomLog($message)
{
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

// <<< แก้ไขตรงนี้: ชื่อสคริปต์ใน Log
writeToCustomLog("Script manage_payment_transfer.php started.");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    writeToCustomLog("Received POST request.");

    // รับค่าจาก POST
    $payment_date = $_POST['payment_date'] ?? null;
    $house_number = $_POST['house_number'] ?? null;
    $detail = $_POST['detail'] ?? null;
    // --- ดึง phone_number จาก POST ---
    $phone_number = $_POST['phone_number'] ?? null;
    //$payment_type = $_POST['payment_type'] ?? null;
    $period_month_start = $_POST['period_month_start'] ?? null;
    $period_month_to = $_POST['period_month_to'] ?? null;
    $period_year = $_POST['period_year'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $receive_first = $_POST['receive_first'] ?? 0;
    $remark = $_POST['remark'] ?? null;
    $picture_payment = $_FILES['picture_payment'] ?? null;
    $payment_method = $_POST['payment_method'] ?? null;

    // ตรวจสอบเงื่อนไข: ถ้าเลือกเดือนมกราคมถึงธันวาคม (1 ถึง 12)
    if ($period_month_start == 1 && $period_month_to == 12) {
        // กำหนดค่า payment_type เป็น 12 ทันที
        $payment_type = 12; // <-- แก้ไขตรงนี้
    } else {
        // ถ้าไม่ใช่กรณี 1-12 ให้คำนวณจำนวนเดือนปกติ
        if ($period_month_to >= $period_month_start) {
            $payment_type = $period_month_to - $period_month_start + 1;
        } else {
            // กรณีข้ามปี (เช่น เริ่ม ธ.ค. -> สิ้นสุด ม.ค.)
            $payment_type = (12 - $period_month_start) + $period_month_to + 1;
        }
    }

    $create_by = $_SESSION['first_name'] . " " . $_SESSION['last_name'];
    if (empty(trim($create_by))) {
        $create_by = 'System';
        writeToCustomLog("create_by session empty or invalid, set to 'System'.");
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

    // --- ส่วนที่ 2: ตรวจสอบและ INSERT/UPDATE เข้า ims_house ---
    $house_alley = '';
    $phone_number_db = '';

    try {
        writeToCustomLog("Checking if house_number '{$house_number}' exists in ims_house.");
        $stmt_check_house = $conn->prepare("SELECT alley, phone_number FROM ims_house WHERE house_number = :house_number");
        $stmt_check_house->bindParam(':house_number', $house_number);
        $stmt_check_house->execute();
        $result_house = $stmt_check_house->fetch(PDO::FETCH_ASSOC);

        if ($result_house) {
            // พบข้อมูลบ้านเลขที่ใน ims_house
            $house_alley = $result_house['alley'];
            $phone_number_db = $result_house['phone_number'];

            writeToCustomLog("House '{$house_number}' found in ims_house. Alley: '{$house_alley}'. Current phone in DB: '{$phone_number_db}'.");

            // <<< แก้ไขตรงนี้: เพิ่มเงื่อนไข !empty($phone_number) และ execute()
            // อัปเดตเบอร์โทรศัพท์ใน ims_house หากเบอร์เดิมว่าง และมีเบอร์ใหม่ส่งมา
            if (($phone_number_db === null || $phone_number_db === '') && !empty($phone_number)) {
                $sql_update_house = "UPDATE ims_house SET phone_number = :phone_number WHERE house_number = :house_number";
                $stmt_update_house = $conn->prepare($sql_update_house);
                $stmt_update_house->bindParam(':phone_number', $phone_number);
                $stmt_update_house->bindParam(':house_number', $house_number);

                if ($stmt_update_house->execute()) {
                    writeToCustomLog("Successfully updated phone number for house '{$house_number}' to '{$phone_number}'.");
                } else {
                    writeToCustomLog("Error updating phone number for house '{$house_number}'. PDO Error Info: " . json_encode($stmt_update_house->errorInfo()));
                }
            } else if (!empty($phone_number_db)) {
                writeToCustomLog("Phone number for house '{$house_number}' already exists in DB: '{$phone_number_db}'. No update needed based on current logic.");
            } else if (empty($phone_number) && ($phone_number_db === null || $phone_number_db === '')) {
                writeToCustomLog("Phone number for house '{$house_number}' is empty in DB, and no new phone number provided from form to update.");
            }

        } else {
            // ไม่พบข้อมูลบ้านเลขที่ใน ims_house, ต้องค้นหา alley และ Insert ใหม่
            writeToCustomLog("House '{$house_number}' not found in ims_house. Attempting to insert new record.");

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
                                phone_number,
                                alley,
                                car_no1, car_no2, car_no3, car_no4, car_no5,
                                create_by, update_by)
                              VALUES (
                                :house_number,
                                :contact_name,
                                :phone_number,
                                :alley,
                                '', '', '', '', '',
                                :create_by, :update_by)";
            $stmt_insert_house = $conn->prepare($ins_house_sql);
            $stmt_insert_house->bindParam(':house_number', $house_number);
            // <<< พิจารณาค่าสำหรับ contact_name ให้เหมาะสม
            $stmt_insert_house->bindParam(':contact_name', $detail); // ใช้ detail เป็น contact_name ชั่วคราว อาจต้องปรับ
            $stmt_insert_house->bindParam(':phone_number', $phone_number); // ผูกค่า phone_number จาก POST
            $stmt_insert_house->bindParam(':alley', $house_alley);
            $stmt_insert_house->bindParam(':create_by', $create_by);
            $stmt_insert_house->bindParam(':update_by', $create_by);

            if ($stmt_insert_house->execute()) {
                writeToCustomLog("Successfully inserted new house '{$house_number}' into ims_house with phone: '{$phone_number}'.");
            } else {
                $errorInfo = $stmt_insert_house->errorInfo();
                $errorMessage = isset($errorInfo[2]) ? $errorInfo[2] : "Unknown PDO error.";
                writeToCustomLog("Error inserting new house into ims_house: {$errorMessage}");
                // หากเกิด error 23000 (Duplicate entry) ในระหว่างที่สคริปต์ทำงาน อาจหมายถึงอีก process เพิ่ง insert ไป
                if ($errorInfo[0] == '23000') {
                    writeToCustomLog("Duplicate entry for house_number '{$house_number}' during ims_house insert. This might be a race condition, continuing.");
                } else {
                    echo "Error: Database error during house registration. Please check logs. " . $errorMessage;
                    exit;
                }
            }
        }
    } catch (PDOException $e) {
        writeToCustomLog("Exception during ims_house check/insert/update: " . $e->getMessage());
        // ตรวจสอบ Duplicate entry (Code 23000) สำหรับกรณีที่มีการ Insert พร้อมกัน
        if ($e->getCode() == '23000') {
            writeToCustomLog("Duplicate house_number '{$house_number}' during ims_house insert. This is expected if another process added it. Continuing.");
        } else {
            writeToCustomLog("Critical Error inserting/updating into ims_house: " . $e->getMessage());
            echo "Error: Database error during house registration. Please check logs. " . $e->getMessage();
            exit;
        }
    }

    // --- ส่วนตรวจสอบช่วงเดือนทับซ้อน (Comprehensive Interval Overlap Check ตาม check_payment.html) ---
    $check_sql = "SELECT id, doc_id, period_month_start, period_month_to, period_year, payment_status, amount 
                  FROM ims_house_payment 
                  WHERE house_number = :house_number 
                    AND period_year = :period_year 
                    AND (:new_start <= period_month_to AND :new_to >= period_month_start)
                  LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bindParam(':house_number', $house_number);
    $check_stmt->bindParam(':period_year', $period_year);
    $check_stmt->bindParam(':new_start', $period_month_start, PDO::PARAM_INT);
    $check_stmt->bindParam(':new_to', $period_month_to, PDO::PARAM_INT);
    $check_stmt->execute();
    $existing_dup = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_dup) {
        $thai_months = ["", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        $dup_s = $thai_months[intval($existing_dup['period_month_start'])] ?? $existing_dup['period_month_start'];
        $dup_e = $thai_months[intval($existing_dup['period_month_to'])] ?? $existing_dup['period_month_to'];
        $dup_desc = ($existing_dup['period_month_start'] == $existing_dup['period_month_to']) ? "งวดเดือน {$dup_s}" : "งวดเดือน {$dup_s} ถึง {$dup_e}";
        
        $msg = "มีข้อมูลการชำระค่าส่วนกลาง{$dup_desc} ปี {$period_year} เรียบร้อยแล้ว (เลขที่เอกสาร: {$existing_dup['doc_id']}) ไม่สามารถบันทึกซ้ำได้";
        writeToCustomLog("Blocked duplicate payment for house {$house_number}: {$msg}");
        echo $msg;
        exit;
    }

    // --- ส่วนที่ 3: สร้าง runno และ doc_id สำหรับ ims_house_payment ---
    if (!isset($conn) || !is_object($conn)) {
        writeToCustomLog("Error: Database connection object is not valid before LAST_DOCUMENT_NUMBER.");
        echo "Error: Database connection object is not valid.";
        exit;
    }

    // ตรวจสอบว่า LAST_DOCUMENT_NUMBER ฟังก์ชันพร้อมใช้งานหรือไม่
    if (!function_exists('LAST_DOCUMENT_NUMBER')) {
        writeToCustomLog("Error: Function LAST_DOCUMENT_NUMBER is not defined. Check util/record_util.php.");
        echo "Error: Required utility function not found.";
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
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf']; // <<< เพิ่ม pdf ถ้าต้องการ
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            return in_array($ext, $allowed);
        }

        writeToCustomLog("Attempting file upload for " . $picture_payment['name'] . ".");
        if (!isAllowedFileType($picture_payment['name'])) {
            writeToCustomLog("Error: Invalid file type for " . $picture_payment['name'] . ".");
            echo "Error: Invalid file type. Only JPG, JPEG, PNG, GIF" . (in_array('pdf', isAllowedFileType('dummy.pdf') ? ['pdf'] : []) ? ", PDF" : "") . " are allowed."; // ปรับข้อความตาม allowed types
            exit;
        }
        // --- ปรับปรุง: ขนาดไฟล์แจ้งเตือนให้ตรงกับเงื่อนไข (เลือก 5MB หรือ 30MB) ---
        if ($picture_payment['size'] > 30 * 1024 * 1024) { // 30MB (เปลี่ยนเป็น 5 * 1024 * 1024 สำหรับ 5MB)
            writeToCustomLog("Error: File " . $picture_payment['name'] . " is too large (" . $picture_payment['size'] . " bytes).");
            echo "Error: File too large. Maximum size is 30MB."; // แก้ข้อความแจ้งเตือนตามที่คุณต้องการ
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

        // --- Google Drive Upload Section ---
        try {
            include_once('../util/google_drive_util.php');
            $googleConfig = include('../config/google_drive_config.php');
            if (!empty($googleConfig['folder_id']) && $googleConfig['folder_id'] !== 'YOUR_GOOGLE_DRIVE_FOLDER_ID') {
                uploadToGoogleDrive($file_path, $file_name, $googleConfig['folder_id'], $googleConfig);
                writeToCustomLog("File successfully uploaded to Google Drive: {$file_name}.");
            }
        } catch (Exception $e) {
            writeToCustomLog("Google Drive Upload Error: " . $e->getMessage());
            error_log("Google Drive Upload Error: " . $e->getMessage());
        }
        // ------------------------------------
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

        $ins_str = "INSERT INTO ims_house_payment (
                        doc_id, payment_date, house_number, detail, runno,
                        period_month_start, period_month_to, period_year, amount, receive_first,
                        picture_payment, remark, payment_type, payment_method, create_by
                    )
                    VALUES (
                        :doc_id, :payment_date, :house_number, :detail, :runno,
                        :period_month_start, :period_month_to, :period_year, :amount, :receive_first,
                        :picture_payment, :remark, :payment_type, :payment_method, :create_by
                    )";
        $stmt = $conn->prepare($ins_str);

        // กำหนดค่าเริ่มต้นสำหรับ picture_payment เป็น null ถ้าไม่มีไฟล์
        $picture_payment_value = $file_name;

        $stmt->bindParam(':doc_id', $doc_id);
        $stmt->bindParam(':payment_date', $payment_date);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->bindParam(':detail', $detail);
        $stmt->bindParam(':runno', $runno);
        $stmt->bindParam(':period_month_start', $period_month_start);
        $stmt->bindParam(':period_month_to', $period_month_to);
        $stmt->bindParam(':period_year', $period_year);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':receive_first', $receive_first);
        $stmt->bindParam(':picture_payment', $picture_payment_value); // ใช้ตัวแปรเดียว
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
// <<< แก้ไขตรงนี้: ชื่อสคริปต์ใน Log
writeToCustomLog("Script manage_payment_transfer.php finished.");

?>