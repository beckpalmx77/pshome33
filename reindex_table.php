<?php
require_once 'config/connect_db.php';

// ส่วนประมวลผล API
if (isset($_GET['action']) && $_GET['action'] == 'optimize' && isset($_GET['table'])) {
    $table = $_GET['table'];
    $response = ['success' => false, 'skipped' => false, 'before' => 0, 'after' => 0, 'message' => ''];

    try {
        // ตรวจสอบ TABLE_TYPE และ ENGINE ในคำสั่งเดียว
        $infoStmt = $conn->prepare(
            "SELECT TABLE_TYPE, ENGINE 
             FROM information_schema.TABLES 
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table"
        );
        $infoStmt->execute(['table' => $table]);
        $info = $infoStmt->fetch(PDO::FETCH_ASSOC);

        // ข้าม VIEW
        if (!$info || $info['TABLE_TYPE'] === 'VIEW') {
            $response['skipped']  = true;
            $response['success']  = true;
            $response['message']  = "⏭️ ข้าม: `$table` [VIEW]";
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        $engine = strtoupper($info['ENGINE']);

        // ข้าม Engine ที่ไม่รองรับ
        if (!in_array($engine, ['INNODB', 'MYISAM', 'ARIA'])) {
            $response['skipped']  = true;
            $response['success']  = true;
            $response['message']  = "⏭️ ข้าม: `$table` [$engine ไม่รองรับ OPTIMIZE]";
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        // วัดขนาดก่อน
        $sizeQuery = "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) 
                      FROM information_schema.TABLES 
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table";
        $stmtSize = $conn->prepare($sizeQuery);
        $stmtSize->execute(['table' => $table]);
        $response['before'] = (float)$stmtSize->fetchColumn();

        // ANALYZE TABLE (อัปเดต index statistics)
        $analyzeResult = $conn->query("ANALYZE TABLE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $analyzeMsg    = $analyzeResult[0]['Msg_text'] ?? 'OK';

        // OPTIMIZE TABLE
        $optResult = $conn->query("OPTIMIZE TABLE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $optMsg    = $optResult[0]['Msg_text'] ?? 'OK';

        // วัดขนาดหลัง
        $stmtSize->execute(['table' => $table]);
        $response['after'] = (float)$stmtSize->fetchColumn();

        $response['success'] = true;
        $saved = max(0, $response['before'] - $response['after']);
        $response['message'] = "✅ [$engine] `$table` [{$response['before']} MB → {$response['after']} MB] ลดไป: " . round($saved, 2) . " MB | ANALYZE: $analyzeMsg | OPTIMIZE: $optMsg";

    } catch (PDOException $e) {
        $response['message'] = "❌ ผิดพลาด: `$table` - " . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// ส่วนการแสดงผล
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
} else {
    // ดึงเฉพาะ BASE TABLE (ไม่รวม VIEW) ตั้งแต่แรก
    $stmt = $conn->query(
        "SELECT TABLE_NAME, TABLE_TYPE 
         FROM information_schema.TABLES 
         WHERE TABLE_SCHEMA = DATABASE() 
         ORDER BY TABLE_NAME"
    );
    $allTables  = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $tableNames = array_column($allTables, 'TABLE_NAME');
    $totalCount = count($tableNames);
    $viewCount  = count(array_filter($allTables, fn($t) => $t['TABLE_TYPE'] === 'VIEW'));
    $baseCount  = $totalCount - $viewCount;

    $dashboard_url = isset($_SESSION['dashboard_page']) ? $_SESSION['dashboard_page'] : 'dashboard.php';
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <style>
            .sidebar-lock {
                position: fixed;
                top: 0; left: 0;
                width: 250px; height: 100%;
                background: rgba(0,0,0,0.1);
                z-index: 9999;
                cursor: not-allowed;
                display: none;
            }
            .working-overlay {
                pointer-events: none;
                opacity: 0.7;
            }
        </style>
    </head>
    <body id="page-top">
    <div id="lock-overlay" class="sidebar-lock"></div>

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
                                <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold">MySQL Table Optimizer</h6>
                                    <a href="<?php echo $dashboard_url; ?>" class="btn btn-sm btn-light shadow-sm text-primary">
                                        <i class="fas fa-home fa-sm"></i> Home
                                    </a>
                                </div>
                                <div class="card-body">
                                    <!-- Summary -->
                                    <div class="row text-center mb-4">
                                        <div class="col-4 border-right">
                                            <span class="text-muted small">ตารางทั้งหมด (BASE TABLE)</span>
                                            <div class="h3 font-weight-bold"><?php echo $baseCount; ?></div>
                                        </div>
                                        <div class="col-4 border-right">
                                            <span class="text-muted small">VIEW (ข้ามทั้งหมด)</span>
                                            <div class="h3 font-weight-bold text-warning"><?php echo $viewCount; ?></div>
                                        </div>
                                        <div class="col-4">
                                            <span class="text-muted small">Total Space Saved</span>
                                            <div class="h3 font-weight-bold text-success"><span id="total-saved">0.00</span> MB</div>
                                        </div>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="text-center mb-4">
                                        <button id="start-btn" class="btn btn-primary btn-lg px-4">
                                            <i class="fas fa-play mr-2"></i>เริ่มรัน Optimize
                                        </button>
                                        <div id="after-action-btns" class="d-none">
                                            <button id="reset-btn" class="btn btn-warning btn-lg px-4">
                                                <i class="fas fa-undo mr-2"></i>Reset หน้าจอ
                                            </button>
                                            <button id="download-btn" class="btn btn-outline-info btn-lg px-4">
                                                <i class="fas fa-file-alt mr-2"></i>ดาวน์โหลดผลลัพธ์
                                            </button>
                                            <a href="<?php echo $dashboard_url; ?>" class="btn btn-outline-secondary btn-lg px-4">
                                                <i class="fas fa-home mr-2"></i>กลับหน้าหลัก
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Progress & Log -->
                                    <div id="ui-section" class="d-none">
                                        <div class="progress mb-3" style="height: 25px;">
                                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;">0%</div>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span id="status-text" class="font-weight-bold text-primary small">รอดำเนินการ...</span>
                                            <span id="count-text" class="text-muted small">0 / <?php echo $totalCount; ?></span>
                                        </div>
                                        <div id="log-window" style="background-color: #1e1e1e; color: #dcdccc; padding: 20px; border-radius: 8px; height: 350px; overflow-y: auto; font-family: 'Consolas', monospace; font-size: 13px; line-height: 1.5; text-align: left;">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tables        = <?php echo json_encode($tableNames); ?>;
            const startBtn      = document.getElementById('start-btn');
            const resetBtn      = document.getElementById('reset-btn');
            const downloadBtn   = document.getElementById('download-btn');
            const afterActionBtns = document.getElementById('after-action-btns');
            const progressBar   = document.getElementById('progress-bar');
            const uiSection     = document.getElementById('ui-section');
            const logWindow     = document.getElementById('log-window');
            const statusText    = document.getElementById('status-text');
            const countText     = document.getElementById('count-text');
            const totalSavedLabel = document.getElementById('total-saved');
            const lockOverlay   = document.getElementById('lock-overlay');
            const sidebar       = document.getElementById('accordionSidebar');

            let logContent = "";
            let totalSaved = 0;

            function setInterfaceLock(isLocked) {
                lockOverlay.style.display = isLocked ? 'block' : 'none';
                if (sidebar) sidebar.classList.toggle('working-overlay', isLocked);
                startBtn.disabled = isLocked;
            }

            function appendLog(message, color = '#dcdccc', isSkipped = false) {
                const time    = new Date().toLocaleTimeString();
                const logLine = `[${time}] ${message}`;
                const div     = document.createElement('div');
                div.style.color        = color;
                div.style.marginBottom = '3px';
                // ตัวอักษรจางลงสำหรับรายการที่ข้าม
                div.style.opacity      = isSkipped ? '0.5' : '1';
                div.innerText          = logLine;
                logWindow.appendChild(div);
                logWindow.scrollTop    = logWindow.scrollHeight;
                logContent            += logLine + "\n";
            }

            startBtn.addEventListener('click', async () => {
                if (!confirm('ยืนยันการเริ่มทำงาน? ระบบจะระงับเมนูชั่วคราวจนกว่าจะเสร็จสิ้น')) return;

                setInterfaceLock(true);
                afterActionBtns.classList.add('d-none');
                startBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังดำเนินการ...';
                uiSection.classList.remove('d-none');
                logWindow.innerHTML = '';
                totalSaved = 0;
                totalSavedLabel.innerText = "0.00";
                logContent = "Database Optimization Report\nDate: " + new Date().toLocaleString() + "\n" + "=".repeat(60) + "\n";

                let completed = 0;
                const total   = tables.length;

                for (const table of tables) {
                    statusText.innerText = `กำลังจัดการ: ${table}...`;
                    try {
                        const res    = await fetch(`?action=optimize&table=${encodeURIComponent(table)}`);
                        const result = await res.json();

                        if (result.skipped) {
                            // VIEW หรือ Engine ไม่รองรับ → แสดงสีเทา จางๆ
                            appendLog(result.message, '#888888', true);
                        } else if (result.success) {
                            const saved = Math.max(0, result.before - result.after);
                            totalSaved += saved;
                            totalSavedLabel.innerText = totalSaved.toFixed(2);
                            appendLog(result.message, '#8cf68c');
                        } else {
                            appendLog(result.message, '#ff6b6b');
                        }

                    } catch (error) {
                        appendLog(`❌ ไม่สามารถประมวลผลตาราง: ${table}`, '#ff6b6b');
                    }

                    completed++;
                    const percent = Math.round((completed / total) * 100);
                    progressBar.style.width  = percent + '%';
                    progressBar.innerText    = percent + '%';
                    countText.innerText      = `${completed} / ${total}`;
                }

                logContent += "=".repeat(60) + "\nTotal Space Saved: " + totalSaved.toFixed(2) + " MB\n";
                statusText.innerText = "✅ เสร็จสมบูรณ์!";
                startBtn.classList.add('d-none');
                afterActionBtns.classList.remove('d-none');
                setInterfaceLock(false);
            });

            resetBtn.addEventListener('click', () => {
                startBtn.classList.remove('d-none');
                startBtn.innerHTML = '<i class="fas fa-play mr-2"></i>เริ่มรัน Optimize';
                afterActionBtns.classList.add('d-none');
                uiSection.classList.add('d-none');
                totalSavedLabel.innerText = "0.00";
                progressBar.style.width   = '0%';
                progressBar.innerText     = '0%';
                logWindow.innerHTML       = '<div style="color: #666;">--- กดปุ่มด้านบนเพื่อเริ่มกระบวนการ ---</div>';
            });

            downloadBtn.addEventListener('click', () => {
                const blob = new Blob([logContent], { type: 'text/plain' });
                const url  = window.URL.createObjectURL(blob);
                const a    = document.createElement('a');
                a.href     = url;
                a.download = `db_optimize_report_${new Date().toISOString().slice(0,10)}.txt`;
                a.click();
                window.URL.revokeObjectURL(url);
            });
        });
    </script>
    </body>
    </html>
<?php } ?>