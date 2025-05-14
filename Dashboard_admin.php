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

    $all_total_house = 621;

    $unregistered_house = $all_total_house - $total_house;

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
                <?php include('includes/Top-Bar.php'); ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">สรุปข้อมูลภาพรวม</h6>
                        <a href="#collapseCard" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseCard" id="toggleCollapse">
                            <i class="fas fa-chevron-down text-white" id="collapseIcon"></i>
                        </a>
                    </div>

                    <div class="collapse show" id="collapseCard">
                        <div class="card-body">

                            <!-- Row: Chart (ซ้าย) + Card ทั้งหมด (ขวา) -->
                            <div class="row">
                                <!-- Chart -->
                                <div class="col-xl-6 col-md-12 mb-4">
                                    <div class="card border-left-danger shadow h-100 py-2">
                                        <div class="card-body text-center d-flex flex-column justify-content-center">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-3">
                                                เปรียบเทียบบ้านที่ลงทะเบียนแล้ว กับบ้านทั้งหมด
                                            </div>
                                            <canvas id="totalHousePieChart" style="max-width: 100%; max-height: 400px;"></canvas>
                                            <p class="text-center mt-3">
                                                <?php
                                                if ($all_total_house > 0) {
                                                    $percent = ($total_house / $all_total_house) * 100;
                                                    echo "จำนวนบ้านทั้งหมด (67 และ 68) : <strong>" . "621" . " หลัง</strong><br>";
                                                    echo "จำนวนบ้านที่ลงทะเบียนในระบบทั้งหมด (67 และ 68) : <strong>" . number_format($total_house) . " หลัง</strong><br>";
                                                    echo "คิดเป็น <strong>" . number_format($percent, 2) . "%</strong> ของบ้านทั้งหมด";
                                                } else {
                                                    echo "ไม่มีข้อมูลบ้านทั้งหมด";
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cards 67, 68, User (ด้านขวา) -->
                                <div class="col-xl-6 col-md-12 mb-4">
                                    <!-- ไม่ใช้ flex-column แล้ว แต่ให้ card แต่ละใบเว้นห่างด้วย mb-3 -->

                                    <!-- Card 67+68 -->
                                    <div class="card border-left-danger shadow mb-3">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                        LINE OA ระบบส่วนกลางพฤกษา 33
                                                    </div>

                                                    <!-- QR + Text -->
                                                    <div class="row align-items-center">
                                                        <!-- รูป QR -->
                                                        <div class="col-auto">
                                                            <img src="img/icon/PS33-COMMONFEE-LINEOA.png" alt="QR Code" class="img-fluid rounded shadow-sm" style="max-height: 60px;">
                                                        </div>

                                                        <!-- ข้อความ -->
                                                        <div class="col">
                                                            <span class="text-gray-800 ml-2">Scan QR Code เพื่อเข้าระบบค่าส่วนกลางพฤกษา 33</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-auto">
                                                    <i class="fab fa-line fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Card 67+68 -->
                                    <div class="card border-left-primary shadow mb-3">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                        จำนวนบ้านที่ลงทะเบียน ระบบส่วนกลางพฤกษา33 ทั้งหมด
                                                    </div>
                                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                        <?php echo number_format($count_67) + number_format($count_68); ?> หลัง
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-home fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card 67 -->
                                    <div class="card border-left-info shadow mb-3">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                        จำนวนบ้านที่ลงทะเบียน ระบบส่วนกลางพฤกษา33 ที่ขึ้นต้นด้วย 67
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

                                    <!-- Card 68 -->
                                    <div class="card border-left-success shadow mb-3">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                        จำนวนบ้านที่ลงทะเบียน ระบบส่วนกลางพฤกษา33 ที่ขึ้นต้นด้วย 68
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

                                    <!-- Card User -->
                                    <div class="card border-left-secondary shadow">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                                        จำนวนสมาชิกที่ลงทะเบียนทั้งหมด
                                                    </div>
                                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                        <?php echo number_format($total_user); ?> User
                                                    </div>
                                                </div>
                                                <div class="col-auto">
                                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div> <!-- end row -->

                        </div> <!-- end card-body -->
                    </div> <!-- end collapse -->
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

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const collapseCard = document.getElementById("collapseCard");
            const collapseIcon = document.getElementById("collapseIcon");

            // ใช้ Bootstrap collapse events
            collapseCard.addEventListener("show.bs.collapse", function () {
                collapseIcon.classList.remove("fa-chevron-down");
                collapseIcon.classList.add("fa-chevron-up");
            });

            collapseCard.addEventListener("hide.bs.collapse", function () {
                collapseIcon.classList.remove("fa-chevron-up");
                collapseIcon.classList.add("fa-chevron-down");
            });
        });
    </script>

    <script>
        const ctxTotal = document.getElementById("totalHousePieChart").getContext('2d');
        const totalHousePieChart = new Chart(ctxTotal, {
            type: 'pie',
            data: {
                labels: ['ลงทะเบียนแล้ว', 'ยังไม่ลงทะเบียน'],
                datasets: [{
                    data: [<?php echo $total_house; ?>, <?php echo $unregistered_house; ?>],
                    backgroundColor: ['#28a745', '#dc3545'],
                    hoverBackgroundColor: ['#218838', '#c82333'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>



    </body>

    </html>

<?php } ?>

