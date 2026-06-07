<?php

// 1. กำหนดค่าคอนฟิกพื้นฐาน
$consumerId     = "wxcEBmEjaLeu1HxqL5AJrEjJRuuMSEnj";
$consumerSecret = "u6GFTbFG8Y50H7V6";

// หมายเหตุ: URL ด้านล่างนี้เป็นเพียงตัวอย่าง (กรุณาตรวจสอบคู่มือของ KBank อีกครั้งว่าใช้แอปพลิเคชันเวอร์ชัน/Endpoint ไหน)
$host = "https://openapi-sandbox.kasikornbank.com";

// ==========================================
// STEP 1: ขอ Access Token (OAuth 2.0)
// ==========================================
echo "=== Step 1: Requesting Access Token ===\n";

$tokenUrl = $host . "/v2/oauth/token";
$payload = "grant_type=client_credentials";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
// ส่งแบบ Basic Authentication (รวม ID และ Secret)
curl_setopt($ch, CURLOPT_USERPWD, $consumerId . ":" . $consumerSecret);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'x-test-mode: true',
    'env-id: OAUTH2'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die("Error requesting token. HTTP Code: $httpCode, Response: $response\n");
}

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? null;

if (!$accessToken) {
    die("Access token not found in response.\n");
}

echo "Access Token Obtained successfully!\n\n";


// ==========================================
// STEP 2: สั่งสร้าง QR Code (ตัวอย่าง API)
// ==========================================
echo "=== Step 2: Generating QR Code ===\n";

// URL สำหรับสร้าง QR Code (อ้างอิงตามสเปกของ KBank Partner API เช่น /v1/qr-codes)
$qrcodeUrl = $host . "/v1/qr-codes";

// ข้อมูลที่ต้องส่งให้ KBank (ปรับเปลี่ยนตามเงื่อนไขในคู่มือของธนาคาร)
$qrRequestBody = [
    "partnerTxnRef" => "TXN" . time(), // เลขอ้างอิงฝั่งเรา (ห้ามซ้ำ)
    "partnerId"     => "YOUR_PARTNER_ID", // ต้องนำมาจากที่ลงทะเบียนไว้กับ KBank
    "amount"        => 150.75,
    "currency"      => "THB",
    "reference1"    => "INV001",
    "reference2"    => "CUST001"
];

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $qrcodeUrl);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($qrRequestBody));
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $accessToken, // แนบ Token ที่ได้จากสเต็ปแรก
    "Content-Type: application/json"
]);

$qrResponse = curl_exec($ch2);
$qrHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "HTTP Code: $qrHttpCode\n";
echo "Response Data:\n";
print_r(json_decode($qrResponse, true) ?? $qrResponse);

?>