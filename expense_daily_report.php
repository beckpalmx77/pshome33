<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>

                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="row">
                                            <div class="col-md-12 col-md-offset-2">
                                                <div class="panel">
                                                    <div class="panel-body">

                                                        <form id="from_data" name="from_data" method="post" action=""
                                                              enctype="multipart/form-data">
                                                            <div class="modal-body">
                                                                <h5 class="modal-title">รายงานสรุปค่าใช้จ่ายประจำวัน</h5>

                                                                <!-- วันที่เอกสาร -->
                                                                <div class="form-group row mb-3">
                                                                    <div class="col-sm-3">
                                                                        <label for="doc_date_start"
                                                                               class="control-label">จากวันที่ใช้จ่าย</label>
                                                                        <div class="input-group">
                                                                            <input type="text" class="form-control"
                                                                                   id="doc_date_start"
                                                                                   name="doc_date_start"
                                                                                   required="required" readonly="true"
                                                                                   placeholder="จากวันที่">
                                                                            <div class="input-group-append">
                                                                            <span class="input-group-text">
                                                                            <i class="fa fa-calendar"
                                                                               aria-hidden="true"></i>
                                                                            </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-3">
                                                                        <label for="doc_date_to" class="control-label">ถึงวันที่ใช้จ่าย</label>
                                                                        <div class="input-group">
                                                                            <input type="text" class="form-control"
                                                                                   id="doc_date_to" name="doc_date_to"
                                                                                   required="required" readonly="true"
                                                                                   placeholder="ถึงวันที่">
                                                                            <div class="input-group-append">
                                                                            <span class="input-group-text">
                                                                            <i class="fa fa-calendar"
                                                                               aria-hidden="true"></i>
                                                                            </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- วิธีชำระเงิน -->
                                                                <div class="form-group row mb-3">
                                                                    <input type="hidden" id="payment_method" name="payment_method" value="">
                                                                    <label for="payment_method_option" class="col-sm-2 col-form-label">วิธีชำระเงิน:</label>
                                                                    <div class="col-sm-4">
                                                                        <select class="form-control" name="payment_method_option" id="payment_method_option" required>
                                                                            <option selected value="cash">เงินสด</option>
                                                                            <option value="bank">เงินโอน</option>
                                                                            <option value="all">ทั้งหมด</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <!-- Footer -->
                                                                <div class="modal-footer">
                                                                    <input type="hidden" name="id" id="id"/>
                                                                    <input type="hidden" name="save_status"
                                                                           id="save_status"/>
                                                                    <input type="hidden" name="action" id="action"
                                                                           value=""/>

                                                                    <button type="button" class="btn btn-primary"
                                                                            id="btnDisplay" onclick="Print_Data();">
                                                                        Report <i class="fa fa-check"></i>
                                                                    </button>

                                                                    <!-- Spin Loader -->
                                                                    <div id="spinner" class="spinner-overlay"
                                                                         style="display:none;">
                                                                        <div class="spinner-border text-primary"
                                                                             role="status">
                                                                            <span class="sr-only">Loading...</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>


                                                        <div id="result"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /.col-md-8 col-md-offset-2 -->
                                        </div>
                                        <!-- /.row -->

                                     </section>


                                </div>

                            </div>

                        </div>

                    </div>
                    <!--Row-->

                </div>

                <!---Container Fluid-->

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
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Bootstrap Datepicker -->
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->
    <script src="vendor/clock-picker/clockpicker.js"></script>
    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="js/MyFrameWork/framework_util.js"></script>
    <script src="js/util.js"></script>

    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-selection--single {
            height: 38px !important;
            padding: 0.375rem 0.75rem !important;
            font-size: 1rem !important;
            line-height: 1.5 !important;
        }

        .select2-selection__rendered {
            line-height: 38px !important;
        }

        .select2-selection__arrow {
            height: 38px !important;
        }
    </style>

    <style>
        /* Spin Loader CSS */
        #spinner {
            display: none;
            position: fixed;
            z-index: 999;
            left: 50%;
            top: 50%;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        $(document).ready(function () {
            // ตั้งค่าวันที่ปัจจุบันเป็นค่าเริ่มต้น
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date_start').val(doc_date);
            $('#doc_date_to').val(doc_date);

            // ตั้งค่า datepicker สำหรับวันที่เริ่มต้นและสิ้นสุด
            $('#doc_date_start, #doc_date_to').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        function Print_Data() {
            // แสดง loader
            document.getElementById('spinner').style.display = 'block';

            // ตั้งค่าการส่งแบบฟอร์ม
            document.forms['from_data'].target = '_blank'; // กำหนดให้ form ส่งไปที่หน้าต่างใหม่
            document.forms['from_data'].action = 'show_expense_daily';
            document.forms['from_data'].submit();

            // ซ่อน loader หลังจากการส่ง
            setTimeout(function () {
                document.getElementById('spinner').style.display = 'none';
            }, 3000);

            return true;
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectPayment = document.getElementById('payment_method_option');
            const hiddenPayment = document.getElementById('payment_method');

            // ตั้งค่าเริ่มต้นตอนโหลดหน้า (ให้ตรงกับค่า selected ใน select)
            hiddenPayment.value = selectPayment.value;

            // เมื่อมีการเปลี่ยนแปลงใน select
            selectPayment.addEventListener('change', function() {
                hiddenPayment.value = this.value;
            });
        });
    </script>

    </body>
    </html>

<?php } ?>