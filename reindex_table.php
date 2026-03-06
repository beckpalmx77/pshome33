<?php
// แก้ไข path ให้ตรงกับโครงสร้างของคุณ
require_once 'config/connect_db.php';

// 1. API สำหรับประมวลผล (เรียกผ่าน Fetch JS)
if (isset($_GET['action']) && $_GET['action'] == 'optimize' && isset($_GET['table'])) {
    $table = $_GET['table'];
    $response = ['success' => false, 'before' => 0, 'after' => 0, 'message' => ''];

    try {
        // Query ดึงขนาดตาราง (หน่วย MB)
        $sizeQuery = "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
                      FROM information_schema.TABLES 
                      WHERE table_schema = DATABASE() AND table_name = :table";

        $stmtSize = $conn->prepare($sizeQuery);

        // เช็คขนาดก่อนทำ
        $stmtSize->execute(['table' => $table]);
        $response['before'] = (float)$stmtSize->fetchColumn();

        // รัน OPTIMIZE (คำสั่ง Reindex ของ MySQL)
        $conn->query("OPTIMIZE TABLE `$table` ");

        // เช็คขนาดหลังทำ
        $stmtSize->execute(['table' => $table]);
        $response['after'] = (float)$stmtSize->fetchColumn();

        $response['success'] = true;
        $saved = max(0, $response['before'] - $response['after']);
        $response['message'] = "สำเร็จ: `$table` [{$response['before']} MB -> {$response['after']} MB] (ลดไป: ".round($saved, 2)." MB)";

    } catch (PDOException $e) {
        $response['message'] = "ผิดพลาด: `$table` - " . $e->getMessage();
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// 2. ดึงรายชื่อตารางเริ่มต้น
$tables = [];
try {
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySQL Reindex & Optimizer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; padding-top: 40px; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .summary-box { background: white; border-radius: 15px; padding: 30px; border: none; }
        .log-window {
            background-color: #1e1e1e; color: #dcdccc; padding: 20px;
            border-radius: 10px; height: 400px; overflow-y: auto;
            font-family: 'Consolas', 'Monaco', monospace; font-size: 13px;
            line-height: 1.6; border: 1px solid #333;
        }
        .success-text { color: #8cf68c; }
        .error-text { color: #ff6b6b; }
        .progress { height: 25px; border-radius: 12px; background-color: #e9ecef; }
        .progress-bar { font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="summary-box shadow mb-4">
                <h3 class="text-dark mb-2">MySQL Optimizer</h3>
                <p class="text-muted small">ระบบจัดระเบียบ Index และเพิ่มประสิทธิภาพการจัดเก็บข้อมูล</p>

                <div class="row text-center my-4">
                    <div class="col-6 border-end">
                        <div class="text-muted small uppercase">Total Tables</div>
                        <div class="h3 fw-bold"><?php echo count($tables); ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small uppercase">Total Space Saved</div>
                        <div class="h3 fw-bold text-success"><span id="total-saved">0.00</span> MB</div>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <button id="start-btn" class="btn btn-primary btn-lg px-5 shadow-sm">Start Optimization</button>
                    <button id="download-btn" class="btn btn-outline-secondary btn-lg d-none">Download Report (.txt)</button>
                </div>
            </div>

            <div id="ui-section" class="d-none">
                <div class="progress mb-2 shadow-sm">
                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;">0%</div>
                </div>
                <div class="d-flex justify-content-between mb-3 px-1">
                    <span id="status-text" class="text-primary fw-bold small">Ready...</span>
                    <span id="count-text" class="text-muted small">0 / <?php echo count($tables); ?></span>
                </div>
                <div class="log-window" id="log-window"></div>
            </div>
        </div>
    </div>
</div>

<script>
    const tables = <?php echo json_encode($tables); ?>;
    const startBtn = document.getElementById('start-btn');
    const downloadBtn = document.getElementById('download-btn');
    const progressBar = document.getElementById('progress-bar');
    const uiSection = document.getElementById('ui-section');
    const logWindow = document.getElementById('log-window');
    const statusText = document.getElementById('status-text');
    const countText = document.getElementById('count-text');
    const totalSavedLabel = document.getElementById('total-saved');

    let logContent = "";
    let totalSaved = 0;

    startBtn.addEventListener('click', async () => {
        if (!confirm('ยืนยันการเริ่มกระบวนการ Reindex? ระหว่างดำเนินการอาจทำให้ตารางถูก Lock ชั่วคราว')) return;

        startBtn.disabled = true;
        startBtn.classList.replace('btn-primary', 'btn-secondary');
        startBtn.innerText = "Processing...";
        uiSection.classList.remove('d-none');
        logWindow.innerHTML = '';
        totalSaved = 0;

        logContent = "Database Optimization Report\nGenerated: " + new Date().toLocaleString() + "\n";
        logContent += "=".repeat(60) + "\n";
        logContent += "Table Name".padEnd(30) + " | Before".padEnd(10) + " | After".padEnd(10) + " | Saved\n";
        logContent += "-".repeat(60) + "\n";

        let completed = 0;
        const total = tables.length;

        for (const table of tables) {
            statusText.innerText = `Optimizing: ${table}...`;

            try {
                // เรียกใช้ API ฝั่ง PHP
                const response = await fetch(`?action=optimize&table=${encodeURIComponent(table)}`);
                const result = await response.json();

                const savedPerTable = Math.max(0, result.before - result.after);
                totalSaved += savedPerTable;
                totalSavedLabel.innerText = totalSaved.toFixed(2);

                const timestamp = new Date().toLocaleTimeString();
                const logLine = `[${timestamp}] ${result.message}`;

                const logDiv = document.createElement('div');
                logDiv.className = result.success ? 'success-text' : 'error-text';
                logDiv.innerText = logLine;
                logWindow.appendChild(logDiv);
                logWindow.scrollTop = logWindow.scrollHeight;

                // เขียนลง Report
                logContent += `${table.padEnd(30)} | ${result.before.toFixed(2).padEnd(8)}MB | ${result.after.toFixed(2).padEnd(8)}MB | ${savedPerTable.toFixed(2)}MB\n`;

            } catch (error) {
                console.error(error);
            }

            completed++;
            const percent = Math.round((completed / total) * 100);
            progressBar.style.width = percent + '%';
            progressBar.innerText = percent + '%';
            countText.innerText = `${completed} / ${total}`;
        }

        statusText.innerText = "All processes completed!";
        startBtn.innerText = "Finished";
        logContent += "-".repeat(60) + "\n";
        logContent += `TOTAL SPACE SAVED: ${totalSaved.toFixed(2)} MB\n`;
        downloadBtn.classList.remove('d-none');
    });

    downloadBtn.addEventListener('click', () => {
        const blob = new Blob([logContent], { type: 'text/plain' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `db_optimization_report_${new Date().toISOString().slice(0,10)}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });
</script>

</body>
</html>