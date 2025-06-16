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
<!-- Content -->
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
                                    <h1 class="h4 text-gray-900 mb-4">SAC LOTTO</h1>
                                </div>

                                <!-- Form to save lotto data -->
                                <form id="lotto-period-form" method="POST" action="save_lotto_period.php">

                                    <div class="form-group">
                                        <label for="period_no">งวดวันที่</label>
                                        <select class="form-control" id="period_no" name="period_no" required>
                                            <option value="">เลือกวันที่</option>
                                            <option value="1">1</option>
                                            <option value="16">16</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="period_month">เดือน</label>
                                        <select class="form-control" id="period_month" name="period_month" required>
                                            <option value="">เลือกเดือน</option>
                                            <option value="1">มกราคม</option>
                                            <option value="2">กุมภาพันธ์</option>
                                            <option value="3">มีนาคม</option>
                                            <option value="4">เมษายน</option>
                                            <option value="5">พฤษภาคม</option>
                                            <option value="6">มิถุนายน</option>
                                            <option value="7">กรกฎาคม</option>
                                            <option value="8">สิงหาคม</option>
                                            <option value="9">กันยายน</option>
                                            <option value="10">ตุลาคม</option>
                                            <option value="11">พฤศจิกายน</option>
                                            <option value="12">ธันวาคม</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="period_year">ปีงวด</label>
                                        <input type="number" class="form-control" id="period_year" name="period_year"
                                               required>
                                    </div>

                                    <div class="form-group">
                                        <label for="lotto_type">ประเภทเลขท้าย</label>
                                        <select class="form-control" id="lotto_type" name="lotto_type" required>
                                            <option value="">เลือกประเภทรางวัล</option>
                                            <option value="2">เลขท้าย 3 ตัว</option>
                                            <option value="1">เลขท้าย 2 ตัว</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="lotto_number_result">เลขรางวัล</label>
                                        <input type="text" class="form-control" id="lotto_number_result"
                                               name="lotto_number_result" required>
                                    </div>

                                    <input type="hidden" id="action" name="action" value="SAVE_PERIOD_RESULT">

                                    <div class="form-group">
                                        <button type="submit" class="form-control btn btn-primary">
                                            <span class="spinner">
                                                <i class="icon-spin icon-refresh" id="spinner"></i>
                                            </span> บันทึกข้อมูล
                                        </button>
                                    </div>


                                    <div class="form-group">
                                        <button type="button" name="backBtn" id="backBtn" tabindex="4"
                                                class="form-control btn btn-danger">
                                            <span>
                                                <i class="fa fa-reply" aria-hidden="true"></i>
                                                กลับหน้าแรก
                                            </span>
                                    </div>

                                </form>

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
        // ส่งฟอร์มเพื่อบันทึกข้อมูล
        $("#lotto-period-form").submit(function (event) {
            event.preventDefault();  // หยุดการ submit แบบเดิม
            let formData = $(this).serialize();

            $.ajax({
                url: "model/manage_lotto_process.php",
                type: "POST",
                data: formData,
                beforeSend: function () {
                    // แสดง spinner ก่อนที่ข้อมูลจะถูกส่ง
                    $("#spinner").show();
                },
                success: function (response) {
                    // จัดการคำตอบจาก server
                    alert(response);  // หรือคุณสามารถเปลี่ยนเป็น redirect หรือการแสดงผลที่ต้องการ
                },
                complete: function () {
                    // ซ่อน spinner หลังจากที่ข้อมูลถูกส่ง
                    $("#spinner").hide();
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {
        let currentMonth = new Date().getMonth() + 1;
        $('#period_month').val(currentMonth);
    });
</script>

<script>
    $(document).ready(function () {
        let currentYear = new Date().getFullYear();
        $('#period_year').val(currentYear);
    });
</script>

<script>
    $(document).ready(function () {
        $("#backBtn").click(function () {
            window.location.href = "sac_lotto_dashboard";
        });
    });
</script>

</body>
</html>
