<?php
session_start();
include('../config/connect_db.php');

header('Content-Type: application/json; charset=utf-8');

$sql_create = "CREATE TABLE IF NOT EXISTS ims_meeting_config (
  id INT AUTO_INCREMENT PRIMARY KEY,
  meeting_year VARCHAR(255) NOT NULL DEFAULT '-',
  meeting_date VARCHAR(255) NOT NULL DEFAULT '-',
  meeting_day VARCHAR(500) DEFAULT '',
  meeting_time VARCHAR(500) DEFAULT '',
  meeting_location VARCHAR(500) DEFAULT '',
  remark TEXT,
  agenda_1 VARCHAR(500) DEFAULT '',
  agenda_2 VARCHAR(500) DEFAULT '',
  agenda_3 VARCHAR(500) DEFAULT '',
  agenda_4 VARCHAR(500) DEFAULT '',
  agenda_5 VARCHAR(500) DEFAULT '',
  agenda_6 VARCHAR(500) DEFAULT '',
  agenda_7 VARCHAR(500) DEFAULT '',
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_meeting (meeting_year, meeting_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$conn->exec($sql_create);

$alters = [
    "meeting_day VARCHAR(500) DEFAULT ''",
    "meeting_time VARCHAR(500) DEFAULT ''",
    "meeting_location VARCHAR(500) DEFAULT ''",
    "topic VARCHAR(500) DEFAULT ''",
];
foreach ($alters as $col) {
    $colName = explode(' ', $col)[0];
    $stmt = $conn->query("SHOW COLUMNS FROM ims_meeting_config LIKE '{$colName}'");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("ALTER TABLE ims_meeting_config ADD {$col}");
    }
}
for ($i = 5; $i <= 7; $i++) {
    $colName = "agenda_{$i}";
    $stmt = $conn->query("SHOW COLUMNS FROM ims_meeting_config LIKE '{$colName}'");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("ALTER TABLE ims_meeting_config ADD {$colName} VARCHAR(500) DEFAULT ''");
    }
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $meeting_year = $_POST['meeting_year'] ?? '';
    $meeting_date = $_POST['meeting_date'] ?? '';

    if (empty($meeting_year) || empty($meeting_date)) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุปีและวันที่ประชุม']);
        exit();
    }

    $meeting_day = $_POST['meeting_day'] ?? '';
    $meeting_time = $_POST['meeting_time'] ?? '';
    $meeting_location = $_POST['meeting_location'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $remark = $_POST['remark'] ?? '';
    $agenda = [];
    for ($i = 1; $i <= 7; $i++) {
        $agenda[$i] = $_POST['agenda_' . $i] ?? '';
    }

    $sql_upsert = "INSERT INTO ims_meeting_config (meeting_year, meeting_date, meeting_day, meeting_time, meeting_location, topic, remark, agenda_1, agenda_2, agenda_3, agenda_4, agenda_5, agenda_6, agenda_7, updated_at)
                   VALUES (:meeting_year, :meeting_date, :meeting_day, :meeting_time, :meeting_location, :topic, :remark, :agenda_1, :agenda_2, :agenda_3, :agenda_4, :agenda_5, :agenda_6, :agenda_7, NOW())
                   ON DUPLICATE KEY UPDATE
                   meeting_day = VALUES(meeting_day),
                   meeting_time = VALUES(meeting_time),
                   meeting_location = VALUES(meeting_location),
                   topic = VALUES(topic),
                   remark = VALUES(remark),
                   agenda_1 = VALUES(agenda_1), agenda_2 = VALUES(agenda_2),
                   agenda_3 = VALUES(agenda_3), agenda_4 = VALUES(agenda_4),
                   agenda_5 = VALUES(agenda_5), agenda_6 = VALUES(agenda_6),
                   agenda_7 = VALUES(agenda_7),
                   updated_at = NOW()";

    $stmt = $conn->prepare($sql_upsert);
    $stmt->bindValue(':meeting_year', $meeting_year);
    $stmt->bindValue(':meeting_date', $meeting_date);
    $stmt->bindValue(':meeting_day', $meeting_day);
    $stmt->bindValue(':meeting_time', $meeting_time);
    $stmt->bindValue(':meeting_location', $meeting_location);
    $stmt->bindValue(':topic', $topic);
    $stmt->bindValue(':remark', $remark);
    for ($i = 1; $i <= 7; $i++) {
        $stmt->bindValue(':agenda_' . $i, $agenda[$i]);
    }
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'บันทึกเรียบร้อย']);
    exit();
}

if ($method === 'GET') {
    $meeting_year = $_GET['meeting_year'] ?? '';
    $meeting_date = $_GET['meeting_date'] ?? '';

    if (empty($meeting_year) || empty($meeting_date)) {
        echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุปีและวันที่ประชุม']);
        exit();
    }

    $sql = "SELECT * FROM ims_meeting_config WHERE meeting_year = :meeting_year AND meeting_date = :meeting_date LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':meeting_year', $meeting_year);
    $stmt->bindValue(':meeting_date', $meeting_date);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'empty', 'data' => null]);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
