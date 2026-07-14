<?php
include('../../config/connect_db.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid Request Method"]);
    exit;
}

$action = $_POST['action'] ?? '';
$topic_id = filter_input(INPUT_POST, 'topic_id', FILTER_VALIDATE_INT);
$house_number = trim($_POST['house_number'] ?? '');
$option_id = filter_input(INPUT_POST, 'option_id', FILTER_VALIDATE_INT);
$line_user_id = trim($_POST['lineUserId'] ?? '');

// 1. ดึงหัวข้อโหวตที่เปิดใช้งานอยู่
if ($action === 'get_active_topics') {
    try {
        $stmt = $conn->prepare("SELECT * FROM ims_vote_topic WHERE status = 'active' ORDER BY topic_id DESC");
        $stmt->execute();
        $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ดึงตัวเลือกสำหรับแต่ละหัวข้อโหวต
        $stmt_opt = $conn->prepare("SELECT * FROM ims_vote_option WHERE topic_id = ? ORDER BY option_id ASC");
        foreach ($topics as &$topic) {
            $stmt_opt->execute([$topic['topic_id']]);
            $topic['options'] = $stmt_opt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(["success" => true, "topics" => $topics]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}

// 2. ตรวจสอบสถานะการโหวตของบ้านเลขที่นี้
if ($action === 'check_vote') {
    if (!$topic_id || empty($house_number)) {
        echo json_encode(["success" => false, "message" => "ข้อมูลไม่ครบถ้วน"]);
        exit;
    }

    try {
        // ตรวจสอบว่าเคยโหวตหรือยัง
        $stmt = $conn->prepare("SELECT r.voted_at, o.option_text 
            FROM ims_vote_record r 
            JOIN ims_vote_option o ON r.option_id = o.option_id 
            WHERE r.topic_id = ? AND r.house_number = ?");
        $stmt->execute([$topic_id, $house_number]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            echo json_encode([
                "success" => true, 
                "voted" => true, 
                "message" => "บ้านเลขที่ $house_number ได้ลงประชามติในหัวข้อนี้ไปแล้ว",
                "record" => $record
            ]);
        } else {
            echo json_encode(["success" => true, "voted" => false]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}

// 3. ส่งคะแนนโหวต (Submit Vote)
if ($action === 'submit_vote') {
    if (!$topic_id || empty($house_number) || !$option_id || empty($line_user_id)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกข้อมูลโหวตให้ครบถ้วน"]);
        exit;
    }

    try {
        // เพิ่มความปลอดภัยขั้นสูง: ตรวจเช็กว่า line_user_id ผูกกับ house_number นี้จริงในตารางมาสเตอร์ ป้องกันการยิงสวมสิทธิ์
        $stmt_check_line = $conn->prepare("SELECT house_number FROM ims_house_line_user WHERE line_user_id = ?");
        $stmt_check_line->execute([$line_user_id]);
        $registered_house = $stmt_check_line->fetchColumn();

        if (!$registered_house || strtolower(preg_replace('/\s+/', '', $registered_house)) !== strtolower(preg_replace('/\s+/', '', $house_number))) {
            echo json_encode(["success" => false, "message" => "บัญชี LINE ของท่านไม่ได้รับอนุญาตให้ใช้สิทธิ์บ้านเลขที่นี้"]);
            exit;
        }

        // ตรวจสอบความซ้ำซ้อนในตารางโหวต
        $stmt_check_vote = $conn->prepare("SELECT voted_at FROM ims_vote_record WHERE topic_id = ? AND house_number = ?");
        $stmt_check_vote->execute([$topic_id, $house_number]);
        $existing = $stmt_check_vote->fetch();

        if ($existing) {
            echo json_encode([
                "success" => false, 
                "message" => "บ้านเลขที่ $house_number ได้ใช้สิทธิ์ลงคะแนนในหัวข้อนี้ไปแล้ว"
            ]);
            exit;
        }

        // ทำการบันทึก
        $stmt_insert = $conn->prepare("INSERT INTO ims_vote_record (topic_id, house_number, option_id) VALUES (?, ?, ?)");
        $stmt_insert->execute([$topic_id, $house_number, $option_id]);

        echo json_encode(["success" => true, "message" => "บันทึกคะแนนโหวตของคุณเรียบร้อยแล้ว ขอบคุณสำหรับการมีส่วนร่วม"]);

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาดในการบันทึก: " . $e->getMessage()]);
    }
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid Action"]);
?>
