<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

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
                                <!--div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div-->
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <form method="post" id="MainrecordForm">
                                            <input type="hidden" class="form-control" id="KeyAddData" name="KeyAddData"
                                                   value="">
                                            <div class="modal-body">
                                                <div class="modal-body">
                                                    <div class="form-group row">
                                                        <div class="col-sm-3">
                                                            <!--label for="payment_date"
                                                                   class="control-label">วันที่</label-->
                                                            <input type="hidden" class="form-control"
                                                                   id="payment_date"
                                                                   name="payment_date"
                                                                   required="required"
                                                                   readonly="true"
                                                                   placeholder="วันที่">
                                                            <div class="input-group-addon">
                                                                <span class="glyphicon glyphicon-th"></span>
                                                            </div>
                                                        </div>


                                                    </div>

                                                    <table cellpadding="0" cellspacing="0" border="0"
                                                           class="display"
                                                           id="TableRecordList"
                                                           width="100%">
                                                        <thead>
                                                        <tr>
                                                            <th>วันที่</th>
                                                            <th>บ้านเลขที่</th>
                                                            <th>ซอย</th>
                                                            <th>ผู้ชำระเงิน</th>
                                                            <th>งวดเดือน</th>
                                                            <th>ปี</th>
                                                            <th>จำนวนเงิน</th>
                                                            <th>สลิป</th>
                                                            <th>แก้ไขสลิป</th>
                                                            <th>สถานะ</th>
                                                        </tr>
                                                        </thead>
                                                        <tfoot>
                                                        <tr>
                                                            <th colspan="6" style="text-align:right">ยอดรวม:</th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                        </tr>
                                                        </tfoot>
                                                    </table>

                                                    <input type="hidden" id="status" name="status" value="">

                                                </div>
                                            </div>

                                            <!--?php include("includes/stick_menu.php"); ?-->

                                            <div class="modal-footer">

                                                <input type="hidden" name="id" id="id"/>
                                                <input type="hidden" name="save_status" id="save_status"/>
                                                <input type="hidden" name="action" id="action"
                                                       value=""/>
                                                <!--button type="button" class="btn btn-primary"
                                                        id="btnSave">Save <i
                                                            class="fa fa-check"></i>
                                                </button-->
                                                <button type="button" class="btn btn-danger"
                                                        id="btnClose">Close <i
                                                            class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </form>

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

            <!-- Modal ดูสลิป -->
            <div class="modal fade" id="slipModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">สลิปการชำระเงิน</h5>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body text-center">
                            <img id="slipImage" src="" style="max-width:100%; height:auto;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal แก้ไขสลิป -->
            <div class="modal fade" id="editSlipModal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form id="formUpdateSlip" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title">อัปโหลดสลิปใหม่</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="payment_id" id="payment_id">
                                <div class="form-group">
                                    <label>เลือกสลิปใหม่ (ไฟล์ภาพ)</label>
                                    <input type="file" name="new_slip" id="new_slip" accept="image/*"
                                           class="form-control" required>
                                </div>
                                <div class="text-center">
                                    <img id="previewSlip" src=""
                                         style="max-width:100%;display:none;border:1px solid #ddd;padding:5px;">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">บันทึก</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                            </div>
                        </form>
                    </div>
                </div>
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

            let title = queryString["title"] + " :  " + queryString["payment_date"];
            let data = "<b>" + title + "</b>";
            $("#title").html(data);
            $("#main_menu").html(queryString["main_menu"]);
            $("#sub_menu").html(queryString["sub_menu"]);
            $('#payment_date').val(queryString["payment_date"]);
            $('#save_status').val("before");
            Load_Data_Detail(queryString["payment_date"], "v_ims_house_payment");
        });
    </script>

    <script>
        function Load_Data_Detail(payment_date, table_name) {

            let formData = {
                action: "GET_HOUSE_PAYMENT_DETAIL",
                sub_action: "GET_MASTER",
                payment_date: payment_date,
                table_name: table_name
            };
            let dataRecords = $('#TableRecordList').DataTable({
                "paging": true,
                "ordering": true,
                'info': true,
                "searching": true,
                'lengthMenu': [[10, 15, 24, 50, 100], [10, 15, 24, 50, 100]],
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
                    'url': 'model/manage_house_payment_calendar_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'payment_date', width: '200px'},
                    {data: 'house_number', width: '100px'},
                    {data: 'alley', width: '50px'},
                    {data: 'detail', width: '200px'},
                    {data: 'month_name_period', width: '200px'},
                    {data: 'period_year', width: '80px'},
                    {data: 'amount', className: 'dt-body-right', width: '120px'}, // คอลัมน์ 6 (นับจาก 0)
                    {data: 'slip', width: '80px'},
                    {data: 'slip_update', width: '80px'},
                    {data: 'payment_status_desc', width: '150px'}
                ],
                // >>> เพิ่ม footerCallback ที่นี่ <<<
                'footerCallback': function (row, data, start, end, display) {
                    var api = this.api();

                    // กำหนดตัวเลขคอลัมน์ของ 'amount' (คอลัมน์ที่ 6)
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    // คำนวณผลรวมสำหรับ 'amount' ในหน้าที่แสดงผล
                    total = api
                        .column(6, {page: 'current'}) // ใช้คอลัมน์ที่ 6
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // แสดงผลรวมในช่อง footer ของคอลัมน์ที่ 6
                    // ใช้ .toFixed(2) เพื่อแสดงทศนิยม 2 ตำแหน่ง และ .toLocaleString() เพื่อจัดรูปแบบตัวเลข
                    $(api.column(6).footer()).html(
                        total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' บาท'
                    );
                }
                // >>> สิ้นสุด footerCallback <<<
            });
        }
    </script>

    <script>
        $(document).ready(function () {
            $("#TableRecordList").on('click', '.slip', function () {
                let id = $(this).attr("id");
                $.ajax({
                    url: "display_slip.php",
                    type: "GET",
                    data: {id: id},
                    dataType: "json",
                    success: function (response) {
                        if (response.status === 1) {
                            $("#slipImage").attr("src", response.image_url);
                            $("#slipModal").modal('show');
                        } else {
                            alert("ไม่พบรูปภาพ");
                        }
                    },
                    error: function () {
                        alert("เกิดข้อผิดพลาดในการโหลดรูปภาพ");
                    }
                });
            });
        });
    </script>

    <script>
        // คลิกปุ่ม แก้ไขสลิป
        $("#TableRecordList").on('click', '.slip_update', function () {
            let id = $(this).attr("id");
            $("#payment_id").val(id);
            $("#new_slip").val("");
            $("#previewSlip").hide();
            $("#editSlipModal").modal('show');
        });

        // Preview รูปทันทีที่เลือกไฟล์
        $("#new_slip").change(function () {
            let reader = new FileReader();
            reader.onload = function (e) {
                $("#previewSlip").attr("src", e.target.result).show();
            }
            reader.readAsDataURL(this.files[0]);
        });
    </script>

    <script>
        $("#formUpdateSlip").on('submit', function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            formData.append("action", "UPDATE_SLIP");

            $.ajax({
                url: "model/update_slip.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response === "success") {
                        alert("อัปเดตสลิปเรียบร้อย");
                        $('#editSlipModal').modal('hide');
                        $('#TableRecordList').DataTable().ajax.reload();
                    } else {
                        alert("เกิดข้อผิดพลาด: " + response);
                    }
                }
            });
        });
    </script>


    </body>

    </html>

<?php } ?>



