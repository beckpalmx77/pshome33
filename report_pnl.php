<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายงานงบกำไรขาดทุน (Profit & Loss)</title>
        <style>
            .section-header { background-color: #f8f9fc; font-weight: bold; }
            .total-row { font-weight: bold; border-top: 2px solid #e3e6f0; }
            .net-profit { font-size: 1.25rem; font-weight: bold; color: #4e73df; }
            #TablePNL td { white-space: nowrap; }
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
                        <h1 class="h4 mb-0 text-gray-800">รายงานงบกำไรขาดทุน (Profit & Loss)</h1>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>วันที่เริ่มต้น :</label>
                                            <input type="date" id="date_start" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label>วันที่สิ้นสุด :</label>
                                            <input type="date" id="date_end" class="form-control">
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary w-100" id="btnSearch">ค้นหา <i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card mb-4">
                                <div class="card-header py-3 text-center">
                                    <h5 class="m-0 font-weight-bold text-primary">งบกำไรขาดทุน</h5>
                                    <p class="mb-0" id="period_text"></p>
                                </div>
                                <div class="card-body">
                                    <table class="table" id="TablePNL">
                                        <tbody>
                                            <!-- Revenue Section -->
                                            <tr class="section-header"><td colspan="2">รายได้ (Revenues)</td></tr>
                                            <tr id="revenue_rows"><td colspan="2" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
                                            <tr class="total-row">
                                                <td>รวมรายได้ทั้งสิ้น</td>
                                                <td class="text-right" id="total_revenue">0.00</td>
                                            </tr>

                                            <!-- Expense Section -->
                                            <tr class="section-header"><td colspan="2">ค่าใช้จ่าย (Expenses)</td></tr>
                                            <tr id="expense_rows"><td colspan="2" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
                                            <tr class="total-row">
                                                <td>รวมค่าใช้จ่ายทั้งสิ้น</td>
                                                <td class="text-right" id="total_expense">0.00</td>
                                            </tr>

                                            <!-- Net Profit Section -->
                                            <tr style="border-top: 3px double #e3e6f0;">
                                                <td class="net-profit">กำไร (ขาดทุน) สุทธิ</td>
                                                <td class="text-right net-profit" id="net_profit">0.00</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php include('includes/Modal-Logout.php');
                include('includes/Footer.php'); ?>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        function loadData() {
            let ds = $('#date_start').val();
            let de = $('#date_end').val();
            $('#period_text').text((ds && de) ? `ประจำวันที่ ${ds} ถึง ${de}` : 'แสดงข้อมูลทั้งหมด');

            $.ajax({
                url: 'model/get_accounting_reports.php',
                method: 'POST',
                data: {
                    action: 'GET_PNL',
                    date_start: ds,
                    date_end: de
                },
                dataType: 'json',
                success: function (response) {
                    // Revenue rows
                    let revHtml = '';
                    if (response.revenue.length > 0) {
                        response.revenue.forEach(r => {
                            revHtml += `<tr><td>${r.name}</td><td class="text-right">${r.amount}</td></tr>`;
                        });
                    } else {
                        revHtml = '<tr><td colspan="2" class="text-center text-muted">ไม่มีข้อมูลรายได้</td></tr>';
                    }
                    $('#revenue_rows').replaceWith(revHtml);

                    // Expense rows
                    let expHtml = '';
                    if (response.expense.length > 0) {
                        response.expense.forEach(e => {
                            expHtml += `<tr><td>${e.name}</td><td class="text-right">${e.amount}</td></tr>`;
                        });
                    } else {
                        expHtml = '<tr><td colspan="2" class="text-center text-muted">ไม่มีข้อมูลค่าใช้จ่าย</td></tr>';
                    }
                    $('#expense_rows').replaceWith(expHtml);

                    $('#total_revenue').text(response.total_revenue);
                    $('#total_expense').text(response.total_expense);
                    $('#net_profit').text(response.net_profit);
                }
            });
        }

        $(document).ready(function () {
            // Set default date range: 1st of current month to today (Local Time)
            let now = new Date();
            let year = now.getFullYear();
            let month = String(now.getMonth() + 1).padStart(2, '0');
            let day = String(now.getDate()).padStart(2, '0');
            
            let firstDay = `${year}-${month}-01`;
            let today = `${year}-${month}-${day}`;

            $('#date_start').val(firstDay);
            $('#date_end').val(today);

            loadData();
            $('#btnSearch').click(loadData);
        });
    </script>
    </body>
    </html>
<?php } ?>
