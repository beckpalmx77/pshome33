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
                                            <button type='button' name='btnExp' id='btnExp'
                                                    class='btn btn-success btn-xs'>Export Excel
                                                <i class="fa fa-file-excel-o"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>เลขที่บ้าน</th>
                                                    <th>ซอย</th>
                                                    <th>ขนาดพื้นที่บ้าน</th>
                                                    <th>ค่าเก็บขยะ</th>
                                                    <th>ค่าส่วนกลาง</th>
                                                    <th>เลขที่โฉนด</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>เลขที่บ้าน</th>
                                                    <th>ซอย</th>
                                                    <th>ขนาดพื้นที่บ้าน</th>
                                                    <th>ค่าเก็บขยะ</th>
                                                    <th>ค่าส่วนกลาง</th>
                                                    <th>เลขที่โฉนด</th>
                                                    <th>สถานะ</th>
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
                                                                    <label for="house_number" class="control-label">เลขที่บ้าน</label>
                                                                    <input type="house_number" class="form-control"
                                                                           id="house_number" name="house_number"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="alley"
                                                                           class="control-label">ซอย</label>
                                                                    <input type="text" class="form-control"
                                                                           id="alley"
                                                                           name="alley"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="land_no"
                                                                           class="control-label">เลขที่โฉนด</label>
                                                                    <input type="text" class="form-control"
                                                                           id="land_no"
                                                                           name="land_no"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="area_size"
                                                                           class="control-label">ขนาดพื้นที่</label>
                                                                    <input type="text" class="form-control"
                                                                           id="area_size"
                                                                           name="area_size"
                                                                           required="required"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="garbage_collection_fee"
                                                                           class="control-label">ค่าเก็บขยะ</label>
                                                                    <input type="text" class="form-control"
                                                                           id="garbage_collection_fee"
                                                                           name="garbage_collection_fee"
                                                                           required="required"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="common_fee"
                                                                           class="control-label">ค่าส่วนกลาง</label>
                                                                    <input type="text" class="form-control"
                                                                           id="common_fee"
                                                                           name="common_fee"
                                                                           required="required"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="remark"
                                                                           class="control-label">หมายเหตุ</label>
                                                                    <input type="text" class="form-control"
                                                                           id="remark"
                                                                           name="remark"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="status"
                                                                           class="control-label">สถานะ</label>
                                                                    <select id="status" name="status"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option>Y</option>
                                                                        <option>N</option>
                                                                    </select>
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

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

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
        $(document).ready(function () {
            let formData = {action: "GET_HOUSE_MASTER", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[7, 10, 20, 50, 100], [7, 10, 20, 50, 100]],
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
                    'url': 'model/manage_house_master_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'house_number'},
                    {data: 'alley'},
                    {data: 'area_size'},
                    {data: 'garbage_collection_fee'},
                    {data: 'common_fee'},
                    {data: 'land_no'},
                    {data: 'status'},
                    {data: 'update'}
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                //alert(formData);
                $.ajax({
                    url: 'model/manage_house_master_process.php',
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
                $('#house_number').val("");
                $('#alley').val("");
                $('#land_no').val("");
                $('#area_size').val("");
                $('#garbage_collection_fee').val("");
                $('#common_fee').val("");
                $('#remark').val("");
                $('#status').val("Y");
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
                url: 'model/manage_house_master_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let house_number = response[i].house_number;
                        let land_no = response[i].land_no;
                        let alley = response[i].alley;
                        let area_size = response[i].area_size;
                        let garbage_collection_fee = response[i].garbage_collection_fee;
                        let common_fee = response[i].common_fee;
                        let remark = response[i].remark;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#house_number').val(house_number);
                        $('#land_no').val(land_no);
                        $('#alley').val(alley);
                        $('#area_size').val(area_size);
                        $('#garbage_collection_fee').val(garbage_collection_fee);
                        $('#common_fee').val(common_fee);
                        $('#remark').val(remark);
                        $('#status').val(status);
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
                url: 'model/manage_house_master_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let house_number = response[i].house_number;
                        let land_no = response[i].land_no;
                        let alley = response[i].alley;
                        let area_size = response[i].area_size;
                        let garbage_collection_fee = response[i].garbage_collection_fee;
                        let common_fee = response[i].common_fee;
                        let remark = response[i].remark;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#house_number').val(house_number);
                        $('#land_no').val(land_no);
                        $('#alley').val(alley);
                        $('#area_size').val(area_size);
                        $('#garbage_collection_fee').val(garbage_collection_fee);
                        $('#common_fee').val(common_fee);
                        $('#remark').val(remark);
                        $('#status').val(status);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
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
            $("#btnExp").click(function () {
                // ส่วนนี้จะเป็นการเปลี่ยนหน้าไปยังไฟล์ PHP ที่ export Excel
                window.open('export_process/export_house_master_excel.php', '_blank');
            });
        });
    </script>

    </body>
    </html>

<?php } ?>