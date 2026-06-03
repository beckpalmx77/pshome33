<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
    exit();
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

// ดึงยอดรวมค่าส่วนกลาง
$sql_sum_house_data = " SELECT SUM(common_fee) AS common_fee FROM ims_house_master ";
$query_sum_house_data = $conn->prepare($sql_sum_house_data);
$query_sum_house_data->execute();
$result_sum_house_data = $query_sum_house_data->fetch(PDO::FETCH_OBJ);
$total_common_fee = $result_sum_house_data->common_fee ?? 0; // เก็บค่าใส่ตัวแปรเพื่อให้ใช้ง่าย

// ดึงยอดเงินชำระค่าส่วนกลาง ของเดือนปัจจุบัน ที่ยืนยันการจัดเก็บได้
$curr_month = (int)date('m');
$curr_year = (int)date('Y');
$thai_months = [
    1 => "มกราคม", 2 => "กุมภาพันธ์", 3 => "มีนาคม", 4 => "เมษายน",
    5 => "พฤษภาคม", 6 => "มิถุนายน", 7 => "กรกฎาคม", 8 => "สิงหาคม",
    9 => "กันยายน", 10 => "ตุลาคม", 11 => "พฤศจิกายน", 12 => "ธันวาคม"
];

$sql_curr_month_collected = "
    SELECT 
        SUM(
            CASE
                WHEN period_month_to = period_month_start THEN amount
                WHEN period_month_to > period_month_start THEN ROUND(amount / (period_month_to - period_month_start + 1), 2)
                ELSE 0
            END
        ) AS total_collected
    FROM ims_house_payment
    WHERE period_year = :year 
      AND payment_status = 'Y'
      AND :month BETWEEN period_month_start AND period_month_to
";
$query_curr_month_collected = $conn->prepare($sql_curr_month_collected);
$query_curr_month_collected->execute([
    ':year' => $curr_year,
    ':month' => $curr_month
]);
$result_curr_month_collected = $query_curr_month_collected->fetch(PDO::FETCH_OBJ);
$total_collected_curr_month = $result_curr_month_collected->total_collected ?? 0;

// ดึงจำนวนบ้านที่จัดเก็บค่าส่วนกลางของเดือนปัจจุบันได้
$sql_curr_month_houses = "
    SELECT COUNT(DISTINCT house_number) AS houses_count
    FROM ims_house_payment
    WHERE period_year = :year 
      AND payment_status = 'Y'
      AND :month BETWEEN period_month_start AND period_month_to
";
$query_curr_month_houses = $conn->prepare($sql_curr_month_houses);
$query_curr_month_houses->execute([
    ':year' => $curr_year,
    ':month' => $curr_month
]);
$result_curr_month_houses = $query_curr_month_houses->fetch(PDO::FETCH_OBJ);
$total_houses_curr_month = $result_curr_month_houses->houses_count ?? 0;

// ดึงข้อมูลบ้านที่ค้างชำระของเดือนปัจจุบัน
$sql_unpaid_houses = "
    SELECT m.house_number, m.alley, m.area_size, m.common_fee, h.contact_name, h.phone_number
    FROM ims_house_master m
    LEFT JOIN ims_house h ON m.house_number = h.house_number
    WHERE m.status = 'Y' 
      AND m.house_number LIKE '6%'
      AND m.house_number NOT IN (
          SELECT DISTINCT house_number 
          FROM ims_house_payment 
          WHERE period_year = :year 
            AND payment_status = 'Y' 
            AND :month BETWEEN period_month_start AND period_month_to
      )
    ORDER BY m.house_number ASC;
";
$query_unpaid_houses = $conn->prepare($sql_unpaid_houses);
$query_unpaid_houses->execute([
    ':year' => $curr_year,
    ':month' => $curr_month
]);
$unpaid_houses_list = $query_unpaid_houses->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">
    <?php include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('includes/Top-Bar.php'); ?>

            <div class="container-fluid"> <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            สรุปข้อมูลภาพรวม
                        </h6>
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
                                            <canvas id="totalHousePieChart" style="max-width: 100%; max-height: 400px;"></canvas>
                                            <p class="text-center mt-3">
                                                จำนวนบ้านทั้งหมด: <strong><?= number_format($all_total_house) ?> หลัง</strong><br>
                                                ลงทะเบียนแล้ว: <strong><?= number_format($total_house) ?> หลัง</strong><br>
                                                คิดเป็น <strong><?= number_format($percent_chk, 2) ?>%</strong><br>
                                                <strong>(ประมาณการยอดรวมค่าส่วนกลาง กรณีที่จัดเก็บได้ 621 หลัง : <?= number_format($total_common_fee, 2) ?> บาท ต่อเดือน)</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-6 col-md-12 mb-4">
                                    <div class="card border-left-danger shadow mb-3">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">LINE OA พฤกษา 33</div>
                                                    <div class="row align-items-center">
                                                        <div class="col-auto"><img src="img/icon/PS33-COMMONFEE-LINEOA.png" style="max-height: 60px;"></div>
                                                        <div class="col"><span class="text-gray-800 small">Scan เพื่อเข้าระบบค่าส่วนกลาง</span></div>
                                                    </div>
                                                </div>
                                                <div class="col-auto"><i class="fab fa-line fa-2x text-gray-300"></i></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-left-warning shadow mb-3">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">ยอดเงินค่าส่วนกลางเดือนปัจจุบันที่จัดเก็บได้</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_collected_curr_month, 2) ?> บาท</div>
                                            <div class="d-flex justify-content-between align-items-center flex-wrap mt-1">
                                                <div>
                                                    <span class="badge bg-light text-warning border mt-1"><i class="fas fa-calendar-alt"></i> ประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?></span>
                                                    <span class="badge bg-light text-success border mt-1 ml-1"><i class="fas fa-home"></i> ชำระแล้ว <?= number_format($total_houses_curr_month) ?> หลัง</span>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm mt-1" data-toggle="modal" data-target="#unpaidHousesModal">
                                                    <i class="fas fa-exclamation-triangle"></i> บ้านที่ค้างชำระประจำเดือน
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-left-primary shadow mb-3">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">บ้านที่ลงทะเบียนทั้งหมด</div>
                                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_house) ?> หลัง</div>
                                                </div>
                                                <div class="col-auto text-right">
                                                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">จำนวน Line User ที่ลงทะเบียน</div>
                                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_user) ?> User</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-left-info shadow mb-3">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">บ้านกลุ่ม 67 (ลงทะเบียนแล้ว)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($count_67) ?> / <?= number_format($count_67_house_master) ?></div>
                                            <div class="progress progress-sm mr-2 mt-2">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $percent_67 ?>%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border-left-success shadow mb-3">
                                        <div class="card-body">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">บ้านกลุ่ม 68 (ลงทะเบียนแล้ว)</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($count_68) ?> / <?= number_format($count_68_house_master) ?></div>
                                            <div class="progress progress-sm mr-2 mt-2">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent_68 ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- กราฟสรุปยอดรวมค่าส่วนกลางรายเดือน ปีปัจจุบัน -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header py-3 bg-light text-primary d-flex align-items-center">
                                            <h6 class="m-0 font-weight-bold"><i class="fas fa-chart-bar"></i> กราฟสรุปยอดรวมค่าส่วนกลางรายเดือน ปี พ.ศ. <?= date('Y') + 543 ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="chart-container" style="position: relative; height:45vh; width:100%">
                                                <canvas id="monthlyBarChart"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> </div> <?php include('includes/Footer.php'); ?>
    </div> </div> <?php include('includes/Modal-Logout.php'); ?>

    <!-- Modal แสดงบ้านที่ค้างชำระของเดือนปัจจุบัน -->
    <div class="modal fade" id="unpaidHousesModal" tabindex="-1" role="dialog" aria-labelledby="unpaidHousesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="unpaidHousesModalLabel"><i class="fas fa-exclamation-triangle"></i> บ้านที่ค้างชำระค่าส่วนกลางประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i> เลขที่บ้าน</span>
                                </div>
                                <input type="text" id="search_house_number" class="form-control" placeholder="ค้นหาเลขที่บ้าน...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="unpaidHousesTable" class="display nowrap table table-striped table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">ลำดับ</th>
                                    <th>บ้านเลขที่</th>
                                    <th>ซอย</th>
                                    <th>ชื่อผู้ติดต่อ</th>
                                    <th>เบอร์โทรศัพท์</th>
                                    <th class="text-right">ค่าส่วนกลาง/เดือน (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $idx = 1;
                                foreach ($unpaid_houses_list as $row_unpaid) {
                                    echo "<tr>";
                                    echo "<td class='text-center'>{$idx}</td>";
                                    echo "<td class='font-weight-bold'>".htmlspecialchars($row_unpaid['house_number'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row_unpaid['alley'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row_unpaid['contact_name'] ?? '')."</td>";
                                    echo "<td>".htmlspecialchars($row_unpaid['phone_number'] ?? '')."</td>";
                                    echo "<td class='text-right text-danger font-weight-bold'>".number_format($row_unpaid['common_fee'] ?? 0, 2)."</td>";
                                    echo "</tr>";
                                    $idx++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTables Buttons and Custom CSS Dependencies -->
    <link rel="stylesheet" href="css/spin_datatables_v2.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<!-- DataTables Buttons Scripts -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
    // Pie Chart Logic
    const ctxTotal = document.getElementById("totalHousePieChart").getContext('2d');
    new Chart(ctxTotal, {
        type: 'pie',
        data: {
            labels: ['ลงทะเบียนแล้ว', 'ยังไม่ลงทะเบียน'],
            datasets: [{
                data: [<?= $total_house ?>, <?= $unregistered_house ?>],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 1
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Collapse Icon Toggle
    $('#collapseCard').on('show.bs.collapse', function () {
        $('#collapseIcon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
    });
    $('#collapseCard').on('hide.bs.collapse', function () {
        $('#collapseIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    });

    // Monthly Bar Chart Logic
    const currentYear = new Date().getFullYear();
    const chartCanvas = document.getElementById('monthlyBarChart');
    const ctxBar = chartCanvas.getContext('2d');

    fetch('model/generate_graph_payment_monthly.php?year=' + currentYear)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching chart data:', data.error);
                return;
            }

            const labels = data.data.map(item => item.month_name);
            const amounts = data.data.map(item => item.total_amount);

            const gradient = ctxBar.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(78, 115, 223, 0.85)');
            gradient.addColorStop(1, 'rgba(102, 126, 234, 0.3)');
            
            const hoverGradient = ctxBar.createLinearGradient(0, 0, 0, 300);
            hoverGradient.addColorStop(0, 'rgba(78, 115, 223, 1)');
            hoverGradient.addColorStop(1, 'rgba(102, 126, 234, 0.6)');

            new Chart(ctxBar, {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'ยอดชำระต่อเดือน (บาท)',
                        data: amounts,
                        backgroundColor: gradient,
                        hoverBackgroundColor: hoverGradient,
                        borderColor: '#4e73df',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: 'bottom'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: function(value) {
                                return value > 0 ? value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '';
                            },
                            font: {
                                weight: 'bold',
                                size: 11
                            },
                            color: '#5a5c69'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'ยอดรวม (บาท)' }
                        },
                        x: {
                            title: { display: true, text: 'เดือน' }
                        }
                    }
                }
            });
        })
        .catch(error => console.error('Error fetching data:', error));

    // Initialize Unpaid Houses DataTable
    const unpaidTable = $('#unpaidHousesTable').DataTable({
        'paging': true,
        'lengthChange': true,
        'pageLength': 5,
        'lengthMenu': [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "ทั้งหมด"]],
        'language': {
            search: 'ค้นหาข้อมูล',
            lengthMenu: 'แสดง _MENU_ รายการ',
            info: 'หน้าที่ _PAGE_ จาก _PAGES_',
            infoEmpty: 'ไม่มีข้อมูล',
            zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
            infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
            paginate: {
                previous: 'ก่อนหน้า',
                last: 'สุดท้าย',
                next: 'ต่อไป'
            }
        },
        'order': [[2, 'asc']],
        'scrollY': '45vh',
        'scrollCollapse': true,
        'scrollX': true,
        'autoWidth': false,
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel"></i> Export to Excel',
                className: 'btn btn-success btn-sm',
                title: 'บ้านค้างชำระค่าส่วนกลางประจำเดือน <?= $thai_months[$curr_month] ?> <?= $curr_year + 543 ?>'
            }
        ]
    });

    // ค้นหาตามเลขที่บ้าน (Column Index 1)
    $('#search_house_number').on('keyup', function () {
        unpaidTable.column(1).search(this.value).draw();
    });

    // Re-adjust columns when the modal is shown to fix layout issues
    $('#unpaidHousesModal').on('shown.bs.modal', function () {
        unpaidTable.columns.adjust().draw();
    });
</script>
</body>
</html>