<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
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
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">ค้นหาข้อมูลทะเบียนรถ</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">ค้นหาข้อมูลทะเบียนรถ</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="col-md-12 col-md-offset-2">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="house_number" class="control-label">บ้านเลขที่</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control"
                                                                   id="house_number"
                                                                   name="house_number"
                                                                   placeholder="กรอกบ้านเลขที่">
                                                            <div class="input-group-append">
                                                                <button type="button" class="btn btn-info" id="btnSearch">
                                                                    <i class="fa fa-search"></i> ค้นหา
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">&nbsp;</label>
                                                        <button type="button" class="btn btn-success btn-block" id="btnPrint" disabled>
                                                            <i class="fa fa-print"></i> ใบคำขอ สติ๊กเกอร์
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">&nbsp;</label>
                                                        <a href="document/sticker_form_template.pdf" target="_blank" class="btn btn-primary btn-block">
                                                            <i class="fa fa-file-pdf-o"></i> เปิดแบบฟอร์มเปล่า PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2 mt-4" id="resultSection" style="display:none;">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">ข้อมูลบ้านเลขที่: <span id="displayHouseNumber"></span></h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">ชื่อผู้ติดต่อ</label>
                                                                <input type="text" class="form-control"
                                                                       id="contact_name" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">หมายเลขโทรศัพท์</label>
                                                                <input type="text" class="form-control"
                                                                       id="phone_number" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">ซอย</label>
                                                                <input type="text" class="form-control"
                                                                       id="alley" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-md-12">
                                                            <label class="control-label"><strong>ทะเบียนรถ</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 1</label>
                                                                <input type="text" class="form-control"
                                                                       id="car_no1" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 2</label>
                                                                <input type="text" class="form-control"
                                                                       id="car_no2" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 3</label>
                                                                <input type="text" class="form-control"
                                                                       id="car_no3" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 4</label>
                                                                <input type="text" class="form-control"
                                                                       id="car_no4" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">จำนวนรถ (คัน)</label>
                                                                <input type="text" class="form-control"
                                                                       id="car_count" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">ค่าจดทะเบียนรถเพิ่ม (บาท)</label>
                                                                <input type="text" class="form-control"
                                                                       id="extra_car_fee" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2 mt-4" id="notFoundSection" style="display:none;">
                                            <div class="alert alert-warning">
                                                ไม่พบข้อมูลบ้านเลขที่: <span id="notFoundHouseNumber"></span>
                                            </div>
                                        </div>

                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <?php include('includes/Footer.php'); ?>
                </div>
            </div>
        </div>

        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>

        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
        <script src="js/myadmin.min.js"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

        <script>
            let currentHouseNumber = '';
            
            $(document).ready(function () {
                $("#house_number").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            type: "POST",
                            url: 'model/manage_pet_record_process.php',
                            dataType: "json",
                            data: {action: "GET_HOUSE_AUTOCOMPLETE", search: request.term},
                            success: function (data) {
                                response(data);
                            }
                        });
                    },
                    minLength: 1,
                    select: function (event, ui) {
                        $("#house_number").val(ui.item.value);
                        $("#btnSearch").click();
                    }
                });
                
                $("#btnSearch").click(function () {
                    let house_number = $('#house_number').val();
                    if (house_number === '') {
                        alertify.warning("กรุณากรอกบ้านเลขที่");
                        return;
                    }

                    let formData = {action: "GET_DATA_BY_HOUSE", house_number: house_number};
                    $.ajax({
                        type: "POST",
                        url: 'model/manage_pet_record_process.php',
                        dataType: "json",
                        data: formData,
                        success: function (response) {
                            $('#resultSection').hide();
                            $('#notFoundSection').hide();
                            $('#btnPrint').prop('disabled', true);
                            
                            if (response && response.length > 0) {
                                let house = response[0];
                                currentHouseNumber = house.house_number;
                                $('#displayHouseNumber').text(house.house_number);
                                $('#contact_name').val(house.contact_name || '');
                                $('#phone_number').val(house.phone_number || '');
                                $('#alley').val(house.alley || '');
                                $('#car_no1').val(house.car_no1 || '');
                                $('#car_no2').val(house.car_no2 || '');
                                $('#car_no3').val(house.car_no3 || '');
                                $('#car_no4').val(house.car_no4 || '');
                                
                                let carCount = 0;
                                if (house.car_no1) carCount++;
                                if (house.car_no2) carCount++;
                                if (house.car_no3) carCount++;
                                if (house.car_no4) carCount++;
                                
                                let extraCarFee = 0;
                                if (carCount > 2) {
                                    extraCarFee = (carCount - 2) * 100;
                                }
                                
                                $('#car_count').val(carCount);
                                $('#extra_car_fee').val(extraCarFee);
                                
                                $('#resultSection').show();
                                $('#btnPrint').prop('disabled', false);
                            } else {
                                currentHouseNumber = '';
                                $('#notFoundHouseNumber').text(house_number);
                                $('#notFoundSection').show();
                            }
                        },
                        error: function (response) {
                            alertify.error("error : " + response);
                        }
                    });
                });

                $("#house_number").keypress(function (e) {
                    if (e.which == 13) {
                        $("#btnSearch").click();
                    }
                });

                $("#btnPrint").click(function () {
                    if (currentHouseNumber !== '') {
                        window.open('print_car_sticker.php?house_number=' + encodeURIComponent(currentHouseNumber), '_blank');
                    }
                });
            });
        </script>

    </body>
    </html>

<?php } ?>
