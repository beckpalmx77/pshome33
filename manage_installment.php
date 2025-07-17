<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['installment_id']) == "") {
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
                        <input type="hidden" id="main_menu" value="<?php echo urldecode($_GET['m']) ?>">
                        <input type="hidden" id="sub_menu" value="<?php echo urldecode($_GET['s']) ?>">
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
                                            <!--button type='button' name='btnExp' id='btnExp'
                                                    class='btn btn-success btn-xs'>Export Excel
                                                <i class="fa fa-file-excel-o"></i>
                                            </button-->
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ชื่อผู้ชำระ</th>
                                                    <th>จำนวนเงินต้น</th>
                                                    <th>จำนวนเงินดาวน์</th>
                                                    <th>จำนวนงวด</th>
                                                    <th>จำนวนผ่อนแต่ละงวด</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                            </table>

                                            <div id="result"></div>

                                        </div>


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
            let formData = {action: "GET_INSTALLMENT", sub_action: "GET_MASTER"};
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
                    'url': 'model/manage_installment_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'installment_id'},
                    {data: 'house_number'},
                    {data: 'debtor'},
                    {data: 'principal_amount'},
                    {data: 'down_payment'},
                    {data: 'num_installments'},
                    {data: 'installment_per_period'},
                    {data: 'update'},
                    {data: 'print'},
                    {data: 'delete'}
                ]
            });
        });
    </script>

    <script>

        $("#btnAdd").click(function () {
            let main_menu = document.getElementById("main_menu").value;
            let sub_menu = document.getElementById("sub_menu").value;
            let url = "manage_installment_data?title=จัดการผ่อนชำระค่าส่วนกลาง(Manage Installment)"
                + '&main_menu=' + main_menu + '&sub_menu=' + sub_menu
                + '&action=ADD';
            window.open(url, '_blank');
        });

    </script>

    <script>
        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};

            $.ajax({
                type: "POST",
                url: 'model/manage_installment_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    if (len > 0) { // ตรวจสอบว่ามีข้อมูลส่งกลับมาหรือไม่
                        for (let i = 0; i < len; i++) {
                            let id = response[i].id;
                            let installment_id = response[i].installment_id;
                            let doc_date = response[i].doc_date;
                            let house_number = response[i].house_number;
                            let debtor = response[i].debtor;
                            let down_payment = response[i].down_payment;
                            let num_installments = response[i].num_installments;
                            let interest_rate = response[i].interest_rate;
                            let principal_amount = response[i].principal_amount;
                            let installment_per_period = response[i].installment_per_period;
                            let picture_doc = response[i].picture_doc;
                            let payment_method = response[i].payment_method;
                            let bank_no = response[i].bank_no;
                            let approve_status = response[i].approve_status;
                            let status = response[i].status;

                            let main_menu = document.getElementById("main_menu").value;
                            let sub_menu = document.getElementById("sub_menu").value;
                            let url = "manage_installment_data?title=จัดซื้อ-จัดจ้าง(Purchase Order)"
                                + '&main_menu=' + main_menu + '&sub_menu=' + sub_menu
                                + '&id=' + id
                                + '&doc_date=' + doc_date
                                + '&installment_id=' + installment_id
                                + '&house_number=' + house_number
                                + '&picture_doc=' + picture_doc
                                + '&debtor=' + debtor
                                + '&principal_amount=' + principal_amount
                                + '&num_installments=' + num_installments
                                + '&installment_per_period=' + installment_per_period
                                + '&interest_rate=' + interest_rate
                                + '&down_payment=' + down_payment
                                + '&payment_method=' + payment_method
                                + '&bank_no=' + bank_no
                                + '&approve_status=' + approve_status
                                + '&status=' + status
                                + '&action=UPDATE';

                            console.log("Generated URL for update:", url); // แสดง URL ใน Console เพื่อตรวจสอบ
                            window.open(url, '_blank');
                        }
                    } else {
                        alertify.error("ไม่พบข้อมูลสำหรับการอัปเดต โปรดตรวจสอบอีกครั้ง."); // แจ้งผู้ใช้หากไม่พบข้อมูล
                    }
                },
                error: function (xhr, status, error) { // ใช้พารามิเตอร์ข้อผิดพลาดเพิ่มเติมเพื่อการ Debug ที่ดีขึ้น
                    console.error("AJAX Error:", status, error, xhr.responseText); // แสดงข้อผิดพลาดใน Console
                    alertify.error("เกิดข้อผิดพลาดในการดึงข้อมูล: " + error);
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
                    url: "model/manage_voucher_detail_process.php",
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

    <script>
        $("#TableRecordList").on('click', '.print', function () {
            let account_type = $('#account_type').val();
            let id = $(this).attr("id");
            let url = "print_payment_voucher_pdf?id=";
            window.open(url + encodeURIComponent(id), "_blank");
        });
    </script>

    </body>
    </html>

<?php } ?>