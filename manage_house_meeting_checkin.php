<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include("config/connect_db.php");

    $current_year = date('Y');
    $YearRecords = [];
    for ($y = $current_year + 1; $y >= $current_year - 1; $y--) {
        $YearRecords[] = $y;
    }
    $url_year = isset($_GET['year']) ? $_GET['year'] : '';
    $url_date = isset($_GET['date']) ? $_GET['date'] : '';
    $default_date = !empty($url_date) ? $url_date : date('d/m/Y');
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css"/>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <?php
                    $sub_menu_name = isset($_GET['s']) ? urldecode($_GET['s']) : 'ตรวจสอบข้อมูลลงทะเบียน';
                    $main_menu_name = isset($_GET['m']) ? urldecode($_GET['m']) : 'การประชุมหมู่บ้าน';
                    $dash_page = isset($_SESSION['dashboard_page']) ? $_SESSION['dashboard_page'] : 'dashboard.php';
                    ?>
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $sub_menu_name; ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $dash_page; ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo $main_menu_name; ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo $sub_menu_name; ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="row mb-3 align-items-end">
                                            <div class="col-md-2">
                                                <div class="form-group mb-0">
                                                    <label for="filter_year" class="font-weight-bold small mb-1">ปีการประชุม:</label>
                                                    <select class="form-control form-control-sm" id="filter_year">
                                                        <option value="">-- แสดงทั้งหมด --</option>
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
                                            <div class="col-md-3">
                                                <div class="form-group mb-0">
                                                    <label for="filter_date" class="font-weight-bold small mb-1">วันที่ประชุม:</label>
                                                    <div class="input-group input-group-sm date" id="datepicker_filter">
                                                        <input type="text" class="form-control" id="filter_date" value="<?php echo $default_date; ?>">
                                                        <div class="input-group-append">
                                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="d-flex align-items-center justify-content-end" style="gap:6px;">
                                                    <a href="manage_meeting_register_summary.php?m=<?php echo urlencode($main_menu_name); ?>&s=<?php echo urlencode('รายละเอียดการประชุมหมู่บ้าน'); ?>" class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-arrow-left"></i> รายการประชุม
                                                    </a>
                                                    <a href="manage_house_meeting_record.php?m=<?php echo urlencode($main_menu_name); ?>&s=<?php echo urlencode('จัดการข้อมูลการประชุม'); ?>" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-users"></i> จัดการข้อมูล
                                                    </a>
                                                    <div id="buttons_container" style="white-space:nowrap;"></div>
                                                    <button type="button" id="btnSearch" class="btn btn-info btn-sm" data-toggle="tooltip" title="ค้นหาข้อมูล">
                                                        <i class="fas fa-search"></i> ค้นหา
                                                    </button>
                                                    <button type="button" id="btnReload" class="btn btn-outline-success btn-sm" data-toggle="tooltip" title="Reload Data">
                                                        <i class="fa fa-refresh"></i> รีโหลด
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="url_year" value="<?php echo $url_year; ?>">
                                        <input type="hidden" id="url_date" value="<?php echo $url_date; ?>">

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
                                                    <th>วันที่ลงทะเบียน</th>
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
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>

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
            $('#datepicker_filter').datepicker({
                format: 'dd/mm/yyyy',
                todayBtn: 'linked',
                todayHighlight: true,
                autoclose: true,
                language: 'th'
            });

            // Auto-load จาก URL params
            var urlYear = $('#url_year').val();
            var urlDate = $('#url_date').val();
            if (urlYear && urlDate) {
                $('#filter_year').val(urlYear);
                $('#filter_date').val(urlDate);
            }

            let dataRecords = $('#TableRecordList').DataTable({
                'dom': 'Blfrtip',
                'lengthMenu': [[10, 20, 50, 100, -1], [10, 20, 50, 100, "แสดงทั้งหมด"]],
                'buttons': [
                    {
                        text: '<i class="fa fa-file-excel"></i> Export Excel',
                        className: 'btn btn-success btn-sm',
                        action: function ( e, dt, node, config ) {
                            let year = $('#filter_year').val();
                            let date = $('#filter_date').val();
                            window.location.href = 'export_process/export_meeting_excel_process.php?meeting_year=' + year + '&meeting_date=' + encodeURIComponent(date);
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
                        d.meeting_date = $('#filter_date').val();
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

            $('#btnSearch').on('click', function () {
                dataRecords.ajax.reload();
            });

            $('#btnReload').on('click', function () {
                dataRecords.ajax.reload();
            });

            $('#filter_year, #filter_date').on('change', function() {
                dataRecords.ajax.reload();
            });

            // Auto-reload ถ้ามาจาก URL
            if (urlYear && urlDate) {
                setTimeout(function(){
                    $('#btnSearch').trigger('click');
                }, 500);
            }

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