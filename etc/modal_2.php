<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <body id="page-top">
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <!-- Page level plugins -->

    <script src="่js/bootbox.js"></script>
    <script src="js/datatables-jquery.js"></script>

    <link rel="stylesheet" href="css/dataTables.min.css"/>
    <link rel="stylesheet" href="css/buttons.dataTables.css"/>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>

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
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>

                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>รหัสสินค้า/วัสดุ</th>
                                                    <th>ชื่อสินค้า/วัสดุ</th>
                                                    <th>ยอดคงเหลือ</th>
                                                    <th>หน่วยนับ</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>รหัสสินค้า/วัสดุ</th>
                                                    <th>ชื่อสินค้า/วัสดุ</th>
                                                    <th>ยอดคงเหลือ</th>
                                                    <th>หน่วยนับ</th>
                                                    <th>Status</th>
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
                                                    <div class="container"></div>
                                                    <div class="modal-body">
                                                        <div class="modal-body">
                                                            <div class="form-group"
                                                            <label for="product_id" class="control-label">รหัสสินค้า/วัสดุ</label>
                                                            <input type="product_id" class="form-control"
                                                                   id="product_id" name="product_id"
                                                                   required="required"
                                                                   placeholder="รหัสสินค้า/วัสดุ">
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="name_t"
                                                                   class="control-label">ชื่อสินค้า/วัสดุ</label>
                                                            <input type="text" class="form-control" id="name_t"
                                                                   name="name_t"
                                                                   required="required"
                                                                   placeholder="ชื่อสินค้า/วัสดุ">
                                                        </div>

                                                        <div class="form-group row">
                                                            <div class="col-sm-4">
                                                                <label for="quantity"
                                                                       class="control-label">ยอดคงเหลือ</label>
                                                                <input type="text" class="form-control" id="quantity"
                                                                       name="quantity"
                                                                       required="required"
                                                                       placeholder="ยอดคงเหลือ">
                                                            </div>
                                                            <input type="hidden" class="form-control" id="unit_id"
                                                                   name="unit_id">
                                                            <div class="col-sm-6">
                                                                <label for="quantity"
                                                                       class="control-label">หน่วยนับ</label>
                                                                <input type="text" class="form-control" id="unit_name"
                                                                       name="unit_name"
                                                                       required="required"
                                                                       placeholder="หน่วยนับ">
                                                            </div>

                                                            <div class="col-sm-2">
                                                                <label for="quantity"
                                                                       class="control-label">เลือก</label>

                                                                <a data-toggle="modal" href="#SearchModal"
                                                                   class="btn btn-primary">
                                                                    Click <i class="fa fa-search"
                                                                             aria-hidden="true"></i>
                                                                </a>
                                                            </div>
                                                        </div>

                                                        <div class="form-group">
                                                            <label for="status" class="control-label"></label>
                                                            <select id="status" name="status"
                                                                    class="form-control" data-live-search="true"
                                                                    title="Please select">
                                                                <option>Active</option>
                                                                <option>InActive</option>
                                                            </select>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="hidden" name="id" id="id"/>
                                                    <input type="hidden" name="action" id="action" value=""/>
                                                    <input type="submit" name="save" id="save" class="btn btn-primary"
                                                           value="Save"/>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">
                                                        Close
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                </div>


                                <div class="modal fade" id="SearchModal">
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

                                                    <table cellpadding="0" cellspacing="0" border="0" class="display"
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

                                <script>

                                    $("#product_id").blur(function () {
                                        let method = $('#action').val();
                                        if (method === "ADD") {
                                            let product_id = $('#product_id').val();
                                            let formData = {action: "SEARCH", product_id: product_id};
                                            $.ajax({
                                                url: 'model/manage_product_process.php',
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

                                        let dataRecords = $('#TableRecordList').DataTable({
                                            'processing': true,
                                            'serverSide': true,
                                            'serverMethod': 'post',
                                            'ajax': {
                                                'url': 'model/get_product_records.php'
                                            },
                                            'columns': [
                                                {data: 'product_id'},
                                                {data: 'name_t'},
                                                {data: 'quantity', className: 'text-right'},
                                                {data: 'unit_name'},
                                                {data: 'status'},
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
                                                url: 'model/manage_product_process.php',
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
                                            $('#product_id').val("");
                                            $('#name_t').val("");
                                            $('#quantity').val("");
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
                                            url: 'model/manage_product_process.php',
                                            dataType: "json",
                                            data: formData,
                                            success: function (response) {
                                                let len = response.length;
                                                for (let i = 0; i < len; i++) {
                                                    let id = response[i].id;
                                                    let product_id = response[i].product_id;
                                                    let name_t = response[i].name_t;
                                                    let quantity = response[i].quantity;
                                                    let unit_id = response[i].unit_id;
                                                    let unit_name = response[i].unit_name;
                                                    let status = response[i].status;

                                                    $('#recordModal').modal('show');
                                                    $('#id').val(id);
                                                    $('#product_id').val(product_id);
                                                    $('#name_t').val(name_t);
                                                    $('#quantity').val(quantity);
                                                    $('#unit_id').val(unit_id);
                                                    $('#unit_name').val(unit_name);
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
                                        let formData = {action: "GET_DATA", id: id};
                                        $.ajax({
                                            type: "POST",
                                            url: 'model/manage_product_process.php',
                                            dataType: "json",
                                            data: formData,
                                            success: function (response) {
                                                let len = response.length;
                                                for (let i = 0; i < len; i++) {
                                                    let id = response[i].id;
                                                    let product_id = response[i].product_id;
                                                    let name_t = response[i].name_t;
                                                    let quantity = response[i].quantity;
                                                    let unit_id = response[i].unit_id;
                                                    let unit_name = response[i].unit_name;
                                                    let status = response[i].status;

                                                    $('#recordModal').modal('show');
                                                    $('#id').val(id);
                                                    $('#product_id').val(product_id);
                                                    $('#name_t').val(name_t);
                                                    $('#quantity').val(quantity);
                                                    $('#unit_id').val(unit_id);
                                                    $('#unit_name').val(unit_name);
                                                    $('#status').val(status);
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
                                    $(document).ready(function () {
                                        let dataRecords = $('#TableUnitList').DataTable({
                                            'lengthMenu': [[5, 10, 20, 100], [5, 10, 20, 100]],
                                            'language': {
                                                search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                                                info: 'หน้าที่ _PAGE_ of _PAGES_',
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
                                                'url': 'model/get_unit_records.php'
                                            },
                                            'columns': [
                                                {data: 'unit_id'},
                                                {data: 'unit_name'},
                                                {data: 'select'}
                                            ]
                                        });
                                    });
                                </script>

                                <script>

                                    $("#TableUnitList").on('click', '.select', function () {
                                        let data = this.id.split('@');
                                        $('#unit_id').val(data[0]);
                                        $('#unit_name').val(data[1]);
                                        $('#SearchModal').modal('hide');
                                    });

                                </script>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </body>
    </html>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>

<?php } ?>