<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$curr_date = date("d-m-Y");
include('includes/Header.php');
?>

<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">

    <?php // include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php // include('includes/Top-Bar.php'); ?>

            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">ข้อมูลสมาชิก</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item active">แก้ไข ข้อมูลสมาชิก</li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-body">
                                <form id="setting_form" method="POST" enctype="multipart/form-data">
                                    <div class="form-group has-success">
                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label for="house_number">บ้านเลขที่</label>
                                                <input type="text" name="house_number" id="house_number" class="form-control" readonly>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label for="f_name">ชื่อ</label>
                                                <input type="text" name="f_name" id="f_name" class="form-control">
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label for="l_name">นามสกุล</label>
                                                <input type="text" name="l_name" id="l_name" class="form-control">
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <label for="l_name">เข้าระบบผ่าน Web</label><br>
                                                <a href="https://ps33.themediathai.com" target="_blank">
                                                    <i class="fas fa-external-link-alt"></i> ps33.themediathai.com
                                                </a>
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="change_password_check">
                                                    <label class="form-check-label" for="change_password_check">เปลี่ยนรหัสผ่าน</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2" id="password_fields" style="display: none;">
                                            <div class="col-md-4">
                                                <label for="password">รหัสผ่านใหม่</label>
                                                <input type="password" name="password" id="password" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="confirm_password">ยืนยันรหัสผ่านใหม่</label>
                                                <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                                            </div>
                                        </div>

                                        <input type="hidden" id="line_user_id" name="line_user_id">
                                        <input type="hidden" id="line_phone" name="line_phone">

                                        <div id="loading" style="display: none; text-align: center; margin-top: 20px;">
                                            <img src="img/spin/spin_cir.gif" alt="Loading..." style="width: 50px;">
                                            <p>กำลังบันทึกข้อมูล...</p>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-block">บันทึกข้อมูล</button>
                                    </div>
                                </form>
                                <div id="result"></div>
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

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

<!-- Scripts -->

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="vendor/select2/dist/js/select2.min.js"></script>
<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
<script src="vendor/clock-picker/clockpicker.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="js/modal/show_department_modal.js"></script>
<script src="js/MyFrameWork/framework_util.js"></script>
<script src="line_oa/house/jsconfig/config_setting_member.js"></script>


<!-- LINE LIFF -->
<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
<script>
    liff.init({liffId: LIFF_ID}).then(() => {
        if (!liff.isLoggedIn()) {
            liff.login();
        } else {
            liff.getProfile().then(profile => {
                const userId = profile.userId;

                fetch('model/get_house_line_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'userId=' + encodeURIComponent(userId)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.house_number) {
                            $('#line_user_id').val(userId);
                            $('#line_phone').val(data.line_phone || '');
                            $('#house_number').val(data.house_number || '');
                            $('#f_name').val(data.f_name || '');
                            $('#l_name').val(data.l_name || '');
                        } else {
                            alert('ไม่พบผู้ใช้งานในระบบ กรุณาลงทะเบียนก่อน');
                            liff.closeWindow();
                        }
                    })
                    .catch(err => {
                        console.error('เกิดข้อผิดพลาด:', err);
                        alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                        liff.closeWindow();
                    });
            });
        }
    });
</script>

<script>
    $('#change_password_check').change(function () {
        $('#password_fields').toggle(this.checked);
        if (!this.checked) {
            $('#password').val('');
            $('#confirm_password').val('');
        }
    });

    $('#setting_form').on("submit", function (e) {
        e.preventDefault();

        if ($('#change_password_check').is(':checked')) {
            const pwd = $('#password').val().trim();
            const confirmPwd = $('#confirm_password').val().trim();
            if (!pwd || !confirmPwd) return alertify.error("กรุณากรอกรหัสผ่านให้ครบ");
            if (pwd !== confirmPwd) return alertify.error("รหัสผ่านไม่ตรงกัน");
        }

        const formData = new FormData(this);

        $.ajax({
            url: "model/manage_setting_member_process.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response == 1) {
                    alertify.success("บันทึกข้อมูลสำเร็จ");
                } else {
                    alertify.error("ไม่สามารถบันทึกข้อมูลได้");
                }
            },
            error: function () {
                alertify.error("เกิดข้อผิดพลาดในการส่งข้อมูล");
            }
        });
    });
</script>
</body>
</html>
