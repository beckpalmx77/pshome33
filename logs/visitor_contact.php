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
                    <div class="d-sm-flex align-items-center justify-content-between mb-2 mb-md-4">
                        <h1 class="h4 mb-0 text-gray-800">บันทึกข้อมูลติดต่อลูกบ้าน</h1>
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
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="col-12 col-md-4">
                                            <div class="form-group">
                                                <label for="house_number" class="control-label">บ้านเลขที่</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control form-control-sm"
                                                           id="house_number"
                                                           name="house_number"
                                                           placeholder="กรอกบ้านเลขที่">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-info btn-sm" id="btnSearch">
                                                            <i class="fa fa-search"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-success btn-sm" id="btnSave" style="display:none;">
                                                            <i class="fa fa-save"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6 col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">&nbsp;</label>
                                                <button type="button" class="btn btn-primary btn-block btn-sm" id="btnNew" disabled>
                                                    <i class="fa fa-plus"></i> <span class="d-none d-md-inline">บันทึกใหม่</span>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2 mt-4" id="resultSection" style="display:none;">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">ข้อมูลบ้านเลขที่: <span id="displayHouseNumber"></span></h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">ชื่อผู้ติดต่อ</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                               id="contact_name" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">หมายเลขโทรศัพท์</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                               id="phone_number" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">ซอย</label>
                                                        <input type="text" class="form-control form-control-sm"
                                                               id="alley" readonly>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2 mt-4" id="formSection" style="display:none;">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">ข้อมูลผู้มาติดต่อ</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                    <div class="col-12 col-md-4">
                                                        <div class="form-group">
                                                            <label class="control-label">ชื่อผู้มาติดต่อ <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                   id="visitor_name"
                                                                   placeholder="กรอกชื่อผู้มาติดต่อ">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="form-group">
                                                            <label class="control-label">เบอร์โทรศัพท์</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                   id="visitor_phone"
                                                                   placeholder="กรอกเบอร์โทรศัพท์">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="form-group">
                                                            <label class="control-label">ประเภทผู้มาติดต่อ</label>
                                                            <select class="form-control form-control-sm" id="visitor_type">
                                                                <option value="">-- เลือก --</option>
                                                                <option value="ส่งอาหาร">ส่งอาหาร</option>
                                                                <option value="ส่งพัสดุ/ส่งของ">ส่งพัสดุ/ส่งของ</option>
                                                                <option value="ญาติ">ญาติ</option>
                                                                <option value="ติดต่อนิติฯ">ติดต่อนิติฯ</option>
                                                                <option value="อื่นๆ">อื่นๆ</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-2">
                                                    <div class="col-12 col-md-4">
                                                        <div class="form-group">
                                                            <label class="control-label">วัตถุประสงค์</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                   id="purpose"
                                                                   placeholder="กรอกวัตถุประสงค์">
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="form-group">
                                                            <label class="control-label">หมายเหตุ</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                   id="note"
                                                                   placeholder="กรอกหมายเหตุ">
                                                        </div>
                                                    </div>
                                                </div>

                                                    <div class="row mt-2">
                                                        <div class="col-12">
                                                            <label class="control-label"><strong>รูปภาพ</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-1">
                                                        <div class="col-4 col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label small">รูป 1</label>
                                                                <input type="file" class="form-control form-control-sm" id="picture_1" accept="image/*">
                                                                <div id="preview_picture_1" class="mt-1"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4 col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label small">รูป 2</label>
                                                                <input type="file" class="form-control form-control-sm" id="picture_2" accept="image/*">
                                                                <div id="preview_picture_2" class="mt-1"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4 col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label small">รูป 3</label>
                                                                <input type="file" class="form-control form-control-sm" id="picture_3" accept="image/*">
                                                                <div id="preview_picture_3" class="mt-1"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4 col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label small">รูป 4</label>
                                                                <input type="file" class="form-control form-control-sm" id="picture_4" accept="image/*">
                                                                <div id="preview_picture_4" class="mt-1"></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-4 col-md-2">
                                                            <div class="form-group">
                                                                <label class="control-label small">รูป 5</label>
                                                                <input type="file" class="form-control form-control-sm" id="picture_5" accept="image/*">
                                                                <div id="preview_picture_5" class="mt-1"></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <label class="control-label"><strong>การแลกบัตร</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-1">
                                                        <div class="col-6 col-md-3">
                                                            <div class="form-group">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input" id="card_exchange" value="Y">
                                                                    <label class="form-check-label" for="card_exchange">แลกบัตรแล้ว</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">หมายเลขบัตร</label>
                                                                <input type="text" class="form-control form-control-sm" id="card_no" placeholder="เลขบัตร">
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">วันที่แลกบัตร</label>
                                                                <input type="text" class="form-control form-control-sm" id="card_exchange_date" readonly>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <label class="control-label"><strong>Check In / Check Out</strong></label>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-1">
                                                        <div class="col-6 col-md-3">
                                                            <div class="form-group">
                                                                <div class="form-check">
                                                                    <input type="checkbox" class="form-check-input" id="check_in_status" value="Y" disabled>
                                                                    <label class="form-check-label" for="check_in_status">Check In แล้ว</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">เวลาเข้า</label>
                                                                <input type="text" class="form-control form-control-sm" id="check_in_datetime" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-6 col-md-3">
                                                            <div class="form-group">
                                                                <label class="control-label">เวลาออก</label>
                                                                <input type="text" class="form-control form-control-sm" id="check_out_datetime" readonly>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="col-12 text-center">
                                                            <!--button type="button" class="btn btn-primary btn-sm mb-1" id="btnCheckIn">
                                                                <i class="fa fa-sign-in-alt"></i> Check In
                                                            </button-->
                                                            <button type="button" class="btn btn-warning btn-sm mb-1" id="btnCheckOut">
                                                                <i class="fa fa-sign-out-alt"></i> Check Out
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm mb-1" id="btnDelete">
                                                                <i class="fa fa-trash"></i> ลบ
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2 mt-4" id="listSection" style="display:none;">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5 class="mb-0">รายการผู้มาติดต่อ</h5>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm mb-0" id="dataTable" width="100%" cellspacing="0">
                                                            <thead class="d-none d-md-table-header-group">
                                                                <tr>
                                                                    <th>ลำดับ</th>
                                                                    <th>ชื่อ</th>
                                                                    <th>ประเภท</th>
                                                                    <th>เบอร์</th>
                                                                    <th>บัตร</th>
                                                                    <th>In</th>
                                                                    <th>Out</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="visitorListBody">
                                                            </tbody>
                                                        </table>
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
            let currentVisitorId = 0;
            let uploadedPictures = {picture_1: '', picture_2: '', picture_3: '', picture_4: '', picture_5: ''};

            $(document).ready(function () {
                $("#house_number").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            type: "POST",
                            url: 'model/manage_visitor_contact_process.php',
                            data: {action: "GET_HOUSE_AUTOCOMPLETE", search: request.term},
                            dataType: "json",
                            success: function (data) {
                                console.log('Autocomplete data:', data);
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
                        url: 'model/manage_visitor_contact_process.php',
                        data: formData,
                        dataType: "json",
                        success: function (response) {
                            $('#resultSection').hide();
                            $('#formSection').hide();
                            $('#listSection').hide();
                            $('#notFoundSection').hide();
                            $('#btnSave').hide();
                            $('#btnNew').prop('disabled', true);

                            if (response && response.house) {
                                let house = response.house;
                                currentHouseNumber = house.house_number;
                                $('#displayHouseNumber').text(house.house_number);
                                $('#contact_name').val(house.contact_name || '');
                                $('#phone_number').val(house.phone_number || '');
                                $('#alley').val(house.alley || '');

                                $('#resultSection').show();
                                $('#btnNew').prop('disabled', false);

                                if (response.visitors && response.visitors.length > 0) {
                                    displayVisitorList(response.visitors);
                                    $('#listSection').show();
                                }
                            } else {
                                currentHouseNumber = '';
                                $('#notFoundHouseNumber').text(house_number);
                                $('#notFoundSection').show();
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

                $("#btnNew").click(function () {
                    resetForm();
                    $('#formSection').show();
                    $('#resultSection').show();
                    $('#btnSave').show();
                    currentVisitorId = 0;
                    uploadedPictures = {picture_1: '', picture_2: '', picture_3: '', picture_4: '', picture_5: ''};
                });

                $("#btnSave").click(function () {
                    saveVisitor();
                });

                $("#btnCheckIn").click(function () {
                    if (currentVisitorId > 0) {
                        performCheckIn();
                    } else {
                        alertify.warning("กรุณาเลือกรายการก่อน");
                    }
                });

                $("#btnCheckOut").click(function () {
                    if (currentVisitorId > 0) {
                        performCheckOut();
                    } else {
                        alertify.warning("กรุณาเลือกรายการก่อน");
                    }
                });

                $("#btnDelete").click(function () {
                    if (currentVisitorId > 0) {
                        if (confirm('ต้องการลบรายการนี้หรือไม่?')) {
                            deleteVisitor();
                        }
                    } else {
                        alertify.warning("กรุณาเลือกรายการก่อน");
                    }
                });

                $('#card_exchange').change(function() {
                    if ($(this).is(':checked')) {
                        $('#card_exchange_date').val(getCurrentDatetime());
                    } else {
                        $('#card_exchange_date').val('');
                    }
                });

                $('#picture_1, #picture_2, #picture_3, #picture_4, #picture_5').change(function() {
                    handlePicturePreview(this);
                });

                function getCurrentDatetime() {
                    let now = new Date();
                    let year = now.getFullYear();
                    let month = String(now.getMonth() + 1).padStart(2, '0');
                    let day = String(now.getDate()).padStart(2, '0');
                    let hours = String(now.getHours()).padStart(2, '0');
                    let minutes = String(now.getMinutes()).padStart(2, '0');
                    let seconds = String(now.getSeconds()).padStart(2, '0');
                    return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
                }

                function displayVisitorList(visitors) {
                    let tbody = $('#visitorListBody');
                    tbody.empty();

                    visitors.forEach(function(v, index) {
                        let cardExchange = v.card_exchange === 'Y' ? '<span class="badge badge-success">แลกแล้ว</span>' : '<span class="badge badge-secondary">ยังไม่ได้แลก</span>';
                        let checkInStatus = v.check_in_status === 'Y' ? '<span class="badge badge-success">เข้าแล้ว</span>' : '<span class="badge badge-secondary">ยังไม่เข้า</span>';

                        // Mobile card view
                        let mobileCard = $('<div class="card mb-2 d-md-none">');
                        mobileCard.append('<div class="card-body p-2">');
                        mobileCard.find('.card-body').append('<h6 class="mb-1">' + v.visitor_name + '</h6>');
                        mobileCard.find('.card-body').append('<p class="mb-1 small"><strong>ประเภท:</strong> ' + (v.visitor_type || '-') + '</p>');
                        mobileCard.find('.card-body').append('<p class="mb-1 small"><strong>เบอร์:</strong> ' + (v.phone_number || '-') + '</p>');
                        mobileCard.find('.card-body').append('<p class="mb-1 small"><strong>บัตร:</strong> ' + cardExchange + '</p>');
                        mobileCard.find('.card-body').append('<p class="mb-1 small"><strong>In:</strong> ' + (v.check_in_datetime || '-') + '</p>');
                        mobileCard.find('.card-body').append('<p class="mb-1 small"><strong>Out:</strong> ' + (v.check_out_datetime || '-') + '</p>');
                        mobileCard.find('.card-body').append('<button class="btn btn-sm btn-primary btnEdit w-100" data-id="' + v.id + '"><i class="fa fa-edit"></i> แก้ไข</button>');
                        tbody.append(mobileCard);

                        // Desktop table row
                        let row = $('<tr class="d-none d-md-table-row">');
                        row.append('<td>' + (index + 1) + '</td>');
                        row.append('<td>' + v.visitor_name + '</td>');
                        row.append('<td>' + (v.visitor_type || '-') + '</td>');
                        row.append('<td>' + (v.phone_number || '-') + '</td>');
                        row.append('<td>' + cardExchange + '</td>');
                        row.append('<td>' + (v.check_in_datetime || '-') + '</td>');
                        row.append('<td>' + (v.check_out_datetime || '-') + '</td>');
                        row.append('<td><button class="btn btn-sm btn-primary btnEdit" data-id="' + v.id + '"><i class="fa fa-edit"></i></button></td>');

                        tbody.append(row);
                    });

                    $('.btnEdit').click(function() {
                        let id = $(this).data('id');
                        editVisitor(id, visitors);
                    });
                }

                function editVisitor(id, visitors) {
                    let visitor = visitors.find(function(v) { return v.id == id; });
                    if (visitor) {
                        currentVisitorId = id;
                        $('#visitor_name').val(visitor.visitor_name || '');
                        $('#visitor_phone').val(visitor.phone_number || '');
                        $('#visitor_type').val(visitor.visitor_type || '');
                        $('#purpose').val(visitor.purpose || '');
                        $('#note').val(visitor.note || '');

                        if (visitor.card_exchange === 'Y') {
                            $('#card_exchange').prop('checked', true);
                        } else {
                            $('#card_exchange').prop('checked', false);
                        }
                        $('#card_no').val(visitor.card_no || '');
                        $('#card_exchange_date').val(visitor.card_exchange_date || '');

                        if (visitor.check_in_status === 'Y') {
                            $('#check_in_status').prop('checked', true);
                        } else {
                            $('#check_in_status').prop('checked', false);
                        }
                        $('#check_in_datetime').val(visitor.check_in_datetime || '');
                        $('#check_out_datetime').val(visitor.check_out_datetime || '');

                        uploadedPictures = {
                            picture_1: visitor.picture_1 || '',
                            picture_2: visitor.picture_2 || '',
                            picture_3: visitor.picture_3 || '',
                            picture_4: visitor.picture_4 || '',
                            picture_5: visitor.picture_5 || ''
                        };

                        displayPicturePreviews();
                        $('#formSection').show();
                        $('#btnSave').show();
                    }
                }

                function displayPicturePreviews() {
                    let baseUrl = 'uploads/visitor/';
                    for (let i = 1; i <= 5; i++) {
                        let pic = uploadedPictures['picture_' + i];
                        let container = $('#preview_picture_' + i);
                        container.empty();
                        if (pic) {
                            container.html('<img src="' + baseUrl + pic + '" style="max-width:80px;max-height:80px;" class="img-thumbnail">');
                        }
                    }
                }

                function handlePicturePreview(input) {
                    let file = input.files[0];
                    if (file) {
                        let reader = new FileReader();
                        let fieldId = $(input).attr('id');
                        reader.onload = function(e) {
                            $('#preview_picture_' + fieldId).html('<img src="' + e.target.result + '" style="max-width:80px;max-height:80px;" class="img-thumbnail">');
                        };
                        reader.readAsDataURL(file);
                    }
                }

                function resetForm() {
                    $('#visitor_name').val('');
                    $('#visitor_phone').val('');
                    $('#visitor_type').val('');
                    $('#purpose').val('');
                    $('#note').val('');
                    $('#card_exchange').prop('checked', false);
                    $('#card_no').val('');
                    $('#card_exchange_date').val('');
                    $('#check_in_status').prop('checked', false);
                    $('#check_in_datetime').val('');
                    $('#check_out_datetime').val('');

                    $('#picture_1, #picture_2, #picture_3, #picture_4, #picture_5').val('');
                    for (let i = 1; i <= 5; i++) {
                        $('#preview_picture_' + i).empty();
                    }

                    currentVisitorId = 0;
                    uploadedPictures = {picture_1: '', picture_2: '', picture_3: '', picture_4: '', picture_5: ''};
                }

                function saveVisitor() {
                    let house_number = currentHouseNumber;
                    let visitor_name = $('#visitor_name').val().trim();
                    let visitor_phone = $('#visitor_phone').val().trim();
                    let visitor_type = $('#visitor_type').val().trim();
                    let purpose = $('#purpose').val().trim();
                    let note = $('#note').val().trim();
                    let card_exchange = $('#card_exchange').is(':checked') ? 'Y' : 'N';
                    let card_no = $('#card_no').val().trim();

                    if (house_number === '') {
                        alertify.warning("กรุณาค้นหาข้อมูลบ้านก่อน");
                        return;
                    }

                    if (visitor_name === '') {
                        alertify.warning("กรุณากรอกชื่อผู้มาติดต่อ");
                        return;
                    }

                    let formData = new FormData();
                    formData.append('action', 'SAVE_VISITOR');
                    formData.append('id', currentVisitorId);
                    formData.append('house_number', house_number);
                    formData.append('visitor_name', visitor_name);
                    formData.append('visitor_phone', visitor_phone);
                    formData.append('visitor_type', visitor_type);
                    formData.append('purpose', purpose);
                    formData.append('note', note);
                    formData.append('card_exchange', card_exchange);
                    formData.append('card_no', card_no);

                    if ($('#picture_1')[0].files[0]) formData.append('picture_1', $('#picture_1')[0].files[0]);
                    if ($('#picture_2')[0].files[0]) formData.append('picture_2', $('#picture_2')[0].files[0]);
                    if ($('#picture_3')[0].files[0]) formData.append('picture_3', $('#picture_3')[0].files[0]);
                    if ($('#picture_4')[0].files[0]) formData.append('picture_4', $('#picture_4')[0].files[0]);
                    if ($('#picture_5')[0].files[0]) formData.append('picture_5', $('#picture_5')[0].files[0]);

                    formData.append('existing_picture_1', uploadedPictures.picture_1);
                    formData.append('existing_picture_2', uploadedPictures.picture_2);
                    formData.append('existing_picture_3', uploadedPictures.picture_3);
                    formData.append('existing_picture_4', uploadedPictures.picture_4);
                    formData.append('existing_picture_5', uploadedPictures.picture_5);

                    $.ajax({
                        type: 'POST',
                        url: 'model/manage_visitor_contact_process.php',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function (response) {
                            console.log('Save Response:', response);
                            if (response && response.result === '1') {
                                alertify.success("บันทึกข้อมูลสำเร็จ");
                                currentVisitorId = response.id || 0;
                                $('#btnSearch').click();
                            } else {
                                alertify.error("ไม่สามารถบันทึกข้อมูลได้: " + (response.msg || ''));
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log('Error:', status, error, xhr.responseText);
                            alertify.error("error : " + status + " - " + error);
                        }
                    });
                }

                function performCheckIn() {
                    $.ajax({
                        type: "POST",
                        url: 'model/manage_visitor_contact_process.php',
                        data: {action: "CHECK_IN", id: currentVisitorId},
                        dataType: "json",
                        success: function (response) {
                            if (response && response.result === '1') {
                                alertify.success("Check In สำเร็จ");
                                $('#btnSearch').click();
                            } else {
                                alertify.error("ไม่สามารถ Check In ได้");
                            }
                        },
                        error: function (xhr, status, error) {
                            alertify.error("error : " + status + " - " + error);
                        }
                    });
                }

                function performCheckOut() {
                    $.ajax({
                        type: "POST",
                        url: 'model/manage_visitor_contact_process.php',
                        data: {action: "CHECK_OUT", id: currentVisitorId},
                        dataType: "json",
                        success: function (response) {
                            if (response && response.result === '1') {
                                alertify.success("Check Out สำเร็จ");
                                $('#btnSearch').click();
                            } else {
                                alertify.error("ไม่สามารถ Check Out ได้");
                            }
                        },
                        error: function (xhr, status, error) {
                            alertify.error("error : " + status + " - " + error);
                        }
                    });
                }

                function deleteVisitor() {
                    $.ajax({
                        type: "POST",
                        url: 'model/manage_visitor_contact_process.php',
                        data: {action: "DELETE_VISITOR", id: currentVisitorId},
                        dataType: "json",
                        success: function (response) {
                            if (response && response.result === '1') {
                                alertify.success("ลบข้อมูลสำเร็จ");
                                $('#btnSearch').click();
                            } else {
                                alertify.error("ไม่สามารถลบข้อมูลได้");
                            }
                        },
                        error: function (xhr, status, error) {
                            alertify.error("error : " + status + " - " + error);
                        }
                    });
                }
            });
        </script>

    </body>
    </html>

<?php } ?>