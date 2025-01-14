<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
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
                        <h1 class="h3 mb-0 text-gray-800">ข้อความติดต่อ</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item">จัดการข้อความ</li>
                            <li class="breadcrumb-item active"
                                aria-current="page">ข้อความติดต่อ
                            </li>
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
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>วันที่รับข้อความ</th>
                                                    <th>ชื่อ</th>
                                                    <th>นามสกุล</th>
                                                    <th>อีเมล์</th>
                                                    <th>โทรศัพท์</th>
                                                    <th>เวลาที่สะดวกติดต่อ</th>
                                                    <th>สถานะ</th>
                                                    <th>action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>วันที่รับข้อความ</th>
                                                    <th>ชื่อ</th>
                                                    <th>นามสกุล</th>
                                                    <th>อีเมล์</th>
                                                    <th>โทรศัพท์</th>
                                                    <th>เวลาที่สะดวกติดต่อ</th>
                                                    <th>สถานะ</th>
                                                    <th>action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>

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

                                        <div class="form-group row">
                                            <div class="col-sm-6">
                                                <label for="f_name" class="control-label">ชื่อ</label>
                                                <input type="f_name" class="form-control"
                                                       id="f_name" name="f_name"
                                                       readonly="true"
                                                       placeholder="ชื่อ">
                                            </div>

                                            <div class="col-sm-6">
                                                <label for="l_name"
                                                       class="control-label">นามสกุล</label>
                                                <input type="text" class="form-control"
                                                       id="l_name" name="l_name"
                                                       readonly="true"
                                                       placeholder="นามสกุล">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-sm-6">
                                                <label for="phone" class="control-label">โทรศัพท์</label>
                                                <input type="phone" class="form-control"
                                                       id="phone" name="phone"
                                                       readonly="true"
                                                       placeholder="โทรศัพท์">
                                            </div>

                                            <div class="col-sm-6">
                                                <label for="email"
                                                       class="control-label">อีเมล์</label>
                                                <input type="text" class="form-control"
                                                       id="email" name="email"
                                                       readonly="true"
                                                       placeholder="อีเมล์">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="status" class="control-label">Status</label>
                                            <select id="status" name="status"
                                                    class="form-control" data-live-search="true"
                                                    title="Please select">
                                                <option value="N">ยังไม่ได้ติดต่อ</option>
                                                <option value="Y">ติดต่อแล้ว</option>
                                            </select>
                                        </div>


                                        <div class="form-group row">
                                            <div class="col-sm-6">
                                                <label for="contact_name" class="control-label">ผู้ติดต่อ</label>
                                                <input type="text" class="form-control"
                                                       id="contact_name" name="contact_name"
                                                       placeholder="ผู้ติดต่อ">
                                            </div>
                                            <div class="col-sm-3">
                                                <label for="contact_date"
                                                       class="control-label">วันที่่ติดต่อ</label>
                                                <input type="text" class="form-control"
                                                       id="contact_date"
                                                       name="contact_date"
                                                       required="required"
                                                       readonly="true"
                                                       placeholder="วันที่่ติดต่อ">
                                                <div class="input-group-addon">
                                                    <span class="glyphicon glyphicon-th"></span>
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <label for="contact_time" class="control-label">เวลาที่ติดต่อ</label>
                                                <input type="time" class="form-control"
                                                       id="contact_time" name="contact_time"
                                                       placeholder="เวลาที่ติดต่อ">
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-sm-6">
                                                <label for="update_date"
                                                       class="control-label">ปรับปรุงล่าสุด</label>
                                                <input type="text" class="form-control"
                                                       id="update_date" name="update_date"
                                                       readonly="true"
                                                       placeholder="ปรับปรุงล่าสุด">
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
                                                class="fa fa-window-close"></i>
                                    </button>
                                </div>
                            </form>

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

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>


    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

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
            $('#contact_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

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
        $(document).ready(function () {

            let formData = {action: "GET_MESSAGE", sub_action: "GET_MASTER"};

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
                'ajax': {
                    'url': 'model/manage_message_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'create_date'},
                    {data: 'f_name'},
                    {data: 'l_name'},
                    {data: 'email'},
                    {data: 'phone'},
                    {data: 'time_contact'},
                    {data: 'status'},
                    {data: 'update'}
                ]
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
                url: 'model/manage_message_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let f_name = response[i].f_name;
                        let l_name = response[i].l_name;
                        let phone = response[i].phone;
                        let email = response[i].email;
                        let contact_name = response[i].contact_name;
                        let contact_date = response[i].contact_date;
                        let contact_time = response[i].contact_time;
                        let status = response[i].status;
                        let update_date = response[i].update_date;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#f_name').val(f_name);
                        $('#l_name').val(l_name);
                        $('#phone').val(phone);
                        $('#email').val(email);
                        $('#contact_name').val(contact_name);
                        $('#contact_date').val(contact_date);
                        $('#contact_time').val(contact_time);
                        $('#status').val(status);
                        $('#update_date').val(update_date);
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
        $(document).ready(function () {
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_message_process.php',
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


    </body>
    </html>

<?php } ?>