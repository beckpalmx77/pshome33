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
                    <h1 class="h5 mb-0 text-gray-800">สติกเกอร์ เข้า - ออก</h1>
                    <br>
                    <!-- โปรไฟล์และข้อมูลผู้ใช้ -->
                    <div class="d-flex align-items-center gap-3">
                        <img id="profilePic"
                             src=""
                             class="rounded-circle"
                             width="50"
                             height="50"
                             alt="Profile Pic"
                             style="margin-right: 3rem;">  <div class="text-sm text-muted" id="user-info-liff3"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <!--div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"></div-->
                            <div class="card-body">
                                <form id="sticker_form" method="POST" enctype="multipart/form-data">

                                    <div class="form-group has-success">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group has-success">
                                                    <label for="full_name" class="control-label">ชื่อ</label>
                                                    <input type="text" name="full_name" id="full_name" class="form-control" readonly="true">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group has-success">
                                                    <label for="house_number" class="control-label">บ้านเลขที่</label>
                                                    <input type="text" name="house_number" id="house_number" class="form-control" readonly="true">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
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

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no1">ทะเบียนรถ 1</label>
                                                    <input type="text" name="car_no1" id="car_no1" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no1_province">จังหวัด</label>
                                                    <input type="text" name="car_no1_province" id="car_no1_province" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no1_brand">ยี่ห้อ</label>
                                                    <input type="text" name="car_no1_brand" id="car_no1_brand" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no1_color">สี</label>
                                                    <input type="text" name="car_no1_color" id="car_no1_color" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no1_type">ประเภท</label>
                                                    <select name="car_no1_type" id="car_no1_type" class="form-control">
                                                        <option value="">-- เลือก --</option>
                                                        <option value="รถยนต์">รถยนต์</option>
                                                        <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no2">ทะเบียนรถ 2</label>
                                                    <input type="text" name="car_no2" id="car_no2" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no2_province">จังหวัด</label>
                                                    <input type="text" name="car_no2_province" id="car_no2_province" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no2_brand">ยี่ห้อ</label>
                                                    <input type="text" name="car_no2_brand" id="car_no2_brand" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no2_color">สี</label>
                                                    <input type="text" name="car_no2_color" id="car_no2_color" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no2_type">ประเภท</label>
                                                    <select name="car_no2_type" id="car_no2_type" class="form-control">
                                                        <option value="">-- เลือก --</option>
                                                        <option value="รถยนต์">รถยนต์</option>
                                                        <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no3">ทะเบียนรถ 3</label>
                                                    <input type="text" name="car_no3" id="car_no3" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no3_province">จังหวัด</label>
                                                    <input type="text" name="car_no3_province" id="car_no3_province" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no3_brand">ยี่ห้อ</label>
                                                    <input type="text" name="car_no3_brand" id="car_no3_brand" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no3_color">สี</label>
                                                    <input type="text" name="car_no3_color" id="car_no3_color" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no3_type">ประเภท</label>
                                                    <select name="car_no3_type" id="car_no3_type" class="form-control">
                                                        <option value="">-- เลือก --</option>
                                                        <option value="รถยนต์">รถยนต์</option>
                                                        <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no4">ทะเบียนรถ 4</label>
                                                    <input type="text" name="car_no4" id="car_no4" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no4_province">จังหวัด</label>
                                                    <input type="text" name="car_no4_province" id="car_no4_province" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no4_brand">ยี่ห้อ</label>
                                                    <input type="text" name="car_no4_brand" id="car_no4_brand" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no4_color">สี</label>
                                                    <input type="text" name="car_no4_color" id="car_no4_color" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no4_type">ประเภท</label>
                                                    <select name="car_no4_type" id="car_no4_type" class="form-control">
                                                        <option value="">-- เลือก --</option>
                                                        <option value="รถยนต์">รถยนต์</option>
                                                        <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no5">ทะเบียนรถ 5</label>
                                                    <input type="text" name="car_no5" id="car_no5" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no5_province">จังหวัด</label>
                                                    <input type="text" name="car_no5_province" id="car_no5_province" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no5_brand">ยี่ห้อ</label>
                                                    <input type="text" name="car_no5_brand" id="car_no5_brand" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no5_color">สี</label>
                                                    <input type="text" name="car_no5_color" id="car_no5_color" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="car_no5_type">ประเภท</label>
                                                    <select name="car_no5_type" id="car_no5_type" class="form-control">
                                                        <option value="">-- เลือก --</option>
                                                        <option value="รถยนต์">รถยนต์</option>
                                                        <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="area_size">พื้นที่บ้าน ตรว.</label>
                                                    <input type="text" name="area_size" id="area_size" class="form-control" readonly="true">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="common_fee">ค่าส่วนกลางรายเดือน (บาท)</label>
                                                    <input type="text" name="common_fee" id="common_fee" class="form-control" readonly="true">
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <input type="hidden" name="house_number_old" id="house_number_old" class="form-control">
                                    <input type="hidden" name="phone_number" id="phone_number" class="form-control">

                                    <!-- Loading Indicator -->
                                    <div id="loading"
                                         style="display: none; text-align: center; margin-top: 20px;">
                                        <img src="img/spin/spin_cir.gif" alt="Loading..." style="width: 50px;">
                                        <p>กำลังบันทึกข้อมูล...</p>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-block">บันทึกข้อมูล
                                    </button>
                                    <button type="button" id="BackBtn" class="btn btn-danger btn-block">
                                        กลับหน้าแรก
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<style>
    body {
        background-color: #f4f0f8; /* พื้นหลังม่วงพาสเทล */
        color: #4a235a;
    }

    .card {
        background-color: #fefaff; /* สีขาวอมม่วงอ่อน */
        border: 1px solid #e0cffa;
        border-radius: 9px;
        box-shadow: 0 4px 6px rgba(180, 155, 255, 0.1);
    }

    .card-body {
        padding-top: 0.25rem;
    }

    label.control-label {
        margin-top: 0;
        padding-top: 0;
        display: block;
    }


    .form-control {
        background-color: #ffffff;
        border: 1px solid #d8c4f2;
        border-radius: 8px;
    }

    .form-control:focus {
        border-color: #b292e6;
        box-shadow: 0 0 0 0.2rem rgba(178, 146, 230, 0.25);
    }

    .btn-primary {
        background-color: #a88bc8;
        border-color: #a88bc8;
    }

    .btn-primary:hover {
        background-color: #9673c4;
        border-color: #9673c4;
    }

    .text-gray-800 {
        color: #6b4699 !important;
    }

    .scroll-to-top {
        background-color: #c9a7f5;
        color: #fff;
    }

    .scroll-to-top:hover {
        background-color: #b792ec;
    }

    .rounded-circle {
        border: 2px solid #cfaef7;
    }

</style>

<script>
    $(document).ready(function () {
        $("#sticker_form").on("submit", function (event) {
            event.preventDefault();
            let formData = new FormData(this);

            $.ajax({
                url: "model/manage_house_sticker_process.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response == 1) {
                        alertify.success("บันทึกข้อมูล สำเร็จ");
                    } else {
                        alertify.error("ไม่สามารถบันทึกข้อมูลได้");
                    }
                },
                error: function () {
                    alertify.error("เกิดข้อผิดพลาดในการส่งข้อมูล");
                }
            });
        });

        function initProvinceAutocomplete(inputId) {
            $("#" + inputId).autocomplete({
                source: function (request, response) {
                    $.ajax({
                        type: "POST",
                        url: 'model/manage_pet_record_process.php',
                        data: {action: "GET_PROVINCE_AUTOCOMPLETE", search: request.term},
                        dataType: "json",
                        success: function (data) {
                            if (Array.isArray(data)) {
                                response(data.map(function(item) {
                                    return {label: item, value: item};
                                }));
                            } else {
                                response([]);
                            }
                        },
                        error: function () {
                            response([]);
                        }
                    });
                },
                minLength: 1
            });
        }

        initProvinceAutocomplete("car_no1_province");
        initProvinceAutocomplete("car_no2_province");
        initProvinceAutocomplete("car_no3_province");
        initProvinceAutocomplete("car_no4_province");
        initProvinceAutocomplete("car_no5_province");
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
                const pictureUrl = profile.pictureUrl;
                const displayName = profile.displayName;

                fetch('model/save_user_profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `userId=${encodeURIComponent(userId)}&pictureUrl=${encodeURIComponent(pictureUrl)}&displayName=${encodeURIComponent(displayName)}`
                });

                fetch('model/get_house_car_number.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'userId=' + encodeURIComponent(userId)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.house_number) {
                            document.getElementById('house_number').value = data.house_number || '';
                            document.getElementById('house_number_old').value = data.house_number || '';
                            document.getElementById('area_size').value = data.area_size || '';
                            document.getElementById('common_fee').value = data.common_fee || '';
                            document.getElementById('full_name').value = (data.f_name || '') + ' ' + (data.l_name || '');
                            document.getElementById('house_status').value = data.house_status || '';
                            document.getElementById('phone_number').value = data.phone_number || '';
                            document.getElementById('car_no1').value = data.car_no1 || '';
                            document.getElementById('car_no1_province').value = data.car_no1_province || '';
                            document.getElementById('car_no1_brand').value = data.car_no1_brand || '';
                            document.getElementById('car_no1_color').value = data.car_no1_color || '';
                            document.getElementById('car_no1_type').value = data.car_no1_type || '';
                            document.getElementById('car_no2').value = data.car_no2 || '';
                            document.getElementById('car_no2_province').value = data.car_no2_province || '';
                            document.getElementById('car_no2_brand').value = data.car_no2_brand || '';
                            document.getElementById('car_no2_color').value = data.car_no2_color || '';
                            document.getElementById('car_no2_type').value = data.car_no2_type || '';
                            document.getElementById('car_no3').value = data.car_no3 || '';
                            document.getElementById('car_no3_province').value = data.car_no3_province || '';
                            document.getElementById('car_no3_brand').value = data.car_no3_brand || '';
                            document.getElementById('car_no3_color').value = data.car_no3_color || '';
                            document.getElementById('car_no3_type').value = data.car_no3_type || '';
                            document.getElementById('car_no4').value = data.car_no4 || '';
                            document.getElementById('car_no4_province').value = data.car_no4_province || '';
                            document.getElementById('car_no4_brand').value = data.car_no4_brand || '';
                            document.getElementById('car_no4_color').value = data.car_no4_color || '';
                            document.getElementById('car_no4_type').value = data.car_no4_type || '';
                            document.getElementById('car_no5').value = data.car_no5 || '';
                            document.getElementById('car_no5_province').value = data.car_no5_province || '';
                            document.getElementById('car_no5_brand').value = data.car_no5_brand || '';
                            document.getElementById('car_no5_color').value = data.car_no5_color || '';
                            document.getElementById('car_no5_type').value = data.car_no5_type || '';
                            document.getElementById('user-info-liff3').innerText = displayName;
                            $('#profilePic').attr('src', profile.pictureUrl || "../img/user-001.png");
                        } else {
                            alert('ไม่พบผู้ใช้งานในระบบ กรุณาลงทะเบียนก่อน');
                            liff.closeWindow();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error');
                        liff.closeWindow();
                    });
            });
        }
});

    $('#BackBtn').on('click', function() {
        window.location.href = 'https://liff.line.me/2007370141-AxBy7eGD';
    });
</script>

</body>
</html>
