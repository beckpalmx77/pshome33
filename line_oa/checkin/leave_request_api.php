<?php
require "../../config/connect_db.php";
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Asia/Bangkok");

// --- 1. การตั้งค่า Log ---
$logDir = __DIR__ . "/logs";
if (!is_dir($logDir)) mkdir($logDir, 0777, true);
$leaveLogFile = $logDir . "/leave_debug_log.txt";

function writeLeaveLog($message) {
    global $leaveLogFile;
    file_put_contents($leaveLogFile, "[" . date("Y-m-d H:i:s") . "] " . $message . PHP_EOL, FILE_APPEND);
}

// --- 2. LINE Configuration ---
$channelAccessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';
$group_ps33_niti = 'Ca579b4e8daae57c0f07c3508696074ae';

writeLeaveLog("🔧 >>> เริ่มต้นประมวลผล Leave Request <<<");

// --- 3. ตรวจสอบข้อมูลนำเข้า ---
if (isset($_POST['user_id'], $_POST['leave_type'], $_POST['start_date'], $_POST['end_date'])) {
    $userId = $_POST['user_id'];
    $displayName = $_POST['display_name'] ?? 'Unknown';
    $leaveType = $_POST['leave_type'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $swapDate = !empty($_POST['swap_date']) ? $_POST['swap_date'] : null;
    $remark = $_POST['remark'] ?? '';
    $timestamp = date('Y-m-d H:i:s');

    // ... (fetch emp_id logic remains same)

    // --- 5. บันทึกฐานข้อมูล ---
    try {
        $sql_ins = "INSERT INTO leave_requests (user_id, emp_id, emp_name, leave_type, start_date, end_date, swap_date, remark, photo_path, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$userId, $emp_id, $emp_name, $leaveType, $startDate, $endDate, $swapDate, $remark, $photoPaths, $timestamp];
        $stmt = $conn->prepare($sql_ins);
        $stmt->execute($params);
        
        writeLeaveLog("✅ บันทึก Leave Successful: $emp_name ($leaveType)");

        // --- 6. ส่ง LINE Notification ---
        $swapText = ($leaveType === 'สลับวันหยุด' && $swapDate) ? "\n🔄 วันที่ชดเชย: $swapDate" : "";
        $textMessage = [
            'type' => 'text',
            'text' => "📝 แจ้งลางาน/สลับวันหยุดใหม่\n👤 ผู้แจ้ง: $emp_name ($emp_id)\n📋 ประเภท: $leaveType\n📅 วันที่: $startDate ถึง $endDate{$swapText}\n✍️ เหตุผล: $remark"
        ];

        // ส่งให้กลุ่ม PS33
        $ch = curl_init('https://api.line.me/v2/bot/message/push');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['to' => $group_ps33_niti, 'messages' => [$textMessage]]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $channelAccessToken
        ]);
        curl_exec($ch);
        curl_close($ch);

        echo "✅ บันทึกการลางานสำเร็จแล้ว";
    } catch (PDOException $e) {
        writeLeaveLog("❌ DB Error: " . $e->getMessage());
        echo "❌ ไม่สามารถบันทึกข้อมูลได้: " . $e->getMessage();
    }
} else {
    echo "❌ ข้อมูลไม่ครบถ้วน";
}
