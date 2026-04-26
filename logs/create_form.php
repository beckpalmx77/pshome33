<?php
include('includes/Header.php');
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
                                    <div class="row">
                                        <div class="col-md-12 col-md-offset-2">
                                            <div class="panel">
                                                <div class="panel-body">

                                                    <form id="from_data">

                                                        <div class="form-group has-success">
                                                            <label for="success"
                                                                   class="control-label">ชื่อผู้ใช้</label>
                                                            <div class="">
                                                                <input type="text" name="user_id"
                                                                       class="form-control"
                                                                       required="required" id="user_id"
                                                                       placeholder="ชื่อผู้ใช้">
                                                            </div>
                                                        </div>

                                                        <div class="form-group has-success">
                                                            <label for="success" class="control-label">รหัสผ่าน
                                                                Password</label>

                                                            <div class="">
                                                                <input type="password" name="password"
                                                                       class="form-control"
                                                                       required="required" id="password">
                                                            </div>
                                                        </div>

                                                        <div class="form-group has-success">
                                                            <label for="success" class="control-label">Email
                                                                Address</label>
                                                            <div class="">
                                                                <input type="text" name="email"
                                                                       class="form-control"
                                                                       required="required" id="email">
                                                            </div>
                                                        </div>

                                                        <div class="form-group has-success">
                                                            <label for="success" class="control-label">ชื่อ First
                                                                Name</label>
                                                            <div class="">
                                                                <input type="text" name="first_name"
                                                                       class="form-control"
                                                                       required="required" id="first_name"
                                                                       placeholder="ชื่อ First Name">
                                                            </div>
                                                        </div>

                                                        <div class="form-group has-success">
                                                            <label for="success" class="control-label">นามสกุล Last
                                                                Name</label>
                                                            <div class="">
                                                                <input type="text" name="last_name"
                                                                       class="form-control"
                                                                       required="required" id="last_name"
                                                                       placeholder="นามสกุล Last Name">
                                                            </div>
                                                        </div>

                                                        <input type="hidden" class="form-control"
                                                               id="department_id"
                                                               name="department_id">

                                                        <div class="form-group has-success">
                                                            <label class="control-label"
                                                                   for="select-testing">แผนก</label>
                                                            <div class=”form-group”>
                                                                <select id="department_id" name="department_id"
                                                                        class="form-control" data-live-search="true"
                                                                        title="Please select">
                                                                    <option
                                                                        value="<?php echo htmlentities($result->department_id); ?>"
                                                                        selected><?php echo htmlentities($result->department_desc); ?></option>
                                                                    <?php $sql1 = "SELECT * from mdepartment";
                                                                    $query1 = $conn->prepare($sql1);
                                                                    $query1->execute();
                                                                    $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                                                    if ($query1->rowCount() > 0) {
                                                                        foreach ($results1 as $result1) { ?>
                                                                            <option
                                                                                value="<?php echo htmlentities($result1->department_id); ?>"><?php echo htmlentities($result1->department_desc); ?></option>
                                                                        <?php }
                                                                    } ?>
                                                                </select>

                                                            </div>
                                                            <span class="help-block"></span>
                                                        </div>

                                                        <div class="form-group has-success">
                                                            <label class="control-label" for="select-testing">ประเภทผู้ใช้
                                                                Account Type (Administrator =
                                                                สิทธิ์จัดการระบบ)</label>

                                                            <div class=”form-group”>
                                                                <select id="account_type" name="account_type"
                                                                        class="form-control" data-live-search="true"
                                                                        title="Please select">
                                                                    <option
                                                                        value="<?php echo htmlentities($result->permission_id); ?>"
                                                                        selected><?php echo htmlentities($result->permission_detail); ?></option>
                                                                    <?php $sql1 = "SELECT * from ims_permission";
                                                                    $query1 = $conn->prepare($sql1);
                                                                    $query1->execute();
                                                                    $results1 = $query1->fetchAll(PDO::FETCH_OBJ);
                                                                    if ($query1->rowCount() > 0) {
                                                                        foreach ($results1 as $result1) { ?>
                                                                            <option
                                                                                value="<?php echo htmlentities($result1->permission_id); ?>"><?php echo htmlentities($result1->permission_detail); ?></option>
                                                                        <?php }
                                                                    } ?>
                                                                </select>

                                                            </div>
                                                            <span class="help-block"></span>
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


                                                        <div class="form-group has-success">

                                                            <div class="">
                                                                <button type="submit"
                                                                        class="btn btn-primary btn-block">
                                                                    Save
                                                            </div>
                                                        </div>

                                                        <div><input id="action" name="action" type="hidden"
                                                                    value="ADD">
                                                        </div>

                                                    </form>

                                                    <div id="result"></div>


                                                </div>
                                            </div>
                                        </div>
                                        <!-- /.col-md-8 col-md-offset-2 -->
                                    </div>
                                    <!-- /.row -->

                                </section>


                            </div>

                        </div>

                    </div>

                </div>
                <!--Row-->

                <!-- Row -->

            </div>

            <!---Container Fluid-->

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
                                        <th>รหัสแผนก</th>
                                        <th>รายละเอียด</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th>รหัสแผนก</th>
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

        <?php
        include('includes/Modal-Logout.php');
        include('includes/Footer.php');
        ?>

    </div>
</div>

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<!-- Select2 -->
<script src="vendor/select2/dist/js/select2.min.js"></script>
<!-- Bootstrap Datepicker -->
<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<!-- Bootstrap Touchspin -->
<script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
<!-- ClockPicker -->
<script src="vendor/clock-picker/clockpicker.js"></script>
<!-- RuangAdmin Javascript -->
<script src="js/myadmin.min.js"></script>
<!-- Javascript for this page -->

<script src="js/modal/show_department_modal.js"></script>

<script src="js/MyFrameWork/framework_util.js"></script>

<script>
    $(document).ready(function () {
        $("form").on("submit", function (event) {
            event.preventDefault();
            let formValues = $(this).serialize();
            $.post("model/manage_account_process.php", formValues, function (response) {
                if (response == 1) {
                    document.getElementById("from_data").reset();
                    alertify.success("บันทึกข้อมูลเรียบร้อย Save Data Success");

                } else if (response == 2) {
                    alertify.error("ไม่สามารถบันทึกข้อมูลได้ มี User นี้แล้ว ");
                } else {
                    alertify.error("ไม่สามารถบันทึกข้อมูลได้ DB Error ");
                }
            });
        });
    });
</script>

<script>

    $('#email').blur(function () {

        let action = "GET_COUNT_RECORDS_COND";
        let table_name = "ims_user";
        let cond = " WHERE user_id = '" + $('#user_id').val() + "'";
        let formData = {action: action, table_name: table_name, cond: cond};
        $.ajax({
            type: "POST",
            url: 'model/manage_general_data.php',
            data: formData,
            success: function (response) {
                if (response > 0) {
                    alertify.error("มี User นี้ในระบบแล้วโปรดใช้ User อื่น");
                    $('#user_id').val("");
                }
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });
    });

</script>

<script>

    $('#email').focusout(function () {
        let email_address = $('#email').val();
        if (ValidateEmail(email_address)) {
            $('#email').val(email_address);
        } else {
            alertify.error("กรุณาป้อน รูปแบบ Email ที่ถูกต้อง");
            $('#email').val("");
        }
    });

</script>

</body>

</html>

