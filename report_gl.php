<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");

    // Fetch account codes for filter
    $sql_acc = "SELECT * FROM ims_chart_of_accounts ORDER BY acc_code ASC";
    $stmt_acc = $conn->prepare($sql_acc);
    $stmt_acc->execute();
    $AccountRecords = $stmt_acc->fetchAll();

    $sql_year = "SELECT DISTINCT(YEAR(gl_date)) AS period_year FROM ims_gl_header ORDER BY period_year DESC";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายงานสมุดรายวันทั่วไป (General Ledger)</title>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <style>
            #TableGL th, #TableGL td {
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
                        <h1 class="h4 mb-0 text-gray-800">รายงานสมุดรายวันทั่วไป (GL)</h1>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">ตัวกรองข้อมูล</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label>วันที่เริ่มต้น :</label>
                                            <input type="date" id="date_start" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label>วันที่สิ้นสุด :</label>
                                            <input type="date" id="date_end" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label>บัญชี :</label>
                                            <select id="acc_code" class="form-control">
                                                <option value="">ทั้งหมด</option>
                                                <?php foreach ($AccountRecords as $row) { ?>
                                                    <option value="<?php echo $row["acc_code"]; ?>">
                                                        <?php echo $row["acc_code"] . " - " . $row["acc_name"]; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
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
                                        <table class="table align-items-center table-flush table-hover" id="TableGL">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>วันที่</th>
                                                <th>เลขที่เอกสาร</th>
                                                <th>รายการ</th>
                                                <th>รหัสบัญชี</th>
                                                <th>ชื่อบัญชี</th>
                                                <th class="text-right">Debit (Dr)</th>
                                                <th class="text-right">Credit (Cr)</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                            <tfoot>
                                                <tr style="background-color: #f8f9fc; font-weight: bold;">
                                                    <td colspan="5" class="text-right">รวมทั้งสิ้น:</td>
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
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        $(document).ready(function () {
            let table = $('#TableGL').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "model/get_gl_report.php",
                    "type": "POST",
                    "data": function (d) {
                        d.date_start = $('#date_start').val();
                        d.date_end = $('#date_end').val();
                        d.acc_code = $('#acc_code').val();
                    },
                    "dataSrc": function(json) {
                        $('#sum_dr').text(json.total_dr);
                        $('#sum_cr').text(json.total_cr);
                        return json.aaData;
                    }
                },
                "columns": [
                    {"data": "gl_date"},
                    {"data": "doc_no"},
                    {"data": "description"},
                    {"data": "acc_code"},
                    {"data": "acc_name"},
                    {"data": "dr_amount", "className": "text-right"},
                    {"data": "cr_amount", "className": "text-right"}
                ],
                "order": [[0, "desc"]],
                "pageLength": 50,
                "language": {
                    "search": "ค้นหารวดเร็ว:",
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    "paginate": {
                        "previous": "ก่อนหน้า",
                        "next": "ถัดไป"
                    }
                }
            });

            $('#btnSearch').click(function() {
                table.draw();
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
