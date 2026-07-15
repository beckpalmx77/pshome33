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
                    <h1 class="h5 mb-0 text-gray-800">หมู่บ้านพฤกษา 33</h1>
                    <br>
                    <!-- โปรไฟล์และข้อมูลผู้ใช้ -->
                    <div class="d-flex align-items-center gap-3">
                        <img id="profilePic"
                             src=""
                             class="rounded-circle"
                             width="50"
                             height="50"
                             alt="Profile Pic"
                             style="margin-right: 3rem;">
                        <div class="text-sm text-muted" id="user-info-liff3"></div>
                    </div>
                </div>

                <!-- สถิติรถและสติกเกอร์ -->
                <div class="row mb-4">
                    <!--div class="col-xl-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            จำนวนรถที่ลงทะเบียน (คัน)
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalCars">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-car fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div-->

                    <input type="hidden" id="totalCars">

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            จำนวนรถที่รับสติกเกอร์แล้ว  (คัน)
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="carsWithSticker">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            จำนวนบ้านที่รับสติกเกอร์แล้ว  (หลัง)
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="stickerReceived">0</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-home fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <!--div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"></div-->
                            <div class="card-body">
                                <form id="sticker_form" method="POST" enctype="multipart/form-data">

                                    <div class="form-group has-success">
                                        <br>
                                        <button type="button" id="stickerBtn" class="btn btn-primary btn-block">
                                            สติกเกอร์ เข้า-ออก
                                        </button>

                                        <button type="button" id="petBtn" class="btn btn-info btn-block">
                                            ลงทะเบียนสัตว์เลี้ยง
                                        </button>

                                        <button type="button" id="greenBtn" class="btn btn-success btn-block" style="margin-top: 5px;">
                                            การลงมติของหมู่บ้าน
                                        </button>
                                    </div>
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

<script src="line_oa/house/jsconfig/config_sticker_pet_landing.js"></script>

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

    .btn-success {
        background-color: #6bc5a0;
        border-color: #6bc5a0;
    }

    .btn-success:hover {
        background-color: #5ab390;
        border-color: #5ab390;
    }

    .rounded-circle {
        border: 2px solid #cfaef7;
    }

</style>



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
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'userId=' + encodeURIComponent(userId)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.house_number) {
                            userHouseNumber = data.house_number;
                            document.getElementById('user-info-liff3').innerText = displayName;
                            $('#profilePic').attr('src', profile.pictureUrl || "../img/user-001.png");
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
<script>
    let userHouseNumber = '';

    $(document).ready(function() {

        // Load statistics
        loadStatistics();

        // When the "Sticker" button is clicked
        $('#stickerBtn').on('click', function() {
            // Redirect to the sticker page URL
            window.location.href = 'https://liff.line.me/2007370141-dLGLWqp1'; // <-- ‼️ **แก้ไข URL ของหน้าสติกเกอร์ที่นี่**
        });

        // When the "Pet Registration" button is clicked
        $('#petBtn').on('click', function() {
            // Redirect to the pet registration page URL
            window.location.href = 'https://liff.line.me/2007370141-WVrXGgKP'; // <-- ‼️ **แก้ไข URL ของหน้าลงทะเบียนสัตว์เลี้ยงที่นี่**
        });

        // When the green button is clicked
        $('#greenBtn').on('click', function() {
            window.location.href = 'https://liff.line.me/2007370141-JHWwGTRP?house_number=' + encodeURIComponent(userHouseNumber);
        });

    });

    function loadStatistics() {
        fetch('model/get_sticker_statistics.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('totalCars').innerText = data.total_cars || 0;
                document.getElementById('carsWithSticker').innerText = data.cars_with_sticker || 0;
                document.getElementById('stickerReceived').innerText = data.sticker_received || 0;
            })
            .catch(error => {
                console.error('Error loading statistics:', error);
            });
    }
</script>


</body>
</html>
