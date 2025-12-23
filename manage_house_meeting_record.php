<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include("config/connect_db.php");

    $current_year = date('Y');
    $YearRecords = [];
    for ($y = $current_year + 1; $y >= $current_year - 0; $y--) {
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
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <table id='TableRecordList' class='display dataTable table table-bordered table-striped' style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>ปี / วันที่</th>
                                                    <th>ชื่อการประชุม</th>
                                                    <th>ผู้เข้าร่วมประชุม</th>
                                                    <th>สถานะการเข้า</th>
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
                                                        <h4 class="modal-title">บันทึกผลการเข้าร่วมประชุม</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="house_number" class="control-label">บ้านเลขที่</label>
                                                                        <input type="text" class="form-control" id="house_number" name="house_number" readonly>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label for="meeting_date_show" class="control-label">วันที่ประชุม</label>
                                                                        <input type="text" class="form-control" id="meeting_date_show" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="meeting_name" class="control-label">ชื่อการประชุม</label>
                                                                <input type="text" class="form-control" id="meeting_name" name="meeting_name" readonly>
                                                            </div>
                                                            <hr>
                                                            <div class="form-group">
                                                                <label for="attendance_name" class="control-label font-weight-bold text-primary">
                                                                    ระบุชื่อผู้เข้าร่วมประชุม (Attendance Name)
                                                                </label>
                                                                <input type="text" class="form-control" id="attendance_name" name="attendance_name" placeholder="เช่น นาย ก. (เจ้าบ้าน)">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label font-weight-bold text-primary">สถานะการเข้าร่วม (Status)</label>
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="chk_meeting_status">
                                                                    <label class="custom-control-label" for="chk_meeting_status" style="cursor: pointer;">
                                                                        ยืนยันการเข้าร่วมประชุม (Attended)
                                                                    </label>
                                                                </div>
                                                                <input type="hidden" name="meeting_status" id="meeting_status" value="N">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-save"></i>
                                                                <input type="submit" name="save" id="save" class="btn btn-primary" value="บันทึกข้อมูล"/>
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
        .dt-buttons .dt-button:hover {
            background-color: #2e59d9;
        }
        /* สีปุ่มพิมพ์ใบเซ็นชื่อ */
        .btn-custom-print {
            background-color: #1cc88a !important;
        }
    </style>

    <script>
        $(document).ready(function () {
            $('#chk_meeting_status').change(function() {
                if($(this).is(":checked")) {
                    $('#meeting_status').val('Y');
                } else {
                    $('#meeting_status').val('N');
                }
            });

            let dataRecords = $('#TableRecordList').DataTable({
                'dom': 'Blfrtip',
                'lengthMenu': [[10, 20, 50, 100, -1], [10, 20, 50, 100, "แสดงทั้งหมด"]],
                'buttons': [
                    // ปุ่มเดิม (Excel)

                    {
                        extend: 'excel',
                        text: '<i class="fa fa-file-excel"></i> Export Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                    },
                    // ปุ่ม Print เดิม (พิมพ์หน้าจอ)
                    {
                        extend: 'print',
                        text: '<i class="fa fa-print"></i> Print View',
                        className: 'btn btn-info btn-sm',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                    },


                    // *** ปุ่มใหม่: พิมพ์ใบเซ็นชื่อตามแบบฟอร์ม ***
                    {
                        text: '<i class="fa fa-print"></i> พิมพ์ใบเซ็นชื่อ (แยกซอย)',
                        className: 'btn btn-custom-print btn-sm',
                        action: function ( e, dt, node, config ) {
                            // ดึงค่าปีที่เลือก
                            let year = $('#filter_year').val();
                            if(year === "") {
                                alert("กรุณาเลือกปีก่อนพิมพ์ใบเซ็นชื่อ");
                                return;
                            }
                            // เปิดหน้าต่างใหม่ไปที่ไฟล์ print_meeting_form.php
                            window.open('print_meeting_form.php?meeting_year=' + year, '_blank');
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
                    'url': 'model/manage_house_meeting_record_process.php',
                    'data': function(d) {
                        d.action = "GET_MEETING_LIST";
                        d.meeting_year = $('#filter_year').val();
                    }
                },
                'columns': [
                    {data: 'house_number'},
                    {data: 'alley', defaultContent: '-'},
                    {
                        data: 'meeting_date',
                        render: function (data, type, row) {
                            return row.meeting_year + ' : ' + data;
                        }
                    },
                    {data: 'meeting_name'},
                    {data: 'attendance_name', defaultContent: '-'},
                    {
                        data: 'meeting_status',
                        render: function (data, type, row) {
                            return (data === 'Y') ? '<span class="badge badge-success"><i class="fa fa-check"></i> เข้าร่วม</span>' : '<span class="badge badge-secondary"><i class="fa fa-times"></i> ไม่เข้าร่วม</span>';
                        },
                        className: "text-center"
                    },
                    {
                        data: 'id',
                        render: function (data, type, row) {
                            return '<button type="button" name="update" id="' + data + '" class="btn btn-warning btn-sm update" title="บันทึกผล"><i class="fa fa-edit"></i> Check-in</button>';
                        },
                        className: "text-center"
                    }
                ]
            });

            $('#filter_year').change(function() {
                dataRecords.ajax.reload();
            });

            dataRecords.buttons().container().appendTo('#buttons_container');

            // (ส่วน JS อื่นๆ คงเดิม)
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_house_meeting_record_process.php',
                    method: "POST",
                    data: formData,
                    dataType: "json",
                    success: function (data) {
                        if (data.status === 'success') {
                            alertify.success(data.message);
                            $('#recordForm')[0].reset();
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

            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                $.ajax({
                    type: "POST",
                    url: 'model/manage_house_meeting_record_process.php',
                    dataType: "json",
                    data: {action: "GET_DATA", id: id},
                    success: function (response) {
                        let data = response[0] || response;
                        $('#recordModal').modal('show');
                        $('#id').val(data.id);
                        $('#house_number').val(data.house_number);
                        $('#meeting_date_show').val(data.meeting_date + ' (' + data.meeting_year + ')');
                        $('#meeting_name').val(data.meeting_name);
                        $('#attendance_name').val(data.attendance_name);
                        if (data.meeting_status === 'Y') {
                            $('#chk_meeting_status').prop('checked', true);
                            $('#meeting_status').val('Y');
                        } else {
                            $('#chk_meeting_status').prop('checked', false);
                            $('#meeting_status').val('N');
                        }
                        $('#action').val('UPDATE_ATTENDANCE');
                    },
                    error: function () { alertify.error("ดึงข้อมูลไม่ได้"); }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>