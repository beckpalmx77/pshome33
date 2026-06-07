<?php
// ดึงข้อมูลจริงของคุณจากข้อ 1 มาใส่
$consumerId     = "osGBH4vjFNr5xurHb3ckHGdRrMAZSSpg";
$consumerSecret = "NObdvhQ2bv76uGFM";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://openapi-sandbox.kasikornbank.com/v2/oauth/token"); // ใส่ URL จริงของกสิกร
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// ข้อ 3: ระบุ Content-Type และ Authorization ใน Header
$authHeader = "Authorization: Basic " . base64_encode($consumerId . ":" . $consumerSecret);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'x-test-mode: true',
    'env-id: OAUTH2',
    $authHeader
]);
curl_setopt($ch, CURLOPT_USERAGENT, 'PostmanRuntime/7.39.0');

// ข้อ 4: ระบุ body เป็น grant_type=client_credentials
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
}
curl_close($ch);

echo "HTTP Status Code: " . $httpCode . "\n";
echo "Response:\n" . $response . "\n";