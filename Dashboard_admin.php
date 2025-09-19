<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
    exit(); // เพิ่ม exit() เพื่อหยุดการทำงาน
}

include('config/connect_db.php');

// ดึงข้อมูลจำนวนบ้านทั้งหมดและจำนวนบ้านที่ลงทะเบียนในคิวรีเดียว
$sql_house_data = "
    SELECT 
        SUM(CASE WHEN h.house_number LIKE '67%' THEN 1 ELSE 0 END) AS count_67,
        SUM(CASE WHEN h.house_number LIKE '68%' THEN 1 ELSE 0 END) AS count_68,
        SUM(CASE WHEN hm.house_number LIKE '67%' THEN 1 ELSE 0 END) AS total_67_master,
        SUM(CASE WHEN hm.house_number LIKE '68%' THEN 1 ELSE 0 END) AS total_68_master
    FROM ims_house_master hm
    LEFT JOIN ims_house h ON h.house_number = hm.house_number;
";
$query_house_data = $conn->prepare($sql_house_data);
$query_house_data->execute();
$results_house_data = $query_house_data->fetch(PDO::FETCH_OBJ);

$count_67 = $results_house_data->count_67 ?? 0;
$count_68 = $results_house_data->count_68 ?? 0;
$count_67_house_master = $results_house_data->total_67_master ?? 0;
$count_68_house_master = $results_house_data->total_68_master ?? 0;

$total_house = $count_67 + $count_68;
$all_total_house = $count_67_house_master + $count_68_house_master;

// ดึงจำนวนสมาชิกทั้งหมด
$sql_member = "SELECT COUNT(*) AS total_user FROM ims_user WHERE account_type = 'user'";
$query_member = $conn->prepare($sql_member);
$query_member->execute();
$result_member = $query_member->fetch(PDO::FETCH_OBJ);
$total_user = $result_member->total_user ?? 0;

// คำนวณค่าที่ใช้ในหน้าเว็บ
$unregistered_house = $all_total_house - $total_house;
$percent_chk = ($all_total_house > 0) ? ($total_house / $all_total_house) * 100 : 0;
$cardClass = ($percent_chk >= 50) ? 'border-left-success' : 'border-left-danger';
$percent_67 = ($count_67_house_master > 0) ? ($count_67 / $count_67_house_master) * 100 : 0;
$percent_68 = ($count_68_house_master > 0) ? ($count_68 / $count_68_house_master) * 100 : 0;

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
                    <a href="#collapseCard" data-toggle="collapse" role="button" aria-expanded="true"
                       aria-controls="collapseCard" id="toggleCollapse">
                        <i class="fas fa-chevron-down text-white" id="collapseIcon"></i>
                    </a>
                </div>

                <div class="collapse show" id="collapseCard">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-xl-6 col-md-12 mb-4">
                                <div class="card <?= $cardClass ?> shadow h-100 py-2">
                                    <div class="card-body text-center d-flex flex-column justify-content-center">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-3">
                                            เปรียบเทียบบ้านที่ลงทะเบียนแล้ว กับบ้านทั้งหมด
                                        </div>
                                        <canvas id="totalHousePieChart"
                                                style="max-width: 100%; max-height: 400px;"></canvas>
                                        <p class="text-center mt-3">
                                            จำนวนบ้านทั้งหมด (67 และ 68) :
                                            <strong><?= number_format($all_total_house) ?> หลัง</strong><br>
                                            จำนวนบ้านที่ลงทะเบียนในระบบทั้งหมด (67 และ 68) :
                                            <strong><?= number_format($total_house) ?> หลัง</strong><br>
                                            คิดเป็น <strong><?= number_format($percent_chk, 2) ?>%</strong>
                                            ของบ้านทั้งหมด
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-md-12 mb-4">
                                <div class="card border-left-danger shadow mb-3">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                    LINE OA ระบบส่วนกลางพฤกษา 33
                                                </div>
                                                <div class="row align-items-center">
                                                    <div class="col-auto">
                                                        <img src="img/icon/PS33-COMMONFEE-LINEOA.png"
                                                             alt="QR Code" class="img-fluid rounded shadow-sm"
                                                             style="max-height: 60px;">
                                                    </div>
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

                                <div class="card border-left-primary shadow mb-3">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    จำนวนบ้านที่ลงทะเบียน ระบบส่วนกลางพฤกษา33 ทั้งหมด
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?= number_format($total_house) ?> หลัง
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-home fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-left-info shadow mb-3">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                    จำนวนบ้านที่ลงทะเบียน ระบบส่วนกลางพฤกษา33 ที่ขึ้นต้นด้วย 67
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?= number_format($count_67) ?> หลัง
                                                    จาก <?= number_format($count_67_house_master) ?> หลัง
                                                </div>
                                                <div class="text-xs text-info mt-1">
                                                    คิดเป็น <strong><?= number_format($percent_67, 2) ?>%</strong>
                                                    ของบ้านทั้งหมด
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-home fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-left-success shadow mb-3">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    จำนวนบ้านที่ลงทะเบียน ระบบส่วนกลางพฤกษา33 ที่ขึ้นต้นด้วย 68
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?= number_format($count_68) ?> หลัง
                                                    จาก <?= number_format($count_68_house_master) ?> หลัง
                                                </div>
                                                <div class="text-xs text-success mt-1">
                                                    คิดเป็น <strong><?= number_format($percent_68, 2) ?>%</strong>
                                                    ของบ้านทั้งหมด
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-home fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-left-secondary shadow">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                                    จำนวนสมาชิกที่ลงทะเบียนทั้งหมด
                                                </div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <?= number_format($total_user) ?> User
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
                    </div>

                </div>

                <?php
                include('includes/Modal-Logout.php');
                include('includes/Footer.php');
                ?>
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
                                data: [<?= $total_house ?>, <?= $unregistered_house ?>],
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