<?php
// worker/payment_processor.php

// เส้นทางไปยัง connect_db.php (ปรับให้ถูกต้องตามโครงสร้างโปรเจกต์ของคุณ)
include(__DIR__ . '/../config/connect_db.php');
// include(__DIR__ . '/../config/lang.php'); // อาจจะไม่จำเป็นต้อง include ใน Worker
include(__DIR__ . '/../util/record_util.php'); // สำหรับ LAST_DOCUMENT_NUMBER

// กำหนดค่า RabbitMQ (ควรย้ายไป config file)
$RABBITMQ_HOST = 'localhost';
$RABBITMQ_PORT = 5672;
$RABBITMQ_USER = 'guest';
$RABBITMQ_PASS = 'guest';
$QUEUE_NAME = 'payment_requests';
$UPLOAD_DIR = __DIR__ . '/../uploads/slips/'; // โฟลเดอร์สำหรับเก็บ Slip (ต้องมีอยู่และมีสิทธิ์เขียน)

// ตรวจสอบและสร้างโฟลเดอร์ถ้าไม่มี
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0777, true);
    echo "Created upload directory: " . $UPLOAD_DIR . "\n";
}

echo "Worker started. Waiting for messages...\n";

try {
    // ใช้ AMQP extension
    $connection = new AMQPConnection($RABBITMQ_HOST, $RABBITMQ_PORT, $RABBITMQ_USER, $RABBITMQ_PASS);
    $channel = new AMQPChannel($connection);
    $queue = new AMQPQueue($channel);
    $queue->setName($QUEUE_NAME);
    $queue->setFlags(AMQP_DURABLE); // ต้องตรงกับ Producer
    $queue->declare();

    // ตั้งค่า prefetch_count เพื่อจำกัดจำนวนข้อความที่ worker ประมวลผลพร้อมกัน
    // (ช่วยป้องกัน worker โหลดเกินไป)
    $channel->qos(0, 1); // 1 หมายถึง worker จะประมวลผลทีละ 1 ข้อความ

    $queue->consume(function($envelope, $queue) use ($conn, $UPLOAD_DIR) {
        $msg_body = $envelope->getBody();
        $message_payload = json_decode($msg_body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Failed to decode JSON from RabbitMQ: " . $msg_body);
            $queue->reject($envelope->getDeliveryTag()); // Reject ข้อความถ้า JSON ไม่ถูกต้อง
            return;
        }

        $payment_data = $message_payload['data'] ?? [];
        $file_base64 = $message_payload['file_base64'] ?? null;
        $original_file_name = $payment_data['original_file_name'] ?? 'slip_no_name.jpg';
        $original_file_type = $payment_data['original_file_type'] ?? 'image/jpeg';

        echo "[Worker] Received message for house: " . ($payment_data['house_number'] ?? 'N/A') . "\n";
        echo "[Worker] Processing payment for amount: " . ($payment_data['amount'] ?? 'N/A') . "\n";

        try {
            $conn->beginTransaction();

            // 1. บันทึกรูปภาพ (ถ้ามี)
            $picture_filename = null;
            if ($file_base64) {
                // สร้างชื่อไฟล์ที่ไม่ซ้ำกัน
                $file_extension = pathinfo($original_file_name, PATHINFO_EXTENSION);
                if (empty($file_extension) && strpos($original_file_type, 'image/') === 0) {
                    $file_extension = explode('/', $original_file_type)[1];
                }
                $new_file_name = uniqid('slip_') . '.' . ($file_extension ?: 'jpg');
                $picture_filepath = $UPLOAD_DIR . $new_file_name;
                file_put_contents($picture_filepath, base64_decode($file_base64));
                $picture_filename = $new_file_name;
                echo "[Worker] Saved slip image: " . $picture_filepath . "\n";
            }

            // 2. ดึงข้อมูล user เพื่อสร้าง doc_id และ contact_name
            $line_user_id = $payment_data['line_user_id'];
            $stmt = $conn->prepare("SELECT f_name, l_name, line_phone FROM ims_house_line_user WHERE line_user_id = :line_user_id");
            $stmt->bindParam(':line_user_id', $line_user_id);
            $stmt->execute();
            $line_user = $stmt->fetch(PDO::FETCH_ASSOC);
            $f_name = $line_user['f_name'] ?? '';
            $l_name = $line_user['l_name'] ?? '';
            $contact_name = trim($f_name . " " . $l_name);
            $line_phone = $line_user['line_phone'] ?? '';


            // สร้าง doc_id และ runno (ใช้ฟังก์ชันจาก util/record_util.php)
            $field = "runno";
            $table = "ims_house_payment";
            $cond = " WHERE house_number = '" . $payment_data['house_number'] . "' AND period_year = '" . $payment_data['period_year'] . "'";
            $runno = LAST_DOCUMENT_NUMBER($conn, $field, $table, $cond);
            $doc_id = "P-" . $payment_data['house_number'] . "-" . $payment_data['period_year'] . "-" . sprintf('%03s', $runno);


            // 3. บันทึกข้อมูลลงฐานข้อมูล (ตาราง ims_house_payment)
            // (ใช้โค้ด INSERT จาก manage_payment_transfer_smart.php เดิม)
            $ins_str = "INSERT INTO ims_house_payment (doc_id, payment_date, house_number, detail,runno,period_month_start,period_month_to,period_year,amount,picture_payment,remark,payment_type,line_user_id,line_picture_profile_show,create_by,payment_method)
            VALUES (:doc_id, :payment_date, :house_number,:detail, :runno,:period_month_start,:period_month_to,:period_year,:amount,:picture_payment,:remark,:payment_type,:line_user_id,:line_picture_profile_show,:create_by,:payment_method)";
            $stmt_insert = $conn->prepare($ins_str);

            $stmt_insert->bindParam(':doc_id', $doc_id);
            $stmt_insert->bindParam(':payment_date', $payment_data['payment_date']);
            $stmt_insert->bindParam(':house_number', $payment_data['house_number']);
            $stmt_insert->bindParam(':detail', $payment_data['detail']);
            $stmt_insert->bindParam(':runno', $runno);
            $stmt_insert->bindParam(':period_month_start', $payment_data['period_month_start']);
            $stmt_insert->bindParam(':period_month_to', $payment_data['period_month_to']);
            $stmt_insert->bindParam(':period_year', $payment_data['period_year']);
            $stmt_insert->bindParam(':amount', $payment_data['amount']);
            $stmt_insert->bindParam(':picture_payment', $picture_filename); // บันทึกชื่อไฟล์ลง DB
            $stmt_insert->bindParam(':remark', $payment_data['remark']);
            $stmt_insert->bindParam(':payment_type', $payment_data['payment_type']);
            $stmt_insert->bindParam(':line_user_id', $payment_data['line_user_id']);
            $stmt_insert->bindParam(':line_picture_profile_show', $payment_data['pictureUrl']);
            $stmt_insert->bindParam(':create_by', $payment_data['detail']); // หรือใช้ displayName
            $stmt_insert->bindParam(':payment_method', $payment_data['payment_method']);

            $stmt_insert->execute();

            // 4. อัปเดตข้อมูล ims_house (ถ้ามี)
            $updateSql = "UPDATE ims_house SET contact_name = :contact_name , phone_number = :phone_number WHERE house_number = :house_number";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindParam(':contact_name', $contact_name);
            $updateStmt->bindParam(':phone_number', $line_phone);
            $updateStmt->bindParam(':house_number', $payment_data['house_number']);
            $updateStmt->execute();


            // 5. ส่งข้อความแจ้งเตือนกลับ LINE (ถ้าต้องการให้ Worker เป็นผู้ส่ง)
            // (ใช้โค้ด LINE API push message จาก manage_payment_transfer_smart.php เดิม)
            $access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU='; // ควรเก็บไว้ในที่ปลอดภัยและดึงมาใช้งานอย่างระมัดระวัง

            $messageData = [
                'to' => $payment_data['line_user_id'],
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => "บันทึกการชำระเงินเรียบร้อย (จาก Worker)\nเลขที่เอกสาร: $doc_id\nจำนวน: {$payment_data['amount']} บาท"
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
            // ตรวจสอบผลลัพธ์การส่ง LINE (สำหรับ debug)
            // error_log("LINE Push Result: " . $result);


            $conn->commit();
            echo "[Worker] Successfully saved payment for house: " . $payment_data['house_number'] . "\n";
            $queue->ack($envelope->getDeliveryTag()); // Acknowledge ข้อความว่าประมวลผลสำเร็จ

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Worker DB Error for house " . ($payment_data['house_number'] ?? 'N/A') . ": " . $e->getMessage());
            echo "[Worker] DB Error: " . $e->getMessage() . "\n";
            // หากเกิดข้อผิดพลาดในการบันทึก, อาจจะ reject ข้อความเพื่อให้ RabbitMQ ส่งไปให้ Worker ตัวอื่นลองใหม่
            // หรือส่งไปที่ Dead-Letter Exchange (DLX)
            $queue->reject($envelope->getDeliveryTag(), AMQP_REQUEUE); // REQUEUE: ส่งกลับเข้าคิวเดิม
        } catch (Exception $e) {
            error_log("Worker General Error for house " . ($payment_data['house_number'] ?? 'N/A') . ": " . $e->getMessage());
            echo "[Worker] General Error: " . $e->getMessage() . "\n";
            $queue->reject($envelope->getDeliveryTag(), AMQP_REQUEUE);
        }
    });

} catch (AMQPConnectionException $e) {
    error_log("Worker RabbitMQ Connection Error: " . $e->getMessage());
    echo "Worker RabbitMQ Connection Error: " . $e->getMessage() . "\n";
} catch (AMQPChannelException $e) {
    error_log("Worker RabbitMQ Channel Error: " . $e->getMessage());
    echo "Worker RabbitMQ Channel Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    error_log("Worker General Error: " . $e->getMessage());
    echo "Worker General Error: " . $e->getMessage() . "\n";
}