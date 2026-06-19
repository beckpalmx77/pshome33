<?php
/**
 * Auto-Sync Transactions to General Ledger (GL) (Web with Progress Bar)
 * จัดหน้าจอและสไตล์เหมือน reindex_table.php
 */

require_once 'config/connect_db.php';
require_once 'util/gl_util.php';

// Helper mapping function for cash/bank methods
function GetCashAccMapping($method) {
    return ($method === 'เงินสด') ? '1101' : '1102';
}

// Smart mapping helper for expenses based on keywords
function GetExpenseAccMapping($purpose) {
    $purpose = mb_strtolower($purpose, 'UTF-8');
    if (mb_strpos($purpose, 'ไฟ') !== false || mb_strpos($purpose, 'ไฟฟ้า') !== false) {
        return '5102'; // ค่าไฟฟ้า (ส่วนกลาง)
    }
    if (mb_strpos($purpose, 'น้ำ') !== false || mb_strpos($purpose, 'ประปา') !== false) {
        return '5103'; // ค่าน้ำประปา (ส่วนกลาง)
    }
    if (mb_strpos($purpose, 'รปภ') !== false || mb_strpos($purpose, 'รักษาความปลอดภัย') !== false || mb_strpos($purpose, 'ตรวจ') !== false) {
        return '5104'; // ค่าจ้าง รปภ.
    }
    if (mb_strpos($purpose, 'สะอาด') !== false || mb_strpos($purpose, 'กวาด') !== false || mb_strpos($purpose, 'ขยะ') !== false || mb_strpos($purpose, 'หญ้า') !== false || mb_strpos($purpose, 'แม่บ้าน') !== false) {
        return '5105'; // ค่าจ้างรักษาความสะอาด
    }
    return '5101'; // ค่าใช้จ่ายทั่วไป
}

// 1. ส่วนประมวลผล AJAX
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $response = ['success' => false, 'message' => ''];

    try {
        if ($action === 'get_tasks') {
            // ดึงเฉพาะรายการที่ยังไม่ได้ผ่านรายการเข้า GL
            $count_pv = $conn->query("
                SELECT COUNT(*) FROM ims_payment_voucher 
                WHERE status = 'Active' AND total_amount > 0 
                AND doc_no NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'PV')
            ")->fetchColumn();

            $count_fee = $conn->query("
                SELECT COUNT(*) FROM ims_house_payment 
                WHERE payment_status = 'Y' AND amount > 0 
                AND doc_id NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'RV')
            ")->fetchColumn();

            $count_other = $conn->query("
                SELECT COUNT(*) FROM ims_reciepts 
                WHERE approve_status = 'Y' AND amount > 0 
                AND doc_id NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'RV')
            ")->fetchColumn();

            $tasks = [];
            
            // Chunks for PV
            $pv_chunk_size = 50;
            for ($i = 0; $i < $count_pv; $i += $pv_chunk_size) {
                $tasks[] = [
                    'type' => 'pv',
                    'limit' => $pv_chunk_size,
                    'label' => "ผ่านรายการใบสำคัญจ่ายค้างโพสต์ (PV) ชุดที่ " . (floor($i / $pv_chunk_size) + 1) . " / " . ceil($count_pv / $pv_chunk_size)
                ];
            }

            // Chunks for Fee
            $fee_chunk_size = 100;
            for ($i = 0; $i < $count_fee; $i += $fee_chunk_size) {
                $tasks[] = [
                    'type' => 'fee',
                    'limit' => $fee_chunk_size,
                    'label' => "ผ่านรายการค่าส่วนกลางค้างโพสต์ (RV) ชุดที่ " . (floor($i / $fee_chunk_size) + 1) . " / " . ceil($count_fee / $fee_chunk_size)
                ];
            }

            // Chunks for Other
            $other_chunk_size = 50;
            for ($i = 0; $i < $count_other; $i += $other_chunk_size) {
                $tasks[] = [
                    'type' => 'other',
                    'limit' => $other_chunk_size,
                    'label' => "ผ่านรายการรายรับอื่นๆ ค้างโพสต์ (RV) ชุดที่ " . (floor($i / $other_chunk_size) + 1) . " / " . ceil($count_other / $other_chunk_size)
                ];
            }

            if (empty($tasks)) {
                $tasks[] = [
                    'type' => 'none',
                    'limit' => 0,
                    'label' => 'ตรวจสอบแล้ว: ไม่มีรายการค้างผ่านบัญชี'
                ];
            }

            echo json_encode([
                'success' => true,
                'tasks' => $tasks,
                'summary' => [
                    'pv_count' => (int)$count_pv,
                    'fee_count' => (int)$count_fee,
                    'other_count' => (int)$count_other
                ]
            ]);
            exit;
        }

        if ($action === 'process_task') {
            $type = $_GET['type'] ?? '';
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

            if ($type === 'none') {
                $response['success'] = true;
                $response['message'] = "✨ ข้อมูลสมุดแยกประเภททั่วไปเป็นปัจจุบันแล้ว ไม่พบรายการค้างผ่านบัญชี";
                echo json_encode($response);
                exit;
            }

            if ($type === 'pv') {
                $conn->beginTransaction();
                // ค้นหารายการค้างโพสต์ PV (ใช้ LIMIT โดยไม่ต้อง OFFSET เพราะตัวที่โพสต์ไปแล้วจะไม่ติดเงื่อนไข NOT IN ในคำขอถัดไป)
                $sql = "SELECT * FROM ims_payment_voucher 
                        WHERE status = 'Active' AND total_amount > 0 
                        AND doc_no NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'PV')
                        ORDER BY id ASC LIMIT :limit";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $cnt = 0;
                $amt = 0;
                foreach ($records as $v) {
                    $exp_acc = GetExpenseAccMapping($v['purpose']);
                    $cash_acc = GetCashAccMapping($v['payment_method']);
                    $entries = [
                        ['acc_code' => $exp_acc, 'dr' => (float)$v['total_amount'], 'cr' => 0],
                        ['acc_code' => $cash_acc, 'dr' => 0, 'cr' => (float)$v['total_amount']]
                    ];
                    $desc = "จ่ายเงินแด่: " . $v['supplier_name'] . " (" . $v['purpose'] . ")";
                    PostToGL($conn, $v['doc_date'], $v['doc_no'], $desc, $entries, 'PV');
                    $amt += (float)$v['total_amount'];
                    $cnt++;
                }
                $conn->commit();

                $response['success'] = true;
                $response['message'] = "✅ [PV] ผ่านรายการรายจ่ายเสร็จสิ้น $cnt รายการ ยอดรวม: " . number_format($amt, 2) . " บาท";
                echo json_encode($response);
                exit;
            }

            if ($type === 'fee') {
                $conn->beginTransaction();
                $sql = "SELECT p.*, v.month_name_start, v.month_name_to 
                        FROM ims_house_payment p
                        LEFT JOIN v_ims_house_payment v ON p.id = v.id
                        WHERE p.payment_status = 'Y' AND p.amount > 0 
                        AND p.doc_id NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'RV')
                        ORDER BY p.id ASC LIMIT :limit";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $cnt = 0;
                $amt = 0;
                foreach ($records as $p) {
                    $cash_acc = GetCashAccMapping($p['payment_method']);
                    $entries = [
                        ['acc_code' => $cash_acc, 'dr' => (float)$p['amount'], 'cr' => 0],
                        ['acc_code' => '4101', 'dr' => 0, 'cr' => (float)$p['amount']]
                    ];
                    $desc = "รับชำระค่าส่วนกลาง บ้านเลขที่ " . $p['house_number'] . " (งวด " . $p['month_name_start'] . " - " . $p['month_name_to'] . " ปี " . $p['period_year'] . ")";
                    PostToGL($conn, $p['payment_date'], $p['doc_id'], $desc, $entries, 'RV');
                    $amt += (float)$p['amount'];
                    $cnt++;
                }
                $conn->commit();

                $response['success'] = true;
                $response['message'] = "✅ [RV] ผ่านรายการค่าส่วนกลางเสร็จสิ้น $cnt รายการ ยอดรวม: " . number_format($amt, 2) . " บาท";
                echo json_encode($response);
                exit;
            }

            if ($type === 'other') {
                $conn->beginTransaction();
                $sql = "SELECT * FROM ims_reciepts 
                        WHERE approve_status = 'Y' AND amount > 0 
                        AND doc_id NOT IN (SELECT doc_no FROM ims_gl_header WHERE source_type = 'RV')
                        ORDER BY id ASC LIMIT :limit";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->execute();
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $cnt = 0;
                $amt = 0;
                foreach ($records as $r) {
                    $cash_acc = GetCashAccMapping($r['payment_method']);
                    $entries = [
                        ['acc_code' => $cash_acc, 'dr' => (float)$r['amount'], 'cr' => 0],
                        ['acc_code' => '4103', 'dr' => 0, 'cr' => (float)$r['amount']]
                    ];
                    $desc = "รับเงินจาก " . $r['supplier_name'] . " (" . $r['description'] . ")";
                    PostToGL($conn, $r['reciept_date'], $r['doc_id'], $desc, $entries, 'RV');
                    $amt += (float)$r['amount'];
                    $cnt++;
                }
                $conn->commit();

                $response['success'] = true;
                $response['message'] = "✅ [RV] ผ่านรายการรายรับอื่นๆ เสร็จสิ้น $cnt รายการ ยอดรวม: " . number_format($amt, 2) . " บาท";
                echo json_encode($response);
                exit;
            }
        }

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $response['message'] = "❌ ผิดพลาด: " . $e->getMessage();
        echo json_encode($response);
        exit;
    }
}

// 2. ส่วนแสดงผล UI
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
} else {
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
                        <h1 class="h4 mb-0 text-gray-800">GL Ledger Sync</h1>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold"><i class="fas fa-sync"></i> Sync New Transactions to GL</h6>
                                    <a href="<?php echo $dashboard_url; ?>" class="btn btn-sm btn-light shadow-sm text-primary font-weight-bold">
                                        <i class="fas fa-home fa-sm"></i> Home
                                    </a>
                                </div>
                                <div class="card-body">
                                    <!-- Summary -->
                                    <div class="row text-center mb-4">
                                        <div class="col-4 border-right">
                                            <span class="text-muted small">รายจ่ายค้างโพสต์ (PV)</span>
                                            <div id="lbl-pv-count" class="h3 font-weight-bold text-danger">-</div>
                                        </div>
                                        <div class="col-4 border-right">
                                            <span class="text-muted small">รายรับค่าส่วนกลางค้างโพสต์ (RV)</span>
                                            <div id="lbl-fee-count" class="h3 font-weight-bold text-danger">-</div>
                                        </div>
                                        <div class="col-4">
                                            <span class="text-muted small">รายรับอื่นๆ ค้างโพสต์ (RV)</span>
                                            <div id="lbl-other-count" class="h3 font-weight-bold text-danger">-</div>
                                        </div>
                                    </div>

                                    <!-- Status Alert -->
                                    <div class="alert alert-info text-dark font-weight-bold" role="alert">
                                        <i class="fas fa-info-circle"></i> ข้อมูลเพิ่มเติม: สคริปต์นี้จะสแกนหาเฉพาะรายการรายรับ-รายจ่ายที่บันทึกอนุมัติใหม่ แต่ยังไม่ได้โพสต์บัญชีแยกประเภททั่วไป (GL) และนำผ่านรายการเข้าสู่ระบบทันที
                                    </div>

                                    <!-- Buttons -->
                                    <div class="text-center mb-4">
                                        <button id="start-btn" class="btn btn-primary btn-lg px-4 font-weight-bold shadow">
                                            <i class="fas fa-play mr-2"></i>เริ่มดึงข้อมูลผ่านบัญชี
                                        </button>
                                        <div id="after-action-btns" class="d-none">
                                            <button id="reset-btn" class="btn btn-warning btn-lg px-4 font-weight-bold">
                                                <i class="fas fa-undo mr-2"></i>Reset ตรวจสอบใหม่
                                            </button>
                                            <button id="download-btn" class="btn btn-outline-info btn-lg px-4 font-weight-bold">
                                                <i class="fas fa-file-alt mr-2"></i>ดาวน์โหลดรายงาน
                                            </button>
                                            <a href="<?php echo $dashboard_url; ?>" class="btn btn-outline-secondary btn-lg px-4 font-weight-bold">
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
                                            <span id="status-text" class="font-weight-bold text-primary small">กำลังตรวจสอบรายการค้างผ่านบัญชี...</span>
                                            <span id="count-text" class="text-muted small">0 / 0</span>
                                        </div>
                                        <div id="log-window" style="background-color: #1e1e1e; color: #dcdccc; padding: 20px; border-radius: 8px; height: 320px; overflow-y: auto; font-family: 'Consolas', monospace; font-size: 13px; line-height: 1.5; text-align: left;">
                                            <div style="color: #666;">--- กดปุ่มเพื่อเริ่มประมวลผลดึงข้อมูลค้างผ่านรายการ ---</div>
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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', async function () {
            const startBtn        = document.getElementById('start-btn');
            const resetBtn        = document.getElementById('reset-btn');
            const downloadBtn     = document.getElementById('download-btn');
            const afterActionBtns = document.getElementById('after-action-btns');
            const progressBar     = document.getElementById('progress-bar');
            const uiSection       = document.getElementById('ui-section');
            const logWindow       = document.getElementById('log-window');
            const statusText      = document.getElementById('status-text');
            const countText       = document.getElementById('count-text');
            const lockOverlay     = document.getElementById('lock-overlay');
            const sidebar         = document.getElementById('accordionSidebar');

            const lblPv = document.getElementById('lbl-pv-count');
            const lblFee = document.getElementById('lbl-fee-count');
            const lblOther = document.getElementById('lbl-other-count');

            let tasks = [];
            let logContent = "";

            // โหลดข้อมูลรายการค้างโพสต์บัญชีสะสม
            async function loadSummary() {
                try {
                    const res = await fetch('sync_transactions_to_gl.php?action=get_tasks');
                    const data = await res.json();
                    if (data.success) {
                        tasks = data.tasks;
                        lblPv.innerText = data.summary.pv_count.toLocaleString();
                        lblFee.innerText = data.summary.fee_count.toLocaleString();
                        lblOther.innerText = data.summary.other_count.toLocaleString();

                        // หากไม่มีงานค้างเลย ให้ปิดปุ่มเริ่มและเปลี่ยนสไตล์เป็นสีเขียว/ปุ่มปิดใช้งาน
                        if (data.summary.pv_count === 0 && data.summary.fee_count === 0 && data.summary.other_count === 0) {
                            startBtn.classList.add('btn-success');
                            startBtn.classList.remove('btn-primary');
                            startBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>ข้อมูลแยกประเภทเป็นปัจจุบันแล้ว';
                            startBtn.disabled = true;
                        } else {
                            startBtn.classList.remove('btn-success');
                            startBtn.classList.add('btn-primary');
                            startBtn.innerHTML = '<i class="fas fa-play mr-2"></i>เริ่มดึงข้อมูลผ่านบัญชี';
                            startBtn.disabled = false;
                        }
                    }
                } catch (e) {
                    console.error("Failed to load sync summary", e);
                }
            }

            await loadSummary();

            function setInterfaceLock(isLocked) {
                lockOverlay.style.display = isLocked ? 'block' : 'none';
                if (sidebar) sidebar.classList.toggle('working-overlay', isLocked);
                startBtn.disabled = isLocked;
            }

            function appendLog(message, color = '#dcdccc') {
                const time    = new Date().toLocaleTimeString();
                const logLine = `[${time}] ${message}`;
                const div     = document.createElement('div');
                div.style.color        = color;
                div.style.marginBottom = '3px';
                div.innerText          = logLine;
                logWindow.appendChild(div);
                logWindow.scrollTop    = logWindow.scrollHeight;
                logContent            += logLine + "\n";
            }

            startBtn.addEventListener('click', async () => {
                if (tasks.length === 1 && tasks[0].type === 'none') return;

                setInterfaceLock(true);
                afterActionBtns.classList.add('d-none');
                startBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังดำเนินการผ่านรายการ...';
                uiSection.classList.remove('d-none');
                logWindow.innerHTML = '';
                
                logContent = "GL Sync Transactions Report\nDate: " + new Date().toLocaleString() + "\n" + "=".repeat(60) + "\n";

                let completed = 0;
                const total = tasks.length;
                countText.innerText = `0 / ${total}`;

                for (const task of tasks) {
                    statusText.innerText = `กำลังประมวลผล: ${task.label}`;
                    
                    try {
                        const url = `sync_transactions_to_gl.php?action=process_task&type=${task.type}&limit=${task.limit}`;
                        const res = await fetch(url);
                        
                        if (!res.ok) {
                            const errText = await res.text();
                            appendLog(`❌ HTTP Error ${res.status}: ${errText.substring(0, 200)}`, '#ff6b6b');
                            continue;
                        }

                        let result;
                        const rawText = await res.text();
                        try {
                            result = JSON.parse(rawText);
                        } catch (jsonErr) {
                            appendLog(`❌ JSON Parse Error: ${rawText.substring(0, 200)}`, '#ff6b6b');
                            continue;
                        }

                        if (result.success) {
                            appendLog(result.message, '#8cf68c');
                        } else {
                            appendLog(result.message, '#ff6b6b');
                        }
                    } catch (e) {
                        appendLog(`❌ เกิดข้อผิดพลาดทางเทคนิค: ${task.label} - ${e.message}`, '#ff6b6b');
                    }

                    // หน่วงเวลา 200ms เพื่อป้องกันการทำงานหนักเกินไปของเซิร์ฟเวอร์
                    await new Promise(resolve => setTimeout(resolve, 200));

                    completed++;
                    const percent = Math.round((completed / total) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.innerText   = percent + '%';
                    countText.innerText     = `${completed} / ${total}`;
                }

                statusText.innerText = "✅ ดึงข้อมูลผ่านบัญชีแยกประเภททั่วไปสำเร็จ!";
                appendLog("🎉 โพสต์รายการคงค้างทั้งหมดเข้าบัญชีสำเร็จเรียบร้อย!");
                startBtn.classList.add('d-none');
                afterActionBtns.classList.remove('d-none');
                setInterfaceLock(false);
            });

            resetBtn.addEventListener('click', async () => {
                startBtn.classList.remove('d-none');
                afterActionBtns.classList.add('d-none');
                uiSection.classList.add('d-none');
                progressBar.style.width = '0%';
                progressBar.innerText   = '0%';
                logWindow.innerHTML     = '<div style="color: #666;">--- กดปุ่มเพื่อเริ่มประมวลผลดึงข้อมูลค้างผ่านรายการ ---</div>';
                await loadSummary();
            });

            downloadBtn.addEventListener('click', () => {
                const blob = new Blob([logContent], { type: 'text/plain' });
                const url  = window.URL.createObjectURL(blob);
                const a    = document.createElement('a');
                a.href     = url;
                a.download = `gl_sync_report_${new Date().toISOString().slice(0,10)}.txt`;
                a.click();
                window.URL.revokeObjectURL(url);
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
