<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(0);

include(__DIR__ . '/../config/connect_db.php');
include(__DIR__ . '/../config/lang.php');
include(__DIR__ . '/../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_date = $_POST['payment_date'];
    $house_number = $_POST['house_number'];
    $detail = $_POST['detail'];
    $period_month_start = $_POST['period_month_start'];
    $period_month_to = $_POST['period_month_to'];
    $period_year = $_POST['period_year'];
    $amount = $_POST['amount'];
    $remark = $_POST['remark'];
    $line_user_id = $_POST['line_user_id'];
    $pictureUrl = $_POST['pictureUrl'];
    $displayName = $_POST['displayName'];
    $picture_payment = $_FILES['picture_payment'];

    $payment_method = "โอนเงิน";

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

    // --- ส่วนตรวจสอบช่วงเดือนทับซ้อนอย่างสมบูรณ์ (Comprehensive Interval Overlap Check ตาม check_payment.html) ---
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
        
        if ($existing_dup['payment_status'] === 'Y') {
            $msg = "มีข้อมูลการชำระค่าส่วนกลาง{$dup_desc} ปี {$period_year} เรียบร้อยแล้ว (เลขที่เอกสาร: {$existing_dup['doc_id']}) ไม่สามารถบันทึกซ้ำได้";
        } else {
            $msg = "มีรายการโอนเงิน{$dup_desc} ปี {$period_year} อยู่ระหว่างรอเจ้าหน้าที่ตรวจสอบ (เลขที่เอกสาร: {$existing_dup['doc_id']})";
        }
        
        echo json_encode([
            'status' => 'duplicate',
            'code' => 2,
            'message' => $msg,
            'doc_id' => $existing_dup['doc_id']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result_save = 0;

    $stmt = $conn->prepare("SELECT * FROM ims_house_line_user WHERE line_user_id = :line_user_id");
    $stmt->bindParam(':line_user_id', $line_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $line_user = $stmt->fetch(PDO::FETCH_ASSOC);
    $f_name = $line_user['f_name'];
    $l_name = $line_user['l_name'];
    $contact_name = $f_name . " " . $l_name;
    $line_phone = $line_user['line_phone'];

    $field = "runno";
    $table = "ims_house_payment";
    $cond = " WHERE house_number = '" . $house_number . "' AND period_year = '" . $period_year . "'";

    $runno = LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond);
    $doc_id = "P-" . $house_number . "-" . $period_year . "-" . sprintf('%03s', $runno);

    // โค้ด JSON สำหรับ Flex Message (Bubble)
    $flex_message_json = [
        "type" => "flex",
        "altText" => "บันทึกการชำระเงินเรียบร้อย",
        "contents" => [
            "type" => "bubble",
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => "✅ บันทึกการชำระเงิน",
                        "weight" => "bold",
                        "size" => "lg",
                        "color" => "#1DB446"
                    ],
                    [
                        "type" => "box",
                        "layout" => "vertical",
                        "margin" => "lg",
                        "spacing" => "sm",
                        "contents" => [
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "เลขที่เอกสาร:",
                                        "color" => "#aaaaaa",
                                        "size" => "sm",
                                        "flex" => 3
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "$doc_id",
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "sm",
                                        "flex" => 5
                                    ]
                                ]
                            ],
                            [
                                "type" => "box",
                                "layout" => "baseline",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "จำนวนเงิน:",
                                        "color" => "#aaaaaa",
                                        "size" => "sm",
                                        "flex" => 3
                                    ],
                                    [
                                        "type" => "text",
                                        "text" => "$amount บาท",
                                        "wrap" => true,
                                        "color" => "#666666",
                                        "size" => "sm",
                                        "flex" => 5
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    $messageData = [
        'to' => $line_user_id,
        'messages' => [
            $flex_message_json
        ]
    ];


    // --- ส่วนสำหรับอัปโหลดรูปภาพ ---
    if ($picture_payment['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($picture_payment['type'], $allowed_types)) {
            error_log("Invalid file type: " . $picture_payment['type']);
            echo "INVALID_FILE_TYPE";
            exit;
        }

        $upload_dir = '../uploads/slips/';
        $file_name = time() . "_" . basename($picture_payment['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($picture_payment['tmp_name'], $file_path)) {
            
            // --- Google Drive Upload Section ---
            try {
                include_once('../util/google_drive_util.php');
                $googleConfig = include('../config/google_drive_config.php');
                if (!empty($googleConfig['folder_id']) && $googleConfig['folder_id'] !== 'YOUR_GOOGLE_DRIVE_FOLDER_ID') {
                    uploadToGoogleDrive($file_path, $file_name, $googleConfig['folder_id'], $googleConfig);
                }
            } catch (Exception $e) {
                error_log("Google Drive Upload Error: " . $e->getMessage());
            }
            // ------------------------------------

            $ins_str = "INSERT INTO ims_house_payment (doc_id, payment_date, house_number, detail,runno,period_month_start,period_month_to,period_year,amount,picture_payment,remark,payment_type,line_user_id,line_picture_profile_show,create_by,payment_method) 
            VALUES (:doc_id, :payment_date, :house_number,:detail, :runno,:period_month_start,:period_month_to,:period_year,:amount,:picture_payment,:remark,:payment_type,:line_user_id,:line_picture_profile_show,:create_by,:payment_method)";
            $stmt = $conn->prepare($ins_str);

            $stmt->bindParam(':doc_id', $doc_id);
            $stmt->bindParam(':payment_date', $payment_date);
            $stmt->bindParam(':house_number', $house_number);
            $stmt->bindParam(':detail', $detail);
            $stmt->bindParam(':runno', $runno);
            $stmt->bindParam(':period_month_start', $period_month_start);
            $stmt->bindParam(':period_month_to', $period_month_to);
            $stmt->bindParam(':period_year', $period_year);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':picture_payment', $file_name);
            $stmt->bindParam(':remark', $remark);
            $stmt->bindParam(':payment_type', $payment_type);
            $stmt->bindParam(':line_user_id', $line_user_id);
            $stmt->bindParam(':line_picture_profile_show', $pictureUrl);
            $stmt->bindParam(':create_by', $detail);
            $stmt->bindParam(':payment_method', $payment_method);

            if ($stmt->execute()) {

                // ======= ส่ง Flex Message ไป LINE =======
                $access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';
                $ch = curl_init('https://api.line.me/v2/bot/message/push');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                curl_close($ch);
                // ======= จบการส่ง Flex Message =======

                echo 1;
            } else {
                echo 0;
            }
        } else {
            error_log("File upload failed.");
            echo "FILE_UPLOAD_FAILED";
        }
    } else {
        // --- กรณีไม่มีการอัปโหลดรูป ---
        $ins_str = "INSERT INTO ims_house_payment (doc_id, payment_date, house_number, detail, period_month_start, period_month_to, period_year, amount, remark, runno,line_user_id,line_picture_profile_show,create_by,payment_method) 
        VALUES (:doc_id, :payment_date, :house_number, :detail, :period_month_start, :period_month_to, :period_year, :amount, :remark, :runno, :line_user_id,:line_picture_profile_show,:create_by,:payment_method)";
        $stmt = $conn->prepare($ins_str);

        $stmt->bindParam(':doc_id', $doc_id);
        $stmt->bindParam(':payment_date', $payment_date);
        $stmt->bindParam(':house_number', $house_number);
        $stmt->bindParam(':detail', $detail);
        $stmt->bindParam(':period_month_start', $period_month_start);
        $stmt->bindParam(':period_month_to', $period_month_to);
        $stmt->bindParam(':period_year', $period_year);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':remark', $remark);
        $stmt->bindParam(':runno', $runno);
        $stmt->bindParam(':line_user_id', $line_user_id);
        $stmt->bindParam(':line_picture_profile_show', $pictureUrl);
        $stmt->bindParam(':create_by', $detail);
        $stmt->bindParam(':payment_method', $payment_method);

        if ($stmt->execute()) {

            $updateSql = "UPDATE ims_house SET contact_name = :contact_name , phone_number = :phone_number WHERE house_number = :house_number";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindParam(':contact_name', $contact_name);
            $updateStmt->bindParam(':phone_number', $line_phone);
            $updateStmt->bindParam(':house_number', $house_number);
            $updateStmt->execute();

            $updateSql_line = "UPDATE ims_house_payment SET line_picture_profile_show = :line_picture_profile_show WHERE house_number = :house_number AND line_user_id = :line_user_id ";
            $updateStmt_line = $conn->prepare($updateSql_line);
            $updateStmt_line->bindParam(':line_picture_profile_show', $pictureUrl);
            $updateStmt_line->bindParam(':house_number', $house_number);
            $updateStmt_line->bindParam(':line_user_id', $line_user_id);
            $updateStmt_line->execute();

            // ======= ส่ง Flex Message ไป LINE =======
            $access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

            $ch = curl_init('https://api.line.me/v2/bot/message/push');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            // ======= จบการส่ง Flex Message =======

            echo 1;
        } else {
            echo 0;
        }
    }
}