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
                        <h1 class="h3 mb-0 text-gray-800">บันทึกข้อมูลทะเบียนรถ (รับสติกเกอร์)</h1>
                        <ol class="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                                </li>
                                <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                                <li class="breadcrumb-item active"
                                    aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                            </ol>
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
                                                                <button type="button" class="btn btn-success" id="btnSave" style="display:none;">
                                                                    <i class="fa fa-save"></i> บันทึก
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">&nbsp;</label>
                                                        <button type="button" class="btn btn-success btn-block" id="btnPrint" disabled>
                                                            <i class="fa fa-print"></i> ใบคำขอ สติกเกอร์
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
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">จำนวนรถ (คัน)</label>
                                                                <input type="text" class="form-control"
                                                                       id="car_count" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">ค่า สติกเกอร์ ทะเบียนรถเพิ่ม (บาท)</label>
                                                                <input type="text" class="form-control"
                                                                       id="extra_car_fee" readonly>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">สถานะการรับสติกเกอร์</label>
<div class="form-check">
                                                                    <input type="checkbox" class="form-check-input" id="sticker_receive_status" value="Y">
                                                                    <label class="form-check-label" for="sticker_receive_status">รับแล้ว</label>
                                                                </div>

                                                                <div class="modal fade" id="stickerReceivedModal" tabindex="-1" role="dialog">
                                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header bg-info text-white">
                                                                                <h5 class="modal-title">แจ้งเตือน</h5>
                                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                                    <span aria-hidden="true">&times;</span>
                                                                                </button>
                                                                            </div>
                                                                            <div class="modal-body text-center">
                                                                                <h4>รับสติกเกอร์แล้ว</h4>
                                                                            </div>
                                                                            <div class="modal-footer justify-content-center">
                                                                                <button type="button" class="btn btn-primary" data-dismiss="modal">ตกลง</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="control-label">วันที่รับ</label>
                                                                <input type="text" class="form-control" id="sticker_receive_date" readonly>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="col-md-12">
                                                            <label class="control-label"><strong>ทะเบียนรถ</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 1</label>
                                                                <input type="text" class="form-control" id="car_no1">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">จังหวัด</label>
                                                                <input type="text" class="form-control" id="car_no1_province">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ยี่ห้อ-รุ่น</label>
                                                                <input type="text" class="form-control" id="car_no1_brand">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">สี</label>
                                                                <input type="text" class="form-control" id="car_no1_color">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ประเภท</label>
                                                                <select class="form-control" id="car_no1_type">
                                                                    <option value="">-- เลือก --</option>
                                                                    <option value="รถยนต์">รถยนต์</option>
                                                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 2</label>
                                                                <input type="text" class="form-control" id="car_no2">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">จังหวัด</label>
                                                                <input type="text" class="form-control" id="car_no2_province">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ยี่ห้อ-รุ่น</label>
                                                                <input type="text" class="form-control" id="car_no2_brand">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">สี</label>
                                                                <input type="text" class="form-control" id="car_no2_color">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ประเภท</label>
                                                                <select class="form-control" id="car_no2_type">
                                                                    <option value="">-- เลือก --</option>
                                                                    <option value="รถยนต์">รถยนต์</option>
                                                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 3</label>
                                                                <input type="text" class="form-control" id="car_no3">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">จังหวัด</label>
                                                                <input type="text" class="form-control" id="car_no3_province">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ยี่ห้อ-รุ่น</label>
                                                                <input type="text" class="form-control" id="car_no3_brand">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">สี</label>
                                                                <input type="text" class="form-control" id="car_no3_color">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ประเภท</label>
                                                                <select class="form-control" id="car_no3_type">
                                                                    <option value="">-- เลือก --</option>
                                                                    <option value="รถยนต์">รถยนต์</option>
                                                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 4</label>
                                                                <input type="text" class="form-control" id="car_no4">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">จังหวัด</label>
                                                                <input type="text" class="form-control" id="car_no4_province">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ยี่ห้อ-รุ่น</label>
                                                                <input type="text" class="form-control" id="car_no4_brand">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">สี</label>
                                                                <input type="text" class="form-control" id="car_no4_color">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ประเภท</label>
                                                                <select class="form-control" id="car_no4_type">
                                                                    <option value="">-- เลือก --</option>
                                                                    <option value="รถยนต์">รถยนต์</option>
                                                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 5</label>
                                                                <input type="text" class="form-control" id="car_no5">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">จังหวัด</label>
                                                                <input type="text" class="form-control" id="car_no5_province">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ยี่ห้อ-รุ่น</label>
                                                                <input type="text" class="form-control" id="car_no5_brand">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">สี</label>
                                                                <input type="text" class="form-control" id="car_no5_color">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ประเภท</label>
                                                                <select class="form-control" id="car_no5_type">
                                                                    <option value="">-- เลือก --</option>
                                                                    <option value="รถยนต์">รถยนต์</option>
                                                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 6</label>
                                                                <input type="text" class="form-control" id="car_no6">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">จังหวัด</label>
                                                                <input type="text" class="form-control" id="car_no6_province">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ยี่ห้อ-รุ่น</label>
                                                                <input type="text" class="form-control" id="car_no6_brand">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">สี</label>
                                                                <input type="text" class="form-control" id="car_no6_color">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ประเภท</label>
                                                                <select class="form-control" id="car_no6_type">
                                                                    <option value="">-- เลือก --</option>
                                                                    <option value="รถยนต์">รถยนต์</option>
                                                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ทะเบียนรถ 7</label>
                                                                <input type="text" class="form-control" id="car_no7">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">จังหวัด</label>
                                                                <input type="text" class="form-control" id="car_no7_province">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ยี่ห้อ-รุ่น</label>
                                                                <input type="text" class="form-control" id="car_no7_brand">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">สี</label>
                                                                <input type="text" class="form-control" id="car_no7_color">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label">ประเภท</label>
                                                                <select class="form-control" id="car_no7_type">
                                                                    <option value="">-- เลือก --</option>
                                                                    <option value="รถยนต์">รถยนต์</option>
                                                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2 mt-3" id="notFoundSection" style="display:none;">
                                            <div class="alert alert-warning">
                                                ไม่พบข้อมูลบ้านเลขที่: <span id="notFoundHouseNumber"></span>
                                            </div>
                                        </div>

                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    ?>

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
                            data: {action: "GET_HOUSE_AUTOCOMPLETE", search: request.term},
                            dataType: "json",
                            success: function (data) {
                                console.log('Autocomplete data:', data);
                                // Transform to autocomplete format if needed
                                if (Array.isArray(data)) {
                                    response(data.map(function(item) {
                                        return {label: item, value: item};
                                    }));
                                } else {
                                    response([]);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.log('Autocomplete Error:', status, error, xhr.responseText);
                                response([]);
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
                        data: formData,
                        dataType: "json",
                        success: function (response) {
                            $('#resultSection').hide();
                            $('#notFoundSection').hide();
                            $('#btnPrint').prop('disabled', true);
                            $('#btnSave').hide();

                            if (response && response.length > 0) {
                                let house = response[0];
                                currentHouseNumber = house.house_number;
                                $('#displayHouseNumber').text(house.house_number);
                                $('#contact_name').val(house.contact_name || '');
                                $('#phone_number').val(house.phone_number || '');
                                $('#alley').val(house.alley || '');
                                $('#car_no1').val(house.car_no1 || '');
                                $('#car_no1_province').val(house.car_no1_province || '');
                                $('#car_no1_brand').val(house.car_no1_brand || '');
                                $('#car_no1_color').val(house.car_no1_color || '');
                                $('#car_no1_type').val(house.car_no1_type || '');
                                $('#car_no2').val(house.car_no2 || '');
                                $('#car_no2_province').val(house.car_no2_province || '');
                                $('#car_no2_brand').val(house.car_no2_brand || '');
                                $('#car_no2_color').val(house.car_no2_color || '');
                                $('#car_no2_type').val(house.car_no2_type || '');
                                $('#car_no3').val(house.car_no3 || '');
                                $('#car_no3_province').val(house.car_no3_province || '');
                                $('#car_no3_brand').val(house.car_no3_brand || '');
                                $('#car_no3_color').val(house.car_no3_color || '');
                                $('#car_no3_type').val(house.car_no3_type || '');
                                $('#car_no4').val(house.car_no4 || '');
                                $('#car_no4_province').val(house.car_no4_province || '');
                                $('#car_no4_brand').val(house.car_no4_brand || '');
                                $('#car_no4_color').val(house.car_no4_color || '');
                                $('#car_no4_type').val(house.car_no4_type || '');
                                $('#car_no5').val(house.car_no5 || '');
                                $('#car_no5_province').val(house.car_no5_province || '');
                                $('#car_no5_brand').val(house.car_no5_brand || '');
                                $('#car_no5_color').val(house.car_no5_color || '');
                                $('#car_no5_type').val(house.car_no5_type || '');
                                $('#car_no6').val(house.car_no6 || '');
                                $('#car_no6_province').val(house.car_no6_province || '');
                                $('#car_no6_brand').val(house.car_no6_brand || '');
                                $('#car_no6_color').val(house.car_no6_color || '');
                                $('#car_no6_type').val(house.car_no6_type || '');
                                $('#car_no7').val(house.car_no7 || '');
                                $('#car_no7_province').val(house.car_no7_province || '');
                                $('#car_no7_brand').val(house.car_no7_brand || '');
                                $('#car_no7_color').val(house.car_no7_color || '');
                                $('#car_no7_type').val(house.car_no7_type || '');

                                let carCount = 0;
                                if (house.car_no1) carCount++;
                                if (house.car_no2) carCount++;
                                if (house.car_no3) carCount++;
                                if (house.car_no4) carCount++;
                                if (house.car_no5) carCount++;
                                if (house.car_no6) carCount++;
                                if (house.car_no7) carCount++;

                                let extraCarFee = 0;
                                if (carCount > 2) {
                                    extraCarFee = (carCount - 2) * 100;
                                }

                                $('#car_count').val(carCount);
                                $('#extra_car_fee').val(extraCarFee);

                                if (house.sticker_receive_status === 'Y') {
                                    $('#sticker_receive_status').prop('checked', true);
                                    $('#sticker_receive_status').prop('disabled', true);
                                    $('#sticker_receive_date').val(house.sticker_receive_date || '');
                                    $('#stickerReceivedModal').modal('show');
                                } else {
                                    $('#sticker_receive_status').prop('checked', false);
                                    $('#sticker_receive_status').prop('disabled', false);
                                    $('#sticker_receive_date').val('');
                                }

                                $('#resultSection').show();
                                $('#btnPrint').prop('disabled', false);
                                $('#btnSave').show();
                            } else {
                                currentHouseNumber = '';
                                $('#notFoundHouseNumber').text(house_number);
                                $('#notFoundSection').show();
                                $('#btnSave').hide();
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log('Search Error:', status, error, xhr.responseText);
                            alertify.error("error : " + status + " - " + error);
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

                $("#btnSave").click(function () {
                    let house_number = currentHouseNumber;
                    let car_no1 = $('#car_no1').val().trim();
                    let car_no1_province = $('#car_no1_province').val().trim();
                    let car_no1_brand = $('#car_no1_brand').val().trim();
                    let car_no1_color = $('#car_no1_color').val().trim();
                    let car_no1_type = $('#car_no1_type').val().trim();
                    let car_no2 = $('#car_no2').val().trim();
                    let car_no2_province = $('#car_no2_province').val().trim();
                    let car_no2_brand = $('#car_no2_brand').val().trim();
                    let car_no2_color = $('#car_no2_color').val().trim();
                    let car_no2_type = $('#car_no2_type').val().trim();
                    let car_no3 = $('#car_no3').val().trim();
                    let car_no3_province = $('#car_no3_province').val().trim();
                    let car_no3_brand = $('#car_no3_brand').val().trim();
                    let car_no3_color = $('#car_no3_color').val().trim();
                    let car_no3_type = $('#car_no3_type').val().trim();
                    let car_no4 = $('#car_no4').val().trim();
                    let car_no4_province = $('#car_no4_province').val().trim();
                    let car_no4_brand = $('#car_no4_brand').val().trim();
                    let car_no4_color = $('#car_no4_color').val().trim();
                    let car_no4_type = $('#car_no4_type').val().trim();
                    let car_no5 = $('#car_no5').val().trim();
                    let car_no5_province = $('#car_no5_province').val().trim();
                    let car_no5_brand = $('#car_no5_brand').val().trim();
                    let car_no5_color = $('#car_no5_color').val().trim();
                    let car_no5_type = $('#car_no5_type').val().trim();
                    let car_no6 = $('#car_no6').val().trim();
                    let car_no6_province = $('#car_no6_province').val().trim();
                    let car_no6_brand = $('#car_no6_brand').val().trim();
                    let car_no6_color = $('#car_no6_color').val().trim();
                    let car_no6_type = $('#car_no6_type').val().trim();
                    let car_no7 = $('#car_no7').val().trim();
                    let car_no7_province = $('#car_no7_province').val().trim();
                    let car_no7_brand = $('#car_no7_brand').val().trim();
                    let car_no7_color = $('#car_no7_color').val().trim();
                    let car_no7_type = $('#car_no7_type').val().trim();
                    let sticker_receive_status = $('#sticker_receive_status').is(':checked') ? 'Y' : 'N';

                    console.log('Saving:', {house_number, car_no1, car_no1_province, car_no1_brand, car_no1_color, car_no1_type, car_no2, car_no2_province, car_no2_brand, car_no2_color, car_no2_type, car_no3, car_no3_province, car_no3_brand, car_no3_color, car_no3_type, car_no4, car_no4_province, car_no4_brand, car_no4_color, car_no4_type, car_no5, car_no5_province, car_no5_brand, car_no5_color, car_no5_type, sticker_receive_status});

                    if (house_number === '') {
                        alertify.warning("กรุณาค้นหาข้อมูลก่อน");
                        return;
                    }

                    $.ajax({
                        type: "POST",
                        url: 'model/manage_pet_record_process.php',
                        data: {
                            action: "UPDATE_CAR_NO",
                            house_number: house_number,
                            car_no1: car_no1,
                            car_no1_province: car_no1_province,
                            car_no1_brand: car_no1_brand,
                            car_no1_color: car_no1_color,
                            car_no1_type: car_no1_type,
                            car_no2: car_no2,
                            car_no2_province: car_no2_province,
                            car_no2_brand: car_no2_brand,
                            car_no2_color: car_no2_color,
                            car_no2_type: car_no2_type,
                            car_no3: car_no3,
                            car_no3_province: car_no3_province,
                            car_no3_brand: car_no3_brand,
                            car_no3_color: car_no3_color,
                            car_no3_type: car_no3_type,
                            car_no4: car_no4,
                            car_no4_province: car_no4_province,
                            car_no4_brand: car_no4_brand,
                            car_no4_color: car_no4_color,
                            car_no4_type: car_no4_type,
                            car_no5: car_no5,
                            car_no5_province: car_no5_province,
                            car_no5_brand: car_no5_brand,
                            car_no5_color: car_no5_color,
                            car_no5_type: car_no5_type,
                            car_no6: car_no6,
                            car_no6_province: car_no6_province,
                            car_no6_brand: car_no6_brand,
                            car_no6_color: car_no6_color,
                            car_no6_type: car_no6_type,
                            car_no7: car_no7,
                            car_no7_province: car_no7_province,
                            car_no7_brand: car_no7_brand,
                            car_no7_color: car_no7_color,
                            car_no7_type: car_no7_type,
                            sticker_receive_status: sticker_receive_status
                        },
                        dataType: "text",
                        success: function (response) {
                            console.log('Raw Response:', response);
                            try {
                                var json = JSON.parse(response);
                                console.log('Parsed:', json);
                                if (json && json.result === '1') {
                                    if (sticker_receive_status === 'Y') {
                                        $('#sticker_receive_status').prop('checked', true);
                                        $('#sticker_receive_status').prop('disabled', true);
                                        $('#stickerReceivedModal').modal('show');
                                    }
                                    alertify.success("บันทึกข้อมูลทะเบียนรถสำเร็จ");
                                } else {
                                    alertify.error("ไม่สามารถบันทึกข้อมูลได้: " + response);
                                }
                            } catch(e) {
                                alertify.error("Parse error: " + response);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log('Error:', status, error, xhr.responseText);
                            alertify.error("error : " + status + " - " + error + " - " + xhr.responseText);
                        }
                    });
                });

                function updateCarCount() {
                    let carCount = 0;
                    if ($('#car_no1').val().trim() !== '') carCount++;
                    if ($('#car_no2').val().trim() !== '') carCount++;
                    if ($('#car_no3').val().trim() !== '') carCount++;
                    if ($('#car_no4').val().trim() !== '') carCount++;
                    if ($('#car_no5').val().trim() !== '') carCount++;
                    if ($('#car_no6').val().trim() !== '') carCount++;
                    if ($('#car_no7').val().trim() !== '') carCount++;

                    let extraCarFee = 0;
                    if (carCount > 2) {
                        extraCarFee = (carCount - 2) * 100;
                    }

                    $('#car_count').val(carCount);
                    $('#extra_car_fee').val(extraCarFee);
                }

                $('#car_no1, #car_no2, #car_no3, #car_no4, #car_no5, #car_no6, #car_no7').on('input', updateCarCount);

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
                                error: function (xhr, status, error) {
                                    console.log('Province Autocomplete Error:', status, error);
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
                initProvinceAutocomplete("car_no6_province");
                initProvinceAutocomplete("car_no7_province");

                function initColorAutocomplete(inputId) {
                    $("#" + inputId).autocomplete({
                        source: function (request, response) {
                            $.ajax({
                                type: "POST",
                                url: 'model/manage_pet_record_process.php',
                                data: {action: "GET_COLOR_AUTOCOMPLETE", search: request.term},
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
                                error: function (xhr, status, error) {
                                    response([]);
                                }
                            });
                        },
                        minLength: 1
                    });
                }

                initColorAutocomplete("car_no1_color");
                initColorAutocomplete("car_no2_color");
                initColorAutocomplete("car_no3_color");
                initColorAutocomplete("car_no4_color");
                initColorAutocomplete("car_no5_color");
                initColorAutocomplete("car_no6_color");
                initColorAutocomplete("car_no7_color");

                function initBrandAutocomplete(inputId) {
                    $("#" + inputId).autocomplete({
                        source: function (request, response) {
                            $.ajax({
                                type: "POST",
                                url: 'model/manage_pet_record_process.php',
                                data: {action: "GET_BRAND_AUTOCOMPLETE", search: request.term},
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
                                error: function (xhr, status, error) {
                                    response([]);
                                }
                            });
                        },
                        minLength: 1
                    });
                }

                initBrandAutocomplete("car_no1_brand");
                initBrandAutocomplete("car_no2_brand");
                initBrandAutocomplete("car_no3_brand");
                initBrandAutocomplete("car_no4_brand");
                initBrandAutocomplete("car_no5_brand");
                initBrandAutocomplete("car_no6_brand");
                initBrandAutocomplete("car_no7_brand");
            });
        </script>

    </body>
    </html>

<?php } ?>