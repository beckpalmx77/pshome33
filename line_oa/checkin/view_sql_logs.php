<?php
// กำหนด Path ให้ชี้ไปยังโฟลเดอร์ที่เก็บไฟล์ SQL
$logDirPath = "line_oa/checkin/logs";
$sqlLogFile = $logDirPath . "debug_insert_queries.sql";

// จัดการการลบ Log
if (isset($_POST['clear_log'])) {
    if (file_exists($sqlLogFile)) {
        unlink($sqlLogFile);
        header("Location: view_sql_logs.php?status=cleared");
        exit;
    }
}

// อ่านข้อมูลจากไฟล์
$logs = [];
if (file_exists($sqlLogFile)) {
    $content = file_get_contents($sqlLogFile);
    // แยกส่วนของแต่ละรายการด้วยเครื่องหมาย ; และบรรทัดว่าง 2 บรรทัดที่เราทำไว้
    $logs = explode(";\n\n", trim($content));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Insert Logs - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary-color: #003366; --bg-color: #f0f2f5; }
        body { background-color: var(--bg-color); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background-color: var(--primary-color); box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .card-sql { border: none; border-radius: 12px; transition: 0.2s; border-left: 6px solid #28a745; margin-bottom: 20px; }
        .card-sql:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        pre { background: #1e1e1e; color: #dcdcdc; padding: 15px; border-radius: 8px; font-size: 0.9rem; overflow-x: auto; }
        .sql-keyword { color: #569cd6; font-weight: bold; }
        .sql-string { color: #ce9178; }
        .timestamp-box { font-size: 0.85rem; color: #6c757d; display: flex; align-items: center; gap: 5px; }
        .empty-state { padding: 100px 0; text-align: center; color: #adb5bd; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1">
            <i class="bi bi-database-check me-2"></i> SQL Check-in Logs
        </span>
        <div class="d-flex gap-2">
            <a href="view_sql_logs.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
            <form method="POST" onsubmit="return confirm('ยืนยันการลบไฟล์ SQL Log ทั้งหมด?');">
                <button type="submit" name="clear_log" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Clear Logs</button>
            </form>
        </div>
    </div>
</nav>

<div class="container">
    <div class="mb-3">
        <small class="text-muted"><i class="bi bi-folder2-open"></i> Path: <?php echo htmlspecialchars($sqlLogFile); ?></small>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'cleared'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> ไฟล์ Log ถูกล้างเรียบร้อยแล้ว
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($logs) || (count($logs) == 1 && trim($logs[0]) == "")): ?>
        <div class="card shadow-sm border-0">
            <div class="empty-state">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <h4 class="mt-3">ไม่พบข้อมูลการบันทึกในขณะนี้</h4>
                <p>คำสั่ง SQL ใหม่ๆ จะปรากฏที่นี่เมื่อมีการเช็คอิน</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php
            // แสดงผลจากใหม่ไปเก่า
            $reversedLogs = array_reverse($logs);
            foreach ($reversedLogs as $index => $log):
                if (trim($log) == "") continue;

                $lines = explode("\n", trim($log));
                $timeInfo = isset($lines[0]) ? str_replace("-- Generated at ", "", $lines[0]) : "Unknown";
                $sqlQuery = isset($lines[1]) ? $lines[1] : $lines[0];
                ?>
                <div class="col-12">
                    <div class="card card-sql shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="timestamp-box">
                                    <i class="bi bi-clock-history"></i> <?php echo htmlspecialchars($timeInfo); ?>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Success</span>
                            </div>
                            <div class="position-relative">
                                <pre><code><?php echo htmlspecialchars($sqlQuery); ?>;</code></pre>
                                <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-2"
                                        onclick="copyToClipboard(this, `<?php echo addslashes($sqlQuery); ?>;`)"
                                        title="Copy SQL">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    function copyToClipboard(btn, text) {
        navigator.clipboard.writeText(text).then(() => {
            const icon = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
            btn.classList.replace('btn-outline-light', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = icon;
                btn.classList.replace('btn-success', 'btn-outline-light');
            }, 2000);
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>