<!DOCTYPE html>
<html lang="th">

<?php
include('includes/Header.php');
?>

<style>
    body {
        font-family: 'Prompt', sans-serif;
    }
</style>

<script>
    $(document).ready(function () {
        $("button").click(function () {
            let username = $("#email").val();
            let password = $("#password").val();
            $.post("login_process.php",
                {
                    username: username,
                    password: password
                },
                function (response) {
                    if (response == 1) {
                        document.location = '<?php echo $_SESSION['dashboard_page']?>';
                    } else {
                        alertify.error("เข้าระบบไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง Invalid Details");
                    }

                });
        });
    });
</script>


<script type='text/javascript'>
    $(document).ready(function(){
        $('#check').click(function(){
            //alert($(this).is(':checked'));
            $(this).is(':checked') ? $('#password').attr('type', 'text') : $('#password').attr('type', 'password');
        });
    });
</script>



<body class="bg-gradient-login">
<!-- Login Content -->
<div class="container-login">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-12 col-md-9">
            <div class="card shadow-sm my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="login-form">
                                <div class="text-center">
                                    <div><img src="img/MyAdmin-Logo.png"/></div>
                                    <h1 class="h4 text-gray-900 mb-4">Login</h1>
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control" id="email"
                                           aria-describedby="emailHelp"
                                           placeholder="Enter Email Address">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="password"
                                           placeholder="Password">
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox small" style="line-height: 1.5rem;">
                                        <input type="checkbox" class="custom-control-input" id="check">
                                        <label class="custom-control-label" for="check">Display Password</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" name="login-submit" id="login-submit" tabindex="4"
                                            class="form-control btn btn-primary">
                                            <span class="spinner">
                                                <i class="icon-spin icon-refresh" id="spinner"></i></span>Log In
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>


