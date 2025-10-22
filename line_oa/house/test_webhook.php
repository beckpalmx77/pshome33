<?php
// hello_lineoa.php
// บันทึก raw input ลงไฟล์ไว้ดู
$rawData = file_get_contents('php://input');
file_put_contents('log.txt', $rawData . PHP_EOL, FILE_APPEND);

// ตอบกลับ 200 OK กลับให้ LINE
http_response_code(200);
echo "Webhook received.";