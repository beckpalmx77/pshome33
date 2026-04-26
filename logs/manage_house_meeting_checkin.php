<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include("config/connect_db.php");

    $current_year = date('Y');
    $YearRecords = [];
    // สร้าง Dropdown ปี ย้อนหลัง-ล่วงหน้า
    for ($y = $current_year + 1; $y >= $current_year - 1; $y--) {
        $YearRecords[] = $y;
    }
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css"/>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="row mb-3 align-items-end">
                                            <div class="col-md-4">
                                                <div class="form-group mb-0">
                                                    <label for="filter_year" class="font-weight-bold">เลือกปีการประชุม (Year):</label>
                                                    <select class="form-control" id="filter_year">
                                                        <option value="">-- แสดงทั้งหมด (All Years) --</option>
                                                        <?php foreach ($YearRecords as $year) {
                                                            $selected = ($year == $current_year) ? 'selected' : '';
                                                            ?>
                                                            <option value="<?php echo $year; ?>" <?php echo $selected; ?>>
                                                                <?php echo $year; ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-8 text-right">
                                                <div id="buttons_container"></div>
                                                <button type="button" id="btnReload" class="btn btn-outline-success btn-xs" data-toggle="tooltip" title="Reload Data">
                                                    <i class="fa fa-refresh"></i> Reload
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <table id='TableRecordList' class='display dataTable table table-bordered table-striped' style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ชื่อ-สกุล</th>
                                                    <th>เบอร์โทรศัพท์</th>
                                                    <th>วันที่ประชุม</th>
                                                    <th>รายละเอียด</th>
                                                    <th>จุดลงทะเบียน</th>
                                                    <th>จัดการ</th>
                                                </tr>
                                                </thead>
                                            </table>
                                            <div id="result"></div>
                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">แก้ไขข้อมูลผู้ลงทะเบียน</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">

                                                            <div class="form-group">
                                                                <label for="meeting_detail_show" class="control-label">รายละเอียดการประชุม</label>
                                                                <input type="text" class="form-control" id="meeting_detail_show" readonly>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="meeting_date_show" class="control-label">วันที่</label>
                                                                <input type="text" class="form-control" id="meeting_date_show" readonly>
                                                            </div>

                                                            <hr>

                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="house_number" class="control-label font-weight-bold">บ้านเลขที่</label>
                                                                        <input type="text" class="form-control" id="house_number" name="house_number" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="phone_number" class="control-label font-weight-bold">เบอร์โทรศัพท์</label>
                                                                        <input type="text" class="form-control" id="phone_number" name="phone_number">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="fullname" class="control-label font-weight-bold text-primary">
                                                                    ชื่อ-นามสกุล (ผู้ลงทะเบียน)
                                                                </label>
                                                                <input type="text" class="form-control" id="fullname" name="fullname" required>
                                                            </div>

                                                            <div class="form-group">
                                                                <label class="control-label text-muted small">
                                                                    * หมายเหตุ: ข้อมูล GPS และจุดลงทะเบียน ไม่สามารถแก้ไขได้
                                                                </label>
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value="UPDATE_ATTENDANCE"/>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-save"></i>
                                                                <input type="submit" name="save" id="save" class="btn btn-primary" value="บันทึกการแก้ไข"/>
                                                            </span>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

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

    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>

    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>

    <style>
        .icon-input-btn { display: inline-block; position: relative; }
        .icon-input-btn input[type="submit"] { padding-left: 2em; }
        .icon-input-btn .fa { display: inline-block; position: absolute; left: 0.65em; top: 30%; }
        .dt-buttons .dt-button {
            background-color: #4e73df;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 15px;
            margin-right: 5px;
        }
        .dt-buttons .dt-button:hover { background-color: #2e59d9; }
    </style>

    <script>
        $(document).ready(function () {

            let dataRecords = $('#TableRecordList').DataTable({
                'dom': 'Blfrtip',
                'lengthMenu': [[10, 20, 50, 100, -1], [10, 20, 50, 100, "แสดงทั้งหมด"]],
                'buttons': [
                    {
                        text: '<i class="fa fa-file-excel"></i> Export Excel',
                        className: 'btn btn-success btn-sm',
                        action: function ( e, dt, node, config ) {
                            let year = $('#filter_year').val();
                            window.location.href = 'export_process/export_meeting_excel_process.php?meeting_year=' + year;
                        }
                    }
                ],
                'language': {
                    search: 'ค้นหา:', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่พบข้อมูล",
                    paginate: { previous: 'ก่อนหน้า', next: 'ถัดไป' }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_house_meeting_checkin_process.php',
                    'data': function(d) {
                        d.action = "GET_MEETING_LIST";
                        d.meeting_year = $('#filter_year').val();
                    }
                },
                'columns': [
                    {data: 'house_number'},
                    {data: 'fullname'},
                    {data: 'phone_number'},
                    {data: 'meeting_date'},
                    {data: 'meeting_detail'},
                    {
                        data: 'checkin_point',
                        render: function (data, type, row) {
                            // แสดงจุดลงทะเบียน + Link Google Maps ถ้ามีพิกัด
                            let txt = data;
                            if(row.lat_addr && row.long_addr) {
                                txt += ' <a href="https://maps.google.com/?q='+row.lat_addr+','+row.long_addr+'" target="_blank"><i class="fa fa-map-marker" aria-hidden="true"></i>\n</a>';
                            }
                            return txt;
                        }
                    },
                    {data: 'created_at'},
/*
                    {
                        data: 'id',
                        render: function (data, type, row) {
                            // --- ปิดการใช้งานปุ่ม Edit โดยการ comment ---
                            return '<button type="button" name="update" id="' + data + '" class="btn btn-warning btn-sm update" title="แก้ไขข้อมูล"><i class="fa fa-edit"></i> Edit</button>';

                            return ''; // คืนค่าว่างเพื่อไม่ให้แสดงอะไร
                        },
                        className: "text-center"
                    }

 */

                ],
                'order': [[ 6, 'desc' ]] // เรียงตาม ID ล่าสุดก่อน
            });

            $('#btnReload').on('click', function () {
                $('#TableRecordList').DataTable().ajax.reload();
            });

            $('#filter_year').change(function() {
                dataRecords.ajax.reload();
            });

            dataRecords.buttons().container().appendTo('#buttons_container');

            // Submit แก้ไขข้อมูล
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_house_meeting_checkin_process.php',
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    success: function (data) {
                        if (data.status === 'success') {
                            alertify.success(data.message);
                            $('#recordModal').modal('hide');
                            dataRecords.ajax.reload();
                        } else {
                            alertify.error(data.message);
                        }
                        $('#save').attr('disabled', false);
                    },
                    error: function() { alertify.error("เชื่อมต่อผิดพลาด"); $('#save').attr('disabled', false); }
                })
            });

            // กดปุ่ม Edit
            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                $.ajax({
                    type: "POST",
                    url: 'model/manage_house_meeting_checkin_process.php',
                    dataType: "json",
                    data: {action: "GET_DATA", id: id},
                    success: function (response) {
                        let data = response[0] || response;
                        $('#recordModal').modal('show');
                        $('#id').val(data.id);
                        $('#house_number').val(data.house_number);
                        $('#fullname').val(data.fullname);
                        $('#phone_number').val(data.phone_number);
                        $('#meeting_date_show').val(data.meeting_date);
                        $('#meeting_detail_show').val(data.meeting_detail);
                    },
                    error: function () { alertify.error("ดึงข้อมูลไม่ได้"); }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>