<?php
session_start();
error_reporting(0);
include('includes/Header.php');
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
                                        </div-->

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>รหัสผู้ใช้</th>
                                                    <th>ชื่อ</th>
                                                    <th>นามสกุล</th>
                                                    <th>Type</th>
                                                    <th>Role</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>#</th>
                                                    <th>รหัสผู้ใช้</th>
                                                    <th>ชื่อ</th>
                                                    <th>นามสกุล</th>
                                                    <th>Type</th>
                                                    <th>Role</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
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
                                                                <div class="form-group row">
                                                                    <div class="col-sm-6">
                                                                        <label for="user_id"
                                                                               class="control-label">รหัสผู้ใช้งาน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="user_id"
                                                                               name="user_id"
                                                                               required="required"
                                                                               placeholder="รหัสผู้ใช้งาน">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="email"
                                                                               class="control-label">Email Address</label>
                                                                        <input type="text" class="form-control"
                                                                               id="email"
                                                                               name="email"
                                                                               required="required"
                                                                               placeholder="Email Address">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-2">
                                                                        <label for="emp_id"
                                                                               class="control-label">รหัสพนักงาน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="emp_id"
                                                                               name="emp_id"
                                                                               required="required"
                                                                               placeholder="รหัสพนักงาน">
                                                                    </div>
                                                                    <div class="col-sm-5">
                                                                        <label for="first_name"
                                                                               class="control-label">ชื่อ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="first_name"
                                                                               name="first_name"
                                                                               required="required"
                                                                               placeholder="ชื่อ">
                                                                    </div>
                                                                    <div class="col-sm-5">
                                                                        <label for="last_name"
                                                                               class="control-label">นามสกุล</label>
                                                                        <input type="text" class="form-control"
                                                                               id="last_name"
                                                                               name="last_name"
                                                                               required="required"
                                                                               placeholder="นามสกุล">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <input type="hidden" class="form-control"
                                                                           id="department_id"
                                                                           name="department_id">
                                                                    <div class="col-sm-10">
                                                                        <label for="department_desc"
                                                                               class="control-label">หน่วยงาน/ฝ่าย/แผนก</label>
                                                                        <input type="text" class="form-control"
                                                                               id="department_desc"
                                                                               name="department_desc"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="หน่วยงาน/ฝ่าย/แผนก">
                                                                    </div>

                                                                    <div class="col-sm-2">
                                                                        <label for="department_id"
                                                                               class="control-label">เลือก</label>
                                                                        <a data-toggle="modal"
                                                                           href="#SearchDepartmentModal"
                                                                           class="btn btn-primary">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <input type="hidden" class="form-control"
                                                                           id="permission_id"
                                                                           name="permission_id">
                                                                    <div class="col-sm-10">
                                                                        <label for="permission_detail"
                                                                               class="control-label">สิทธิ์การใช้งาน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="permission_detail"
                                                                               name="permission_detail"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="สิทธิ์การใช้งาน">
                                                                    </div>

                                                                    <div class="col-sm-2">
                                                                        <label for="permission_id"
                                                                               class="control-label">เลือก</label>
                                                                        <a data-toggle="modal"
                                                                           href="#SearchPermissionModal"
                                                                           class="btn btn-primary">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <div class=”form-group”>
                                                                    <label for="role" class="control-label">ROLE การ Approve</label>
                                                                    <select id="role" name="role"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="SUPERVISOR">SUPERVISOR</option>
                                                                        <option value="EMPLOYEE">EMPLOYEE</option>
                                                                        <option value="HR">HR</option>
                                                                        <option value="ADMIN">ADMIN</option>
                                                                        <option value="OBSERVE">OBSERVE</option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="approve_permission" class="control-label">สิทธิ์อนุมัติเอกสาร</label>
                                                                    <select id="approve_permission" name="approve_permission"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="N">ไม่มีสิทธิ์</option>
                                                                        <option value="Y">มีสิทธิ์</option>
                                                                    </select>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-12">
                                                                        <label for="document_dept_cond"
                                                                               class="control-label">แผนกที่อนุมัติเอกสารได้</label>
                                                                        <input type="text" class="form-control"
                                                                               id="document_dept_cond"
                                                                               name="document_dept_cond"
                                                                               placeholder="แผนกที่อนุมัติเอกสารได้">
                                                                    </div>
                                                                </div>

                                                                <div class=”form-group”>
                                                                    <label for="status" class="control-label">Status</label>
                                                                    <select id="status" name="status"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option>Active</option>
                                                                        <option>Inactive</option>
                                                                    </select>
                                                                </div>



                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Save"/>
                                                            </span>
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
                                                                    <th>รหัสหน่วยงาน/ฝ่าย/แผนก</th>
                                                                    <th>รายละเอียด</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสหน่วยงาน/ฝ่าย/แผนก</th>
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

                                        <div class="modal fade" id="SearchPermissionModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Permission</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TablePermissionList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัสสิทธิ์การใช้งาน</th>
                                                                    <th>รายละเอียดสิทธิ์การใช้งาน</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสสิทธิ์การใช้งาน</th>
                                                                    <th>รายละเอียดสิทธิ์การใช้งาน</th>
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

    <script src="js/modal/show_department_modal.js"></script>
    <script src="js/modal/show_permision_modal.js"></script>

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
            let formData = {action: "GET_ACCOUNT", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_account_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'line_no'},
                    {data: 'user_id'},
                    {data: 'first_name'},
                    {data: 'last_name'},
                    {data: 'picture'},
                    {data: 'role'},
                    {data: 'status'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_account_process.php',
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

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_account_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let user_id = response[i].user_id;
                        let emp_id = response[i].emp_id;
                        let email = response[i].email;
                        let first_name = response[i].first_name;
                        let last_name = response[i].last_name;
                        let permission_id = response[i].permission_id;
                        let permission_detail = response[i].permission_detail;
                        let department_id = response[i].department_id;
                        let department_desc = response[i].department_desc;
                        let approve_permission = response[i].approve_permission;
                        let document_dept_cond = response[i].document_dept_cond;
                        let role = response[i].role;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#user_id').val(user_id);
                        $('#emp_id').val(emp_id);
                        $('#email').val(email);
                        $('#first_name').val(first_name);
                        $('#last_name').val(last_name);
                        $('#permission_id').val(permission_id);
                        $('#permission_detail').val(permission_detail);
                        $('#department_id').val(department_id);
                        $('#department_desc').val(department_desc);
                        $('#approve_permission').val(approve_permission);
                        $('#document_dept_cond').val(document_dept_cond);
                        $('#role').val(role);
                        $('#status').val(status);
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

        $("#TableRecordList").on('click', '.delete', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_account_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let user_id = response[i].user_id;
                        let emp_id = response[i].emp_id;
                        let email = response[i].email;
                        let first_name = response[i].first_name;
                        let last_name = response[i].last_name;
                        let permission_id = response[i].permission_id;
                        let permission_detail = response[i].permission_detail;
                        let department_id = response[i].department_id;
                        let department_desc = response[i].department_desc;
                        let approve_permission = response[i].approve_permission;
                        let document_dept_cond = response[i].document_dept_cond;
                        let role = response[i].role;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#user_id').val(user_id);
                        $('#emp_id').val(emp_id);
                        $('#email').val(email);
                        $('#first_name').val(first_name);
                        $('#last_name').val(last_name);
                        $('#permission_id').val(permission_id);
                        $('#permission_detail').val(permission_detail);
                        $('#department_id').val(department_id);
                        $('#department_desc').val(department_desc);
                        $('#approve_permission').val(approve_permission);
                        $('#document_dept_cond').val(document_dept_cond);
                        $('#role').val(role);
                        $('#status').val(status);
                        $('.modal-title').html("<i class='fa fa-minus'></i> Delete Record");
                        $('#action').val('DELETE');
                        $('#save').val('Confirm Delete');
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
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    <script>
        $('#check').click(function () {
            if ('password' == $('#test-input').attr('type')) {
                $('#test-input').prop('type', 'text');
            } else {
                $('#test-input').prop('type', 'password');
            }
        });
    </script>



    </body>
    </html>

<?php } ?>