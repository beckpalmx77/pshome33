<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_date = $_POST['payment_date'];
    $house_number = $_POST['house_number'];
    $detail = $_POST['detail'];
    $payment_type = $_POST['payment_type'];
    $period_month_start = $_POST['period_month_start'];
    $period_month_to = $_POST['period_month_to'];
    $period_year = $_POST['period_year'];
    $amount = $_POST['amount'];
    $remark = $_POST['remark'];
    $line_user_id = $_POST['line_user_user_id']; // แก้ไขตรงนี้ให้ตรงกับชื่อที่ส่งมาจาก Frontend
    $pictureUrl = $_POST['pictureUrl'];
    $displayName = $_POST['displayName'];
    $picture_payment = $_FILES['picture_payment'];

    $payment_method = "โอนเงิน";

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

    // --- Start of Idempotency Check (คงไว้เพื่อป้องกันการบันทึกข้อมูลซ้ำ) ---
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM ims_house_payment WHERE doc_id = :doc_id");
    $stmt_check->bindParam(':doc_id', $doc_id);
    $stmt_check->execute();
    $doc_exists = $stmt_check->fetchColumn();

    if ($doc_exists > 0) {
        echo 1; // Indicate success as the record already exists
        exit;
    }
    // --- End of Idempotency Check ---

    // --- เพิ่ม Logic สำหรับชื่อเดือนและ Timestamp ---
    $monthNames = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];
    $start_month_name = $monthNames[$period_month_start];
    $to_month_name = $monthNames[$period_month_to];

    // สร้าง timestamp วันที่เวลาปัจจุบัน
    $current_datetime = date('d-m-Y H:i:s');
    // --- สิ้นสุด Logic สำหรับชื่อเดือนและ Timestamp ---

    // --- กำหนด Base URL สำหรับรูปภาพ (สำคัญมาก) ---
    // *** คุณต้องเปลี่ยน 'https://yourdomain.com' ให้เป็นโดเมนจริงของคุณ ***
    $base_url = 'https://yourdomain.com/'; // เปลี่ยนเป็นโดเมนของคุณ
    $slip_base_path = $base_url . 'uploads/slips/';

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
        $image_url = $slip_base_path . $file_name; // URL ที่จะใช้ใน LINE API

        if (move_uploaded_file($picture_payment['tmp_name'], $file_path)) {
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
                // ======= Send LINE Message (มีการอัปโหลดรูปภาพ) =======
                $access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

                $messages_to_send = [
                    [
                        'type' => 'text',
                        'text' => "✅ ยอดเงินเข้าเรียบร้อย\n" .
                            "เลขที่เอกสาร: $doc_id\n" .
                            "บ้านเลขที่: $house_number\n" .
                            "ชำระค่าส่วนกลางเดือน: $start_month_name - $to_month_name ปี $period_year\n" .
                            "ยอดชำระ: $amount บาท\n" .
                            "วันที่ทำรายการ: $current_datetime\n" .
                            "ขอขอบคุณที่ชำระค่าส่วนกลาง"
                    ],
                    [
                        'type' => 'image',
                        'originalContentUrl' => $image_url,
                        'previewImageUrl' => $image_url
                    ]
                ];

                $messageData = [
                    'to' => $line_user_id,
                    'messages' => $messages_to_send
                ];

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
                // ======= End Send LINE Message =======

                echo 1;
            } else {
                echo 0;
            }
        } else {
            error_log("File upload failed.");
            echo "FILE_UPLOAD_FAILED";
        }
    } else {
        // ไม่มีการอัปโหลดรูป (ส่งแค่ข้อความ)
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

            // ======= Send LINE Message (ไม่มีการอัปโหลดรูปภาพ - ส่งแค่ข้อความ) =======
            $access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

            $messageData = [
                'to' => $line_user_id,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => "✅ ยอดเงินเข้าเรียบร้อย\n" .
                            "เลขที่เอกสาร: $doc_id\n" .
                            "บ้านเลขที่: $house_number\n" .
                            "ชำระค่าส่วนกลางเดือน: $start_month_name - $to_month_name ปี $period_year\n" .
                            "ยอดชำระ: $amount บาท\n" .
                            "วันที่ทำรายการ: $current_datetime\n" .
                            "ขอขอบคุณที่ชำระค่าส่วนกลาง"
                    ]
                ]
            ];

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
            // ======= End Send LINE Message =======

            echo 1;
        } else {
            echo 0;
        }
    }
}
?>