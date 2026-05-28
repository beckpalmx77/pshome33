<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <link rel="stylesheet" href="css/spin_datatables_v2.css"/>
        <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
        <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.dataTables.min.css"/>

        <style>
            .card-body {
                padding: 1rem;
            }
            #TableRecordList thead th {
                background-color: #f8f9fc;
            }
            .fixedHeader-floating {
                background-color: white !important;
                z-index: 1000;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .modal-body {
                padding: 1rem;
            }
            .modal-footer {
                padding: 0.75rem 1rem;
            }
            .form-group.row {
                margin-bottom: 0.5rem;
            }
            .zoom-container {
                position: relative;
                overflow: hidden;
                display: inline-block;
            }
            .zoom-container img {
                transition: transform 0.3s ease;
            }
            .zoom-container:hover img {
                transform: scale(1.5);
                cursor: zoom-out;
            }
        </style>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <div class="container-fluid" id="container-wrapper">
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
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-start">
                                    <button type="button" id="btnAdd" class="btn btn-primary btn-xs">
                                        <i class="fa fa-plus"></i> เพิ่มข้อมูลการลา
                                    </button>
                                    <button type="button" id="btnReload" class="btn btn-outline-success btn-xs ml-2">
                                        <i class="fa fa-refresh"></i> Reload Data
                                    </button>
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="col-md-12">
                                            <table id="TableRecordList" class="display nowrap" style="width:100%;">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>รหัสพนักงาน</th>
                                                    <th>ชื่อพนักงาน</th>
                                                    <th>ประเภทการลา</th>
                                                    <th>วันที่เริ่ม</th>
                                                    <th>วันที่สิ้นสุด</th>
                                                    <th>หมายเหตุ</th>
                                                    <th>รูปภาพ</th>
                                                    <th>วันที่บันทึก</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                            </table>
                                        </div>

                                        <!-- Modal for Add/Update -->
                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">จัดการข้อมูลการลา</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <form method="post" id="recordForm" enctype="multipart/form-data">
                                                        <div class="modal-body">
                                                            <div class="form-group row">
                                                                <div class="col-sm-6">
                                                                    <label for="emp_id">รหัสพนักงาน</label>
                                                                    <div class="input-group">
                                                                        <input type="text" class="form-control" id="emp_id" name="emp_id" required readonly>
                                                                        <div class="input-group-append">
                                                                            <button class="btn btn-primary" type="button" id="btnSearchEmp">
                                                                                <i class="fas fa-search"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label for="emp_name">ชื่อพนักงาน</label>
                                                                    <input type="text" class="form-control" id="emp_name" name="emp_name" required readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <div class="col-sm-6">
                                                                    <label for="leave_type">ประเภทการลา</label>
                                                                    <select class="form-control" id="leave_type" name="leave_type" onchange="toggleSwapDate()" required>
                                                                        <option value="">เลือกประเภทการลา</option>
                                                                        <option value="ลาป่วย">ลาป่วย</option>
                                                                        <option value="ลากิจ">ลากิจ</option>
                                                                        <option value="ลาพักร้อน">ลาพักร้อน</option>
                                                                        <option value="สลับวันหยุด">สลับวันหยุด (ระบุวันชดเชย)</option>
                                                                        <option value="อื่นๆ">อื่นๆ</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label for="user_id">User ID (System)</label>
                                                                    <input type="text" class="form-control" id="user_id" name="user_id" value="<?php echo $_SESSION['alogin'] ?>" readonly>
                                                                </div>
                                                            </div>
                                                            <div id="swap_date_container" style="display:none;">
                                                                <div class="form-group row">
                                                                    <div class="col-sm-6">
                                                                        <label for="swap_date">🔄 วันที่มาทำงานชดเชย :</label>
                                                                        <input type="text" class="form-control datepicker" id="swap_date" name="swap_date" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <div class="col-sm-6">
                                                                    <label id="label_start_date" for="start_date">วันที่เริ่ม</label>
                                                                    <input type="text" class="form-control datepicker" id="start_date" name="start_date" required readonly>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label id="label_end_date" for="end_date">วันที่สิ้นสุด</label>
                                                                    <input type="text" class="form-control datepicker" id="end_date" name="end_date" required readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <div class="col-sm-12">
                                                                    <label for="remark">หมายเหตุ</label>
                                                                    <textarea class="form-control" id="remark" name="remark" rows="3"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="form-group row">
                                                                <div class="col-sm-6">
                                                                    <label for="leave_photo">รูปภาพประกอบ (ถ้ามี)</label>
                                                                    <input type="file" class="form-control-file" id="leave_photo" name="leave_photo" accept="image/*">
                                                                </div>
                                                                <div class="col-sm-6 text-center">
                                                                    <div class="zoom-container">
                                                                        <img id="preview_image" src="#" alt="Preview" style="display: none; max-width: 150px; margin-top: 10px;">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value="ADD"/>
                                                            <button type="submit" class="btn btn-primary" id="saveButton">Save <i class="fa fa-check"></i></button>
                                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close <i class="fa fa-times"></i></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Confirm Delete Modal -->
                                        <div class="modal fade" id="confirmDeleteModal">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">ยืนยันการลบ</h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">คุณต้องการลบข้อมูลการลานี้ใช่หรือไม่?</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                                                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">ลบข้อมูล</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Employee Search Modal -->
                                        <div class="modal fade" id="EmpSearchModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">ค้นหาพนักงาน</h4>
                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table id="TableEmpList" class="display nowrap" style="width:100%;">
                                                            <thead>
                                                            <tr>
                                                                <th>รหัสพนักงาน</th>
                                                                <th>ชื่อพนักงาน</th>
                                                                <th>Action</th>
                                                            </tr>
                                                            </thead>
                                                        </table>
                                                    </div>
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
    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.datepicker').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'fixedHeader': true,
                'language': {
                    search: 'ค้นหา',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
                    paginate: { previous: 'ก่อนหน้า', last: 'สุดท้าย', next: 'ต่อไป' }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'scrollX': true,
                'ajax': {
                    'url': 'model/manage_leave_document_process.php',
                    'data': function(d) {
                        d.action = 'GET_LEAVE_DATA';
                    }
                },
                'columns': [
                    {data: 'id'},
                    {data: 'emp_id'},
                    {data: 'emp_name'},
                    {data: 'leave_type'},
                    {data: 'start_date'},
                    {data: 'end_date'},
                    {data: 'remark'},
                    {data: 'photo'},
                    {data: 'created_at'},
                    {data: 'action'}
                ]
            });

            let empRecords = $('#TableEmpList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    paginate: { previous: 'ก่อนหน้า', last: 'สุดท้าย', next: 'ต่อไป' }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_leave_document_process.php',
                    'data': function(d) {
                        d.action = 'GET_EMPLOYEE';
                    }
                },
                'columns': [
                    {data: 'emp_id'},
                    {data: 'emp_name'},
                    {data: 'action'}
                ]
            });

            $('#btnSearchEmp').click(function () {
                $('#EmpSearchModal').modal('show');
                empRecords.ajax.reload();
            });

            $('#TableEmpList').on('click', '.select_emp', function () {
                let data = $(this).attr('id').split('|');
                $('#emp_id').val(data[0]);
                $('#emp_name').val(data[1]);
                $('#EmpSearchModal').modal('hide');
            });

            $('#btnAdd').click(function () {
                $('#recordForm')[0].reset();
                $('#preview_image').hide();
                $('.modal-title').html("<i class='fa fa-plus'></i> เพิ่มข้อมูลการลา");
                $('#action').val('ADD');
                $('#id').val("");
                $('#recordModal').modal('show');
            });

            $('#btnReload').click(function () {
                dataRecords.ajax.reload();
            });

            $('#recordForm').on('submit', function (e) {
                e.preventDefault();
                let formData = new FormData(this);
                $('#saveButton').attr('disabled', 'disabled');
                $.ajax({
                    url: 'model/manage_leave_document_process.php',
                    method: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordModal').modal('hide');
                        $('#saveButton').attr('disabled', false);
                        dataRecords.ajax.reload();
                    },
                    error: function() {
                        alertify.error("เกิดข้อผิดพลาด");
                        $('#saveButton').attr('disabled', false);
                    }
                });
            });

            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                $.ajax({
                    type: "POST",
                    url: 'model/manage_leave_document_process.php',
                    dataType: "json",
                    data: {action: "GET_DATA", id: id},
                    success: function (response) {
                        if (response.length > 0) {
                            let data = response[0];
                            $('#id').val(data.id);
                            $('#emp_id').val(data.emp_id);
                            $('#emp_name').val(data.emp_name);
                            $('#leave_type').val(data.leave_type);
                            $('#start_date').val(data.start_date);
                            $('#end_date').val(data.end_date);
                            $('#swap_date').val(data.swap_date);
                            $('#remark').val(data.remark);
                            $('#user_id').val(data.user_id);
                            
                            toggleSwapDate(); // Refresh UI for swap date container
                            
                            if (data.photo_path) {
                                $('#preview_image').attr('src', data.photo_path).show();
                            } else {
                                $('#preview_image').hide();
                            }

                            $('.modal-title').html("<i class='fa fa-edit'></i> แก้ไขข้อมูลการลา");
                            $('#action').val('UPDATE');
                            $('#recordModal').modal('show');
                        }
                    }
                });
            });

            let deleteId = null;
            $("#TableRecordList").on('click', '.delete', function () {
                deleteId = $(this).attr("id");
                $("#confirmDeleteModal").modal("show");
            });

            $("#confirmDeleteBtn").click(function () {
                $.ajax({
                    url: 'model/manage_leave_document_process.php',
                    method: "POST",
                    data: {id: deleteId, action: "DELETE"},
                    success: function (data) {
                        alertify.success(data);
                        $("#confirmDeleteModal").modal("hide");
                        dataRecords.ajax.reload();
                    }
                });
            });

            $("#leave_photo").change(function() {
                if (this.files && this.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview_image').attr('src', e.target.result).show();
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });

        function toggleSwapDate() {
            const leaveType = document.getElementById('leave_type').value;
            const swapContainer = document.getElementById('swap_date_container');
            const labelStart = document.getElementById('label_start_date');
            const labelEnd = document.getElementById('label_end_date');

            if (leaveType === 'สลับวันหยุด') {
                swapContainer.style.display = 'block';
                labelStart.textContent = '📅 วันที่ขอหยุด :';
                labelEnd.textContent = '📅 ถึงวันที่ :';
            } else {
                swapContainer.style.display = 'none';
                labelStart.textContent = 'วันที่เริ่ม :';
                labelEnd.textContent = 'วันที่สิ้นสุด :';
            }
        }
    </script>
    </body>
    </html>
<?php } ?>