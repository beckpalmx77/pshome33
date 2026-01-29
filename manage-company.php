<?php
include('includes/Header.php');
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
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>ชื่อบริษัท</th>
                                                    <th>เบอร์โทร</th>
                                                    <th>เว็บไซต์</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>ชื่อบริษัท</th>
                                                    <th>เบอร์โทร</th>
                                                    <th>เว็บไซต์</th>
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
                                                            <h6 class="font-weight-bold text-primary">ข้อมูลบริษัท</h6>
                                                            <div class="form-group">
                                                                <label for="company_name" class="control-label">ชื่อบริษัท <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="company_name" name="company_name" required placeholder="ระบุชื่อบริษัท">
                                                            </div>

                                                            <hr>
                                                            <h6 class="font-weight-bold text-primary">ที่อยู่</h6>

                                                            <div class="form-group">
                                                                <label for="address_1" class="control-label">ที่อยู่ 1</label>
                                                                <input type="text" class="form-control" id="address_1" name="address_1" placeholder="บ้านเลขที่, อาคาร, ซอย">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="address_2" class="control-label">ที่อยู่ 2</label>
                                                                <input type="text" class="form-control" id="address_2" name="address_2" placeholder="ถนน, แขวง/ตำบล">
                                                            </div>

                                                            <div class="form-group row">
                                                                <div class="col-sm-6">
                                                                    <label for="state" class="control-label">จังหวัด</label>
                                                                    <input type="text" class="form-control" id="state" name="state" placeholder="ระบุจังหวัด">
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label for="zip_code" class="control-label">รหัสไปรษณีย์</label>
                                                                    <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="ระบุรหัสไปรษณีย์">
                                                                </div>
                                                            </div>

                                                            <hr>
                                                            <h6 class="font-weight-bold text-primary">ข้อมูลติดต่อ</h6>
                                                            <div class="form-group row">
                                                                <div class="col-sm-6">
                                                                    <label for="phone" class="control-label">เบอร์โทรศัพท์</label>
                                                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="ระบุเบอร์โทรศัพท์">
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <label for="website" class="control-label">เว็บไซต์ <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" id="website" name="website" required placeholder="https://...">
                                                                </div>
                                                            </div>

                                                            <hr>
                                                            <h6 class="font-weight-bold text-primary">ข้อมูลธนาคาร</h6>

                                                            <div class="form-group row">
                                                                <div class="col-sm-4">
                                                                    <label for="bank_name" class="control-label">ชื่อธนาคาร <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" id="bank_name" name="bank_name" required placeholder="ระบุธนาคาร">
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <label for="bank_account_no" class="control-label">เลขที่บัญชี <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" id="bank_account_no" name="bank_account_no" required placeholder="ระบุเลขที่บัญชี">
                                                                </div>
                                                                <div class="col-sm-4">
                                                                    <label for="bank_account_name" class="control-label">ชื่อบัญชี <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" id="bank_account_name" name="bank_account_name" required placeholder="ระบุชื่อบัญชี">
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
        // Check duplicate company name
        $("#company_name").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let company_name = $('#company_name').val();
                let formData = {action: "SEARCH", company_name: company_name};
                $.ajax({
                    url: 'model/manage_company_process.php',
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
            // Load Table
            let formData = {action: "GET_COMPANY", sub_action: "GET_MASTER"};
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
                    'url': 'model/manage_company_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'company_name'},
                    {data: 'phone'},
                    {data: 'website'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });

            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_company_process.php',
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
                $('#recordForm')[0].reset(); // Reset form
                $('#id').val("");
                $('.modal-title').html("<i class='fa fa-plus'></i> เพิ่มข้อมูลบริษัท");
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
                url: 'model/manage_company_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let company_name = response[i].company_name;
                        let address_1 = response[i].address_1;
                        let address_2 = response[i].address_2;
                        let state = response[i].state;
                        let zip_code = response[i].zip_code;
                        let phone = response[i].phone;
                        let bank_name = response[i].bank_name;
                        let bank_account_name = response[i].bank_account_name;
                        let bank_account_no = response[i].bank_account_no;
                        let website = response[i].website;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#company_name').val(company_name);
                        $('#address_1').val(address_1);
                        $('#address_2').val(address_2);
                        $('#state').val(state);
                        $('#zip_code').val(zip_code);
                        $('#phone').val(phone);
                        $('#bank_name').val(bank_name);
                        $('#bank_account_name').val(bank_account_name);
                        $('#bank_account_no').val(bank_account_no);
                        $('#website').val(website);

                        $('.modal-title').html("<i class='fa fa-edit'></i> แก้ไขข้อมูลบริษัท");
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
                url: 'model/manage_company_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let company_name = response[i].company_name;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#company_name').val(company_name);

                        $('.modal-title').html("<i class='fa fa-minus'></i> ลบข้อมูลบริษัท");
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