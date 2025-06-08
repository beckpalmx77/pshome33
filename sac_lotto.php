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
                                    <button type="button" id="customer-submit" class="form-control btn btn-primary">
                                        <span class="spinner">
                                            <i class="icon-spin icon-refresh" id="spinner"></i>
                                        </span>ลงทะเบียนร้านค้า
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="button" id="check-customer-submit" class="form-control btn btn-primary">
                                        <span class="spinner">
                                            <i class="icon-spin icon-refresh" id="spinner"></i>
                                        </span>ตรวจสอบการลงทะเบียน
                                    </button>
                                </div>

                                <div class="form-group">
                                    <button type="button" id="sac-submit" class="form-control btn btn-primary">
                                        <span class="spinner">
                                            <i class="icon-spin icon-refresh" id="spinner"></i>
                                        </span>ผู้ดูแลระบบ
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

<!-- jQuery และ Script -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $("#sac-submit").click(function () {
            window.location.href = "sac_lotto_login";
        });

        $("#customer-submit").click(function () {
            window.location.href = "sac_lotto_select";
        });

        $("#check-customer-submit").click(function () {
            window.location.href = "search_lotto_data";
        });
    });
</script>

</body>
</html>
