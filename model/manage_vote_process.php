<?php
session_start();
error_reporting(0);
require_once('../config/connect_db.php');

if (empty($_SESSION['alogin'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'GET_ALL') {
    try {
        $stmt = $conn->prepare("SELECT t.*, 
            (SELECT COUNT(*) FROM ims_vote_record r WHERE r.topic_id = t.topic_id) as total_votes 
            FROM ims_vote_topic t 
            ORDER BY t.topic_id DESC");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $data]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

if ($action === 'GET_DATA') {
    $id = $_POST['id'] ?? 0;
    try {
        // Get Topic
        $stmt = $conn->prepare("SELECT * FROM ims_vote_topic WHERE topic_id = ?");
        $stmt->execute([$id]);
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get Options
        $stmt_opt = $conn->prepare("SELECT * FROM ims_vote_option WHERE topic_id = ? ORDER BY option_id ASC");
        $stmt_opt->execute([$id]);
        $options = $stmt_opt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "topic" => $topic,
            "options" => $options
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

if ($action === 'ADD') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $options = $_POST['options'] ?? [];

    if (empty($title) || count($options) < 2) {
        echo json_encode(["status" => "error", "message" => "กรุณาระบุหัวข้อและกรอกตัวเลือกอย่างน้อย 2 ตัวเลือก"]);
        exit;
    }

    try {
        $conn->beginTransaction();

        // 1. Insert Topic
        $stmt = $conn->prepare("INSERT INTO ims_vote_topic (title, description, status) VALUES (?, ?, 'active')");
        $stmt->execute([$title, $description]);
        $topic_id = $conn->lastInsertId();

        // 2. Insert Options
        $stmt_opt = $conn->prepare("INSERT INTO ims_vote_option (topic_id, option_text) VALUES (?, ?)");
        foreach ($options as $opt_text) {
            $opt_text = trim($opt_text);
            if ($opt_text !== '') {
                $stmt_opt->execute([$topic_id, $opt_text]);
            }
        }

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "บันทึกและเปิดโหวตสำเร็จ"]);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

if ($action === 'TOGGLE_STATUS') {
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? 'active';

    if (!in_array($status, ['active', 'inactive'])) {
        echo json_encode(["status" => "error", "message" => "สถานะไม่ถูกต้อง"]);
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE ims_vote_topic SET status = ? WHERE topic_id = ?");
        $stmt->execute([$status, $id]);
        echo json_encode(["status" => "success", "message" => "อัปเดตสถานะสำเร็จ"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

if ($action === 'DELETE') {
    $id = $_POST['id'] ?? 0;
    try {
        $stmt = $conn->prepare("DELETE FROM ims_vote_topic WHERE topic_id = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => "success", "message" => "ลบหัวข้อโหวตเรียบร้อยแล้ว"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

if ($action === 'GET_RESULTS') {
    $id = $_POST['id'] ?? 0;
    try {
        // Get Topic
        $stmt = $conn->prepare("SELECT * FROM ims_vote_topic WHERE topic_id = ?");
        $stmt->execute([$id]);
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get options with their vote count
        $stmt_opt = $conn->prepare("SELECT o.*, 
            (SELECT COUNT(*) FROM ims_vote_record r WHERE r.option_id = o.option_id) as vote_count 
            FROM ims_vote_option o 
            WHERE o.topic_id = ? 
            ORDER BY o.option_id ASC");
        $stmt_opt->execute([$id]);
        $options = $stmt_opt->fetchAll(PDO::FETCH_ASSOC);

        // Get total votes
        $stmt_total = $conn->prepare("SELECT COUNT(*) FROM ims_vote_record WHERE topic_id = ?");
        $stmt_total->execute([$id]);
        $total_votes = $stmt_total->fetchColumn();

        // Get Audit Logs (Voting History)
        $stmt_logs = $conn->prepare("SELECT r.house_number, r.voted_at, o.option_text 
            FROM ims_vote_record r 
            JOIN ims_vote_option o ON r.option_id = o.option_id 
            WHERE r.topic_id = ? 
            ORDER BY r.voted_at DESC");
        $stmt_logs->execute([$id]);
        $logs = $stmt_logs->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "topic" => $topic,
            "options" => $options,
            "total_votes" => $total_votes,
            "logs" => $logs
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}
?>
