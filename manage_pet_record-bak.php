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
                                                    <th>ชื่อผู้ติดต่อ</th>
                                                    <th>หมายเลขโทรศัพท์</th>
                                                    <th>จำนวนสัตว์เลี้ยง</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>ชื่อผู้ติดต่อ</th>
                                                    <th>หมายเลขโทรศัพท์</th>
                                                    <th>จำนวนสัตว์เลี้ยง</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-xl">
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
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="house_number"
                                                                                   class="control-label">บ้านเลขที่</label>
                                                                            <input type="house_number"
                                                                                   class="form-control"
                                                                                   id="house_number" name="house_number"
                                                                                   required
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
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
                                                                    <div class="col-md-2">
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

                                                                    <div class="col-md-3">
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

                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="pet_quantity"
                                                                                   class="control-label">จำนวนสัตว์เลี้ยง</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="pet_quantity"
                                                                                   name="pet_quantity"
                                                                                   required
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>

                                                                </div>


                                                                <div class="row">
                                                                    <div class="col-md-2">
                                                                        <div class="form-group has-success">
                                                                            <label for="type_1" class="control-label">ประเภทสัตว์เลี้ยง</label>
                                                                            <select name="type_1" id="type_1"
                                                                                    class="form-control" required>
                                                                                <option value="-">-</option>
                                                                                <option value="D">สุนัข</option>
                                                                                <option value="C">แมว</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="pet_1"
                                                                                   class="control-label">ชื่อสัตว์เลี้ยง
                                                                                1</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="pet_1"
                                                                                   name="pet_1"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label">เลือก
                                                                                รูปภาพสัตว์เลี้ยง
                                                                                1</label>
                                                                            <input type="file" name="picture_pet_1"
                                                                                   id="picture_pet_1"
                                                                                   class="form-control">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label"></label>
                                                                            <img id="preview_pet_1" src="#"
                                                                                 alt="Preview" class="img-thumbnail"
                                                                                 style="display: none; max-height: 90px; margin-top: 10px;"/>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-2">
                                                                        <div class="form-group has-success">
                                                                            <label for="type_2" class="control-label">ประเภทสัตว์เลี้ยง</label>
                                                                            <select name="type_2" id="type_2"
                                                                                    class="form-control" required>
                                                                                <option value="-">-</option>
                                                                                <option value="D">สุนัข</option>
                                                                                <option value="C">แมว</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="pet_2"
                                                                                   class="control-label">ชื่อสัตว์เลี้ยง
                                                                                2</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="pet_2"
                                                                                   name="pet_2"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label">เลือก
                                                                                รูปภาพสัตว์เลี้ยง
                                                                                2</label>
                                                                            <input type="file" name="picture_pet_2"
                                                                                   id="picture_pet_2"
                                                                                   class="form-control">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label"></label>
                                                                            <img id="preview_pet_2" src="#"
                                                                                 alt="Preview" class="img-thumbnail"
                                                                                 style="display: none; max-height: 90px; margin-top: 10px;"/>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-2">
                                                                        <div class="form-group has-success">
                                                                            <label for="type_3" class="control-label">ประเภทสัตว์เลี้ยง</label>
                                                                            <select name="type_3" id="type_3"
                                                                                    class="form-control" required>
                                                                                <option value="-">-</option>
                                                                                <option value="D">สุนัข</option>
                                                                                <option value="C">แมว</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="pet_3"
                                                                                   class="control-label">ชื่อสัตว์เลี้ยง
                                                                                3</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="pet_3"
                                                                                   name="pet_3"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label">เลือก
                                                                                รูปภาพสัตว์เลี้ยง
                                                                                3</label>
                                                                            <input type="file" name="picture_pet_3"
                                                                                   id="picture_pet_3"
                                                                                   class="form-control">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label"></label>
                                                                            <img id="preview_pet_3" src="#"
                                                                                 alt="Preview" class="img-thumbnail"
                                                                                 style="display: none; max-height: 90px; margin-top: 10px;"/>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-2">
                                                                        <div class="form-group has-success">
                                                                            <label for="type_4" class="control-label">ประเภทสัตว์เลี้ยง</label>
                                                                            <select name="type_4" id="type_4"
                                                                                    class="form-control" required>
                                                                                <option value="-">-</option>
                                                                                <option value="D">สุนัข</option>
                                                                                <option value="C">แมว</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="pet_4"
                                                                                   class="control-label">ชื่อสัตว์เลี้ยง
                                                                                4</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="pet_4"
                                                                                   name="pet_4"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label">เลือก
                                                                                รูปภาพสัตว์เลี้ยง
                                                                                4</label>
                                                                            <input type="file" name="picture_pet_4"
                                                                                   id="picture_pet_4"
                                                                                   class="form-control">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label"></label>
                                                                            <img id="preview_pet_4" src="#"
                                                                                 alt="Preview" class="img-thumbnail"
                                                                                 style="display: none; max-height: 90px; margin-top: 10px;"/>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-2">
                                                                        <div class="form-group has-success">
                                                                            <label for="type_5" class="control-label">ประเภทสัตว์เลี้ยง</label>
                                                                            <select name="type_5" id="type_5"
                                                                                    class="form-control" required>
                                                                                <option value="-">-</option>
                                                                                <option value="D">สุนัข</option>
                                                                                <option value="C">แมว</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="pet_5"
                                                                                   class="control-label">ชื่อสัตว์เลี้ยง
                                                                                5</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="pet_5"
                                                                                   name="pet_5"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label">เลือก
                                                                                รูปภาพสัตว์เลี้ยง
                                                                                5</label>
                                                                            <input type="file" name="picture_pet_5"
                                                                                   id="picture_pet_5"
                                                                                   class="form-control">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label"></label>
                                                                            <img id="preview_pet_5" src="#"
                                                                                 alt="Preview" class="img-thumbnail"
                                                                                 style="display: none; max-height: 90px; margin-top: 10px;"/>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-md-2">
                                                                        <div class="form-group has-success">
                                                                            <label for="type_6" class="control-label">ประเภทสัตว์เลี้ยง</label>
                                                                            <select name="type_6" id="type_6"
                                                                                    class="form-control" required>
                                                                                <option value="-">-</option>
                                                                                <option value="D">สุนัข</option>
                                                                                <option value="C">แมว</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="pet_6"
                                                                                   class="control-label">ชื่อสัตว์เลี้ยง
                                                                                6</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="pet_6"
                                                                                   name="pet_6"
                                                                                   placeholder="">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label">เลือก
                                                                                รูปภาพสัตว์เลี้ยง
                                                                                6</label>
                                                                            <input type="file" name="picture_pet_6"
                                                                                   id="picture_pet_6"
                                                                                   class="form-control">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="picture3"
                                                                                   class="control-label"></label>
                                                                            <img id="preview_pet_6" src="#"
                                                                                 alt="Preview" class="img-thumbnail"
                                                                                 style="display: none; max-height: 90px; margin-top: 10px;"/>
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
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    //include('includes/Footer.php');
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
                    url: 'model/manage_pet_record_process.php',
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
            let formData = {action: "GET_PET", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100, 500, 621], [5, 10, 20, 50, 100, 500, 621]],
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
                    'url': 'model/manage_pet_record_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'house_number'},
                    {data: 'alley'},
                    {data: 'contact_name'},
                    {data: 'phone_number'},
                    {data: 'pet_quantity', className: 'text-right'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

            /*

                        $("#recordModal---bak").on('submit', '#recordForm', function (event) {
                            event.preventDefault();
                            $('#save').attr('disabled', 'disabled');
                            let formData = $(this).serialize();
                            //alert(formData);
                            $.ajax({
                                url: 'model/manage_pet_record_process.php',
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
             */

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
                $('#alley').val("");
                $('#remark').val("");

                $('#type_1').val("-");
                $('#type_2').val("-");
                $('#type_3').val("-");
                $('#type_4').val("-");
                $('#type_5').val("-");
                $('#type_6').val("-");

                $('#pet_1').val();
                $('#pet_2').val();
                $('#pet_3').val();
                $('#pet_4').val();
                $('#pet_5').val();
                $('#pet_6').val();

                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
            });
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_pet_record_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    if (len > 0) {
                        let record = response[0]; // ใช้ object แรกโดยตรง

                        $('#recordModal').modal('show');
                        $('#id').val(record.id);
                        $('#house_number').val(record.house_number);
                        $('#contact_name').val(record.contact_name);
                        $('#phone_number').val(record.phone_number);
                        $('#alley').val(record.alley);
                        $('#pet_quantity').val(record.pet_quantity);

                        // --- START: ส่วนแสดงรูปภาพ Preview ตอนกดแก้ไข ---

                        // วน Loop 6 ครั้งสำหรับสัตว์เลี้ยงแต่ละตัว
                        for (let i = 1; i <= 6; i++) {
                            let type = record['type_' + i];
                            let pet_name = record['pet_' + i];
                            let picture_filename = record['picture_pet_' + i];

                            // ตั้งค่าประเภทและชื่อ
                            $('#type_' + i).val(type);
                            $('#pet_' + i).val(pet_name);

                            // เลือก element ของ image preview
                            let previewImage = $('#preview_pet_' + i);

                            // ตรวจสอบว่ามีชื่อไฟล์รูปภาพหรือไม่
                            if (picture_filename) {
                                // สร้าง path ไปยังรูปภาพ
                                let imagePath = 'uploads/pet/' + picture_filename;

                                // ตั้งค่า src และแสดงรูปภาพ
                                previewImage.attr('src', imagePath).show();
                            } else {
                                // ถ้าไม่มีชื่อไฟล์ ให้ซ่อนรูปภาพ preview
                                previewImage.hide().attr('src', '#');
                            }
                        }

                        // --- END: ส่วนแสดงรูปภาพ Preview ตอนกดแก้ไข ---


                        $('.modal-title').html("<i class='fa fa-edit'></i> Edit Record");
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
        $(document).ready(function () {

            // Function to handle image preview for pets 1 through 6
            function setupPetImagePreview(petNumber) {
                // When a file is selected in the input for a specific pet
                $('#picture_pet_' + petNumber).on('change', function (event) {

                    // The corresponding image tag for preview
                    let previewImage = $('#preview_pet_' + petNumber);

                    // Check if the user has selected a file
                    if (event.target.files && event.target.files[0]) {
                        let reader = new FileReader();

                        // When the FileReader has finished reading the image
                        reader.onload = function (e) {
                            // Set the src of the <img> to the image data
                            previewImage.attr('src', e.target.result);
                            // Show the <img> tag
                            previewImage.show();
                        }

                        // Start reading the selected file as a Data URL
                        reader.readAsDataURL(event.target.files[0]);
                    } else {
                        // If the user cancels file selection, hide the preview
                        previewImage.hide();
                        previewImage.attr('src', '#');
                    }
                });
            }

            // Loop to set up the preview for all 6 pet image inputs
            for (let i = 1; i <= 6; i++) {
                setupPetImagePreview(i);
            }

            // Reset image previews when the modal is closed or form is submitted/cleared
            $('#recordModal').on('hidden.bs.modal', function () {
                for (let i = 1; i <= 6; i++) {
                    $('#preview_pet_' + i).hide().attr('src', '#');
                }
                $('#recordForm')[0].reset();
            });

            // Also, ensure previews are cleared when clicking "btnAdd"
            $("#btnAdd").click(function () {
                for (let i = 1; i <= 6; i++) {
                    $('#preview_pet_' + i).hide().attr('src', '#');
                }
            });

        });
    </script>

    <script>
        $("#recordModal").on('submit', '#recordForm', function (event) {
            event.preventDefault();
            $('#save').attr('disabled', 'disabled');

            // เปลี่ยนมาใช้ FormData เพื่อให้สามารถส่งไฟล์ได้
            let formData = new FormData(this);

            $.ajax({
                url: 'model/manage_pet_record_process.php',
                method: "POST",
                data: formData,
                // เพิ่ม 2 บรรทัดนี้ >> สำคัญมากสำหรับการอัปโหลดไฟล์
                contentType: false,
                processData: false,
                success: function (data) {
                    alertify.success(data);
                    $('#recordForm')[0].reset();
                    // เมื่อฟอร์มถูกรีเซ็ต ต้องซ่อนรูป preview ทั้งหมดด้วย
                    for (let i = 1; i <= 6; i++) {
                        $('#preview_pet_' + i).hide().attr('src', '#');
                    }
                    $('#recordModal').modal('hide');
                    $('#save').attr('disabled', false);
                    $('#TableRecordList').DataTable().ajax.reload();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // เพิ่มส่วนจัดการ error เพื่อให้ดีบักง่ายขึ้น
                    alertify.error("AJAX Error: " + textStatus + ' : ' + errorThrown);
                    $('#save').attr('disabled', false);
                }
            });
        });
    </script>

    </body>
    </html>

<?php } ?>