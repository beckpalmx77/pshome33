<?php

// 1. กำหนดข้อมูลของคุณและ Endpoint ตามตัวอย่าง
$consumerId     = "wxcEBmEjaLeu1HxqL5AJrEjJRuuMSEnj";
$consumerSecret = "u6GFTbFG8Y50H7V6";
//$tokenUrl = "https://sandbox-api.kasikornbank.com/v2/oauth/token";
$tokenUrl = "https://openapi-sandbox.kasikornbank.com/v2/oauth/token";

// 2. เตรียมข้อมูล Body แบบ x-www-form-urlencoded
$payload = http_build_query([
    'grant_type' => 'client_credentials'
]);

// 3. เริ่มต้นสั่งงาน cURL
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// ส่ง Header Content-Type ตามตัวอย่าง cURL
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'x-test-mode: true',
    'env-id: OAUTH2'
]);

// สำหรับ Authorization: Basic ระบบ cURL จะจับคู่ ID:Secret แล้วเข้ารหัส Base64 ให้เองอัตโนมัติครับ
curl_setopt($ch, CURLOPT_USERPWD, $consumerId . ":" . $consumerSecret);

// 4. ประมวลผลและดูผลลัพธ์
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// ถ้าระบบ cURL ทำงานผิดพลาด (เช่น ต่อเน็ตไม่ได้ หรือ URL ผิด)
if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch); //  แก้ไขเป็น curl_error เรียบร้อยครับ
}

curl_close($ch);

// 5. แสดงผลลัพธ์ที่ได้จาก K-API
echo "HTTP Status Code: " . $httpCode . "\n";
echo "Response:\n";
print_r(json_decode($response, true) ?? $response);

?>