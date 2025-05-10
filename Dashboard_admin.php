<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {

    include('config/connect_db.php');

    // SQL ดึงจำนวนบ้านไม่ซ้ำ แยกตาม prefix
    $sql = "SELECT 
            CASE 
                WHEN house_number LIKE '67%' THEN '67xx'
                WHEN house_number LIKE '68%' THEN '68xx'
            END AS prefix,
            COUNT(DISTINCT house_number) AS total
        FROM ims_house
        WHERE house_number LIKE '67%' OR house_number LIKE '68%'
        GROUP BY prefix";

    $query = $conn->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    // เตรียมตัวแปร
    $count_67 = 0;
    $count_68 = 0;

    foreach ($results as $row) {
        if ($row->prefix === '67xx') {
            $count_67 = $row->total;
        } elseif ($row->prefix === '68xx') {
            $count_68 = $row->total;
        }
    }

    $total_house = $count_67 + $count_68;

    $sql_member = "SELECT COUNT(*) AS total_user FROM ims_user WHERE account_type = 'user'";
    $query_member = $conn->prepare($sql_member);
    $query_member->execute();
    $result_member = $query_member->fetch(PDO::FETCH_OBJ);
    $total_user = $result_member->total_user ?? 0;

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

                <!-- Card หลัก -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-dark text-white">
                        <h6 class="m-0 font-weight-bold">สรุปข้อมูลภาพรวม</h6>
                    </div>
                    <div class="card-body">

                        <!-- แถวที่ 1: รวมบ้านทั้งหมด + สมาชิก -->
                        <div class="row">
                            <!-- บ้านรวม -->
                            <div class="col-xl-6 col-md-6 mb-4">
                                <div class="card border-left-dark shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                                    จำนวนบ้านที่ลงทะเบียนทั้งหมด (67 และ 68)
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo number_format($total_house); ?> หลัง
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-city fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- สมาชิก -->
                            <div class="col-xl-6 col-md-6 mb-4">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    จำนวนสมาชิกทั้งหมด
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo number_format($total_user); ?> คน
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-users fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- แถวที่ 2: 67xx และ 68xx -->
                        <div class="row">
                            <!-- 67xx -->
                            <div class="col-xl-6 col-md-6 mb-4">
                                <div class="card border-left-info shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                    จำนวนบ้านขึ้นต้นด้วย 67
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo number_format($count_67); ?> หลัง
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-home fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 68xx -->
                            <div class="col-xl-6 col-md-6 mb-4">
                                <div class="card border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    จำนวนบ้านขึ้นต้นด้วย 68
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?php echo number_format($count_68); ?> หลัง
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-home fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- end card-body -->
                </div> <!-- end card -->



            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>
    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="js/chart/chart-area-demo.js"></script>

    <link href='vendor/calendar/main.css' rel='stylesheet'/>
    <script src='vendor/calendar/main.js'></script>
    <script src='vendor/calendar/locales/th.js'></script>

    <script src='js/clock_time.js'></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>


    </body>

    </html>

<?php } ?>

