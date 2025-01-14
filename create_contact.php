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

                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="customer_id" class="control-label">รหัสลูกค้า</label>
                                                                        <input type="customer_id" class="form-control"
                                                                               id="customer_id" name="customer_id"
                                                                               readonly="true"
                                                                               placeholder="รหัสลูกค้า สร้างอัตโนมัติ">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="f_name"
                                                                               class="control-label">ชื่อ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="f_name"
                                                                               name="f_name"
                                                                               required="required"
                                                                               placeholder="ชื่อ">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="l_name"
                                                                               class="control-label">นามสกุล</label>
                                                                        <input type="text" class="form-control"
                                                                               id="l_name"
                                                                               name="l_name"
                                                                               required="required"
                                                                               placeholder="นามสกุล">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-6">
                                                                        <label for="phone" class="control-label">โทรศัพท์</label>
                                                                        <input type="phone" class="form-control"
                                                                               id="phone" name="phone"
                                                                               placeholder="โทรศัพท์">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="email"
                                                                               class="control-label">อีเมล์</label>
                                                                        <input type="email" class="form-control"
                                                                               id="email"
                                                                               name="email"
                                                                               required="required"
                                                                               placeholder="อีเมล์">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-12">
                                                                        <label for="address"
                                                                               class="control-label">ที่อยู่</label>
                                                                        <input type="address" class="form-control"
                                                                               id="address" name="address"
                                                                               required="required"
                                                                               placeholder="ที่อยู่">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="province_name"
                                                                               class="control-label">จังหวัด</label>
                                                                        <input type="hidden" id="province"
                                                                               name="province">
                                                                        <input type="text" class="form-control"
                                                                               id="province_name"
                                                                               name="province_name"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="จังหวัด">
                                                                    </div>
                                                                    <div class="col-sm-2">
                                                                        <label for="province"
                                                                               class="control-label">เลือก</label>

                                                                        <a data-toggle="modal"
                                                                           href="#SearchProvinceModal"
                                                                           class="btn btn-primary">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </a>
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="amphure_name"
                                                                               class="control-label">เขต/อำเภอ</label>
                                                                        <input type="hidden" id="amphure"
                                                                               name="amphure">
                                                                        <input type="text" class="form-control"
                                                                               id="amphure_name"
                                                                               name="amphure_name"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="เขต/อำเภอ">

                                                                    </div>
                                                                    <div class="col-sm-2">
                                                                        <label for="amphure"
                                                                               class="control-label">เลือก</label>

                                                                        <!--a data-toggle="modal"
                                                                           href="#SearchAmphureModal"
                                                                           class="btn btn-primary">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </a-->
                                                                        <button type="button"
                                                                                class="btn btn-primary"
                                                                                data-toggle="modal"
                                                                                data-target="#SearchAmphureModal"
                                                                                id="AmphureBtn">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </button>

                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="tumbol" class="control-label">แขวง/ตำบล</label>
                                                                        <input type="hidden" id="tumbol" name="tumbol">
                                                                        <input type="text" class="form-control"
                                                                               id="tumbol_name"
                                                                               name="tumbol_name"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="แขวง/ตำบล">
                                                                    </div>
                                                                    <div class="col-sm-2">
                                                                        <label for="tumbol"
                                                                               class="control-label">เลือก</label>

                                                                        <a data-toggle="modal"
                                                                           href="#SearchTumbolModal"
                                                                           class="btn btn-primary">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </a>
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="zipcode"
                                                                               class="control-label">รหัสไปรษณีย์</label>
                                                                        <input id="zipcode" type="text" name="zipcode"
                                                                               class="form-control"
                                                                               placeholder="">
                                                                        <div class="help-block with-errors"></div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="status"
                                                                       class="control-label">Status</label>
                                                                <select id="status" name="status"
                                                                        class="form-control"
                                                                        data-live-search="true"
                                                                        title="Please select">
                                                                    <option>Active</option>
                                                                    <option>Inactive</option>
                                                                </select>
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

    <?php
    include('includes/Modal_search_province_amphure_tumbol.php');
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

    <script src="js/modal/show_province_modal.js"></script>
    <script src="js/modal/show_amphure_modal.js"></script>
    <script src="js/modal/show_tumbol_modal.js"></script>

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

        $("#customer_id").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let customer_id = $('#customer_id').val();
                let formData = {action: "SEARCH", customer_id: customer_id};
                $.ajax({
                    url: 'model/manage_customer_process.php',
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
            let formData = {action: "GET_CUSTOMER", sub_action: "GET_MASTER"};
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
                    'url': 'model/manage_customer_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'customer_id'},
                    {data: 'f_name'},
                    {data: 'l_name'},
                    {data: 'phone'},
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
                    url: 'model/manage_customer_process.php',
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
                $('#customer_id').val("");
                $('#f_name').val("");
                $('#l_name').val("");
                $('#address').val("");
                $('#phone').val("");
                $('#tumbol').val("");
                $('#tumbol_name').val("");
                $('#amphure').val("");
                $('#amphure_name').val("");
                $('#province').val("");
                $('#province_name').val("");
                $('#zipcode').val("");
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
                url: 'model/manage_customer_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let customer_id = response[i].customer_id;
                        let f_name = response[i].f_name;
                        let l_name = response[i].l_name;
                        let address = response[i].address;
                        let phone = response[i].phone;
                        let email = response[i].email;
                        let province = response[i].province;
                        let province_name = response[i].province_name;
                        let amphure = response[i].amphure;
                        let amphure_name = response[i].amphure_name;
                        let tumbol = response[i].tumbol;
                        let tumbol_name = response[i].tumbol_name;
                        let zipcode = response[i].zipcode;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#customer_id').val(customer_id);
                        $('#f_name').val(f_name);
                        $('#l_name').val(l_name);
                        $('#address').val(address);
                        $('#phone').val(phone);
                        $('#province').val(province);
                        $('#province_name').val(province_name);
                        $('#amphure').val(amphure);
                        $('#amphure_name').val(amphure_name);
                        $('#tumbol').val(tumbol);
                        $('#tumbol_name').val(tumbol_name);
                        $('#zipcode').val(zipcode);
                        $('#email').val(email);
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
                url: 'model/manage_customer_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let customer_id = response[i].customer_id;
                        let f_name = response[i].f_name;
                        let l_name = response[i].l_name;
                        let address = response[i].address;
                        let phone = response[i].phone;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#customer_id').val(customer_id);
                        $('#f_name').val(f_name);
                        $('#l_name').val(l_name);
                        $('#address').val(address);
                        $('#phone').val(phone);
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

    <script type="text/javascript">
        $("#AmphureBtn").click(function () {
            let province = $("#province").val();
            let province_name = $("#province_name").val();
            let str = "You Have Entered "
                + "province: " + province
                + " and province_name: " + province_name;
            $("#province_para").val(province);
            $("#province_name_para").val(province_name);
        });
    </script>



    </body>
    </html>

<?php } ?>

