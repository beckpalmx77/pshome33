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
    $line_user_id = $_POST['line_user_id'];
    $pictureUrl = $_POST['pictureUrl'];
    $displayName = $_POST['displayName'];
    $picture_payment = $_FILES['picture_payment'];

    $payment_method = "โอนเงิน";

    $stmt = $conn->prepare("SELECT * FROM ims_house_line_user WHERE line_user_id = :line_user_id");
    $stmt->bindParam(':line_user_id', $line_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $line_user = $stmt->fetch(PDO::FETCH_ASSOC);
    $f_name = $line_user['f_name'];
    $l_name = $line_user['l_name'];

    $field = "runno";
    $table = "ims_house_payment";
    $cond = " WHERE house_number = '" . $house_number . "' AND period_year = '" . $period_year . "'";

    $runno = LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond);
    $doc_id = "P-" . $house_number . "-" . $period_year . "-" . sprintf('%03s', $runno);

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
                // ======= ส่งเฉพาะข้อความไป LINE =======
                $access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

                $messageData = [
                    'to' => $line_user_id,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => "บันทึกการชำระเงินเรียบร้อย\nเลขที่เอกสาร: $doc_id\nจำนวน: $amount บาท"
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
                // ======= จบส่งเฉพาะข้อความ =======

                echo 1;
            } else {
                echo 0;
            }
        } else {
            error_log("File upload failed.");
            echo "FILE_UPLOAD_FAILED";
        }
    } else {
        // ไม่มีการอัปโหลดรูป
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
            // ======= ส่งเฉพาะข้อความไป LINE =======
            $access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

            $messageData = [
                'to' => $line_user_id,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => "บันทึกการชำระเงินเรียบร้อย\nเลขที่เอกสาร: $doc_id\nจำนวน: $amount บาท"
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
            // ======= จบส่งเฉพาะข้อความ =======

            echo 1;
        } else {
            echo 0;
        }
    }
}