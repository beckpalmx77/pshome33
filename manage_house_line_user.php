<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['line_user_id']) == "") {
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
                                            <button type="button" id="btnReload" class="btn btn-outline-success btn-xs" data-toggle="tooltip" title="Reload Data">
                                                <i class="fa fa-refresh"></i> Reload
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable nowrap' style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>ลำดับ</th>
                                                    <th>LINE User ID</th>
                                                    <th>LINE User NAME</th>
                                                    <th>ชื่อ</th>
                                                    <th>นามสกุล</th>
                                                    <th>หมายเลขโทรศัพท์</th>
                                                    <th>Picture Profile</th>
                                                    <th>บ้านเลขที่</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>ลำดับ</th>
                                                    <th>LINE User ID</th>
                                                    <th>LINE User NAME</th>
                                                    <th>ชื่อ</th>
                                                    <th>นามสกุล</th>
                                                    <th>หมายเลขโทรศัพท์</th>
                                                    <th>Picture Profile</th>
                                                    <th>บ้านเลขที่</th>
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

                                                                <div class="form-group">
                                                                    <label for="line_user_id" class="control-label">line_user_id</label>
                                                                    <input type="line_user_id" class="form-control"
                                                                           id="line_user_id" name="line_user_id"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="line_user_name"
                                                                           class="control-label">line_user_name</label>
                                                                    <input type="text" class="form-control"
                                                                           id="line_user_name"
                                                                           name="line_user_name"
                                                                           required="required"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-md-4">
                                                                        <label for="f_name"
                                                                               class="control-label">ชื่อ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="f_name" name="f_name" required
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="l_name" class="control-label">นามสกุล</label>
                                                                        <input type="text" class="form-control"
                                                                               id="l_name" name="l_name" required
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <label for="line_phone" class="control-label">หมายเลขโทรศัพท์</label>
                                                                        <input type="text" class="form-control"
                                                                               id="line_phone" name="line_phone"
                                                                               required placeholder="">
                                                                    </div>
                                                                </div>


                                                                <div class="form-group">
                                                                    <label for="line_picture_profile"
                                                                           class="control-label">line_picture_profile</label>
                                                                    <textarea class="form-control"
                                                                              id="line_picture_profile"
                                                                              name="line_picture_profile"
                                                                              rows="4"
                                                                              placeholder=""></textarea>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label class="control-label">line_picture_profile</label>
                                                                    <div>
                                                                        <img id="line_picture_profile_img"
                                                                             src=""
                                                                             alt="line_picture_profile"
                                                                             class="img-fluid img-thumbnail"
                                                                             style="max-height: 150px; cursor: pointer;"
                                                                             data-toggle="modal"
                                                                             data-target="#imageModal">
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="user_type" id="user_type" value=""/>
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

                                        <div class="modal fade" id="imageModal" tabindex="-1" role="dialog"
                                             aria-labelledby="imageModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">

                                                    <!-- ปุ่ม X -->
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="imageModalLabel">รูปภาพ</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span> <!-- ตัว X -->
                                                        </button>
                                                    </div>

                                                    <!-- รูปภาพ -->
                                                    <div class="modal-body text-center">
                                                        <img id="modalImage" src="" alt="Preview" class="img-fluid">
                                                    </div>

                                                    <!-- ปุ่ม "ปิด" -->
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">ปิด
                                                        </button>
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
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    //include('includes/Footer.php');
    ?>


    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <!-- Page level plugins -->

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

        $("#line_user_name").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let line_user_id = $('#line_user_id').val();
                let line_user_name = $('#line_user_name').val();
                let formData = {action: "SEARCH", line_user_id: line_user_id, line_user_name: line_user_name};
                $.ajax({
                    url: 'model/manage_house_line_user_process.php',
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
                'lengthMenu': [[5, 10, 20, 50, 100, 500 , 621], [5, 10, 20, 50, 100, 500 , 621]],
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
                    'url': 'model/manage_house_line_user_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'no'},
                    {data: 'line_user_id'},
                    {data: 'line_user_name'},
                    {data: 'f_name'},
                    {data: 'l_name'},
                    {data: 'line_phone'},
                    {
                        data: 'line_picture_profile', // คอลัมน์ที่เก็บ URL รูปภาพ
                        render: function (data, type, row) {
                            let imageUrl = data ? data : 'img/icon/none_img.png'; // ถ้าไม่มี data ใช้รูป default
                            return '<img src="' + imageUrl + '" alt="image" style="width: 50px; height: auto;">';
                        }
                    },
                    {data: 'house_number'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

            $('#btnReload').on('click', function () {
                $('#TableRecordList').DataTable().ajax.reload();
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                //alert(formData);
                $.ajax({
                    url: 'model/manage_house_line_user_process.php',
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
            <!-- *** FOR SUBMIT FORM *** -->


        });

    </script>

    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#line_user_id').val("");
                $('#line_user_name').val("");
                $('#phone_number').val("");
                $('#house_number').val("");
                $('#line_phone').val("");
                $('#line_picture_profile').val("");
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
                url: 'model/manage_house_line_user_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let line_user_id = response[i].line_user_id;
                        let line_user_name = response[i].line_user_name;
                        let phone_number = response[i].phone_number;
                        let house_number = response[i].house_number;
                        let f_name = response[i].f_name;
                        let l_name = response[i].l_name;
                        let line_phone = response[i].line_phone;
                        let line_picture_profile = response[i].line_picture_profile;
                        let user_type = response[i].user_type;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#line_user_id').val(line_user_id);
                        $('#line_user_name').val(line_user_name);
                        $('#phone_number').val(phone_number);
                        $('#house_number').val(house_number);
                        $('#f_name').val(f_name);
                        $('#l_name').val(l_name);
                        $('#line_phone').val(line_phone);
                        $('#line_picture_profile').val(line_picture_profile);

                        $('#line_picture_profile_img').attr('src', line_picture_profile);

                        $('#user_type').val(user_type);
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
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_house_line_user_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let line_user_id = response[i].line_user_id;
                        let line_user_name = response[i].line_user_name;
                        let phone_number = response[i].phone_number;
                        let house_number = response[i].house_number;
                        let f_name = response[i].f_name;
                        let l_name = response[i].l_name;
                        let line_phone = response[i].line_phone;
                        let line_picture_profile = response[i].line_picture_profile;
                        let user_type = response[i].user_type;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#line_user_id').val(line_user_id);
                        $('#line_user_name').val(line_user_name);
                        $('#phone_number').val(phone_number);
                        $('#house_number').val(house_number);
                        $('#f_name').val(f_name);
                        $('#l_name').val(l_name);
                        $('#line_phone').val(line_phone);
                        $('#line_picture_profile').val(line_picture_profile);

                        $('#line_picture_profile_img').attr('src', line_picture_profile);

                        $('#user_type').val(user_type);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Delete Record");
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

    <script>
        $(document).ready(function () {
            $('#line_picture_profile_img').on('click', function () {
                var src = $(this).attr('src');
                $('#modalImage').attr('src', src);
            });
        });
    </script>


    </body>
    </html>

<?php } ?>