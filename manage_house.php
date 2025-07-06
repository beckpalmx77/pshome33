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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
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

                                        <div class="col-md-12 col-md-offset-2">
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>

                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>ขนาดพื้นที่ (ตรว)</th>
                                                    <th>ค่าเก็บขยะ (บาท)</th>
                                                    <th>ค่าส่วนกลาง (บาท)</th>
                                                    <th>ชื่อผู้ติดต่อ</th>
                                                    <th>หมายเลขโทรศัพท์</th>
                                                    <th>Picture</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>ขนาดพื้นที่ (ตรว)</th>
                                                    <th>ค่าเก็บขยะ (บาท)</th>
                                                    <th>ค่าส่วนกลาง (บาท)</th>
                                                    <th>ชื่อผู้ติดต่อ</th>
                                                    <th>หมายเลขโทรศัพท์</th>
                                                    <th>Picture</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="house_number" class="control-label">บ้านเลขที่</label>
                                                                            <input type="house_number" class="form-control"
                                                                                   id="house_number" name="house_number"
                                                                                   required
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="contact_name"
                                                                                   class="control-label">ชื่อผู้ติดต่อ</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="contact_name"
                                                                                   name="contact_name"
                                                                                   required
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="alley"
                                                                                   class="control-label">ซอย</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="alley"
                                                                                   name="alley"
                                                                                   required
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="phone_number"
                                                                                   class="control-label">หมายเลขโทรศัพท์</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="phone_number"
                                                                                   name="phone_number"
                                                                                   required
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group has-success">
                                                                            <label for="house_status" class="control-label">สถานะที่อยู่อาศัย</label>
                                                                            <select name="house_status" id="house_status" class="form-control" required>
                                                                                <option value="-">-</option>
                                                                                <option value="O">บ้านตนเอง - ครอบครัว</option>
                                                                                <option value="R">บ้านเช่า</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="car_no1"
                                                                                   class="control-label">ทะเบียนรถ 1</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="car_no1"
                                                                                   name="car_no1"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="car_no2"
                                                                                   class="control-label">ทะเบียนรถ 2</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="car_no2"
                                                                                   name="car_no2"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="car_no3"
                                                                                   class="control-label">ทะเบียนรถ 3</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="car_no3"
                                                                                   name="car_no3"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="car_no4"
                                                                                   class="control-label">ทะเบียนรถ 4</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="car_no4"
                                                                                   name="car_no4"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="car_no5"
                                                                                   class="control-label">ทะเบียนรถ 5</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="car_no5"
                                                                                   name="car_no5"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="remark"
                                                                                   class="control-label">หมายเหตุ</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="remark"
                                                                                   name="remark"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Save"/>
                                                            </span>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-times"></i>
                                                            </button>
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
            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>


    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <style>

        .icon-input-btn {
            display: inline-block;
            position: relative;
        }

        .icon-input-btn input[type="submit"] {
            padding-left: 2em;
        }

        .icon-input-btn .fa {
            display: inline-block;
            position: absolute;
            left: 0.65em;
            top: 30%;
        }
    </style>
    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <script>

        $("#contact_name").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let house_number = $('#house_number').val();
                let contact_name = $('#contact_name').val();
                let formData = {action: "SEARCH", house_number: house_number, contact_name: contact_name};
                $.ajax({
                    url: 'model/manage_house_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        if (data == 2) {
                            alert("Duplicate มีข้อมูลนี้แล้วในระบบ กรุณาตรวจสอบ");
                        }
                    }
                })
            }
        });

    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_HOUSE", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100, 500 , 621], [5, 10, 20, 50, 100, 500, 621]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                <?php  if ($_SESSION['deviceType'] !== 'computer') {
                    echo "'scrollX': true,";
                }?>
                'ajax': {
                    'url': 'model/manage_house_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'house_number'},
                    {data: 'alley'},
                    { data: 'area_size', className: 'text-right' },
                    { data: 'garbage_collection_fee', className: 'text-right' },
                    { data: 'common_fee', className: 'text-right' },
                    {data: 'contact_name'},
                    {data: 'phone_number'},
                    {
                        data: 'line_picture_profile', // คอลัมน์ที่เก็บ URL รูปภาพ
                        render: function(data, type, row) {
                            let imageUrl = data ? data : 'img/icon/none_img.png'; // ถ้าไม่มี data ใช้รูป default
                            return '<img src="' + imageUrl + '" alt="image" style="width: 50px; height: auto;">';
                        }
                    },
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                //alert(formData);
                $.ajax({
                    url: 'model/manage_house_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    }
                })
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#house_number').val("");
                $('#contact_name').val("");
                $('#phone_number').val("");
                $('#house_status').val("");
                $('#alley').val("");
                $('#remark').val("");
                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
            });
        });
    </script>

    <script>

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_house_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let house_number = response[i].house_number;
                        let contact_name = response[i].contact_name;
                        let phone_number = response[i].phone_number;
                        let house_status = response[i].house_status;
                        let alley = response[i].alley;
                        let remark = response[i].remark;
                        let car_no1 = response[i].car_no1;
                        let car_no2 = response[i].car_no2;
                        let car_no3 = response[i].car_no3;
                        let car_no4 = response[i].car_no4;
                        let car_no5 = response[i].car_no5;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#house_number').val(house_number);
                        $('#contact_name').val(contact_name);
                        $('#phone_number').val(phone_number);
                        $('#house_status').val(house_status);
                        $('#alley').val(alley);
                        $('#remark').val(remark);
                        $('#car_no1').val(car_no1);
                        $('#car_no2').val(car_no2);
                        $('#car_no3').val(car_no3);
                        $('#car_no4').val(car_no4);
                        $('#car_no5').val(car_no5);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action').val('UPDATE');
                        $('#save').val('Save');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>

        $("#TableRecordList").on('click', '.delete', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_house_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let house_number = response[i].house_number;
                        let contact_name = response[i].contact_name;
                        let phone_number = response[i].phone_number;
                        let house_status = response[i].house_status;
                        let alley = response[i].alley;
                        let remark = response[i].remark;
                        let car_no1 = response[i].car_no1;
                        let car_no2 = response[i].car_no2;
                        let car_no3 = response[i].car_no3;
                        let car_no4 = response[i].car_no4;
                        let car_no5 = response[i].car_no5;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#house_number').val(house_number);
                        $('#contact_name').val(contact_name);
                        $('#phone_number').val(phone_number);
                        $('#house_status').val(house_status);
                        $('#alley').val(alley);
                        $('#remark').val(remark);
                        $('#car_no1').val(car_no1);
                        $('#car_no2').val(car_no2);
                        $('#car_no3').val(car_no3);
                        $('#car_no4').val(car_no4);
                        $('#car_no5').val(car_no5);
                        $('.modal-title').html("<i class='fa fa-minus'></i> Delete Record");
                        $('#action').val('DELETE');
                        $('#save').val('Confirm Delete');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    </body>
    </html>

<?php } ?>