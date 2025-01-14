<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
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
                <!-- Container Fluid-->
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
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <!--div class="col-md-12 col-md-offset-2">
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>
                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div-->

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>รหัสพนักงาน</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>หน่วยงาน</th>
                                                    <th>ชื่อเล่น</th>
                                                    <th>วันเริ่มงาน</th>
                                                    <th>dept_id_approve</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>รหัสพนักงาน</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>หน่วยงาน</th>
                                                    <th>ชื่อเล่น</th>
                                                    <th>วันเริ่มงาน</th>
                                                    <th>dept_id_approve</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <div class="form-group">
                                                                    <label for="text"
                                                                           class="control-label">รหัสพนักงาน</label>
                                                                    <input type="emp_id" class="form-control"
                                                                           id="emp_id" name="emp_id"
                                                                           placeholder="รหัสพนักงาน">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="prefix" class="control-label">คำนำหน้าชื่อ</label>
                                                                    <select id="prefix" name="prefix"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="นาย">นาย</option>
                                                                        <option value="นาง">นาง</option>
                                                                        <option value="นางสาว">นางสาว</option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="sex" class="control-label">เพศ</label>
                                                                    <select id="sex" name="sex"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="-">ไม่ระบุ</option>
                                                                        <option value="M">ชาย</option>
                                                                        <option value="F">หญิง</option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="f_name"
                                                                               class="control-label">ชื่อ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="f_name"
                                                                               name="f_name"
                                                                               required="required"
                                                                               placeholder="ชื่อ">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="l_name"
                                                                               class="control-label">นามสกุล</label>
                                                                        <input type="text" class="form-control"
                                                                               id="l_name"
                                                                               name="l_name"
                                                                               required="required"
                                                                               placeholder="นามสกุล">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="nick_name"
                                                                               class="control-label">ชื่อเล่น</label>
                                                                        <input type="text" class="form-control"
                                                                               id="nick_name"
                                                                               name="nick_name"
                                                                               required="required"
                                                                               placeholder="ชื่อเล่น">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="start_work_date"
                                                                               class="control-label">วันทีเริ่มงาน</label>
                                                                        <i class="fa fa-calendar"
                                                                           aria-hidden="true"></i>
                                                                        <input type="text" class="form-control"
                                                                               id="start_work_date"
                                                                               name="start_work_date"
                                                                               required="required"
                                                                               value=""
                                                                               readonly="true"
                                                                               placeholder="วันทีเริ่มงาน">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="work_age"
                                                                               class="control-label">อายุงาน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="work_age"
                                                                               name="work_age"
                                                                               value=""
                                                                               readonly="true"
                                                                               placeholder="อายุงาน">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="week_holiday"
                                                                               class="control-label">วันหยุดประจำสัปดาห์</label>
                                                                        <select id="week_holiday" name="week_holiday"
                                                                                class="form-control"
                                                                                data-live-search="true"
                                                                                title="Please select">
                                                                            <option value="0">ไม่ระบุ</option>
                                                                            <option value="1">วันจันทร์</option>
                                                                            <option value="2">วันอังคาร</option>
                                                                            <option value="3">วันพุธ</option>
                                                                            <option value="4">วันพฤหัสบดี</option>
                                                                            <option value="5">วันศุกร์</option>
                                                                            <option value="6">วันเสาร์</option>
                                                                            <option value="7">วันอาทิตย์</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="position"
                                                                               class="control-label">ตำแหน่ง</label>
                                                                        <input type="text" class="form-control"
                                                                               id="position"
                                                                               name="position"
                                                                               placeholder="ตำแหน่ง">
                                                                    </div>
                                                                </div>


                                                                <div class="form-group row">
                                                                    <input type="hidden" class="form-control"
                                                                           id="dept_id"
                                                                           name="dept_id">
                                                                    <div class="col-sm-10">
                                                                        <label for="department_id"
                                                                               class="control-label">หน่วยงาน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="department_id"
                                                                               name="department_id"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="หน่วยงาน">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <input type="hidden" class="form-control"
                                                                           id="work_time_id"
                                                                           name="work_time_id">
                                                                    <div class="col-sm-10">
                                                                        <label for="work_time_detail"
                                                                               class="control-label">ตารางเวลาทำงาน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="work_time_detail"
                                                                               name="work_time_detail"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="ตารางเวลาทำงาน">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-window-close"></i>
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchDepartmentModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableDepartmentList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัสหน่วยงาน</th>
                                                                    <th>รายละเอียด</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสหน่วยงาน</th>
                                                                    <th>รายละเอียด</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchWorkTimeModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableWorkTimeList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัสหน่วยงาน</th>
                                                                    <th>รายละเอียด</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสหน่วยงาน</th>
                                                                    <th>รายละเอียด</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>


    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="js/util/calculate_datetime.js"></script>
    <script src="js/modal/show_department_modal.js"></script>
    <script src="js/modal/show_worktime_modal.js"></script>

    <!-- Page level plugins -->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>


    <style>

        .icon-input-btn {
            display: inline-block;
            position: relative;
        }

        .icon-input-btn input[type="submit"] {
            padding-left: 2em;
        }

        .icon-input-btn .fa {
            display: inline-block;
            position: absolute;
            left: 0.65em;
            top: 30%;
        }
    </style>
    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_EMPLOYEE", sub_action: "GET_MASTER" ,page_manage: "ADMIN",};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_employee_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'emp_id'},
                    {data: 'full_name'},
                    {data: 'department_id'},
                    {data: 'nick_name'},
                    {data: 'start_work_date'},
                    {data: 'dept_id_approve'},
                    {data: 'status'},
                    {data: 'update'},
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_employee_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    }
                })
            });
            <!-- *** FOR SUBMIT FORM *** -->
        });
    </script>

    <script>
        $(document).ready(function () {

            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#emp_id').val("");
                $('#f_name').val("");
                $('#l_name').val("");
                $('#dept_id').val("");
                $('#department_id').val("");
                $('#work_time_id').val("");
                $('#work_time_desc').val("");
                $('#start_work_date').val("");
                $('#work_age').val("");
                $('#ืnick_name').val("");
                $('#prefix').val("");
                $('#position').val("");
                $('#remark').val("");
                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
            });
        });
    </script>

    <script>

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            // alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_employee_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let emp_id = response[i].emp_id;
                        let f_name = response[i].f_name;
                        let l_name = response[i].l_name;
                        let prefix = response[i].prefix;
                        let sex = response[i].sex;
                        let nick_name = response[i].nick_name;
                        let start_work_date = response[i].start_work_date;
                        let dept_id = response[i].dept_id;
                        let department_id = response[i].department_id;
                        let work_time_id = response[i].work_time_id;
                        let work_time_detail = response[i].work_time_detail;
                        let position = response[i].position;
                        let remark = response[i].remark;
                        let week_holiday = response[i].week_holiday;
                        let dept_id_approve = response[i].dept_id_approve;

                        let work_age = 0 ;
                        let start_w_date = start_work_date.substr(3,2) + "/" + start_work_date.substr(0,2) + "/" + start_work_date.substr(6,10);
                        work_age = getAge(start_w_date);

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#emp_id').val(emp_id);
                        $('#f_name').val(f_name);
                        $('#l_name').val(l_name);

                        $('#prefix').val(prefix);
                        $('#sex').val(sex);
                        $('#nick_name').val(nick_name);
                        $('#start_work_date').val(start_work_date);
                        $('#dept_id').val(dept_id);
                        $('#department_id').val(department_id);
                        $('#work_time_id').val(work_time_id);
                        $('#work_time_detail').val(work_time_detail);
                        $('#work_age').val(work_age);
                        $('#position').val(position);
                        $('#remark').val(remark);
                        $('#week_holiday').val(week_holiday);
                        $('#dept_id_approve').val(dept_id_approve);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action').val('UPDATE');
                        $('#save').val('Save');

                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>


    <script>
        $(document).ready(function () {
            $('#start_work_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>

        function getAge(startWorkDate) {

            // แปลงวันที่จากรูปแบบ DD/MM/YYYY เป็น Date object
            const parts = startWorkDate.split("/");
            const startDate = new Date(parts[2], parts[1] - 1, parts[0]); // ปี, เดือน(0-11), วัน

            const today = new Date();

            let years = today.getFullYear() - startDate.getFullYear();
            let months = today.getMonth() - startDate.getMonth();
            let days = today.getDate() - startDate.getDate();

            // เช็คว่าต้องลดปีออกหรือไม่
            if (days < 0) {
                months--;
                const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0); // วันสุดท้ายของเดือนก่อนหน้า
                days += lastMonth.getDate(); // เพิ่มวันจากเดือนก่อนหน้า
            }

            // เช็คว่าเดือนน้อยกว่าศูนย์หรือไม่
            if (months < 0) {
                years--;
                months += 12; // เพิ่มเดือนจากปีที่แล้ว
            }

            return { years, months, days };

    </script>




    </body>
    </html>

<?php } ?>