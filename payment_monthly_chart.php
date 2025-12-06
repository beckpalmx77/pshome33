<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
    header("Location: index.php");
} else {
    // กำหนดปีปัจจุบันในรูปแบบ ค.ศ. (ใช้สำหรับค่าเริ่มต้นของ select box)
    $current_year_en = date('Y');
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
                        <h1 class="h3 mb-0 text-gray-800">รายงานสรุปยอดรวมค่าส่วนกลางรายเดือน</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item">รายงาน</li>
                            <li class="breadcrumb-item active" aria-current="page">กราฟรายเดือน</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">เลือกปีที่ต้องการดูรายงาน</h6>
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="row mb-4 g-3 align-items-end">

                                            <div class="col-auto">
                                                <label for="selectYear" class="control-label"><b>ปี พ.ศ.</b></label>
                                                <select id="selectYear" class="form-control" style="min-width: 120px;">
                                                    <?php
                                                    for ($y = $current_year_en + 543; $y >= $current_year_en + 543 - 5; $y--) {
                                                        $selected = ($y - 543 == $current_year_en) ? 'selected' : '';
                                                        echo "<option value='".($y - 543)."' $selected>$y</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-auto">
                                                <button type='button' name='btnLoadChart' id='btnLoadChart'
                                                        class='btn btn-success'>แสดงกราฟ (Reload Data)
                                                    <i class="fa fa-chart-bar"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <hr/>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4 id="chartTitle" class="text-center mb-4"></h4>
                                                <div class="chart-container" style="position: relative; height:40vh; width:100%">
                                                    <canvas id="monthlyBarChart"></canvas>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="result"></div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <?php include('includes/Modal-Logout.php'); ?>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script>
        let myBarChart = null; // ตัวแปรสำหรับเก็บ Object กราฟ

        /**
         * ฟังก์ชันดึงข้อมูลจาก Backend และสร้าง/อัพเดตกราฟ
         */
        function loadChart() {
            const selectedYear = document.getElementById('selectYear').value;
            const chartCanvas = document.getElementById('monthlyBarChart');
            const chartTitle = document.getElementById('chartTitle');
            const ctx = chartCanvas.getContext('2d');

            // ล้างกราฟเก่าออกก่อน (ถ้ามี)
            if (myBarChart) {
                myBarChart.destroy();
            }

            // แสดงสถานะกำลังโหลด
            chartTitle.textContent = 'กำลังโหลดข้อมูล...';

            // ดึงข้อมูลจาก PHP-Backend
            // *** PATH ที่ถูกต้อง: 'model/generate_graph_payment_monthly.php' ***
            fetch('model/generate_graph_payment_monthly.php?year=' + selectedYear)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert('Error from backend: ' + data.error);
                        chartTitle.textContent = 'ไม่พบข้อมูล หรือเกิดข้อผิดพลาดในการดึงข้อมูล';
                        return;
                    }

                    // --- เตรียมข้อมูลสำหรับ Chart.js ---
                    const labels = data.data.map(item => item.month_name); // ชื่อเดือน (ม.ค. 2568)
                    const amounts = data.data.map(item => item.total_amount); // ยอดรวม

                    chartTitle.textContent = data.report_title;

                    // --- สร้างกราฟแท่ง ---
                    myBarChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'ยอดชำระต่อเดือน (บาท)',
                                data: amounts,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
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
                .catch(error => {
                    console.error('Error fetching data:', error);
                    chartTitle.textContent = 'ไม่สามารถดึงข้อมูลกราฟได้ (โปรดตรวจสอบ Console)';
                });
        }

        // ผูก Event Listener
        $(document).ready(function() {
            // 1. โหลดกราฟครั้งแรกเมื่อเข้าหน้า
            loadChart();

            // 2. เมื่อคลิกปุ่มแสดงกราฟ
            $("#btnLoadChart").click(loadChart);

            // 3. เมื่อค่าใน dropdown ปีมีการเปลี่ยนแปลง (โหลดอัตโนมัติ)
            $("#selectYear").change(loadChart);
        });
    </script>

    <style>
        .icon-input-btn { display: inline-block; position: relative; }
        .icon-input-btn input[type="submit"] { padding-left: 2em; }
        .icon-input-btn .fa { display: inline-block; position: absolute; left: 0.65em; top: 30%; }
    </style>


    </body>
    </html>

<?php } ?>