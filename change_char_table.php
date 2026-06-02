<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    require_once 'config/connect_db.php';
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <input type="hidden" id="main_menu" value="<?php echo urldecode($_GET['m']) ?>">
                        <input type="hidden" id="sub_menu" value="<?php echo urldecode($_GET['s']) ?>">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">กระบวนการเปลี่ยนอักขระเป็น utf8mb4 (รองรับ Emoji และภาษาไทย)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="fa fa-exclamation-triangle"></i> <strong>คำเตือน:</strong> กรุณาสำรองฐานข้อมูลก่อนดำเนินการ กระบวนการนี้อาจใช้เวลาครู่หนึ่ง
                                    </div>

                                    <?php
                                    // เตรียมข้อมูลตารางไว้ก่อน
                                    $stmt = $conn->prepare("SELECT table_name FROM information_schema.TABLES WHERE table_schema = :db AND table_type = 'BASE TABLE'");
                                    $stmt->execute(['db' => DB_NAME]);
                                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                    $stmt->closeCursor();
                                    ?>

                                    <?php if (!isset($_POST['btnStart'])): ?>
                                        <div class="mb-4 text-center">
                                            <form method="POST" action="">
                                                <button type="submit" name="btnStart" id="btnStart" class="btn btn-primary btn-lg px-5">
                                                    <i class="fa fa-play"></i> เริ่มต้นการประมวลผล (Start Upgrade)
                                                </button>
                                            </form>
                                        </div>
                                        <div class="mb-2"><strong>📊 รายการตารางที่พบในฐานข้อมูล (<?php echo count($tables); ?> ตาราง):</strong></div>
                                        <div style="max-height: 300px; overflow-y: auto; background: #f8f9fc; padding: 15px; border-radius: 5px; border: 1px solid #e3e6f0;">
                                            <?php foreach ($tables as $table): ?>
                                                <div class="text-muted"><i class="fa fa-table"></i> <?php echo $table; ?> <small>[รอการประมวลผล]</small></div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div id="process-result">
                                        <?php
                                        if (isset($_POST['btnStart'])) {
                                            // เปิด Error Reporting เพื่อให้เห็นถ้ามีปัญหา
                                            error_reporting(E_ALL);
                                            ini_set('display_errors', 1);
                                            
                                            // ตั้งค่าหน่วงเวลาให้นานขึ้น
                                            set_time_limit(600); 

                                            echo "<div class='mb-3'><strong>🚀 เริ่มต้นการอัปเกรดฐานข้อมูล: " . DB_NAME . "</strong></div>";
                                            // บังคับส่งข้อมูลออกทันที
                                            echo str_repeat(' ', 1024); // เติมช่องว่างให้เกิน Buffer ของ Browser บางตัว
                                            if (ob_get_level()) ob_flush();
                                            flush();

                                            try {
                                                // 1. Database Level
                                                $conn->exec("ALTER DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                                                echo "<div class='text-success mb-3'><i class='fa fa-check-circle'></i> 1. ตั้งค่ามาตรฐานฐานข้อมูลเป็น utf8mb4 สำเร็จ</div>";
                                                if (ob_get_level()) ob_flush();
                                                flush();

                                                // 2. Table Level
                                                echo "<div class='mb-2'><strong>2. กำลังดำเนินการแปลงตาราง...</strong></div>";
                                                echo "<div style='max-height: 300px; overflow-y: auto; background: #f8f9fc; padding: 15px; border-radius: 5px; border: 1px solid #e3e6f0;'>";
                                                
                                                foreach ($tables as $table) {
                                                    echo "<div><i class='fa fa-table text-primary'></i> ตาราง: $table ";
                                                    try {
                                                        $conn->query("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")->closeCursor();
                                                        echo "<span class='text-success small'>[สำเร็จ]</span>";
                                                    } catch (PDOException $e) {
                                                        echo "<span class='text-danger small'>[ผิดพลาด: " . $e->getMessage() . "]</span>";
                                                    }
                                                    echo "</div>";
                                                    if (ob_get_level()) ob_flush();
                                                    flush();
                                                }
                                                echo "</div>";

                                                // 3. Optimization
                                                echo "<div class='mt-3 mb-2'><strong>3. จัดระเบียบข้อมูล (Optimization)...</strong></div>";
                                                foreach ($tables as $table) {
                                                    try {
                                                        $conn->query("OPTIMIZE TABLE `$table`")->closeCursor();
                                                    } catch (Exception $e) {}
                                                    if (ob_get_level()) ob_flush();
                                                    flush();
                                                }

                                                echo "<div class='alert alert-success mt-4'>";
                                                echo "<h4><i class='fa fa-check-circle'></i> ดำเนินการเสร็จสิ้นสมบูรณ์!</h4>";
                                                echo "ฐานข้อมูลของคุณรองรับภาษาไทยสมบูรณ์แบบและ Emoji เรียบร้อยแล้ว";
                                                echo "</div>";
                                                echo "<a href='change_char_table.php' class='btn btn-secondary mt-2'><i class='fa fa-arrow-left'></i> กลับหน้าหลัก</a>";

                                            } catch (PDOException $e) {
                                                echo "<div class='alert alert-danger'>❌ เกิดข้อผิดพลาดร้ายแรง: " . $e->getMessage() . "</div>";
                                                echo "<a href='change_char_table.php' class='btn btn-secondary mt-2'>ลองใหม่อีกครั้ง</a>";
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!---Container Fluid-->
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include('includes/Modal-Logout.php'); ?>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#btnStart').on('click', function() {
                if(confirm('คุณแน่ใจหรือไม่ว่าต้องการเริ่มกระบวนการอัปเกรด? กรุณาตรวจสอบว่าได้สำรองข้อมูลเรียบร้อยแล้ว')) {
                    // ไม่ต้อง disabled ปุ่มเพื่อป้องกันปัญหาส่งค่า POST ไม่ไปในบาง browser
                    $(this).html('<i class="fa fa-spinner fa-spin"></i> กำลังประมวลผล... กรุณารอสักครู่');
                } else {
                    return false;
                }
            });
        });
    </script>

    </body>
    </html>
<?php } ?>
