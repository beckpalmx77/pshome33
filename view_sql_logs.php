<?php
// กำหนด Path ให้ชี้ไปยังโฟลเดอร์ที่เก็บไฟล์ SQL
$logDirPath = "line_oa/checkin/logs/";
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
        pre { background: #1e1e1e; color: #dcdcdc; padding: 15px; border-radius: 8px; font-size: 0.9rem; overflow-x: auto; margin-bottom: 0; }
        .timestamp-box { font-size: 0.9rem; color: #495057; display: flex; align-items: center; gap: 8px; font-weight: 500; }
        .empty-state { padding: 100px 0; text-align: center; color: #adb5bd; }
        /* ปรับแต่งปุ่ม Copy ให้นิ่งขึ้น */
        .btn-copy { min-width: 100px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4">
    <div class="container">
        <span class="navbar-brand mb-0 h1">
            <i class="bi bi-database-check me-2"></i> SQL Check-in Logs
        </span>
        <div class="d-flex gap-2">
            <a href="view_sql_logs.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-clockwise"></i> รีเฟรช</a>
            <!--form method="POST" onsubmit="return confirm('ยืนยันการลบไฟล์ SQL Log ทั้งหมด?');">
                <button type="submit" name="clear_log" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> ล้าง Log</button>
            </form-->
        </div>
    </div>
</nav>

<div class="container">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <small class="text-muted"><i class="bi bi-folder2-open"></i> <?php echo htmlspecialchars($sqlLogFile); ?></small>
        <span class="badge bg-secondary"><?php echo count($logs); ?> รายการ</span>
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
                <h4 class="mt-3">ไม่พบข้อมูลในขณะนี้</h4>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php
            $reversedLogs = array_reverse($logs);
            foreach ($reversedLogs as $index => $log):
                if (trim($log) == "") continue;

                $lines = explode("\n", trim($log));
                $timeInfo = isset($lines[0]) ? str_replace("-- Generated at ", "", $lines[0]) : "Unknown";
                $sqlQuery = isset($lines[1]) ? $lines[1] : $lines[0];
                $fullSql = htmlspecialchars($sqlQuery) . ";";
                ?>
                <div class="col-12">
                    <div class="card card-sql shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="timestamp-box">
                                    <i class="bi bi-clock-history text-primary"></i>
                                    <span><?php echo htmlspecialchars($timeInfo); ?></span>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary btn-copy"
                                            onclick="copyToClipboard(this, `<?php echo addslashes($sqlQuery); ?>;`)">
                                        <i class="bi bi-copy"></i> คัดลอก SQL
                                    </button>
                                </div>
                            </div>
                            <div class="bg-dark rounded-3 position-relative">
                                <pre><code><?php echo $fullSql; ?></code></pre>
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
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> คัดลอกแล้ว!';
            btn.classList.replace('btn-outline-primary', 'btn-success');

            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.replace('btn-success', 'btn-outline-primary');
            }, 2000);
        }).catch(err => {
            console.error('ไม่สามารถคัดลอกได้: ', err);
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>