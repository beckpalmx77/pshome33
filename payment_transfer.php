<?php

session_start();
error_reporting(0);
$curr_date = date("d-m-Y");
include('includes/Header.php');

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">โอนเงินและแนบ Slip</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">โอนเงินและแนบ Slip</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between"></div>
                                <div class="card-body">
                                    <form id="transfer_form" method="POST" enctype="multipart/form-data">

                                        <div class="form-group has-success">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="form-group has-success">
                                                        <label for="payment_date"
                                                               class="control-label">วันที่ชำระ</label>
                                                        <i class="fa fa-calendar"
                                                           aria-hidden="true"></i>
                                                        <input type="text" class="form-control"
                                                               id="payment_date"
                                                               name="payment_date"
                                                               required="required"
                                                               value="<?php echo $curr_date ?>"
                                                               readonly="true"
                                                               placeholder="">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group has-success">
                                                        <label for="house_number"
                                                               class="control-label">บ้านเลขที่</label>
                                                        <input type="text" name="house_number" class="form-control"
                                                               required
                                                               id="house_number"
                                                               value="<?php echo $_SESSION['house_number']?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group has-success">
                                                        <label class="control-label">ตัวเลือกการชำระ</label>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                   name="payment_option" id="option_monthly"
                                                                   value="monthly" checked>
                                                            <label class="form-check-label" for="option_monthly">ชำระรายเดือน</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio"
                                                                   name="payment_option" id="option_yearly"
                                                                   value="yearly">
                                                            <label class="form-check-label" for="option_yearly">ชำระรายปี</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group has-success">
                                                        <label for="payment_type"
                                                               class="control-label">จำนวนเดือนที่ชำระ</label>
                                                        <input type="number" name="payment_type" class="form-control"
                                                               required
                                                               id="payment_type"
                                                               value="1">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- งวดปี และ งวดเดือน ในบรรทัดเดียวกัน -->
                                        <div class="form-group has-success">
                                            <div class="row">
                                                <!-- งวดเดือน -->
                                                <div class="col-md-4">
                                                    <label for="period_month_start" class="control-label">เริ่มงวดเดือน</label>
                                                    <select name="period_month_start" class="form-control" required
                                                            id="period_month_start">
                                                        <option value="">เลือกงวดเดือน</option>
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
                                                <div class="col-md-4">
                                                    <label for="period_month_to" class="control-label">ถึงงวดเดือน</label>
                                                    <select name="period_month_to" class="form-control" required
                                                            id="period_month_to">
                                                        <option value="">เลือกงวดเดือน</option>
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
                                                <!-- งวดปี -->
                                                <div class="col-md-4">
                                                    <label for="period_year" class="control-label">งวดปี</label>
                                                    <input type="number" name="period_year" class="form-control"
                                                           required id="period_year" placeholder=""
                                                           value="<?php echo date("Y") ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group has-success">
                                            <div class="row">
                                                <!-- งวดปี -->
                                                <div class="col-md-6">
                                                    <!-- ชื่อผู้โอน -->
                                                    <div class="form-group has-success">
                                                        <label for="detail" class="control-label">ชื่อผู้โอน</label>
                                                        <input type="text" name="detail" class="form-control" required
                                                               id="detail">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <!-- จำนวนเงินที่โอน -->
                                                    <div class="form-group has-success">
                                                        <label for="amount"
                                                               class="control-label">จำนวนเงินที่โอน</label>
                                                        <input type="number" name="amount" class="form-control"
                                                               required id="amount">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <!-- หมายเหตุ -->
                                        <div class="form-group has-success">
                                            <label for="remark" class="control-label">หมายเหตุ</label>
                                            <input name="remark" class="form-control" id="remark" value="">
                                        </div>

                                        <!-- แนบ Slip -->
                                        <div class="form-group has-success">
                                            <label for="picture_payment" class="control-label">แนบ Slip</label>
                                            <input type="file" name="picture_payment" class="form-control"
                                                   required id="picture_payment">
                                            <img id="preview_image" src="#" alt="Preview Image"
                                                 style="display: none; margin-top: 10px; max-width: 300px;"/>
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

    <script>
        $(document).ready(function () {
            // Preview Image
            $("#picture_payment").on("change", function () {
                const file = this.files[0];
                const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
                if (file && !allowedTypes.includes(file.type)) {
                    alert("กรุณาอัปโหลดไฟล์รูปภาพเท่านั้น (JPEG, PNG, GIF)");
                    this.value = "";
                } else if (file && file.size > 2 * 1024 * 1024) {
                    alert("ขนาดไฟล์ต้องไม่เกิน 2 MB");
                    this.value = "";
                } else {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $("#preview_image").attr("src", e.target.result).show();
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Submit Form with Loading Indicator
            $("#transfer_form").on("submit", function (event) {
                event.preventDefault();
                // ตรวจสอบว่า period_month_start <= period_month_to หรือไม่
                let period_month_start = parseInt($("#period_month_start").val());
                let period_month_to = parseInt($("#period_month_to").val());

                if (period_month_start > period_month_to) {
                    alertify.error("กรุณาตรวจสอบเดือนเริ่มต้นและเดือนสิ้นสุดให้ถูกต้อง (เริ่มต้นต้องน้อยกว่าหรือเท่ากับสิ้นสุด)");
                    return;  // หยุดการบันทึกข้อมูล
                }

                //$("#loading").show();
                let formData = new FormData(this);

                formData.append('period_month_start', document.getElementById('period_month_start').value);
                formData.append('period_month_to', document.getElementById('period_month_to').value);
                formData.append('payment_type', document.getElementById('payment_type').value);

                let dataToShow = '';

                // สร้างข้อความที่จะแสดงใน alert
                for (let [key, value] of formData.entries()) {
                    dataToShow += `${key}: ${value} | `;
                }

                // ลบตัว '|' สุดท้ายออก (ถ้ามี)
                if (dataToShow.endsWith(' | ')) {
                    dataToShow = dataToShow.slice(0, -3);
                }

                // แสดงค่าใน alert
                //alert(dataToShow);

                //alert($("#period_month_start").val() + " | " + $("#period_month_to").val());

                $.ajax({
                    url: "model/manage_payment_transfer.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        //$("#loading").hide();
                        if (response == 1) {
                            alertify.success("โอนเงินและส่ง Slip สำเร็จ");
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

    <script>
        $(document).ready(function () {
            function toggleFields() {
                if ($("#option_monthly").is(":checked")) {
                    $("#period_month_start").prop("disabled", false).val(1); // กำหนดเป็น มกราคม
                    $("#period_month_to").prop("disabled", false).val(1); // กำหนดเป็น มกราคม
                    $("#period_year").prop("disabled", false);
                    $("#payment_type").prop("disabled", false);
                    $("#payment_type").val(1);
                } else if ($("#option_yearly").is(":checked")) {
                    $("#period_month_start").prop("disabled", true).val(1); // กำหนดเป็น มกราคม
                    $("#period_month_to").prop("disabled", true).val(12);   // กำหนดเป็น ธันวาคม
                    $("#period_year").prop("disabled", false);
                    $("#payment_type").prop("disabled", true);
                    $("#period_month_start").val(1); // กำหนดเป็น มกราคม
                    $("#period_month_to").val(12); // กำหนดเป็น ธันวาคม
                    $("#payment_type").val(12);
                }
            }

            // Initial toggle state
            toggleFields();

            // Update on radio button change
            $("input[name='payment_option']").on("change", toggleFields);
        });
    </script>



    </body>
    </html>

<?php } ?>
