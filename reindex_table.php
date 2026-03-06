<?php
// 1. ส่วนประมวลผล API (ต้องอยู่บนสุด)
require_once 'config/connect_db.php';

if (isset($_GET['action']) && $_GET['action'] == 'optimize' && isset($_GET['table'])) {
    $table = $_GET['table'];
    $response = ['success' => false, 'before' => 0, 'after' => 0, 'message' => ''];
    try {
        $sizeQuery = "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
                      FROM information_schema.TABLES 
                      WHERE table_schema = DATABASE() AND table_name = :table";
        $stmtSize = $conn->prepare($sizeQuery);
        $stmtSize->execute(['table' => $table]);
        $response['before'] = (float)$stmtSize->fetchColumn();

        $conn->query("OPTIMIZE TABLE `$table` ");

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

// 2. ส่วนการแสดงผล
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
} else {
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800">Database Optimization</h1>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-primary">
                                    <h6 class="m-0 font-weight-bold text-white">MySQL Table Optimizer</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center mb-4">
                                        <div class="col-6 border-right">
                                            <span class="text-muted small">Total Tables</span>
                                            <div class="h3 font-weight-bold"><?php echo count($tables); ?></div>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted small">Total Space Saved</span>
                                            <div class="h3 font-weight-bold text-success"><span id="total-saved">0.00</span> MB</div>
                                        </div>
                                    </div>

                                    <div class="text-center mb-4">
                                        <button id="start-btn" class="btn btn-primary btn-lg">
                                            <i class="fas fa-play mr-2"></i> เริ่มรัน Optimize
                                        </button>
                                        <button id="download-btn" class="btn btn-outline-info btn-lg d-none">
                                            <i class="fas fa-file-alt mr-2"></i> ดาวน์โหลดผลลัพธ์
                                        </button>
                                    </div>

                                    <div id="ui-section" class="d-none">
                                        <div class="progress mb-3" style="height: 25px;">
                                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;">0%</div>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span id="status-text" class="font-weight-bold text-primary small">รอดำเนินการ...</span>
                                            <span id="count-text" class="text-muted small">0 / <?php echo count($tables); ?></span>
                                        </div>

                                        <div id="log-window" style="background-color: #1e1e1e; color: #dcdccc; padding: 20px; border-radius: 8px; height: 350px; overflow-y: auto; font-family: 'Consolas', monospace; font-size: 13px; line-height: 1.5; text-align: left !important;">
                                            <div style="color: #666;">--- กดปุ่มด้านบนเพื่อเริ่มกระบวนการ ---</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                if (!confirm('ยืนยันการเริ่มทำงาน?')) return;

                startBtn.disabled = true;
                startBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> กำลังทำงาน...';
                uiSection.classList.remove('d-none');
                logWindow.innerHTML = '';
                totalSaved = 0;

                logContent = "Database Optimization Report\nDate: " + new Date().toLocaleString() + "\n" + "=".repeat(50) + "\n";

                let completed = 0;
                const total = tables.length;

                for (const table of tables) {
                    statusText.innerText = `กำลังจัดการ: ${table}...`;

                    try {
                        // ดึงข้อมูลผ่าน AJAX Fetch
                        const response = await fetch(`?action=optimize&table=${encodeURIComponent(table)}`);
                        const result = await response.json();

                        const savedPerTable = Math.max(0, result.before - result.after);
                        totalSaved += savedPerTable;
                        totalSavedLabel.innerText = totalSaved.toFixed(2);

                        const logLine = `[${new Date().toLocaleTimeString()}] ${result.message}`;
                        const logDiv = document.createElement('div');
                        logDiv.style.color = result.success ? "#8cf68c" : "#ff6b6b";
                        logDiv.style.marginBottom = "3px";
                        logDiv.innerText = logLine;

                        logWindow.appendChild(logDiv);
                        logWindow.scrollTop = logWindow.scrollHeight;

                        logContent += logLine + "\n";
                    } catch (error) {
                        const errorDiv = document.createElement('div');
                        errorDiv.style.color = "#ff6b6b";
                        errorDiv.innerText = `[ERROR] ไม่สามารถประมวลผลตาราง: ${table}`;
                        logWindow.appendChild(errorDiv);
                    }

                    completed++;
                    const percent = Math.round((completed / total) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.innerText = percent + '%';
                    countText.innerText = `${completed} / ${total}`;
                }

                statusText.innerText = "เสร็จสมบูรณ์!";
                startBtn.innerText = "ดำเนินการเสร็จสิ้น";
                downloadBtn.classList.remove('d-none');
            });

            downloadBtn.addEventListener('click', () => {
                const blob = new Blob([logContent], { type: 'text/plain' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `db_optimize_report.txt`;
                a.click();
            });
        });
    </script>
    </body>
    </html>
<?php } ?>