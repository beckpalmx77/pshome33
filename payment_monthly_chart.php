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
                            <div class="card mb-4 search-card">
                                <div class="card-header py-3 search-card-header">
                                    <h6><i class="fas fa-chart-bar"></i> ตัวเลือกรายงานกราฟรายเดือน</h6>
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="form-row align-items-end mb-4">

                                            <div class="col-auto">
                                                <label for="selectYear" class="control-label font-weight-bold">ปี พ.ศ.</label>
                                                <select id="selectYear" class="form-control" style="min-width: 150px;">
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
                                                        class='btn btn-success'>
                                                    <i class="fas fa-sync-alt"></i> แสดงกราฟ (Reload Data)
                                                </button>
                                            </div>
                                        </div>

                                        <hr/>

                                        <div class="chart-wrapper">
                                            <h5 id="chartTitle" class="text-center mb-4 text-gray-800 font-weight-bold"></h5>
                                            <div class="chart-container" style="position: relative; height:50vh; width:100%">
                                                <canvas id="monthlyBarChart"></canvas>
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

                    // สร้าง vertical gradient สำหรับกราฟแท่ง
                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, 'rgba(78, 115, 223, 0.85)'); // Primary Blue
                    gradient.addColorStop(1, 'rgba(102, 126, 234, 0.3)');  // Indigo-Purple
                    
                    const hoverGradient = ctx.createLinearGradient(0, 0, 0, 300);
                    hoverGradient.addColorStop(0, 'rgba(78, 115, 223, 1)');
                    hoverGradient.addColorStop(1, 'rgba(102, 126, 234, 0.6)');

                    // --- สร้างกราฟแท่ง ---
                    myBarChart = new Chart(ctx, {
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
                                borderRadius: 6, // ทำขอบด้านบนให้มน
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
        /* Card Container */
        .search-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            background: #ffffff;
            overflow: hidden;
        }
        .search-card-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
            border: none;
            padding: 12px 20px !important;
        }
        .search-card-header h6 {
            color: #ffffff !important;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 0.6rem 1rem;
            height: 45px;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .btn-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%) !important;
            border: none;
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            height: 45px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-success:hover {
            background: linear-gradient(135deg, #13855c 0%, #0e6243 100%) !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(28, 200, 138, 0.2);
            color: white;
        }
        .chart-wrapper {
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            border: 1px solid #eaecf4;
            margin-top: 20px;
        }
    </style>


    </body>
    </html>

<?php } ?>