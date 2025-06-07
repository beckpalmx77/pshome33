<?php
session_start();
error_reporting(0);

include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่า POST
    $fields = [
        'payment_date', 'house_number', 'detail', 'payment_type',
        'period_month_start', 'period_month_to', 'period_year',
        'amount', 'remark', 'line_user_id', 'pictureUrl', 'displayName'
    ];
    foreach ($fields as $field) {
        $$field = $_POST[$field];
    }

    $picture_payment = $_FILES['picture_payment'];
    $payment_method = "โอนเงิน";

    // ข้อมูล LINE USER
    $stmt = $conn->prepare("SELECT * FROM ims_house_line_user WHERE line_user_id = :line_user_id");
    $stmt->bindParam(':line_user_id', $line_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $line_user = $stmt->fetch(PDO::FETCH_ASSOC);
    $contact_name = $line_user['f_name'] . " " . $line_user['l_name'];
    $line_phone = $line_user['line_phone'];

    // สร้าง doc_id
    $runno = LAST_DOCUMENT_NUMBER($conn, 'runno', 'ims_house_payment', "WHERE house_number = '$house_number' AND period_year = '$period_year'");
    $doc_id = "P-$house_number-$period_year-" . sprintf('%03s', $runno);

    $has_file = ($picture_payment['error'] == 0);
    $file_name = '';

    // ถ้ามีไฟล์แนบ ตรวจสอบและอัปโหลด
    if ($has_file) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($picture_payment['type'], $allowed_types)) {
            error_log("Invalid file type: " . $picture_payment['type']);
            echo "INVALID_FILE_TYPE";
            exit;
        }

        $upload_dir = '../uploads/slips/';
        $file_name = time() . "_" . basename($picture_payment['name']);
        $file_path = $upload_dir . $file_name;

        if (!move_uploaded_file($picture_payment['tmp_name'], $file_path)) {
            error_log("File upload failed.");
            echo "FILE_UPLOAD_FAILED";
            exit;
        }
    }

    // Prepare SQL
    $columns = [
        'doc_id', 'payment_date', 'house_number', 'detail', 'runno',
        'period_month_start', 'period_month_to', 'period_year',
        'amount', 'remark', 'line_user_id', 'line_picture_profile_show',
        'create_by', 'payment_method'
    ];
    if ($has_file) {
        $columns[] = 'picture_payment';
    }

    $placeholders = array_map(fn($col) => ":$col", $columns);
    $sql = "INSERT INTO ims_house_payment (" . implode(',', $columns) . ")
            VALUES (" . implode(',', $placeholders) . ")";
    $stmt = $conn->prepare($sql);

    // Bind values
    foreach ($columns as $col) {
        $value = $$col ?? ($col === 'runno' ? $runno : '');
        if ($col === 'picture_payment') $value = $file_name;
        $stmt->bindValue(":$col", $value);
    }

    // Execute & update
    if ($stmt->execute()) {
        $updateSql = "UPDATE ims_house SET contact_name = :contact_name, phone_number = :phone_number WHERE house_number = :house_number";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([
            ':contact_name' => $contact_name,
            ':phone_number' => $line_phone,
            ':house_number' => $house_number
        ]);

        // ======= ส่งเฉพาะข้อความไป LINE =======
        $access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';
        $messageData = [
            'to' => $line_user_id,
            'messages' => [[
                'type' => 'text',
                'text' => "บันทึกการชำระเงินเรียบร้อย\nเลขที่เอกสาร: $doc_id\nจำนวน: $amount บาท"
            ]]
        ];
        $ch = curl_init('https://api.line.me/v2/bot/message/push');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token
            ],
            CURLOPT_POSTFIELDS => json_encode($messageData),
            CURLOPT_RETURNTRANSFER => true
        ]);
        curl_exec($ch);
        curl_close($ch);
        // ======= จบส่งข้อความ =======

        echo 1;
    } else {
        echo 0;
    }
}
