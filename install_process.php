<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'check_requirements':
        checkRequirements();
        break;
    case 'test_connection':
        testConnection();
        break;
    case 'install':
        runInstall();
        break;
    case 'create_admin':
        createAdmin();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function sanitizeIdentifier($name) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
}

function checkRequirements() {
    $checks = [];
    $phpVersion = phpversion();
    $checks[] = [
        'name' => 'PHP Version',
        'detail' => 'เวอร์ชัน ' . $phpVersion . ' (ต้องการ >= 7.4)',
        'pass' => version_compare($phpVersion, '7.4.0', '>=')
    ];

    $exts = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'curl', 'gd', 'fileinfo', 'zip', 'intl'];
    foreach ($exts as $ext) {
        $checks[] = [
            'name' => 'Extension: ' . $ext,
            'detail' => extension_loaded($ext) ? 'ติดตั้งแล้ว' : 'ยังไม่ได้ติดตั้ง',
            'pass' => extension_loaded($ext)
        ];
    }

    $writable_dirs = ['config', 'uploads', 'cache', 'logs', 'img_doc', 'img_sig', 'scans', 'backups', 'document'];
    foreach ($writable_dirs as $dir) {
        $path = __DIR__ . '/' . $dir;
        $exists = is_dir($path);
        $writable = $exists && is_writable($path);
        $detail = '';
        if (!$exists) {
            $detail = 'ไดเรกทอรีไม่มีอยู่ (จะสร้างให้)';
            $pass = true;
        } else {
            $detail = $writable ? ' writable' : ' ไม่สามารถเขียนได้';
            $pass = $writable;
        }
        $checks[] = [
            'name' => 'Directory: ' . $dir . '/',
            'detail' => $detail,
            'pass' => $pass
        ];
    }

    $checks[] = [
        'name' => 'ini_set / set_time_limit',
        'detail' => function_exists('set_time_limit') ? 'พร้อมใช้งาน' : 'ไม่รองรับ',
        'pass' => function_exists('set_time_limit')
    ];

    $sqlFile = __DIR__ . '/db/house_dbs.sql';
    $checks[] = [
        'name' => 'ไฟล์ SQL Schema',
        'detail' => file_exists($sqlFile) ? 'พบไฟล์ house_dbs.sql (' . round(filesize($sqlFile)/1024) . ' KB)' : 'ไม่พบไฟล์ house_dbs.sql',
        'pass' => file_exists($sqlFile)
    ];

    $modiFile = __DIR__ . '/db/modi_struc_2.sql';
    $checks[] = [
        'name' => 'ไฟล์ SQL Index/View',
        'detail' => file_exists($modiFile) ? 'พบไฟล์ modi_struc_2.sql' : 'ไม่พบไฟล์ modi_struc_2.sql (ไม่บังคับ)',
        'pass' => true
    ];

    echo json_encode($checks);
}

function testConnection() {
    $host = $_POST['host'] ?? 'localhost';
    $port = $_POST['port'] ?? '3306';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $dbname = $_POST['dbname'] ?? 'house_dbs';

    if (empty($user)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอก MySQL Username']);
        return;
    }

    $dbname = sanitizeIdentifier($dbname);

    try {
        $conn = new PDO(
            "mysql:host={$host};port={$port}",
            $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $conn->prepare("SHOW DATABASES LIKE ?");
        $stmt->execute([$dbname]);
        $exists = $stmt->fetch() ? true : false;

        $msg = 'เชื่อมต่อ MySQL สำเร็จ!';
        if ($exists) {
            $msg .= ' พบฐานข้อมูล "' . htmlspecialchars($dbname) . '" แล้ว (จะใช้ฐานข้อมูลเดิม)';
        } else {
            $msg .= ' ไม่พบฐานข้อมูล "' . htmlspecialchars($dbname) . '" (ระบบจะสร้างให้ใหม่)';
        }

        $conn = null;
        echo json_encode(['success' => true, 'message' => $msg]);
    } catch (PDOException $e) {
        $msg = 'เชื่อมต่อ MySQL ไม่สำเร็จ: ' . $e->getMessage();
        if (strpos($e->getMessage(), 'Access denied') !== false) {
            $msg .= '<br><small>ตรวจสอบ Username และ Password อีกครั้ง</small>';
        }
        echo json_encode(['success' => false, 'message' => $msg]);
    }
}

function runInstall() {
    $host = $_POST['host'] ?? 'localhost';
    $port = $_POST['port'] ?? '3306';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $dbname = $_POST['dbname'] ?? 'house_dbs';
    $logs = [];

    if (empty($user)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        return;
    }

    $dbname = sanitizeIdentifier($dbname);

    set_time_limit(300);
    @ini_set('max_execution_time', 300);

    try {
        $logs[] = ['type' => 'info', 'msg' => 'เชื่อมต่อ MySQL Server...'];
        $conn = new PDO(
            "mysql:host={$host};port={$port}",
            $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $conn->prepare("SHOW DATABASES LIKE ?");
        $stmt->execute([$dbname]);
        if (!$stmt->fetch()) {
            $logs[] = ['type' => 'info', 'msg' => 'กำลังสร้างฐานข้อมูล "' . htmlspecialchars($dbname) . '"...'];
            $conn->exec("CREATE DATABASE `" . $dbname . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $logs[] = ['type' => 'success', 'msg' => 'สร้างฐานข้อมูล "' . htmlspecialchars($dbname) . '" สำเร็จ'];
        } else {
            $logs[] = ['type' => 'warn', 'msg' => 'พบฐานข้อมูล "' . htmlspecialchars($dbname) . '" แล้ว ข้ามการสร้าง'];
        }

        $conn->exec("USE `" . $dbname . "`");
        $logs[] = ['type' => 'info', 'msg' => 'กำลังนำเข้าตาราง...'];

        $sqlFiles = [
            __DIR__ . '/db/house_dbs.sql',
        ];
        $modiFile = __DIR__ . '/db/modi_struc_2.sql';
        if (file_exists($modiFile)) {
            $sqlFiles[] = $modiFile;
        }

        $allStatements = [];
        foreach ($sqlFiles as $sqlFile) {
            if (!file_exists($sqlFile)) {
                $logs[] = ['type' => 'warn', 'msg' => 'ข้ามไฟล์: ' . basename($sqlFile) . ' (ไม่พบไฟล์)'];
                continue;
            }
            $logs[] = ['type' => 'info', 'msg' => 'อ่านไฟล์: ' . basename($sqlFile) . '...'];
            $content = file_get_contents($sqlFile);
            $content = str_replace('SET NAMES utf8mb4;', '', $content);
            $content = str_replace('SET FOREIGN_KEY_CHECKS = 0;', '', $content);
            $content = str_replace('SET FOREIGN_KEY_CHECKS = 1;', '', $content);
            $stmts = array_filter(
                array_map('trim', explode(';', $content)),
                function($s) { return !empty($s) && $s !== ''; }
            );
            $allStatements = array_merge($allStatements, $stmts);
        }

        $tables = [];
        $views = [];
        $procedures = [];
        $others = [];

        foreach ($allStatements as $sql) {
            $upper = strtoupper(preg_replace('/\s+/', ' ', trim($sql)));
            if (preg_match('/^CREATE\s+TABLE\s+/i', $sql)) {
                $tables[] = $sql;
            } elseif (preg_match('/^(CREATE\s+(ALGORITHM|OR\s+REPLACE)\s+.*VIEW|CREATE\s+VIEW)\s+/i', $sql)) {
                $views[] = $sql;
            } elseif (preg_match('/^CREATE\s+PROCEDURE\s+/i', $sql) || preg_match('/^CREATE\s+FUNCTION\s+/i', $sql)) {
                $procedures[] = $sql;
            } else {
                $others[] = $sql;
            }
        }

        $orderedStatements = array_merge($tables, $others, $procedures, $views);
        $total = count($orderedStatements);
        $created = 0;
        $errors = 0;
        $tableCount = 0;

        $logs[] = ['type' => 'info', 'msg' => 'พบคำสั่ง SQL ทั้งหมด ' . $total . ' รายการ (TABLE: ' . count($tables) . ', VIEW: ' . count($views) . ', PROCEDURE: ' . count($procedures) . ', อื่นๆ: ' . count($others) . ')'];

        foreach ($orderedStatements as $i => $sql) {
            $trimmed = preg_replace('/\s+/', ' ', trim($sql));
            if (preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $trimmed, $m)) {
                $logs[] = ['type' => 'info', 'msg' => 'สร้างตาราง: ' . $m[1] . '...'];
                $tableCount++;
            } elseif (preg_match('/CREATE\s+(?:ALGORITHM|OR REPLACE|VIEW)\s+`?(\w+)`?/i', $trimmed, $m)) {
                $logs[] = ['type' => 'info', 'msg' => 'สร้าง View: ' . $m[1] . '...'];
            } elseif (preg_match('/CREATE\s+(?:PROCEDURE|FUNCTION)\s+`?(\w+)`?/i', $trimmed, $m)) {
                $logs[] = ['type' => 'info', 'msg' => 'สร้าง Procedure: ' . $m[1] . '...'];
            }

            try {
                $conn->exec($sql);
                $created++;
            } catch (PDOException $e) {
                $errno = $e->getCode();
                if ($errno == 1050 || $errno == 1062 || $errno == 1061 || $errno == 1065) {
                    $errors++;
                } else {
                    $errors++;
                    $logs[] = ['type' => 'warn', 'msg' => '  ข้าม: ' . substr($e->getMessage(), 0, 100)];
                }
            }
        }

        $stmt = $conn->query("SHOW TABLES");
        $finalTableCount = $stmt->rowCount();
        $logs[] = ['type' => 'success', 'msg' => 'ฐานข้อมูลมีตารางทั้งหมด ' . $finalTableCount . ' ตาราง'];

        $conn->exec("SET FOREIGN_KEY_CHECKS = 1");

        $logs[] = ['type' => 'success', 'msg' => 'นำเข้าสำเร็จ (สร้าง: ' . $created . ', ข้าม/ผิดพลาด: ' . $errors . ')'];

        $conn = null;

        echo json_encode([
            'success' => true,
            'message' => 'ติดตั้งฐานข้อมูลสำเร็จ',
            'logs' => $logs,
            'tables' => $finalTableCount
        ]);

    } catch (PDOException $e) {
        $logs[] = ['type' => 'error', 'msg' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการติดตั้ง: ' . $e->getMessage(),
            'logs' => $logs
        ]);
    }
}

function createAdmin() {
    $host = $_POST['host'] ?? 'localhost';
    $port = $_POST['port'] ?? '3306';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $dbname = $_POST['dbname'] ?? 'house_dbs';
    $adminUser = $_POST['admin_user'] ?? 'admin';
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminName = $_POST['admin_name'] ?? 'ผู้ดูแลระบบ';
    $systemName = $_POST['system_name'] ?? 'ระบบบริหารงานนิติบุคคลหมู่บ้าน พฤกษา 33';

    if (empty($adminUser) || empty($adminPass)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
        return;
    }

    $adminUser = sanitizeIdentifier($adminUser);
    $dbname = sanitizeIdentifier($dbname);

    try {
        $conn = new PDO(
            "mysql:host={$host};port={$port};dbname={$dbname}",
            $user, $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $conn->exec("SET NAMES 'utf8mb4'");

        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $parts = explode(' ', $adminName, 2);
        $fname = $parts[0] ?? $adminUser;
        $lname = $parts[1] ?? '';
        $adminEmail = $adminUser . '@pshome33.com';

        $stmtDel = $conn->prepare("DELETE FROM ims_user WHERE user_id = ?");
        $stmtDel->execute([$adminUser]);

        $stmt = $conn->prepare("INSERT INTO ims_user (user_id, email, first_name, last_name, password, account_type, status, company, user_signature, create_date) VALUES (?, ?, ?, ?, ?, 'admin', 'Active', 'PS33', '', NOW())");
        $stmt->execute([$adminUser, $adminEmail, $fname, $lname, $hash]);

        $configPath = __DIR__ . '/config/db_value.inc';
        $configContent = "<?php\n\n";
        $configContent .= "define('DB_HOST','" . addslashes($host) . "');\n";
        $configContent .= "define('DB_PORT','" . addslashes($port) . "');\n";
        $configContent .= "define('DB_USER','" . addslashes($user) . "');\n";
        $configContent .= "define('DB_PASS','" . addslashes($pass) . "');\n";
        $configContent .= "define('DB_NAME','" . addslashes($dbname) . "');\n";
        file_put_contents($configPath, $configContent);

        $installedPath = __DIR__ . '/config/installed.inc';
        file_put_contents($installedPath, date('Y-m-d H:i:s'));

        $langPath = __DIR__ . '/config/lang.php';
        $langContent = "<?php\n";
        $langContent .= "\$company = 'PS33';\n";
        $langContent .= "\$system_name = \"" . addslashes($systemName) . "\";\n";
        $langContent .= "\$system_name_1 = \"" . addslashes($systemName) . "\";\n";
        $langContent .= "\$system_name_2 = \"" . addslashes($systemName) . "\";\n";
        $langContent .= "\$save_success = \"Complete : บันทึกข้อมูลเรียบร้อยแล้ว\";\n";
        $langContent .= "\$del_success = \"Complete : ลบข้อมูลเรียบร้อยแล้ว\";\n";
        $langContent .= "\$error = \"Error : มีความผิดพลาดในระบบ  กรุณาตรวจสอบ\";\n";
        $langContent .= "\$dup = \"Duplicate มีข้อมูลนี้แล้วในระบบ กรุณาตรวจสอบ\";\n";
        $langContent .= "\$Approve_Success = \"มีการอนุมัติเอกสารแล้ว ไม่สามารถแก้ไขข้อมูลได้\";\n";
        $langContent .= "\$Error_Over = \"Over : เกินจำนวนวันสูงสุดที่กำหนด หรือ อายุงานไม่เป็นตามเงื่อนไข ไม่สามารถบันทึกข้อมูลได้\";\n";
        $langContent .= "\$Error_Over1 = \"Over : เกินจำนวนวันสูงสุดที่กำหนด หรือ อายุงานไม่เป็นตามเงื่อนไข ไม่สามารถบันทึกข้อมูลได้\";\n";
        $langContent .= "\$Error_Over2 = \"Over : เกินจำนวนวันสูงสุดที่กำหนด ไม่สามารถบันทึกข้อมูลได้\";\n\n";
        $langContent .= "\$adm = '{$adminEmail}';\n";
        $langContent .= "\$pwd = '" . password_hash($adminPass, PASSWORD_DEFAULT) . "';\n\n";
        $langContent .= "\$account_type_default = 'user';\n";
        $langContent .= "\$user_password = '" . password_hash('123456', PASSWORD_DEFAULT) . "';\n";
        $langContent .= "/*123456*/\n\n";
        $langContent .= "\$user_picture = 'img/icon/user-001.png';\n";
        $langContent .= "\$email = '@pshome33.com';\n\n";
        $langContent .= "\$contact_y = \"ตอบแล้ว\";\n";
        $langContent .= "\$contact_n = \"ยังไม่ตอบ\";\n";
        file_put_contents($langPath, $langContent);

        $conn = null;

        echo json_encode([
            'success' => true,
            'message' => 'สร้างบัญชีผู้ดูแลระบบสำเร็จ'
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ]);
    }
}
