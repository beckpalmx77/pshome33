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
        <title>รายงานงบทดลอง (Trial Balance)</title>
        <style>
            #TableTrialBalance th, #TableTrialBalance td {
                white-space: nowrap;
            }
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
                        <h1 class="h4 mb-0 text-gray-800">รายงานงบทดลอง (Trial Balance)</h1>
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

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="table-responsive p-3">
                                        <table class="table table-bordered table-hover" id="TableTrialBalance">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>รหัสบัญชี</th>
                                                <th>ชื่อบัญชี</th>
                                                <th>หมวดบัญชี</th>
                                                <th class="text-right">Debit (Dr)</th>
                                                <th class="text-right">Credit (Cr)</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: #f8f9fc; font-weight: bold;">
                                                    <td colspan="3" class="text-right">รวมทั้งสิ้น:</td>
                                                    <td id="sum_dr" class="text-right text-primary">0.00</td>
                                                    <td id="sum_cr" class="text-right text-danger">0.00</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
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
            $.ajax({
                url: 'model/get_accounting_reports.php',
                method: 'POST',
                data: {
                    action: 'GET_TRIAL_BALANCE',
                    date_start: $('#date_start').val(),
                    date_end: $('#date_end').val()
                },
                dataType: 'json',
                success: function (response) {
                    let html = '';
                    response.aaData.forEach(row => {
                        html += `<tr>
                            <td>${row.acc_code}</td>
                            <td>${row.acc_name}</td>
                            <td>${row.acc_group}</td>
                            <td class="text-right">${row.dr}</td>
                            <td class="text-right">${row.cr}</td>
                        </tr>`;
                    });
                    $('#TableTrialBalance tbody').html(html);
                    $('#sum_dr').text(response.total_dr);
                    $('#sum_cr').text(response.total_cr);
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
