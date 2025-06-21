<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

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
                        <input type="hidden" id="account_type" name="account_type"
                               value="<?php echo $_SESSION['account_type']; ?>">
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

                                        <!--div class="col-md-12 col-md-offset-2">
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>
                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div-->

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id="TableRecordList" class="display nowrap" style="width:100%;">
                                                <thead>
                                                <tr>
                                                    <th>id</th>
                                                    <th>บ้านเลขที่</th>
                                                    <th>งวดเดือน</th>
                                                    <th>งวดเดือน</th>
                                                    <th>ปี</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>Slip</th>
                                                    <th>สถานะ</th>
                                                    <th>ลบข้อมูล</th>
                                                </tr>
                                                </thead>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <!-- Modal -->
                                        <div class="modal fade" id="slipModal" tabindex="-1" role="dialog"
                                             aria-labelledby="slipModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content text-center">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="slipModalLabel">
                                                            หลักฐานการโอนเงิน</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                            <!-- ปุ่มปิดมุมขวาบน -->
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img id="slipImage" src="" alt="Slip Image"
                                                             class="img-fluid rounded shadow-sm">
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <!--a id="downloadSlip" href="#" download class="btn btn-success">ดาวน์โหลด</a-->
                                                        <!--button type="button" class="btn btn-primary" id="printSlip">พิมพ์</button-->
                                                        <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">ปิด
                                                        </button> <!-- ปุ่มปิดล่าง -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                                             aria-labelledby="confirmDeleteLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title" id="confirmDeleteLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="close text-white"
                                                                data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณต้องการลบข้อมูลนี้ใช่หรือไม่?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">ยกเลิก
                                                        </button>
                                                        <button type="button" class="btn btn-danger"
                                                                id="confirmDeleteBtn">ลบข้อมูล
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

    <script src="js/util/calculate_datetime.js"></script>

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

    <style>
        .dataTables_wrapper {
            overflow-x: auto;
        }
    </style>

    <style>
        .zoom-container {
            position: relative;
            overflow: hidden;
            display: inline-block; /* เพื่อควบคุมขนาดของพื้นที่ */
        }

        .zoom-container img {
            transition: transform 0.3s ease; /* ให้ภาพขยายแบบนุ่มนวล */
        }

        .zoom-container:hover img {
            transform: scale(1.5); /* กำหนดระดับการ Zoom */
            cursor: zoom-out; /* เปลี่ยน cursor */
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
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา บ้านเลขที่', lengthMenu: 'แสดง _MENU_ รายการ',
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
                'scrollX': true,
                'ajax': {
                    'url': 'model/manage_common_fee_payment_dup_process.php',
                    'type': 'POST',
                    'data': function (d) {
                        d.action = 'GET_COMMON_FEE';
                        d.sub_action = 'GET_MASTER';
                        d.page_manage = 'ADMIN';
                        return d;
                    }
                },
                'columns': [
                    {data: 'id'},
                    {data: 'house_number'},
                    {data: 'month_name_start'},
                    {data: 'month_name_to'},
                    {data: 'period_year'},
                    {data: 'amount', className: 'dt-body-right', width: '120px'},
                    {data: 'slip'},
                    {data: 'payment_status_desc'},
                    {data: 'delete'},
                ],
                'autoWidth': false // ปิด autowidth เพื่อให้ width ที่กำหนดมีผลจริง
            });
        });
    </script>

    <script>
        // ฟังก์ชันเปิดรูปในหน้าต่างใหม่
        function openImageInNewWindow(imageSrc) {
            if (imageSrc && imageSrc !== "#") {
                window.open(imageSrc, '_blank');
            } else {
                alert('ไม่มีรูปภาพที่จะแสดง');
            }
        }
    </script>

    <script>
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
                        $("#downloadSlip").attr("href", response.image_url);
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
    </script>

    <script>
        let deleteId = null;

        $("#TableRecordList").on('click', '.delete', function () {
            deleteId = $(this).attr("id");
            $("#confirmDeleteModal").modal("show");
        });

        $("#confirmDeleteBtn").on("click", function () {
            if (deleteId) {
                $.ajax({
                    url: "model/manage_common_fee_payment_dup_process.php",
                    method: "POST",
                    data: {id: deleteId, action: "DELETE"},
                    success: function (response) {
                        $("#confirmDeleteModal").modal("hide");
                        $('#TableRecordList').DataTable().ajax.reload();
                        alertify.success("ลบข้อมูลเรียบร้อยแล้ว");
                    },
                    error: function () {
                        alertify.error("เกิดข้อผิดพลาดในการลบข้อมูล");
                    }
                });
            }
        });

    </script>

    </body>
    </html>

<?php } ?>