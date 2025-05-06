<?php
include('includes/Header.php');
$curr_date = date("d-m-Y");
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
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page']?>">Home</a></li>
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
                                            <label for="description"
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
                                                    <th>วันที่</th>
                                                    <th>เดือน</th>
                                                    <th>ปี</th>
                                                    <th>รายการ</th>
                                                    <th>ประเภทค่าใช้จ่าย</th>
                                                    <th>จำนวนรายการ</th>
                                                    <th>หน่วยนับ</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>การตรวจสอบ</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>วันที่</th>
                                                    <th>เดือน</th>
                                                    <th>ปี</th>
                                                    <th>รายการ</th>
                                                    <th>ประเภทค่าใช้จ่าย</th>
                                                    <th>จำนวนรายการ</th>
                                                    <th>หน่วยนับ</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>การตรวจสอบ</th>
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

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="expense_date"
                                                                               class="control-label">วันที่ทำรายการ</label>
                                                                        <i class="fa fa-calendar"
                                                                           aria-hidden="true"></i>
                                                                        <input type="text" class="form-control"
                                                                               id="expense_date"
                                                                               name="expense_date"
                                                                               value="<?php echo $curr_date ?>"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>

                                                                    <div class="col-sm-8">
                                                                        <label for="description"
                                                                               class="control-label">รายการค่าใช้จ่าย</label>
                                                                        <input type="text" class="form-control"
                                                                               id="description"
                                                                               name="description"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <input type="hidden" class="form-control"
                                                                           id="category_id"
                                                                           name="category_id">
                                                                    <div class="col-sm-10">
                                                                        <label for="category_name"
                                                                               class="control-label">ประเภทค่าใช้จ่าย</label>
                                                                        <input type="text" class="form-control"
                                                                               id="category_name"
                                                                               name="category_name"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>

                                                                    <div class="col-sm-2">
                                                                        <label for="qty"
                                                                               class="control-label">เลือก</label>

                                                                        <a data-toggle="modal" href="#Search-PG-Modal"
                                                                           class="btn btn-primary">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="qty"
                                                                               class="control-label">จำนวน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="qty"
                                                                               name="qty"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>
                                                                    <input type="hidden" class="form-control"
                                                                           id="unit_id"
                                                                           name="unit_id">
                                                                    <div class="col-sm-5">
                                                                        <label for="qty"
                                                                               class="control-label">หน่วยนับ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="unit_name"
                                                                               name="unit_name"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>

                                                                    <div class="col-sm-2">
                                                                        <label for="qty"
                                                                               class="control-label">เลือก</label>

                                                                        <a data-toggle="modal" href="#SearchUnitModal"
                                                                           class="btn btn-primary">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="amount"
                                                                               class="control-label">จำนวนเงิน (บาท)</label>
                                                                        <input type="text" class="form-control"
                                                                               id="amount"
                                                                               name="amount"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="inv"
                                                                               class="control-label">หมายเลขใบเสร็จฯ / Invoice</label>
                                                                        <input type="text" class="form-control"
                                                                               id="inv"
                                                                               name="inv"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="remark"
                                                                               class="control-label">หมายเหตุ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="remark"
                                                                               name="remark"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="approve_status" class="control-label">การตรวจสอบรายการ</label>
                                                                    <select id="approve_status" name="approve_status"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option value="N" selected>ยังไม่ยืนยัน (รอตรวจสอบ)</option>
                                                                        <option value="Y">ยืนยันรายการ (อนุมัติ)</option>
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
                                                                        class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="Search-PG-Modal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableCatList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>ประเภทค่าใช้จ่าย</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>ประเภทค่าใช้จ่าย</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchUnitModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableUnitList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>หน่วยนับ</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>หน่วยนับ</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
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



    <!--script src="js/modal/show_brand_modal.js"></script-->
    <script src="js/modal/show_category_modal.js"></script>
    <script src="js/modal/show_unit_modal.js"></script>

    <!-- Page level plugins -->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

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

        $("#expense_date").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let expense_date = $('#expense_date').val();
                let formData = {action: "SEARCH", expense_date: expense_date};
                $.ajax({
                    url: 'model/manage_expense_process.php',
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
            let formData = {action: "GET_EXPENSE", sub_action: "GET_MASTER"};
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
                    'url': 'model/manage_expense_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'expense_date'},
                    {data: 'month_name'},
                    {data: 'exp_year'},
                    {data: 'description'},
                    {data: 'category_name'},
                    {data: 'qty', className: 'text-right'},
                    {data: 'unit_name'},
                    {data: 'amount', className: 'text-right'},
                    {data: 'approve_status'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_expense_process.php',
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

                let today = new Date();
                let day = String(today.getDate()).padStart(2, '0');
                let month = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
                let year = today.getFullYear();
                let formattedDate = day + '-' + month + '-' + year;

                $('#recordModal').modal('show');
                $('#id').val("");
                $('#expense_date').val(formattedDate);
                $('#description').val("");
                $('#category_id').val("");
                $('#category_name').val("");
                $('#unit_id').val("");
                $('#unit_name').val("");
                $('#remark').val("");
                $('#inv').val("");
                $('#qty').val("");
                $('#amount').val("");
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
                url: 'model/manage_expense_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let expense_date = response[i].expense_date;
                        let description = response[i].description;
                        let category_id = response[i].category_id;
                        let category_name = response[i].category_name;
                        let inv = response[i].inv;
                        let qty = response[i].qty;
                        let unit_id = response[i].unit_id;
                        let unit_name = response[i].unit_name;
                        let amount = response[i].amount;
                        let remark = response[i].remark;
                        let approve_status = response[i].approve_status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#expense_date').val(expense_date);
                        $('#description').val(description);
                        $('#category_id').val(category_id);
                        $('#category_name').val(category_name);
                        $('#inv').val(inv);
                        $('#qty').val(qty);
                        $('#unit_id').val(unit_id);
                        $('#unit_name').val(unit_name);
                        $('#amount').val(amount);
                        $('#remark').val(remark);
                        $('#approve_status').val(approve_status);
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
                url: 'model/manage_expense_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let expense_date = response[i].expense_date;
                        let description = response[i].description;
                        let category_id = response[i].category_id;
                        let category_name = response[i].category_name;
                        let inv = response[i].inv;
                        let qty = response[i].qty;
                        let unit_id = response[i].unit_id;
                        let unit_name = response[i].unit_name;
                        let amount = response[i].amount;
                        let remark = response[i].remark;
                        let approve_status = response[i].approve_status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#expense_date').val(expense_date);
                        $('#description').val(description);
                        $('#category_id').val(category_id);
                        $('#category_name').val(category_name);
                        $('#inv').val(inv);
                        $('#qty').val(qty);
                        $('#unit_id').val(unit_id);
                        $('#unit_name').val(unit_name);
                        $('#amount').val(amount);
                        $('#remark').val(remark);
                        $('#approve_status').val(approve_status);
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
            $('#expense_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>



    </body>
    </html>

<?php } ?>