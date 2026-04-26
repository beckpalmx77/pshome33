<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');

// ตรวจสอบ Login ตามโครงสร้างระบบเดิมของคุณ
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
} else {
    // กำหนด Path ของไฟล์ SQL (อิงตามโครงสร้างที่คุณแจ้งไว้)
    $logDirPath = "line_oa/checkin/logs/";
    $sqlLogFile = $logDirPath . "debug_insert_queries.sql";

    // อ่านข้อมูลจากไฟล์
    $logs = [];
    if (file_exists($sqlLogFile)) {
        $content = file_get_contents($sqlLogFile);
        $logs = explode(";\n\n", trim($content));
    }

    // ดึงค่า status มาตรวจสอบ (ถ้าไม่มีให้เป็นค่าว่างเพื่อป้องกัน Warning)
    $status = $_GET['status'] ?? '';
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <head>
        <style>
            .card-sql { border: none; border-radius: 12px; transition: 0.2s; border-left: 6px solid #28a745; margin-bottom: 20px; background-color: #fff; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
            .card-sql:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
            pre { background: #1e1e1e; color: #dcdcdc; padding: 15px; border-radius: 8px; font-size: 0.85rem; overflow-x: auto; margin-bottom: 0; }
            .timestamp-box { font-size: 0.9rem; color: #495057; display: flex; align-items: center; gap: 8px; font-weight: 600; }
            .btn-copy { min-width: 110px; }
            .log-container { max-height: 800px; overflow-y: auto; padding: 10px; }
        </style>
    </head>

    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <?php if ($status == 'cleared'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> ล้างข้อมูล Log เรียบร้อยแล้ว
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-database"></i> รายการคำสั่ง SQL ล่าสุด (ตรวจสอบการ Insert)
                                    </h6>
                                    <div class="btn-group">
                                        <button onclick="location.reload();" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-sync-alt"></i> รีเฟรชข้อมูล
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <small class="text-muted d-block mb-3">Path: <?php echo htmlspecialchars($sqlLogFile); ?></small>

                                    <div class="log-container">
                                        <?php if (empty($logs) || (count($logs) == 1 && trim($logs[0]) == "")): ?>
                                            <div class="text-center py-5">
                                                <i class="fas fa-inbox fa-3x text-light"></i>
                                                <p class="mt-3 text-muted">ไม่พบข้อมูลการบันทึกในโฟลเดอร์ logs</p>
                                            </div>
                                        <?php else: ?>
                                            <?php
                                            $reversedLogs = array_reverse($logs);
                                            foreach ($reversedLogs as $log):
                                                if (trim($log) == "") continue;
                                                $lines = explode("\n", trim($log));
                                                $timeInfo = isset($lines[0]) ? str_replace("-- Generated at ", "", $lines[0]) : "Unknown";
                                                $sqlQuery = isset($lines[1]) ? $lines[1] : $lines[0];
                                                ?>
                                                <div class="card card-sql">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <div class="timestamp-box">
                                                                <i class="far fa-clock text-primary"></i>
                                                                <span><?php echo htmlspecialchars($timeInfo); ?></span>
                                                            </div>
                                                            <button class="btn btn-xs btn-primary btn-copy shadow-sm"
                                                                    onclick="copyToClipboard(this, `<?php echo addslashes($sqlQuery); ?>;`)">
                                                                <i class="far fa-copy"></i> คัดลอก SQL
                                                            </button>
                                                        </div>
                                                        <div class="position-relative">
                                                            <pre><code><?php echo htmlspecialchars($sqlQuery); ?>;</code></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            include('includes/Modal-Logout.php');
            include('includes/Footer.php');
            ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        function copyToClipboard(btn, text) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> คัดลอกแล้ว';
                btn.classList.replace('btn-primary', 'btn-success');
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.replace('btn-success', 'btn-primary');
                }, 2000);
            }).catch(err => {
                alert('ไม่สามารถคัดลอกได้');
            });
        }
    </script>
    </body>
    </html>

<?php } ?>