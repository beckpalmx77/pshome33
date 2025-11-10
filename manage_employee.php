<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['position_desc']) == "") {
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

                                        <div class="col-md-12 col-md-offset-2">
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>
                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>รหัสพนักงาน</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>ชื่อเล่น</th>
                                                    <th>ตำแหน่ง</th>
                                                    <th>วันเริ่มงาน</th>
                                                    <th>เวลาทำงาน</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>รหัสพนักงาน</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>ชื่อเล่น</th>
                                                    <th>ตำแหน่ง</th>
                                                    <th>วันเริ่มงาน</th>
                                                    <th>เวลาทำงาน</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-xl">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">รายละเอียดพนักงาน</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>
                                                    <form method="post" id="recordForm" enctype="multipart/form-data">
                                                        <div class="modal-body">
                                                            <div class="form-group row">
                                                                <div class="col-sm-3">
                                                                    <label for="emp_id" class="control-label">รหัสพนักงาน</label>
                                                                    <input type="text" class="form-control"
                                                                           id="emp_id" name="emp_id" readonly="true"
                                                                           placeholder="รหัสพนักงาน">
                                                                </div>

                                                                <div class="col-sm-3">
                                                                    <label>รูปภาพ</label>
                                                                    <input type="file" name="image" id="image"
                                                                           class="form-control" accept="image/*">
                                                                    <input type="hidden" name="old_image"
                                                                           id="old_image">
                                                                    <br>
                                                                    <img id="preview-image" src="#"
                                                                         style="max-width:100px; display:none;"
                                                                         class="img-thumbnail"/>
                                                                </div>

                                                                <div class="col-sm-3">
                                                                    <label for="prefix" class="control-label">คำนำหน้าชื่อ</label>
                                                                    <select id="prefix" name="prefix"
                                                                            class="form-control"
                                                                            data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="นาย">นาย</option>
                                                                        <option value="นาง">นาง</option>
                                                                        <option value="นางสาว">นางสาว</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-sm-3">
                                                                    <label for="sex" class="control-label">เพศ</label>
                                                                    <select id="sex" name="sex"
                                                                            class="form-control"
                                                                            data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="-">ไม่ระบุ</option>
                                                                        <option value="M">ชาย</option>
                                                                        <option value="F">หญิง</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-3">
                                                                    <label for="f_name"
                                                                           class="control-label">ชื่อ</label>
                                                                    <input type="text" class="form-control"
                                                                           id="f_name" name="f_name" required="required"
                                                                           placeholder="ชื่อ">
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="l_name"
                                                                           class="control-label">นามสกุล</label>
                                                                    <input type="text" class="form-control"
                                                                           id="l_name" name="l_name" required="required"
                                                                           placeholder="นามสกุล">
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="nick_name" class="control-label">ชื่อเล่น</label>
                                                                    <input type="text" class="form-control"
                                                                           id="nick_name" name="nick_name"
                                                                           required="required"
                                                                           placeholder="ชื่อเล่น">
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="start_work_date" class="control-label">วันทีเริ่มงาน</label>
                                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                                    <input type="text" class="form-control"
                                                                           id="start_work_date" name="start_work_date"
                                                                           required="required" value="" readonly="true"
                                                                           placeholder="วันทีเริ่มงาน">
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-3">
                                                                    <label for="work_age"
                                                                           class="control-label">อายุงาน</label>
                                                                    <input type="text" class="form-control"
                                                                           id="work_age" name="work_age" value=""
                                                                           readonly="true"
                                                                           placeholder="อายุงาน">
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="status" class="control-label">สถานะการทำงาน</label>
                                                                    <select id="status" name="status"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="Y" selected>ทำงานปกติ</option>
                                                                        <option value="N">ลาออก</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-3">
                                                                    <label for="week_holiday" class="control-label">วันหยุดประจำสัปดาห์</label>
                                                                    <select id="week_holiday" name="week_holiday"
                                                                            class="form-control" data-live-search="true"
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
                                                                <div class="col-sm-3">
                                                                    <label for="phone"
                                                                           class="control-label">โทรศัพท์</label>
                                                                    <input type="text" class="form-control"
                                                                           id="phone" name="phone" placeholder="">
                                                                </div>
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm2">
                                                                    <label for="salary_type" class="control-label">ประเภทพนักงาน</label>
                                                                    <select id="salary_type" name="salary_type"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="M">รายเดือน</option>
                                                                        <option value="D">รายวัน</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-2">
                                                                    <label for="salary"
                                                                           class="control-label">เงินเดือน/ค่าจ้าง</label>
                                                                    <input type="text" class="form-control"
                                                                           id="salary" name="salary" placeholder="">
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <input type="hidden" id="position_id"
                                                                           name="position_id">
                                                                    <label for="position_desc" class="control-label">ตำแหน่ง</label>
                                                                    <div class="input-group">
                                                                        <input type="text" class="form-control"
                                                                               id="position_desc" name="position_desc"
                                                                               readonly="true" placeholder="">
                                                                        <div class="input-group-append">
                                                                            <a data-toggle="modal"
                                                                               href="#SearchPositionModal"
                                                                               class="btn btn-primary">
                                                                                เลือก <i class="fa fa-search"
                                                                                         aria-hidden="true"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <input type="hidden" class="form-control"
                                                                           id="work_time_id" name="work_time_id">
                                                                    <label for="work_time_detail" class="control-label">ตารางเวลาทำงาน</label>
                                                                    <div class="input-group">
                                                                        <input type="text" class="form-control"
                                                                               id="work_time_detail"
                                                                               name="work_time_detail"
                                                                               readonly placeholder="ตารางเวลาทำงาน">
                                                                        <div class="input-group-append">
                                                                            <a data-toggle="modal"
                                                                               href="#SearchWorkTimeModal"
                                                                               class="btn btn-primary">
                                                                                เลือก <i class="fa fa-search"
                                                                                         aria-hidden="true"></i>
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <span class="icon-input-btn">
                                                            <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="บันทึก"/>
                                                            </span>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">ปิด <i class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchPositionModal">
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
                                                                   id="TablePositionList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัสตำแหน่ง</th>
                                                                    <th>รายละเอียด</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสตำแหน่ง</th>
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
                                                                    <th>รหัสตารางเวลาการทำงาน</th>
                                                                    <th>รายละเอียด</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสตารางเวลาการทำงาน</th>
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

    <script src="js/modal/show_position_modal.js"></script>
    <script src="js/modal/show_worktime_modal.js"></script>

    <script src="js/util/calculate_datetime.js"></script>

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
            let formData = {action: "GET_EMPLOYEE", sub_action: "GET_MASTER", page_manage: "ADMIN",};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
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
                <?php  if ($_SESSION['deviceType'] !== 'computer') {
                    echo "'scrollX': true,";
                }?>
                'ajax': {
                    'url': 'model/manage_employee_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'emp_id'},
                    {data: 'full_name'},
                    {data: 'nick_name'},
                    {data: 'position_desc'},
                    {data: 'start_work_date'},
                    {data: 'work_time_detail'},
                    {data: 'status'},
                    {data: 'update'},
                ]
            });

            // ✅ ใช้ FormData สำหรับส่งทั้งข้อมูลฟอร์ม + ไฟล์
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');

                let formData = new FormData(this); // ใช้ this เพื่อแนบไฟล์ด้วย

                $.ajax({
                    url: 'model/manage_employee_process.php',
                    method: "POST",
                    data: formData,
                    contentType: false, // สำคัญ: ปิด contentType เพื่อให้ browser set multipart/form-data
                    processData: false, // สำคัญ: ห้ามแปลง formData เป็น string
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    },
                    error: function () {
                        alertify.error("ไม่สามารถส่งข้อมูลได้");
                        $('#save').attr('disabled', false);
                    }
                });
            });


        });
    </script>

    <script>
        $(document).ready(function () {

            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');

                // เคลียร์ค่าทั้งหมด
                $('#id').val("");
                $('#emp_id').val("");
                $('#f_name').val("");
                $('#l_name').val("");
                $('#image').val("");  // เคลียร์ฟิลด์ภาพ
                $('#old_image').val(""); // เคลียร์ old_image
                $('#position_id').val("");
                $('#position_desc').val("");
                $('#work_time_id').val("");
                $('#work_time_detail').val("");
                $('#start_work_date').val("");
                $('#work_age').val("");
                $('#nick_name').val("");  // แก้ไขเป็นชื่อฟิลด์ที่ถูกต้อง
                $('#prefix').val("");
                $('#remark').val("");
                $('#phone').val("");
                $('#week_holiday').val("");

                // รีเซ็ต select หรือ textarea ถ้ามี
                $('#position_id').val('').change();
                $('#work_time_id').val('').change();

                // รีเซ็ตการแสดงรูปภาพ
                $('#preview-image').hide();

                // ตั้งค่าชื่อ modal และ action
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
                        let phone = response[i].phone;
                        let start_work_date = response[i].start_work_date;
                        let position_id = response[i].position_id;
                        let position_desc = response[i].position_desc;
                        let work_time_id = response[i].work_time_id;
                        let work_time_detail = response[i].work_time_detail;
                        let remark = response[i].remark;
                        let salary_type = response[i].salary_type;
                        let salary = response[i].salary;
                        let week_holiday = response[i].week_holiday;
                        let work_age = 0;
                        let start_w_date = start_work_date.substr(3, 2) + "/" + start_work_date.substr(0, 2) + "/" + start_work_date.substr(6, 10);
                        let image = response[i].image;
                        work_age = getAge(start_w_date);
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#emp_id').val(emp_id);
                        $('#f_name').val(f_name);
                        $('#l_name').val(l_name);
                        $('#prefix').val(prefix);
                        $('#sex').val(sex);
                        $('#nick_name').val(nick_name);
                        $('#phone').val(phone);
                        $('#start_work_date').val(start_work_date);
                        $('#position_id').val(position_id);
                        $('#position_desc').val(position_desc);
                        $('#work_time_id').val(work_time_id);
                        $('#work_time_detail').val(work_time_detail);
                        $('#remark').val(remark);
                        $('#salary_type').val(salary_type);
                        $('#salary').val(salary);
                        $('#week_holiday').val(week_holiday);
                        $('#work_age').val(work_age);
                        $('#status').val(status);

                        if (image) {
                            $('#preview-image').attr('src', 'uploads/employees/' + image).show();
                        } else if ($('#old_image').val()) {
                            // หากมี old_image ให้แสดงภาพเดิม
                            $('#preview-image').attr('src', 'uploads/employees/' + $('#old_image').val()).show();
                        } else {
                            $('#preview-image').hide();
                        }


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

            return {years, months, days};

    </script>

    <script>
        $('#image').on('change', function () {
            const input = this;
            const preview = document.getElementById("preview-image");

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = "block";
                };
                reader.readAsDataURL(input.files[0]);
            }
        });
    </script>


    </body>
    </html>

<?php } ?>