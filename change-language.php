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

                                                                <div class="form-group has-success">
                                                                    <label class="control-label" for="lang">เปลี่ยนภาษา
                                                                        Change Language</label>
                                                                    <div class=”form-group”>
                                                                        <select id="lang" name="lang"
                                                                                class="form-control"
                                                                                data-live-search="true"
                                                                                title="Please select">
                                                                            <option>th</option>
                                                                            <option>en</option>
                                                                        </select>
                                                                    </div>
                                                                    <span class="help-block"></span>
                                                                </div>
                                                                <div class="">
                                                                    <button type="submit"
                                                                            class="btn btn-primary btn-block">
                                                                        Save
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <input id="action" name="action" type="hidden"
                                                                       value="CHL">
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
                        alertify.success("เปลี่ยนภาษาเรียบร้อยแล้ว กรุณาเข้าระบบอีกครั้ง");
                    } else if (response == 2) {
                        alertify.error("XXX");
                    } else {
                        alertify.error("YYY");
                    }
                });
            });
        });
    </script>


    <script>

        $(document).ready(function () {
            let email = '<?php echo $_SESSION['alogin']; ?>';
            let login_id = '<?php echo $_SESSION['login_id']; ?>';
            let lang = '<?php echo $_SESSION['lang']; ?>';
            $('#email').val(email);
            $('#login_id').val(login_id);
            $('#lang').val(lang);
        });

    </script>

    </body>

    </html>

<?php } ?>