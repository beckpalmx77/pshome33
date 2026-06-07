<?php
// 1. รับข้อมูล Webhook Payload จาก LINE
$content = file_get_contents('php://input');
$events = json_decode($content, true);

// ใส่ Channel Access Token ของคุณที่นี่
$access_token = 'UeQDGaIitsNRqYib1mPUo1VjLZfY6lQYvLK1LguyO0hIEYYMZHABHfWEu9UvM4hK8QrGR1V5pUNu/SO+7kOvvLoLjecwTGAE9JsslpnkD1+4mpRtyJqDcZZyQa4/WCuDNHNE9fL1sqR1ujE+mXLnwgdB04t89/1O/w1cDnyilFU=';

// 2. ตรวจสอบว่ามีข้อมูลส่งมาหรือไม่
if (!is_null($events['events'])) {
    foreach ($events['events'] as $event) {

        // ดึงค่าต่างๆ ออกมา
        $type = $event['type'];
        $sourceType = isset($event['source']['type']) ? $event['source']['type'] : 'unknown';
        $userId = isset($event['source']['userId']) ? $event['source']['userId'] : 'ไม่พบ User ID';
        $groupId = isset($event['source']['groupId']) ? $event['source']['groupId'] : 'ไม่พบ Group ID';

        // --- ส่วนที่เพิ่มเข้ามา: ดึงชื่อกลุ่ม ---
        $groupName = 'ไม่พบชื่อกลุ่ม (อาจไม่ใช่ Chat กลุ่ม)';
        if ($sourceType === 'group' && $groupId !== 'ไม่พบ Group ID') {
            $groupName = getLineGroupName($groupId, $access_token);
        }
        // ----------------------------------

        // 3. จัดรูปแบบข้อความที่จะบันทึกลงไฟล์
        $log_text = "เวลา: " . date('Y-m-d H:i:s') . "\n";
        $log_text .= "ประเภท Event: " . $type . "\n";
        $log_text .= "มาจาก (Source): " . $sourceType . "\n";
        $log_text .= "--- ไอดีที่คุณต้องการ ---\n";
        $log_text .= "User ID: " . $userId . "\n";
        $log_text .= "Group ID: " . $groupId . "\n";
        $log_text .= "Group Name: " . $groupName . "\n"; // บันทึกชื่อกลุ่มเพิ่มเข้าไป
        $log_text .= "-----------------------\n\n";

        // 4. บันทึกข้อมูลลงไฟล์ชื่อ log_id.txt
        file_put_contents(__DIR__ . '/webhook_ps33_house_log_id.txt', $log_text, FILE_APPEND);
    }
}

// ตอบกลับ LINE Server
http_response_code(200);
echo "OK";

// --- ฟังก์ชันสำหรับดึงชื่อกลุ่มจาก LINE API ---
function getLineGroupName($groupId, $accessToken) {
    $url = 'https://api.line.me/v2/bot/group/' . $groupId . '/summary';

    $headers = [
        'Authorization: Bearer ' . $accessToken
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $result = curl_exec($ch);
    curl_close($ch);

    if ($result) {
        $profile = json_decode($result, true);
        if (isset($profile['groupName'])) {
            return $profile['groupName']; // ส่งชื่อกลุ่มกลับไป
        }
    }

    return 'ไม่สามารถดึงชื่อกลุ่มได้ (อาจเพราะ Bot ไม่ได้อยู่ในกลุ่มนี้)';
}
?>