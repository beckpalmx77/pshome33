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
                        <h1 class="h3 mb-0 text-gray-800">อัปเกรดระบบฐานข้อมูล (Character Set Upgrade)</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item">Settings</li>
                            <li class="breadcrumb-item active" aria-current="page">Character Set Upgrade</li>
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
                                        <i class="fa fa-exclamation-triangle"></i> <strong>คำเตือน:</strong> กรุณาสำรองฐานข้อมูลก่อนดำเนินการ กระบวนการนี้อาจใช้เวลาครู่หนึ่งขึ้นอยู่กับขนาดของข้อมูล
                                    </div>
                                    
                                    <div id="process-result">
                                        <?php
                                        // เริ่มต้นกระบวนการ
                                        set_time_limit(600); 

                                        try {
                                            echo "<div class='mb-3'><strong>🚀 เริ่มต้นการอัปเกรดฐานข้อมูล: " . DB_NAME . "</strong></div>";

                                            // 1. ตั้งค่าระดับ Database
                                            $conn->exec("ALTER DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                                            echo "<div class='text-success mb-2'><i class='fa fa-check-circle'></i> 1. ตั้งค่ามาตรฐานฐานข้อมูลเป็น utf8mb4 สำเร็จ</div>";

                                            // 2. ดึงรายชื่อตาราง
                                            $stmt = $conn->prepare("SELECT table_name FROM information_schema.TABLES WHERE table_schema = :db AND table_type = 'BASE TABLE'");
                                            $stmt->execute(['db' => DB_NAME]);
                                            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                                            $stmt->closeCursor(); // ล้างคำสั่งที่ค้างอยู่เพื่อให้รันคำสั่งถัดไปได้

                                            echo "<div class='mb-2'><strong>2. กำลังดำเนินการกับ " . count($tables) . " ตาราง:</strong></div>";
                                            echo "<div style='max-height: 300px; overflow-y: auto; background: #f8f9fc; padding: 15px; border-radius: 5px; border: 1px solid #e3e6f0;'>";
                                            
                                            foreach ($tables as $table) {
                                                echo "<div><i class='fa fa-table text-primary'></i> ตาราง: $table ";
                                                try {
                                                    // ใช้ query()->closeCursor() แทน exec() เพื่อความแน่นอนในการล้าง Buffer
                                                    $conn->query("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci")->closeCursor();
                                                    echo "<span class='text-success small'>[สำเร็จ]</span>";
                                                } catch (PDOException $e) {
                                                    echo "<span class='text-danger small'>[ผิดพลาด: " . $e->getMessage() . "]</span>";
                                                }
                                                echo "</div>";
                                            }
                                            echo "</div>";

                                            // 3. ปิดท้ายด้วยการ Optimize
                                            echo "<div class='mt-3 mb-2'><strong>3. จัดระเบียบข้อมูล (Optimization)...</strong></div>";
                                            foreach ($tables as $table) {
                                                try {
                                                    $conn->query("OPTIMIZE TABLE `$table`")->closeCursor();
                                                } catch (Exception $e) {
                                                    // ข้ามหากติดปัญหาจุกจิก
                                                }
                                            }

                                            echo "<div class='alert alert-success mt-4'>";
                                            echo "<h4><i class='fa fa-check-circle'></i> ดำเนินการเสร็จสิ้นสมบูรณ์!</h4>";
                                            echo "ฐานข้อมูลของคุณรองรับภาษาไทยสมบูรณ์แบบและ Emoji เรียบร้อยแล้ว";
                                            echo "</div>";

                                        } catch (PDOException $e) {
                                            echo "<div class='alert alert-danger'>❌ เกิดข้อผิดพลาดร้ายแรง: " . $e->getMessage() . "</div>";
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

    </body>
    </html>
<?php } ?>
