<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");

    $month_num = ltrim(date('m'), '0');
    $sql_month = "SELECT * FROM ims_month ORDER BY month ASC";
    $stmt_month = $conn->prepare($sql_month);
    $stmt_month->execute();
    $MonthRecords = $stmt_month->fetchAll();

    $sql_year = "SELECT DISTINCT(exp_year) AS period_year FROM v_ims_expenses WHERE exp_year >= 2024 
                 UNION 
                 SELECT DISTINCT(period_year) AS period_year FROM ims_house_payment WHERE period_year >= 2024
                 ORDER BY period_year DESC";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายงานงบกระแสเงินสด (Cash Flow Statement)</title>
        <style>
            .month-checkbox {
                margin-right: 15px;
                margin-bottom: 5px;
                display: inline-block;
            }
            .month-checkbox input {
                margin-right: 5px;
            }
            .text-right {
                text-align: right;
            }
            .font-weight-bold {
                font-weight: bold;
            }
            .text-success { color: #28a745; }
            .text-danger { color: #dc3545; }
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
                        <h1 class="h4 mb-0 text-gray-800">รายงานงบกระแสเงินสด (Cash Flow)</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?? '#' ?>">Home</a></li>
                            <li class="breadcrumb-item">รายงาน</li>
                            <li class="breadcrumb-item active">งบกระแสเงินสด</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form id="form_data" method="post" action="">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <label>เลือกเดือน :</label><br>
                                                <div>
                                                    <label class="month-checkbox">
                                                        <input type="checkbox" name="months[]" value="all" id="check_all"> ทั้งหมด
                                                    </label>
                                                    <?php foreach ($MonthRecords as $row) {
                                                        $checked = ((int)$row["month_id"] == (int)$month_num) ? 'checked' : '';
                                                        ?>
                                                        <label class="month-checkbox">
                                                            <input type="checkbox" name="months[]" value="<?php echo $row["month_id"]; ?>" class="month-checkbox-item" <?php echo $checked; ?>>
                                                            <?php echo $row["month_name"]; ?>
                                                        </label>
                                                    <?php } ?>
                                                </div>
                                                <br>
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <label for="year">เลือกปี :</label>
                                                        <select name="year" id="year" class="form-control" required>
                                                            <?php foreach ($YearRecords as $row) { ?>
                                                                <option value="<?php echo $row["period_year"]; ?>">
                                                                    <?php echo $row["period_year"]; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <br>
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <button type="button" class="btn btn-primary" id="btnSearch">
                                                            แสดงรายงาน <i class="fa fa-search"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-success" id="btnExportExcel">
                                                            Export Excel <i class="fa fa-file-excel"></i>
                                                        </button>
                                                        <!--button type="button" class="btn btn-danger" id="btnPrintPdf">
                                                            Print PDF <i class="fa fa-file-pdf"></i>
                                                        </button-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Table -->
                    <div class="row d-none" id="report_section">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">ตารางสรุปงบกระแสเงินสด</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="TableCashFlow">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>เดือน/ปี</th>
                                                    <th>รายรับ (Inflow)</th>
                                                    <th>รายจ่าย (Outflow)</th>
                                                    <th>คงเหลือ (Net Cash Flow)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cash_flow_data">
                                            </tbody>
                                            <tfoot id="cash_flow_footer">
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php include('includes/Modal-Logout.php'); include('includes/Footer.php'); ?>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkAll = document.getElementById('check_all');
            const monthCheckboxes = document.querySelectorAll('.month-checkbox-item');

            checkAll.addEventListener('change', function () {
                monthCheckboxes.forEach(cb => cb.checked = checkAll.checked);
            });

            monthCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    if (!this.checked) checkAll.checked = false;
                    else {
                        const allChecked = Array.from(monthCheckboxes).every(cb => cb.checked);
                        checkAll.checked = allChecked;
                    }
                });
            });

            const allCheckedInit = Array.from(monthCheckboxes).every(cb => cb.checked);
            checkAll.checked = allCheckedInit;

            const btnSearch = document.getElementById('btnSearch');
            const btnExportExcel = document.getElementById('btnExportExcel');
            const reportSection = document.getElementById('report_section');
            const form = document.getElementById('form_data');

            btnSearch.addEventListener('click', function() {
                const selectedMonths = Array.from(monthCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
                const year = document.getElementById('year').value;

                if (selectedMonths.length === 0 && !checkAll.checked) {
                    alert('กรุณาเลือกอย่างน้อย 1 เดือน');
                    return;
                }

                $.ajax({
                    url: 'model/manage_cash_flow_process.php',
                    method: 'POST',
                    data: {
                        action: 'GET_CASH_FLOW',
                        months: selectedMonths,
                        year: year
                    },
                    dataType: 'json',
                    success: function(response) {
                        let html = '';
                        let totalInflow = 0;
                        let totalOutflow = 0;
                        let totalNet = 0;

                        response.forEach(row => {
                            const net = parseFloat(row.inflow) - parseFloat(row.outflow);
                            totalInflow += parseFloat(row.inflow);
                            totalOutflow += parseFloat(row.outflow);
                            totalNet += net;

                            html += `<tr>
                                <td>${row.month_name} ${row.year}</td>
                                <td class="text-right text-success">${numberWithCommas(row.inflow)}</td>
                                <td class="text-right text-danger">${numberWithCommas(row.outflow)}</td>
                                <td class="text-right font-weight-bold ${net >= 0 ? 'text-success' : 'text-danger'}">${numberWithCommas(net)}</td>
                            </tr>`;
                        });

                        $('#cash_flow_data').html(html);

                        let footerHtml = `<tr class="bg-light">
                            <td class="font-weight-bold">รวมทั้งสิ้น</td>
                            <td class="text-right font-weight-bold text-success">${numberWithCommas(totalInflow)}</td>
                            <td class="text-right font-weight-bold text-danger">${numberWithCommas(totalOutflow)}</td>
                            <td class="text-right font-weight-bold ${totalNet >= 0 ? 'text-success' : 'text-danger'}">${numberWithCommas(totalNet)}</td>
                        </tr>`;
                        $('#cash_flow_footer').html(footerHtml);

                        reportSection.classList.remove('d-none');
                    }
                });
            });

            btnExportExcel.addEventListener('click', function() {
                const selectedMonths = Array.from(monthCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
                if (selectedMonths.length === 0 && !checkAll.checked) {
                    alert('กรุณาเลือกอย่างน้อย 1 เดือน');
                    return;
                }
                form.action = 'export_process/export_cash_flow_report_process.php';
                form.submit();
            });

            function numberWithCommas(x) {
                return parseFloat(x).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        });
    </script>
    </body>
    </html>
<?php } ?>
