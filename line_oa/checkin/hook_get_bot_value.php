<?php


// กำหนดค่า Content Type เป็น JSON (ถ้าต้องการส่ง response เป็น JSON กลับไป)
// header('Content-Type: application/json'); // ไม่จำเป็นถ้าแค่ตอบ OK

// --- ค่าคงที่และตัวแปร ---
// ใส่ Channel Secret ของคุณที่นี่
// **สำคัญ:** ใน Production ควรดึงค่านี้มาจาก Environment Variable หรือไฟล์ Config ที่ปลอดภัย
// $channelSecret = getenv('LINE_CHANNEL_SECRET');
$channelSecret = 'a4d4833b1333f6227889c22bcd2c38c2'; // <--- แทนที่ด้วย Channel Secret จริงของคุณ

// --- รับข้อมูล Request ---
// 1. รับ Signature จาก Header
$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? ''; // ใช้ Null Coalescing Operator (PHP 7+)
// หรือใช้: $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

// 2. รับ Raw Body ของ Request
$body = file_get_contents('php://input');

// ตรวจสอบว่ามีข้อมูลที่จำเป็นครบถ้วนหรือไม่
if (empty($signature)) {
    error_log("Webhook Error: Missing X-Line-Signature header");
    http_response_code(400); // Bad Request
    echo 'Missing signature';
    exit();
}

if (empty($body)) {
    error_log("Webhook Error: Request body is empty");
    http_response_code(400); // Bad Request
    echo 'Empty request body';
    exit();
}

// --- ตรวจสอบ Signature (สำคัญมากเพื่อความปลอดภัย) ---
try {
    // คำนวณ Hash ของ Body ด้วย Channel Secret
    // ใช้ hash_hmac() และตั้งค่าพารามิเตอร์ที่ 4 เป็น true เพื่อให้ได้ raw binary output
    $hash = hash_hmac('sha256', $body, $channelSecret, true);
    // เข้ารหัส Hash ด้วย Base64
    $computedSignature = base64_encode($hash);

    // เปรียบเทียบ Signature ที่คำนวณได้กับที่ส่งมาใน Header
    // ใช้ hash_equals() เพื่อป้องกัน Timing Attack
    if (!hash_equals($signature, $computedSignature)) {
        error_log("Webhook Error: Invalid signature. Comp: " . $computedSignature . " Recv: " . $signature);
        http_response_code(400); // Bad Request
        echo 'Invalid signature';
        exit();
    }
} catch (Exception $e) {
    error_log("Webhook Error: Exception during signature validation - " . $e->getMessage());
    http_response_code(400); // Bad Request
    echo 'Signature validation error';
    exit();
}


// --- แปลงข้อมูล JSON (ถ้า Signature ถูกต้อง) ---
// ตั้งค่าพารามิเตอร์ที่สองเป็น true หากต้องการผลลัพธ์เป็น Array แทน Object
// $data = json_decode($body, true);
$data = json_decode($body); // ค่า default คือ ได้ผลลัพธ์เป็น Object

// ตรวจสอบว่าการแปลง JSON สำเร็จหรือไม่
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Webhook Error: Invalid JSON received - " . json_last_error_msg());
    http_response_code(400); // Bad Request
    echo 'Invalid JSON';
    exit();
}

// --- ประมวลผล Events ---
try {
    // ใช้ -> สำหรับเข้าถึง property ของ object
    // ใช้ ?? [] เพื่อป้องกัน error หาก events ไม่มีอยู่ หรือไม่ใช่ array/object ที่วนลูปได้
    $events = $data->events ?? [];

    // เขียน Log ข้อมูลที่ได้รับ (สำหรับ Debug)
    error_log("Webhook Received Data: " . print_r($data, true));

    foreach ($events as $event) {
        error_log("Processing Event Type: " . ($event->type ?? 'N/A'));

        // ตรวจสอบว่ามี source หรือไม่ ก่อนเข้าถึง property ภายใน
        if (isset($event->source)) {
            $source = $event->source;
            $sourceType = $source->type ?? 'N/A';
            error_log("Source Type: " . $sourceType);

            if ($sourceType === 'group' && isset($source->groupId)) {
                $groupId = $source->groupId;
                $userId = $source->userId ?? null; // User ที่ทำให้เกิด event (ถ้ามี)
                error_log("  Group ID: " . $groupId);
                if ($userId) {
                    error_log("  User ID: " . $userId);
                }
                // --- คุณสามารถนำ $groupId ไปใช้งานต่อได้ที่นี่ ---
                // เช่น: saveToDatabase($groupId);

            } elseif ($sourceType === 'user' && isset($source->userId)) {
                $userId = $source->userId;
                error_log("  User ID: " . $userId);
                // จัดการ event จากผู้ใช้ 1:1

            } elseif ($sourceType === 'room' && isset($source->roomId)) {
                $roomId = $source->roomId;
                $userId = $source->userId ?? null; // User ที่ทำให้เกิด event (ถ้ามี)
                error_log("  Room ID: " . $roomId);
                if ($userId) {
                    error_log("  User ID: " . $userId);
                }
                // จัดการ event จากห้องแชทหลายคน
            }

            // จัดการ event ประเภทอื่นๆ ตามต้องการ
            if ($event->type === 'message' && isset($event->message)) {
                $message = $event->message;
                $messageType = $message->type ?? 'N/A';
                error_log("  Message Type: " . $messageType);
                if ($messageType === 'text' && isset($message->text)) {
                    error_log("  Message Text: " . $message->text);
                }
                // เพิ่มการจัดการ message ประเภทอื่นๆ เช่น image, sticker ได้ที่นี่
            }
        } else {
            error_log("Webhook Warning: Event has no source object.");
        }
    } // end foreach loop

} catch (Exception $e) {
    // จัดการ Error ที่อาจเกิดขึ้นระหว่างประมวลผล Event
    error_log("Webhook Error: Exception during event processing - " . $e->getMessage());
    // อาจจะตอบ 200 OK เพื่อไม่ให้ LINE ส่งมาซ้ำ หรือตอบ 500 เพื่อแจ้งปัญหา ก็ได้
    // แต่โดยทั่วไป LINE แนะนำให้ตอบ 200 แม้จะประมวลผลภายในผิดพลาด เพื่อป้องกันการ retry ที่ไม่จำเป็น
    // http_response_code(500); // Internal Server Error
    // echo 'Error processing events';
    // exit();
}


// --- ตอบกลับ LINE ว่าได้รับข้อมูลเรียบร้อย ---
// **สำคัญ:** ต้องตอบกลับด้วย HTTP Status Code 200 OK ภายในเวลาที่กำหนด (ไม่กี่วินาที)
// เพื่อให้ LINE ทราบว่า Webhook ของเราทำงานปกติ ไม่เช่นนั้น LINE อาจส่ง Event เดิมมาซ้ำ
http_response_code(200);
echo 'OK'; // เนื้อหา Response ไม่สำคัญ แต่ต้องมี Status Code 200

exit(); // จบการทำงานของ Script

