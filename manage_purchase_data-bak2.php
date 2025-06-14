<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    $curr_date = date("d-m-Y");
    ?>

    <!DOCTYPE html>
    <html lang="th">

    <style>

        .feedback {
            background-color: #31B0D5;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            border-color: #46b8da;
        }


        #menu_fix_button {
            position: fixed;
            bottom: 4px;
            right: 80px;
        }

    </style>

    <body id="page-top">
    <div id="wrapper">


        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><span id="title"></span></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><span id="main_menu"></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><span id="sub_menu"></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <form method="post" id="MainrecordForm">
                                            <input type="hidden" class="form-control" id="KeyAddData" name="KeyAddData"
                                                   value="">
                                            <div class="modal-body">
                                                <div class="modal-body">
                                                    <div class="form-group row">
                                                        <div class="col-sm-2">
                                                            <label for="doc_no"
                                                                   class="control-label">เลขที่เอกสาร
                                                                (สร้างอัตโนมัติ)</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_no" name="doc_no"
                                                                   readonly="true"
                                                                   placeholder="เลขที่เอกสาร">
                                                        </div>

                                                        <div class="col-sm-2">
                                                            <label for="doc_date"
                                                                   class="control-label">วันที่</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_date"
                                                                   name="doc_date"
                                                                   required="required"
                                                                   readonly="true"
                                                                   value="<?php echo $curr_date ?>">
                                                            <div class="input-group-addon">
                                                                <span class="glyphicon glyphicon-th"></span>
                                                            </div>
                                                        </div>

                                                        <input type="hidden" class="form-control" id="supplier_id"
                                                               name="supplier_id">

                                                        <div class="col-sm-6" style="position: relative;">
                                                            <label for="supplier_name"
                                                                   class="control-label">ชื่อผู้ขาย</label>
                                                            <input type="text" class="form-control" id="supplier_name"
                                                                   name="supplier_name" required placeholder="">
                                                            <div id="supplierList" class="list-group"
                                                                 style="position: absolute; z-index: 999; width: 100%;"></div>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label for="CusModal"
                                                                   class="control-label"> เลือกชื่อผู้ขาย/ผู้รับเหมา </label>
                                                            <a data-toggle="modal" href="#SearchSupModal"
                                                               class="btn btn-primary">
                                                                Click <i class="fa fa-search"
                                                                         aria-hidden="true"></i>
                                                            </a>
                                                        </div>

                                                    </div>

                                                    <button type='button' name='btnAdd' id='btnAdd'
                                                            class='btn btn-primary btn-xs'>Add เพิ่มรายการสินค้า
                                                        <i class="fa fa-plus"></i>
                                                    </button>

                                                    <table cellpadding="0" cellspacing="0" border="0"
                                                           class="display"
                                                           id="TablePurchaseDetailList"
                                                           width="100%">
                                                        <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>สินค้า</th>
                                                            <th>จำนวน</th>
                                                            <th>หน่วยนับ</th>
                                                            <th>ราคา/หน่วย</th>
                                                            <th>รวมราคา</th>
                                                            <th>Action</th>
                                                            <th>Action</th>
                                                        </tr>
                                                        </thead>
                                                    </table>

                                                    <div class="form-group">
                                                        <label for="status"
                                                               class="control-label">Status</label>
                                                        <select id="status" name="status"
                                                                class="form-control"
                                                                data-live-search="true"
                                                                title="Please select">
                                                            <option value="Inactive">รอการยืนยันรายการ</option>
                                                            <option value="Active">ยืนยันรายการ</option>

                                                        </select>
                                                    </div>

                                                </div>
                                            </div>

                                            <!--?php include("includes/stick_menu.php"); ?-->

                                            <div class="modal-footer">
                                                <input type="hidden" name="id" id="id"/>
                                                <input type="hidden" name="save_status" id="save_status"/>
                                                <input type="hidden" name="action" id="action"
                                                       value=""/>
                                                <button type="button" class="btn btn-primary"
                                                        id="btnSave">Save <i
                                                            class="fa fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger"
                                                        id="btnClose">Close <i
                                                            class="fa fa-window-close"></i>
                                                </button>
                                            </div>
                                        </form>

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

                                                        <div class="form-group row">
                                                            <div class="col-sm-5">
                                                                <input type="hidden" class="form-control"
                                                                       id="KeyAddDetail"
                                                                       name="KeyAddDetail" value="">
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input type="hidden" class="form-control"
                                                                       id="doc_no_detail"
                                                                       name="doc_no_detail" value="">
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input type="hidden" class="form-control"
                                                                       id="doc_date_detail"
                                                                       name="doc_date_detail" value="">
                                                            </div>
                                                        </div>

                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <div class="form-group row">
                                                                    <div class="col-sm-10" style="position: relative;">
                                                                        <label for="product_name"
                                                                               class="control-label">สินค้า/รายการจ้าง</label>
                                                                        <input type="text" id="product_name"
                                                                               name="product_name" class="form-control">
                                                                        <input type="hidden" id="product_id"
                                                                               name="product_id">
                                                                        <div id="productList" class="list-group"
                                                                             style="position: absolute; z-index: 999;"></div>
                                                                    </div>
                                                                    <div class="col-md-2 d-flex align-items-end">
                                                                        <a data-toggle="modal"
                                                                           href="#SearchProductModal"
                                                                           class="btn btn-primary w-100">
                                                                            Click <i class="fa fa-search"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="quantity"
                                                                               class="control-label">จำนวน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="quantity"
                                                                               name="quantity"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>

                                                                    <input type="hidden" class="form-control"
                                                                           id="unit_id"
                                                                           name="unit_id">
                                                                    <div class="col-sm-5">
                                                                        <label for="unit_name"
                                                                               class="control-label">หน่วยนับ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="unit_name"
                                                                               name="unit_name"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>

                                                                    <div class="col-md-2 d-flex align-items-end">
                                                                        <a data-toggle="modal" href="#SearchUnitModal"
                                                                           class="btn btn-primary w-100">
                                                                            Click <i class="fa fa-search"></i>
                                                                        </a>
                                                                    </div>

                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="price"
                                                                               class="control-label">ราคา/หน่วย</label>
                                                                        <input type="text" class="form-control"
                                                                               id="price"
                                                                               name="price"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-5">
                                                                        <label for="total_price"
                                                                               class="control-label">ราคารวม</label>
                                                                        <input type="text" class="form-control"
                                                                               id="total_price"
                                                                               name="total_price"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="detail_id" id="detail_id"/>
                                                            <input type="hidden" name="action_detail"
                                                                   id="action_detail" value=""/>
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

                                        <div class="modal fade" id="SearchSupModal">
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
                                                                   id="TableSupplierList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัสลูกค้า</th>
                                                                    <th>ชื่อผู้ขาย</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสลูกค้า</th>
                                                                    <th>ชื่อผู้ขาย</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="modal fade" id="SearchProductModal">
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
                                                                   id="TableProductList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>สินค้า</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>สินค้า</th>
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


                                        <div id="result"></div>

                                    </section>


                                </div>

                            </div>

                        </div>

                    </div>
                    <!--Row-->

                    <!-- Row -->

                </div>

                <!---Container Fluid-->

            </div>

            <?php
            include('includes/Modal-Logout.php');
            include('includes/Footer.php');
            ?>

        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Select2 -->
    <script src="vendor/select2/dist/js/select2.min.js"></script>


    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->

    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <script src="js/util.js"></script>
    <script src="js/Calculate.js"></script>
    <!-- Javascript for this page -->

    <script src="js/modal/show_supplier_modal.js"></script>
    <script src="js/modal/show_unit_modal.js"></script>
    <script src="js/modal/show_product_data_modal.js"></script>

    <!--script src="js/modal/show_supplier_modal.js"></script>
    <script src="js/modal/show_unit_modal.js"></script-->

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
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
            $('#doc_date').datepicker({
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
            $("#btnClose").click(function () {
                if ($('#save_status').val() !== '') {
                    window.opener = self;
                    window.close();
                } else {
                    alertify.error("กรุณากด save อีกครั้ง");
                }
            });
        });
    </script>

    <script type="text/javascript">
        let queryString = new Array();
        $(function () {
            if (queryString.length == 0) {
                if (window.location.search.split('?').length > 1) {
                    let params = window.location.search.split('?')[1].split('&');
                    for (let i = 0; i < params.length; i++) {
                        let key = params[i].split('=')[0];
                        let value = decodeURIComponent(params[i].split('=')[1]);
                        queryString[key] = value;
                    }
                }
            }

            let data = "<b>" + queryString["title"] + "</b>";
            $("#title").html(data);
            $("#main_menu").html(queryString["main_menu"]);
            $("#sub_menu").html(queryString["sub_menu"]);
            $('#action').val(queryString["action"]);

            $('#save_status').val("before");

            if (queryString["action"] === 'ADD') {
                let KeyData = generate_token(15);
                $('#KeyAddData').val(KeyData + ":" + Date.now());
                $('#save_status').val("add");
            }

            if (queryString["doc_no"] != null && queryString["supplier_name"] != null) {

                $('#doc_no').val(queryString["doc_no"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#supplier_id').val(queryString["supplier_id"]);
                $('#supplier_name').val(queryString["supplier_name"]);

                //Load_Data_Detail(queryString["doc_no"], "v_purchase_detail");
            }
        });

    </script>


    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                if ($('#doc_date').val() == '' || $('#supplier_name').val() == '') {
                    alertify.error("กรุณาป้อนวันที่ / ชื่อผู้ขาย ");
                } else {
                    $('#recordModal').modal('show');
                    $('#KeyAddDetail').val($('#KeyAddData').val());
                    $('#doc_no_detail').val($('#doc_no').val());
                    $('#doc_date_detail').val($('#doc_date').val());
                    $('#product_name').val("");
                    $('#name_t').val("");
                    $('#quantity').val("");
                    $('#unit_id').val("");
                    $('#unit_name').val("");
                    $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                    $('#action_detail').val('ADD');
                    $('#save').val('Save');
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#supplier_name').keyup(function () {
                let query = $(this).val();
                if (query.length >= 2) {
                    $.ajax({
                        url: "model/search_supplier.php",
                        method: "POST",
                        data: {query: query},
                        success: function (data) {
                            $('#supplierList').fadeIn().html(data);
                        }
                    });
                } else {
                    $('#supplierList').fadeOut();
                }
            });

            // เมื่อคลิกเลือกชื่อผู้ขาย
            $(document).on('click', '.supplier-item', function () {
                let name = $(this).data('name');
                let id = $(this).data('id');

                $('#supplier_name').val(name);
                $('#supplier_id').val(id);
                $('#supplierList').fadeOut();
            });

            // คลิกนอกกล่องแล้วซ่อน suggestion
            $(document).click(function (e) {
                if (!$(e.target).closest('#supplierList, #supplier_name').length) {
                    $('#supplierList').fadeOut();
                }
            });
        });
    </script>

    <script>

        $(document).ready(function () {
            function fetchProductList(query = '') {
                $.ajax({
                    url: "model/search_product.php",
                    method: "POST",
                    data: {query: query},
                    success: function (data) {
                        $('#productList').fadeIn().html(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error, xhr.responseText);
                    }
                });
            }

            $('#product_name').on('keyup', function () {
                let query = $(this).val();
                if (query.length >= 0) {
                    fetchProductList(query);
                } else {
                    $('#productList').fadeOut();
                }
            });

            // ✅ เมื่อคลิกช่อง input ให้แสดงรายการ แม้ยังไม่ได้พิมพ์
            $('#product_name').on('click', function () {
                let query = $(this).val();
                fetchProductList(query);
            });

            // ✅ เมื่อคลิกเลือก item
            $(document).on('click', '.product-item', function () {
                let product_name = $(this).data('product_name');
                let id = $(this).data('id');

                $('#product_name').val(product_name);
                $('#product_id').val(id);
                $('#productList').fadeOut();
            });

            // ✅ ซ่อนรายการเมื่อคลิกนอกกล่อง
            $(document).on('click', function (e) {
                if (!$(e.target).closest('#productList, #product_name').length) {
                    $('#productList').fadeOut();
                }
            });
        });


    </script>

    <script>

        $(document).ready(function () {
            function fetchunitList(query = '') {
                $.ajax({
                    url: "model/search_unit.php",
                    method: "POST",
                    data: {query: query},
                    success: function (data) {
                        $('#unitList').fadeIn().html(data);
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", status, error, xhr.responseText);
                    }
                });
            }

            $('#unit_name').on('keyup', function () {
                let query = $(this).val();
                if (query.length >= 0) {
                    fetchunitList(query);
                } else {
                    $('#unitList').fadeOut();
                }
            });

            // ✅ เมื่อคลิกช่อง input ให้แสดงรายการ แม้ยังไม่ได้พิมพ์
            $('#unit_name').on('click', function () {
                let query = $(this).val();
                fetchunitList(query);
            });

            // ✅ เมื่อคลิกเลือก item
            $(document).on('click', '.unit-item', function () {
                let unit_name = $(this).data('unit_name');
                let id = $(this).data('id');

                $('#unit_name').val(unit_name);
                $('#unit_id').val(id);
                $('#unitList').fadeOut();
            });

            // ✅ ซ่อนรายการเมื่อคลิกนอกกล่อง
            $(document).on('click', function (e) {
                if (!$(e.target).closest('#unitList, #unit_name').length) {
                    $('#unitList').fadeOut();
                }
            });
        });


    </script>


    </html>

<?php } ?>



