<?php
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
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page']?>">Home</a></li>
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
                                                                <label for="success" class="control-label">ชื่อผู้ใช้</label>

                                                                <div class="">
                                                                    <input type="text" name="username"
                                                                           class="form-control"
                                                                           required="required" id="username">
                                                                </div>
                                                            </div>

                                                            <div class="form-group has-success">
                                                                <label for="success" class="control-label">รหัสผ่าน
                                                                    ปัจจุบัน
                                                                    Current Password</label>
                                                                <div class="">
                                                                    <input type="password" name="password"
                                                                           class="form-control"
                                                                           required="required" id="password">
                                                                </div>
                                                            </div>
                                                            <div class="form-group has-success">
                                                                <label for="success" class="control-label">รหัสผ่านใหม่
                                                                    New Password</label>

                                                                <div class="">
                                                                    <input type="password" name="new_password"
                                                                           class="form-control"
                                                                           required="required" id="new_password">
                                                                </div>
                                                            </div>
                                                            <div class="form-group has-success">
                                                                <label for="success" class="control-label">ป้อนรหัสผ่านใหม่อีกครั้ง
                                                                    Retype New Password</label>

                                                                <div class="">
                                                                    <input type="password" name="re_password"
                                                                           class="form-control"
                                                                           required="required" id="re_password">
                                                                </div>
                                                            </div>

                                                            <div class="form-group has-success">

                                                                <div class="">
                                                                    <button type="submit"
                                                                            class="btn btn-primary btn-block">
                                                                        Save
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <input id="action" name="action" type="hidden"
                                                                       value="CHG">
                                                            </div>
                                                            <div>
                                                                <input id="login_id" name="login_id" type="hidden">
                                                            </div>

                                                        </form>

                                                        <div id="result"></div>

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

    <script>
        $(document).ready(function () {
            $("form").on("submit", function (event) {
                event.preventDefault();
                let formValues = $(this).serialize();
                $.post("model/manage_account_process.php", formValues, function (response) {

                    if (response == 1) {
                        alertify.success("เปลี่ยนรหัสผ่านเรียบร้อยแล้ว กรุณาเข้าระบบด้วยรหัสผ่านใหม่อีกครั้ง");
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

        $('#re_password').blur(function () {
            if ($('#new_password').val() !== $('#re_password').val()) {
                alertify.error("กรุณาป้อนรหัสผ่านใหม่ 2 ครั้งให้เหมือนกัน !!! ");
            }
        });

    </script>


    <script>

        $(document).ready(function () {
            let username = '<?php echo $_SESSION['alogin']; ?>';
            let login_id = '<?php echo $_SESSION['login_id']; ?>';
            $('#username').val(username);
            $('#login_id').val(login_id);

        });

    </script>

    </body>

    </html>

<?php } ?>