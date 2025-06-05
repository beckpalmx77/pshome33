<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$curr_date = date("d-m-Y");
include('includes/Header.php');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลสมาชิก</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            background-color: #ffe5e5;
            color: #4b0000;
            font-family: 'Sarabun', sans-serif;
        }

        .card {
            background-color: #fff0f0;
            border: 1px solid #ffb3b3;
            box-shadow: 0 4px 8px rgba(255, 100, 100, 0.2);
            border-radius: 1rem;
        }

        .form-control {
            border: 1px solid #ffb3b3;
            background-color: #fffafa;
        }

        .form-check-input:checked {
            background-color: #ff9999;
            border-color: #ff9999;
        }

        .btn-primary {
            background-color: #ff6666;
            border-color: #ff6666;
        }

        .btn-primary:hover {
            background-color: #ff4d4d;
            border-color: #ff4d4d;
        }

        label {
            color: #b30000;
            font-weight: 500;
        }

        .text-muted {
            color: #a94442 !important;
        }

        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .scroll-to-top {
            background-color: #ff9999;
        }

        #loading {
            text-align: center;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .profile-info {
                flex-direction: row;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container py-4">
                <div class="profile-header">
                    <h1 class="h5 text-gray-800">ข้อมูลสมาชิก</h1>
                    <div class="profile-info">
                        <img id="profilePic" src="" class="rounded-circle" width="50" height="50" alt="Profile Pic">
                        <div class="text-sm text-muted" id="user-info-liff1"></div>
                    </div>
                </div>

                <div class="card p-4">
                    <form id="setting_form" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="house_number" id="house_number" readonly>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="f_name">ชื่อ</label>
                                <input type="text" name="f_name" id="f_name" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label for="l_name">นามสกุล</label>
                                <input type="text" name="l_name" id="l_name" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>เข้าระบบผ่าน Web Browser ได้ที่:</label>
                            <p class="form-control-plaintext">https://ps33.themediathai.com</p>

                            <label for="line_phone">ชื่อผู้ใช้:</label>
                            <input type="text" name="line_phone" id="line_phone" class="form-control" readonly>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="change_password_check">
                            <label class="form-check-label" for="change_password_check">เปลี่ยนรหัสผ่าน</label>
                        </div>

                        <div class="row" id="password_fields" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label for="password">รหัสผ่านใหม่</label>
                                <input type="password" name="password" id="password" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" name="confirm_password" id="confirm_password"
                                       class="form-control">
                            </div>
                        </div>

                        <input type="hidden" id="line_user_id" name="line_user_id">

                        <div id="loading" style="display: none;">
                            <img src="img/spin/spin_cir.gif" alt="Loading..." style="width: 50px;">
                            <p>กำลังบันทึกข้อมูล...</p>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary px-5">บันทึกข้อมูล</button>
                        </div>
                    </form>
                    <div id="result" class="mt-3"></div>
                </div>
            </div>
        </div>

        <?php include('includes/Modal-Logout.php');
        include('includes/Footer.php'); ?>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

<!-- JS -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="line_oa/house/jsconfig/config_setting_member.js"></script>
<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>

<script>
    liff.init({liffId: LIFF_ID}).then(() => {
        if (!liff.isLoggedIn()) {
            liff.login();
        } else {
            liff.getProfile().then(profile => {
                const userId = profile.userId;
                const pictureUrl = profile.pictureUrl;
                const displayName = profile.displayName;

                fetch('model/save_user_profile.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `userId=${encodeURIComponent(userId)}&pictureUrl=${encodeURIComponent(pictureUrl)}&displayName=${encodeURIComponent(displayName)}`
                });

                fetch('model/get_house_line_user.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'userId=' + encodeURIComponent(userId)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.house_number) {
                            $('#line_user_id').val(userId);
                            $('#profilePic').attr('src', pictureUrl || "../img/user-001.png");
                            $('#line_phone').val(data.line_phone || '');
                            $('#house_number').val(data.house_number || '');
                            $('#f_name').val(data.f_name || '');
                            $('#l_name').val(data.l_name || '');
                            document.getElementById('user-info-liff1').innerText = `บ้านเลขที่: ${data.house_number}`;
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
            if (!pwd || !confirmPwd) {
                alert("กรุณากรอกรหัสผ่านให้ครบ");
                return;
            }
            if (pwd !== confirmPwd) {
                alert("รหัสผ่านไม่ตรงกัน");
                return;
            }
        }

        // Debug ข้อมูลในฟอร์มก่อนส่ง
        const formData = new FormData(this);

        // แสดงค่า key และ value ใน FormData
        for (let pair of formData.entries()) {
            console.log(pair[0]+ ': ' + pair[1]);
        }

        $.ajax({
            url: "model/manage_setting_member_process.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                console.log('Response:', response);
                if (response == 1) {
                    alert("บันทึกข้อมูลสำเร็จ");
                } else {
                    alert("ไม่สามารถบันทึกข้อมูลได้");
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert("เกิดข้อผิดพลาดในการส่งข้อมูล");
            }
        });
    });
</script>
</body>
</html>
