<?php
// login.php

// ----------------------------------------------------------------------
// 1. PHP Logic & Security Checks
// ----------------------------------------------------------------------

// ตรวจสอบและดึงค่าจาก Cookie พร้อมทำ Security Escaping เพื่อป้องกัน XSS
// **สำคัญ: เราจะไม่ดึงรหัสผ่านจาก Cookie เด็ดขาด**
$username_cookie = isset($_COOKIE["username"]) ? htmlspecialchars($_COOKIE["username"], ENT_QUOTES, 'UTF-8') : '';
$remember_chk_cookie = isset($_COOKIE["remember_chk"]) ? htmlspecialchars($_COOKIE["remember_chk"], ENT_QUOTES, 'UTF-8') : '';

// สมมติว่า includes/Header.php และ includes/CheckDevice.php มีอยู่จริง
include('includes/Header.php');
include('includes/CheckDevice.php');

// สมมติว่า $_SESSION['deviceType'] ถูกกำหนดใน CheckDevice.php
$device_class = (isset($_SESSION['deviceType']) && $_SESSION['deviceType'] == 'computer') ? 'color-blue' : 'color-red';

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ระบบบริหารงานนิติบุคคลหมู่บ้าน</title>
    <style>
        body {
            font-family: 'Prompt', sans-serif;
        }
        /* Custom CSS Classes */
        .color-blue {
            color: blue;
        }
        .color-red {
            color: red;
        }
        .toggleeye {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 2;
            color: darkgrey;
        }
    </style>
</head>

<body>
<div class="container-login">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-12 col-md-9">
            <div class="card shadow-sm my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="login-form">
                                <div class="text-center">
                                    <div>
                                        <img src="img/logo/logo text-01.png" class="img-fluid" alt="Logo" style="max-width: 100%; height: auto;">
                                    </div>
                                    <h1 class="h4 text-gray-900 mb-4">ระบบบริหารงานนิติบุคคลหมู่บ้าน พฤกษา 33</h1>
                                </div>
                                <div class="form-group">
                                    <label for="username">ชื่อผู้ใช้</label>
                                    <input type="text" class="form-control" id="username"
                                           value=""
                                           placeholder="Enter User Name">
                                </div>

                                <div class="form-group">
                                    <label for="password">รหัสผ่าน</label>
                                    <div style="position: relative;">
                                        <input type="password" class="form-control" id="password"
                                               value=""
                                               placeholder="Password">
                                        <span class="far fa-eye toggleeye" id="togglePassword"></span>
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="check" id="remember"
                                                   name="remember">
                                            <label class="form-check-label" for="remember">
                                                <p class="<?php echo $device_class; ?>">Remember Me 30 Days</p>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" name="login-submit" id="login-submit" tabindex="4"
                                            class="form-control btn btn-primary">
                                            <span class="spinner">
                                                <i class="icon-spin icon-refresh" id="spinner"></i></span> Log In
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // ฟังก์ชันหลักสำหรับตรวจสอบและส่งข้อมูล Login
    function check_login() {
        let username = $("#username").val();
        let password = $("#password").val();
        let remember = "";

        if ($("#remember").prop("checked")) {
            remember = $("#remember").val();
        }

        if (username !== "" && password !== "") {
            $.ajax
            ({
                type: 'post',
                url: 'login_process.php',
                data: {
                    username: username,
                    password: password, // ส่งรหัสผ่านไป Server เพื่อ HASH และตรวจสอบ
                    remember: remember,
                },
                success: function (response) {
                    if (response !== "0") {
                        // Login สำเร็จ - Redirect ไปหน้าตามที่ Server ระบุ
                        window.location.href = response;
                    } else {
                        // Login ไม่สำเร็จ
                        alert("เข้าระบบไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง");
                        $("#password").val(""); // เคลียร์ช่องรหัสผ่าน
                        // ไม่ต้อง Redirect ซ้ำไปหน้า login.php
                    }
                },
                error: function(xhr, status, error) {
                    // จัดการข้อผิดพลาดเมื่อเกิดปัญหาในการเชื่อมต่อ AJAX
                    alert("เกิดข้อผิดพลาดในการเชื่อมต่อ: " + error);
                }
            });
        } else {
            alert("กรุณากรอกข้อมูลให้ครบถ้วน");
        }

        return false;
    }

    // รวม Event Handlers ทั้งหมดไว้ใน $(document).ready() เดียว
    $(document).ready(function () {
        // 1. กำหนดค่าเริ่มต้นจาก Cookie
        let username = '<?php echo $username_cookie; ?>';
        let remember_chk = '<?php echo $remember_chk_cookie; ?>';

        $("#username").val(username);
        if (remember_chk === "check") {
            $("#remember").prop('checked', true);
        }

        // 2. ผูก Event กับปุ่ม Login (เมื่อคลิก)
        $("#login-submit").click(function () {
            check_login();
        });

        // 3. ผูก Event กด Enter (Key Up)
        $(document).keyup(function(event) {
            if (event.which === 13) {
                check_login();
            }
        });

        // 4. Toggle Password (ซ่อน/แสดงรหัสผ่าน)
        $('#togglePassword').click(function () {
            let passwordField = $('#password');
            let fieldType = passwordField.attr('type');

            if (fieldType === 'password') {
                passwordField.attr('type', 'text');
                $(this).removeClass('far fa-eye').addClass('far fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                $(this).removeClass('far fa-eye-slash').addClass('far fa-eye');
            }
        });
    });
</script>

</body>
</html>