<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'backup':
        runBackup();
        break;
    case 'reset':
        runReset();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getDbConfig() {
    return [
        'host' => $_POST['host'] ?? 'localhost',
        'port' => $_POST['port'] ?? '3306',
        'user' => $_POST['user'] ?? '',
        'pass' => $_POST['pass'] ?? '',
        'dbname' => $_POST['dbname'] ?? 'house_dbs'
    ];
}

function runBackup() {
    $config = getDbConfig();
    $doBackup = ($_POST['backup'] ?? '1') === '1';
    $backupLogs = [];

    if (!$doBackup) {
        echo json_encode([
            'success' => true,
            'backup_success' => false,
            'backup_logs' => [['type' => 'warn', 'msg' => 'ข้ามการสำรองข้อมูลตามที่ผู้ใช้เลือก']]
        ]);
        return;
    }

    if (empty($config['user'])) {
        echo json_encode([
            'success' => false,
            'backup_success' => false,
            'backup_logs' => [['type' => 'error', 'msg' => 'กรุณากรอกข้อมูล MySQL ให้ครบถ้วน']]
        ]);
        return;
    }

    try {
        $conn = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}",
            $config['user'], $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $conn->exec("SET NAMES 'utf8mb4'");

        $backupDir = __DIR__ . '/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
            $backupLogs[] = ['type' => 'info', 'msg' => 'สร้างโฟลเดอร์ backups/ สำเร็จ'];
        }

        $date = date('Y-m-d_H-i-s');
        $backupFile = $backupDir . "/backup_{$config['dbname']}_{$date}.sql";

        $backupLogs[] = ['type' => 'info', 'msg' => 'เริ่มสำรองฐานข้อมูล "' . $config['dbname'] . '"...'];

        $stmt = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $totalTables = count($tables);
        $backupLogs[] = ['type' => 'info', 'msg' => 'พบตารางทั้งหมด ' . $totalTables . ' ตาราง'];

        $handle = fopen($backupFile, 'w');
        fwrite($handle, "-- PS33 Home System Backup\n");
        fwrite($handle, "-- Date: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: {$config['dbname']}\n");
        fwrite($handle, "-- Tables: {$totalTables}\n\n");
        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

        $rowCount = 0;
        foreach ($tables as $table) {
            $backupLogs[] = ['type' => 'info', 'msg' => 'สำรองตาราง: ' . $table . '...'];

            $createStmt = $conn->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(PDO::FETCH_NUM);
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
            fwrite($handle, $createRow[1] . ";\n\n");

            $dataStmt = $conn->query("SELECT * FROM `{$table}`");
            $colCount = $dataStmt->columnCount();
            $tableRows = 0;

            while ($row = $dataStmt->fetch(PDO::FETCH_NUM)) {
                $values = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = $conn->quote($val);
                    }
                }
                fwrite($handle, "INSERT INTO `{$table}` VALUES(" . implode(', ', $values) . ");\n");
                $tableRows++;
                $rowCount++;
            }

            $backupLogs[] = ['type' => 'success', 'msg' => '  - ' . $table . ': ' . $tableRows . ' แถว'];
            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($handle);

        $fileSize = filesize($backupFile);
        $fileSizeStr = formatSize($fileSize);

        $conn = null;

        $backupLogs[] = ['type' => 'success', 'msg' => '=== สำรองข้อมูลเสร็จสิ้น ==='];
        $backupLogs[] = ['type' => 'success', 'msg' => 'ไฟล์: backups/' . basename($backupFile)];
        $backupLogs[] = ['type' => 'success', 'msg' => 'ขนาดไฟล์: ' . $fileSizeStr];
        $backupLogs[] = ['type' => 'success', 'msg' => 'จำนวนตาราง: ' . $totalTables];
        $backupLogs[] = ['type' => 'success', 'msg' => 'จำนวนแถวทั้งหมด: ' . $rowCount];

        echo json_encode([
            'success' => true,
            'backup_success' => true,
            'backup_file' => basename($backupFile),
            'backup_size' => $fileSizeStr,
            'backup_tables' => $totalTables,
            'backup_rows' => $rowCount,
            'backup_logs' => $backupLogs
        ]);

    } catch (PDOException $e) {
        $backupLogs[] = ['type' => 'error', 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        echo json_encode([
            'success' => false,
            'backup_success' => false,
            'backup_logs' => $backupLogs
        ]);
    }
}

function runReset() {
    $config = getDbConfig();
    $logs = [];

    if (empty($config['user'])) {
        echo json_encode([
            'success' => false,
            'logs' => [['type' => 'error', 'msg' => 'กรุณากรอกข้อมูล MySQL ให้ครบถ้วน']]
        ]);
        return;
    }

    try {
        $conn = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}",
            $config['user'], $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $conn->exec("SET NAMES 'utf8mb4'");
        $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

        $logs[] = ['type' => 'info', 'msg' => 'เริ่มรีเซ็ตฐานข้อมูล "' . $config['dbname'] . '"...'];

        $stmt = $conn->query("SHOW TABLES");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $totalTables = count($tables);
        $logs[] = ['type' => 'info', 'msg' => 'พบตาราง ' . $totalTables . ' ตาราง กำลังลบทั้งหมด...'];

        $dropped = 0;
        foreach ($tables as $table) {
            try {
                $conn->exec("DROP TABLE IF EXISTS `{$table}`");
                $logs[] = ['type' => 'info', 'msg' => 'ลบตาราง: ' . $table];
                $dropped++;
            } catch (PDOException $e) {
                $logs[] = ['type' => 'warn', 'msg' => 'ไม่สามารถลบตาราง ' . $table . ': ' . $e->getMessage()];
            }
        }

        $logs[] = ['type' => 'success', 'msg' => 'ลบตารางเสร็จสิ้น (' . $dropped . '/' . $totalTables . ')'];

        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        $conn = null;

        $configFiles = [
            'config/installed.inc'  => 'installed.inc',
            'config/db_value.inc'   => 'db_value.inc',
            'config/lang.php'       => 'lang.php',
        ];

        foreach ($configFiles as $relPath => $name) {
            $fullPath = __DIR__ . '/' . $relPath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
                $logs[] = ['type' => 'info', 'msg' => 'ลบไฟล์ ' . $name . ' เรียบร้อย'];
            }
        }

        $logs[] = ['type' => 'success', 'msg' => '=== การรีเซ็ตเสร็จสิ้น ==='];
        $logs[] = ['type' => 'success', 'msg' => 'ระบบพร้อมสำหรับการติดตั้งใหม่'];

        echo json_encode([
            'success' => true,
            'logs' => $logs,
            'dropped' => $dropped,
            'total' => $totalTables
        ]);

    } catch (PDOException $e) {
        $logs[] = ['type' => 'error', 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        echo json_encode([
            'success' => false,
            'logs' => $logs
        ]);
    }
}

function formatSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
