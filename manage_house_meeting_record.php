<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
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
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>ปี / วันที่</th>
                                                    <th>ชื่อการประชุม</th>
                                                    <th>ผู้เข้าร่วมประชุม</th> <th>สถานะการเข้า</th> <th>จัดการ</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>ปี / วันที่</th>
                                                    <th>ชื่อการประชุม</th>
                                                    <th>ผู้เข้าร่วมประชุม</th>
                                                    <th>สถานะการเข้า</th>
                                                    <th>จัดการ</th>
                                                </tr>
                                                </tfoot>
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

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>

    <style>
        .icon-input-btn { display: inline-block; position: relative; }
        .icon-input-btn input[type="submit"] { padding-left: 2em; }
        .icon-input-btn .fa { display: inline-block; position: absolute; left: 0.65em; top: 30%; }
    </style>

    <script>
        $(document).ready(function () {
            // Checkbox Logic
            $('#chk_meeting_status').change(function() {
                if($(this).is(":checked")) {
                    $('#meeting_status').val('Y');
                } else {
                    $('#meeting_status').val('N');
                }
            });

            // *** แก้ไข URL ให้ตรงกับไฟล์ Backend (_record_process.php) ***
            let formData = {action: "GET_MEETING_LIST"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
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
                    'url': 'model/manage_house_meeting_record_process.php', // แก้ไขชื่อไฟล์
                    'data': formData
                },
                'columns': [
                    {data: 'house_number'},
                    {data: 'alley'},
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
                            if (data === 'Y') {
                                return '<span class="badge badge-success"><i class="fa fa-check"></i> เข้าร่วม</span>';
                            } else {
                                return '<span class="badge badge-secondary"><i class="fa fa-times"></i> ไม่เข้าร่วม</span>';
                            }
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

            // *** SUBMIT FORM (Save Update) ***
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_house_meeting_record_process.php', // แก้ไขชื่อไฟล์
                    method: "POST",
                    data: formData,
                    dataType: "json", // คาดหวัง JSON กลับมา
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
                    error: function() {
                        alertify.error("เกิดข้อผิดพลาดในการเชื่อมต่อ");
                        $('#save').attr('disabled', false);
                    }
                })
            });

            // *** Click Update Button ***
            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                let formData = {action: "GET_DATA", id: id};
                $.ajax({
                    type: "POST",
                    url: 'model/manage_house_meeting_record_process.php', // แก้ไขชื่อไฟล์
                    dataType: "json",
                    data: formData,
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

                        $('.modal-title').html("<i class='fa fa-edit'></i> บันทึกผลการเข้าร่วมประชุม");
                        $('#action').val('UPDATE_ATTENDANCE');
                        $('#save').val('บันทึกข้อมูล');
                    },
                    error: function (response) {
                        alertify.error("ไม่สามารถดึงข้อมูลได้");
                    }
                });
            });
        });
    </script>

    </body>
    </html>

<?php } ?>