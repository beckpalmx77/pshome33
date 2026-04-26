<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['display_name']) == "") {
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

                                            <!--button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button-->
                                            <button type="button" id="btnReload" class="btn btn-outline-success btn-xs" data-toggle="tooltip" title="Reload Data">
                                                <i class="fa fa-refresh"></i> Reload
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>ชื่อ Line</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>รูป</th>
                                                    <th>เวลา</th>
                                                    <th>รายละเอียด</th>
                                                    <th>จุด Check In</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>ชื่อ Line</th>
                                                    <th>ชื่อ-นามสกุล</th>
                                                    <th>รูป</th>
                                                    <th>เวลา</th>
                                                    <th>รายละเอียด</th>
                                                    <th>จุด Check In</th>
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
                                                                    <label for="display_name"
                                                                           class="control-label">ชื่อ</label>
                                                                    <input type="display_name" class="form-control"
                                                                           id="display_name" name="display_name"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="checkin_time"
                                                                           class="control-label">เวลา</label>
                                                                    <input type="text" class="form-control"
                                                                           id="checkin_time"
                                                                           name="checkin_time"
                                                                           required="required"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="check_type"
                                                                           class="control-label">รายละเอียด</label>
                                                                    <input type="text" class="form-control"
                                                                           id="check_type"
                                                                           name="check_type"
                                                                           required="required"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-12">
                                                                        <label for="images">ภาพ: (Cick ที่รูปเพื่อขยาย)</label>
                                                                        <div id="imagePreview" class="d-flex flex-wrap gap-2"></div>
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

                                        <div class="modal fade" id="imageModal" tabindex="-1" role="dialog"
                                             aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content bg-dark">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <!-- ภาพที่จะแสดงใน modal -->
                                                        <img id="modalImage" src="" class="img-fluid rounded"
                                                             style="max-height: 80vh;">
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
    </div>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
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

        $("#checkin_time").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let display_name = $('#display_name').val();
                let checkin_time = $('#checkin_time').val();
                let formData = {action: "SEARCH", display_name: display_name, checkin_time: checkin_time};
                $.ajax({
                    url: 'model/manage_check_in_out_process.php',
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
            let formData = {action: "GET_CHECK_IN_OUT", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
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
                    'url': 'model/manage_check_in_out_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'display_name'},
                    {data: 'emp_name'},
                    {
                        data: 'line_picture_profile', // คอลัมน์ที่เก็บ URL รูปภาพ
                        render: function(data, type, row) {
                            let imageUrl = data ? data : 'img/icon/none_img.png'; // ถ้าไม่มี data ใช้รูป default
                            return '<img src="' + imageUrl + '" alt="image" style="width: 50px; height: auto;">';
                        }
                    },
                    {data: 'checkin_time'},
                    {data: 'check_type'},
                    {data: 'map_link' },
                    {data: 'detail'}
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
                    url: 'model/manage_check_in_out_process.php',
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
                $('#display_name').val("");
                $('#checkin_time').val("");
                $('#check_type').val("");
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
                url: 'model/manage_check_in_out_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let display_name = response[i].display_name;
                        let checkin_time = response[i].checkin_time;
                        let check_type = response[i].check_type;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#display_name').val(display_name);
                        $('#checkin_time').val(checkin_time);
                        $('#check_type').val(check_type);
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
                url: 'model/manage_check_in_out_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let display_name = response[i].display_name;
                        let checkin_time = response[i].checkin_time;
                        let check_type = response[i].check_type;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#display_name').val(display_name);
                        $('#checkin_time').val(checkin_time);
                        $('#check_type').val(check_type);
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

    <script>
        $("#TableRecordList").on('click', '.detail', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_check_in_out_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let display_name = response[i].display_name;
                        let checkin_time = response[i].checkin_time;
                        let check_type = response[i].check_type;
                        let images = response[i].images; // เพิ่มตรงนี้

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#display_name').val(display_name);
                        $('#checkin_time').val(checkin_time);
                        $('#check_type').val(check_type);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action').val('UPDATE');
                        $('#save').val('Save').prop('disabled', true);

                        $('#imagePreview').html('');

                        if (images && images.trim() !== "") {
                            let filenames = images.split(',');
                            filenames.forEach(filename => {
                                filename = filename.trim();
                                if (filename !== "") {
                                    let imgTag = `<img src="line_oa/checkin/uploads/${filename}" class="img-thumbnail m-1 img-preview" style="height: 100px; cursor: pointer;">`;
                                    $('#imagePreview').append(imgTag);
                                }
                            });
                        }

                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });
    </script>

    <script>
        $(document).on('click', '.img-preview', function () {
            let imgSrc = $(this).attr('src');  // ดึง src ของภาพที่ถูกคลิก
            $('#modalImage').attr('src', imgSrc);  // เปลี่ยน src ของ modal image ให้เป็นภาพที่ถูกคลิก
            $('#imageModal').modal('show');  // แสดง modal ที่มีภาพขนาดใหญ่
        });
    </script>


    </body>
    </html>

<?php } ?>