<?php

/**
 * ฟังก์ชันสำหรับดึงชื่อกลุ่ม LINE จาก Group ID
 * * @param string $groupId ID ของกลุ่ม LINE (ขึ้นต้นด้วย C...)
 * @param string $channelAccessToken Token ของ LINE Bot
 * @return array|null คืนค่าแอปเรย์ข้อมูลกลุ่ม หรือ null ถ้าไม่พบ/เกิดข้อผิดพลาด
 */
function getLineGroupName($groupId, $channelAccessToken) {
    // 1. กำหนด Endpoint ของ LINE API
    $url = "https://api.line.me/v2/bot/group/" . urlencode($groupId) . "/summary";

    // 2. ตั้งค่า Header สำหรับการยืนยันตัวตน
    $headers = [
        "Authorization: Bearer " . $channelAccessToken,
        "Content-Type: application/json"
    ];

    // 3. เริ่มต้นใช้ cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // ตั้งเวลา Timeout 10 วินาที

    // 4. ยิง Request และรับ Response
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // ตรวจสอบ Error ของ cURL
    if (curl_errno($ch)) {
        error_log('cURL Error: ' . curl_error($ch));
        curl_close($ch);
        return null;
    }

    curl_close($ch);

    // 5. ประมวลผลผลลัพธ์
    if ($httpCode === 200) {
        // ดึงสำเร็จ แปลง JSON เป็น Array ของ PHP
        return json_decode($response, true);
    } else {
        // เกิดข้อผิดพลาด เช่น บอทไม่ได้อยู่ในกลุ่ม หรือ Token ผิด
        error_log("LINE API Error (HTTP Status $httpCode): " . $response);
        return null;
    }
}

// ==========================================
// ตัวอย่างการเรียกใช้งาน (Usage Example)
// ==========================================

$channelToken = "YOUR_LINE_CHANNEL_ACCESS_TOKEN"; // ใส่ Token ของคุณที่นี่
$targetGroupId = "Ca123456789abcdef0123456789abcdef"; // ใส่ Group ID ที่ต้องการเช็ค

$groupInfo = getLineGroupName($targetGroupId, $channelToken);

if ($groupInfo !== null) {
    echo "<h3>ค้นหาสำเร็จ!</h3>";
    echo "<b>Group ID:</b> " . htmlspecialchars($groupInfo['groupId']) . "<br>";
    echo "<b>ชื่อกลุ่ม:</b> " . htmlspecialchars($groupInfo['groupName']) . "<br>";
    if (isset($groupInfo['pictureUrl'])) {
        echo "<b>รูปโปรไฟล์กลุ่ม:</b> <br><img src='" . htmlspecialchars($groupInfo['pictureUrl']) . "' width='100'>";
    }
} else {
    echo "<h3>ไม่สามารถดึงข้อมูลกลุ่มได้</h3>";
    echo "โปรดตรวจสอบว่า LINE Bot ยังอยู่ในกลุ่มนี้ หรือ Channel Access Token ถูกต้องหรือไม่";
}