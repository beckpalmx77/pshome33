<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");

    // ดึงปีที่มีในระบบมาใส่ Dropdown
    $sql_year = "SELECT DISTINCT(period_year) AS period_year FROM ims_house_payment WHERE period_year > 0 ORDER BY period_year DESC";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายงานสรุปยอดชำระรายเดือน</title>
        <!--link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.bootstrap4.min.css"-->

        <link rel="stylesheet" href="css/spin_datatables.css"/>
        <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

        <style>
            .btn-process {
                min-width: 150px;
            }
            /* Style สำหรับช่องค้นหาในตาราง */
            .filter-input {
                width: 100%;
                padding: 4px;
                box-sizing: border-box;
                font-size: 0.85rem;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            /* ปรับให้ Header Filter ดูสะอาดตา */
            .filter-row th {
                background-color: #f8f9fc;
                padding: 5px;
            }

            /* --- เพิ่ม CSS จัดระเบียบปุ่ม Export และ ตัวเลือกจำนวนรายการ --- */
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dt-buttons {
                display: inline-block; /* ให้แสดงผลในบรรทัดเดียวกัน */
                vertical-align: middle;
                margin-right: 10px; /* เว้นระยะห่าง */
                margin-bottom: 10px;
            }
            /* ปรับตำแหน่งช่องค้นหา (Filter) ให้ชิดขวาเหมือนเดิม ถ้ามันตกบรรทัด */
            .dataTables_wrapper .dataTables_filter {
                float: right;
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
                    <input type="hidden" id="main_menu" name="main_menu" value="<?php echo urldecode($_GET['m']) ?>">
                    <input type="hidden" id="sub_menu" name="sub_menu" value="<?php echo urldecode($_GET['s']) ?>">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">ตัวเลือกการค้นหา</h6>
                                </div>
                                <div class="card-body">
                                    <form id="form_search">
                                        <div class="form-group row">
                                            <label for="select_year" class="col-sm-2 col-form-label">เลือกปี :</label>
                                            <div class="col-sm-3">
                                                <select name="select_year" id="select_year" class="form-control">
                                                    <?php foreach ($YearRecords as $row) { ?>
                                                        <option value="<?php echo $row["period_year"]; ?>">
                                                            <?php echo $row["period_year"]; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <button type="button" class="btn btn-primary btn-process" id="btnProcessAndView">
                                                    <i class="fas fa-sync-alt"></i> ประมวลผล & แสดงข้อมูล
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">ตารางข้อมูลการชำระเงิน</h6>
                                </div>
                                <div class="table-responsive p-3">
                                    <table class="table align-items-center table-flush table-hover" id="dataTablePayment">
                                        <thead class="thead-light">
                                        <tr>
                                            <th>บ้านเลขที่</th>
                                            <th>ซอย</th>
                                            <th>ม.ค.</th>
                                            <th>ก.พ.</th>
                                            <th>มี.ค.</th>
                                            <th>เม.ย.</th>
                                            <th>พ.ค.</th>
                                            <th>มิ.ย.</th>
                                            <th>ก.ค.</th>
                                            <th>ส.ค.</th>
                                            <th>ก.ย.</th>
                                            <th>ต.ค.</th>
                                            <th>พ.ย.</th>
                                            <th>ธ.ค.</th>
                                            <th>รวมปี</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th colspan="14" style="text-align:right">ยอดรวมทั้งหมด:</th>
                                            <th></th> </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php
                    include('includes/Modal-Logout.php');
                    include('includes/Footer.php');
                ?>

            </div>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <!--script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/ruang-admin.min.js"></script>

    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.bootstrap4.min.js"></script-->

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="js/util/calculate_datetime.js"></script>
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
        $(document).ready(function () {
            let table;

            function loadTable(year) {
                if ($.fn.DataTable.isDataTable('#dataTablePayment')) {
                    $('#dataTablePayment').DataTable().destroy();
                }

                $('#dataTablePayment thead tr.filter-row').remove();
                $('#dataTablePayment thead tr').clone(true).addClass('filter-row').appendTo('#dataTablePayment thead');

                $('#dataTablePayment thead tr:eq(1) th').each(function (i) {
                    var title = $(this).text();
                    if (i === 0 || i === 1) {
                        $(this).html('<input type="text" class="filter-input" placeholder="ค้นหา ' + title + '" />');
                        $('input', this).on('keyup change', function () {
                            if (table.column(i).search() !== this.value) {
                                table.column(i).search(this.value).draw();
                            }
                        });
                    } else {
                        $(this).html('');
                    }
                });

                table = $('#dataTablePayment').DataTable({
                    "processing": true,
                    "serverSide": false,
                    "orderCellsTop": true,
                    "fixedHeader": true,
                    "ajax": {
                        "url": "process/fetch_house_payment_data.php",
                        "type": "POST",
                        "data": { year: year },
                        "dataSrc": "data"
                    },
                    "columns": [
                        { "data": "house_number" },
                        { "data": "alley" },
                        { "data": "amount_period_month_1", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_2", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_3", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_4", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_5", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_6", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_7", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_8", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_9", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_10", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_11", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "amount_period_month_12", render: $.fn.dataTable.render.number(',', '.', 2) },
                        { "data": "total", render: $.fn.dataTable.render.number(',', '.', 2) }
                    ],
                    "lengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "ทั้งหมด"]],

                    // --- แก้ไขตรงนี้ครับ ---
                    // เปลี่ยนจาก 'Blfrtip' เป็น 'lBfrtip'
                    // (l = Length ขึ้นก่อน, B = Buttons ตามหลัง)
                    "dom": 'lBfrtip',

                    "buttons": [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fas fa-file-excel"></i> Export Excel',
                            titleAttr: 'Export to Excel',
                            className: 'btn btn-success',
                            title: 'รายงานสรุปยอดชำระรายเดือน ประจำปี ' + year
                        }
                    ],
                    "footerCallback": function (row, data, start, end, display) {
                        let api = this.api();
                        let intVal = function (i) {
                            return typeof i === 'string' ? i.replace(/[\$,]/g, '') * 1 : typeof i === 'number' ? i : 0;
                        };
                        let grandTotal = api.column(14, { page: 'current' }).data().reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                        $(api.column(14).footer()).html(grandTotal.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
                    },
                    "language": {
                        "lengthMenu": "แสดง _MENU_ รายการ",
                        "emptyTable": "ไม่พบข้อมูล หรือยังไม่ได้กดประมวลผล",
                        "processing": "กำลังโหลดข้อมูล...",
                        "zeroRecords": "ไม่พบข้อมูลที่ค้นหา",
                        "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                        "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                        "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                        "search": "ค้นหาภาพรวม:",
                        "paginate": {
                            "first": "หน้าแรก",
                            "last": "หน้าสุดท้าย",
                            "next": "ถัดไป",
                            "previous": "ก่อนหน้า"
                        }
                    }
                });
            }

            function processAndDisplay(selectedYear) {
                let btn = $('#btnProcessAndView');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังประมวลผล...');

                $.ajax({
                    url: 'process/process_house_payment_year.php',
                    type: 'POST',
                    data: { year: selectedYear },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            loadTable(selectedYear);
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('เชื่อมต่อ Server ไม่ได้ (กรุณาดู Console)');
                    },
                    complete: function () {
                        btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> ประมวลผล & แสดงข้อมูล');
                    }
                });
            }

            let initialYear = $('#select_year').val();
            if(initialYear) {
                processAndDisplay(initialYear);
            }

            $('#select_year').change(function() {
                let newYear = $(this).val();
                processAndDisplay(newYear);
            });

            $('#btnProcessAndView').click(function (e) {
                e.preventDefault();
                let selectedYear = $('#select_year').val();
                processAndDisplay(selectedYear);
            });

        });
    </script>
    </body>
    </html>
<?php } ?>