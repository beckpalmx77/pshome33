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
    <!--?php include('includes/Side-Bar.php'); ?-->

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!--?php include('includes/Top-Bar.php'); ?-->

            <!-- Container Fluid-->
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">สติกเกอร์ เข้า - ออก</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">สติกเกอร์ เข้า - ออก</li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"></div>
                            <div class="card-body">
                                <form id="sticker_form" method="POST" enctype="multipart/form-data">

                                    <div class="form-group has-success">

                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="form-group has-success">
                                                    <label for="full_name" class="control-label">ชื่อ</label>
                                                    <input type="text" name="full_name" id="full_name" class="form-control" readonly="true">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="form-group has-success">
                                                    <label for="house_number" class="control-label">บ้านเลขที่</label>
                                                    <input type="text" name="house_number" id="house_number" class="form-control" readonly="true">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="form-group has-success">
                                                    <label for="house_status" class="control-label">สถานะที่อยู่อาศัย</label>
                                                    <select name="house_status" id="house_status" class="form-control">
                                                        <option value="-">-</option>
                                                        <option value="O">บ้านตนเอง - ครอบครัว</option>
                                                        <option value="R">บ้านเช่า</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no1">ทะเบียนรถ 1</label>
                                                    <input type="text" name="car_no1" id="car_no1" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no2">ทะเบียนรถ 2</label>
                                                    <input type="text" name="car_no2" id="car_no2" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <!--div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no3">ทะเบียนรถ 3</label>
                                                    <input type="text" name="car_no3" id="car_no3" class="form-control">
                                                </div>
                                            </div>
                                        </div-->

                                        <!--div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no4">ทะเบียนรถ 4</label>
                                                    <input type="text" name="car_no4" id="car_no4" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no5">ทะเบียนรถ 5</label>
                                                    <input type="text" name="car_no5" id="car_no5" class="form-control">
                                                </div>
                                            </div>
                                        </div-->


                                    </div>

                                    <!-- Loading Indicator -->
                                    <div id="loading"
                                         style="display: none; text-align: center; margin-top: 20px;">
                                        <img src="img/spin/spin_cir.gif" alt="Loading..." style="width: 50px;">
                                        <p>กำลังบันทึกข้อมูล...</p>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block">บันทึกข้อมูล
                                    </button>
                                </form>

                                <div id="result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container Fluid-->

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
<script src="vendor/select2/dist/js/select2.min.js"></script>
<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
<script src="vendor/clock-picker/clockpicker.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="js/modal/show_department_modal.js"></script>
<script src="js/MyFrameWork/framework_util.js"></script>

<script src="line_oa/house/jsconfig/config_house_sticker.js"></script>

<script>
    $(document).ready(function () {
        // Submit Form with Loading Indicator
        $("#sticker_form").on("submit", function (event) {
            event.preventDefault();
            //$("#loading").show();
            let formData = new FormData(this);

            let dataToShow = '';

            // สร้างข้อความที่จะแสดงใน alert
            for (let [key, value] of formData.entries()) {
                dataToShow += `${key}: ${value} | `;
            }

            // ลบตัว '|' สุดท้ายออก (ถ้ามี)
            if (dataToShow.endsWith(' | ')) {
                dataToShow = dataToShow.slice(0, -3);
            }

            $.ajax({
                url: "model/manage_house_sticker_process.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    //$("#loading").hide();
                    if (response == 1) {
                        alertify.success("บันทึกข้อมูล สำเร็จ");
                    } else {
                        alertify.error("ไม่สามารถบันทึกข้อมูลได้");
                    }
                },
                error: function () {
                    //$("#loading").hide();
                    alertify.error("เกิดข้อผิดพลาดในการส่งข้อมูล");
                }
            });
        });
    });
</script>

<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
<script>
    liff.init({liffId: LIFF_ID}).then(() => {
        if (!liff.isLoggedIn()) {
            liff.login();
        } else {
            liff.getProfile().then(profile => {
                const userId = profile.userId;

                //alert(userId);

                fetch('model/get_house_car_number.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'userId=' + encodeURIComponent(userId)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.house_number) {
                            document.getElementById('house_number').value = data.house_number || '';
                            document.getElementById('full_name').value = `${data.f_name || ''} ${data.l_name || ''}`;
                            document.getElementById('house_status').value = data.house_status || '';
                            document.getElementById('car_no1').value = data.car_no1 || '';
                            document.getElementById('car_no2').value = data.car_no2 || '';

                        } else {
                            alert('ไม่พบผู้ใช้งานในระบบ กรุณาลงทะเบียนก่อน');
                            liff.closeWindow();
                        }
                    })
                    .catch(error => {
                        console.error('เกิดข้อผิดพลาด:', error);
                        alert('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                        liff.closeWindow();
                    });
            });
        }
    });

</script>

</body>
</html>