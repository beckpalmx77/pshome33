<!DOCTYPE html>
<html lang="th">

<?php
include('includes/Header.php');
include('includes/CheckDevice.php');
?>

<style>
    body {
        font-family: 'Prompt', sans-serif;
    }
</style>

<style type="text/css">
    .toggleeye {
        float: right;
        margin-right: 6px;
        margin-top: -20px;
        position: relative;
        z-index: 2;
        color: darkgrey;
    }
</style>


<script>

    $(document).ready(function () {
        let username = '<?php if (isset($_COOKIE["username"])) {
            echo $_COOKIE["username"];
        } ?>';
        let password = '<?php if (isset($_COOKIE["password"])) {
            echo $_COOKIE["password"];
        } ?>';
        let remember_chk = '<?php echo $_COOKIE["remember_chk"]?>';

        $("#username").val(username);
        $("#password").val(password);

        if (remember_chk === "check") {
            $("#remember").prop('checked', true); // Checked
        }

    });

</script>

<script>
    $(document).ready(function () {
        $("button").click(function () {
            let username = $("#username").val();
            let password = $("#password").val();
            let remember = "";

            if ($("#remember").prop("checked")) {
                remember = $("#remember").val();
            }

            if (username != "" && password != "") {
                $.ajax
                ({
                    type: 'post',
                    url: 'lotto_login_process.php',
                    data: {
                        username: username,
                        password: password,
                        remember: remember,
                    },
                    success: function (response) {
                        if (response !== "0") {
                            window.location.href = response;
                        } else {
                            alert("เข้าระบบไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง");
                            window.location.href = "login.php";
                        }
                    }
                });
            } else {
                alert("Please Fill All The Details");
            }

            return false;
        });
    });

</script>


<script type='text/javascript'>
    $(document).ready(function () {
        $('#togglePassword').click(function () {
            //alert($(this).is(':checked'));
            $('#password').attr('type') === 'password' ? $('#password').attr('type', 'text') : $('#password').attr('type', 'password');
        });
    });
</script>

<body class="bg-gradient-login">
<!-- Login Content -->
<div class="container-login">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-6 col-md-9">
            <div class="card shadow-sm my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="login-form">
                                <div class="text-center">
                                    <div><img src="img/logo/logo text-01.png" width="400" height="158"/></div>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="username"
                                           value=""
                                           placeholder="Enter User Name">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="password"
                                           value=""
                                           placeholder="Password">
                                    <span class="far fa-eye toggleeye" id="togglePassword"
                                          style="cursor: pointer;"></span>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="on" id="remember"
                                                   name="remember">
                                            <label class="form-check-label" for="remember">
                                                <?php if ($_SESSION['deviceType'] == 'computer') { ?>
                                                    <p style="color:blue;">Remember Me 30 Days</p>
                                                <?php } else { ?>
                                                    <p style="color:red;">Remember Me 30 Days</p>
                                                <?php } ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" name="login-submit" id="login-submit" tabindex="4"
                                            class="form-control btn btn-primary">
                                            <span class="spinner">
                                                <i class="icon-spin icon-refresh" id="spinner"></i></span> Log In
                                </div>
                                <div class="form-group">
                                    <button type="button" name="backBtn" id="backBtn" tabindex="4"
                                            class="form-control btn btn-danger">
                                            <span>
                                                <i class="fa fa-reply" aria-hidden="true"></i>
                                                กลับหน้าแรก
                                            </span>
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
    $(document).ready(function () {
        $("#backBtn").click(function () {
            window.location.href = "sac_lotto";
        });
    });
</script>

</body>
</html>


