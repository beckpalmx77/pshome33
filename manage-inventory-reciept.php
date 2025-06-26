<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['doc_no']) == "") {
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
                                                    <th>วันที่</th>
                                                    <th>ผู้ขอเบิก</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>สถานะ</th>
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
            let formData = {action: "GET_INVENTORY", sub_action: "GET_MASTER"};
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
                    'url': 'model/manage_inventory_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'doc_no'},
                    {data: 'doc_date'},
                    {data: 'supplier_name'},
                    {data: 'total_amount'},
                    {data: 'approve_status_desc'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });
        });
    </script>

    <script>

        $("#btnAdd").click(function () {
            let main_menu = document.getElementById("main_menu").value;
            let sub_menu = document.getElementById("sub_menu").value;
            let url = "manage-inventory-reciept-data?title=เอกสารการเบิกพัสดุ"
                + '&main_menu=' + main_menu + '&sub_menu=' + sub_menu
                + '&action=ADD';
            window.open(url, '_blank');
        });

    </script>

    <script>

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_inventory_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_no = response[i].doc_no;
                        let doc_date = response[i].doc_date;
                        let requester = response[i].requester;
                        let supplier_id = response[i].supplier_id;
                        let supplier_name = response[i].supplier_name;
                        let purpose = response[i].purpose;
                        let total_amount = response[i].total_amount;
                        let picture_doc = response[i].picture_doc;
                        let main_menu = document.getElementById("main_menu").value;
                        let sub_menu = document.getElementById("sub_menu").value;
                        let url = "manage-inventory-reciept-data?title=เอกสารการเบิกพัสดุ"
                            + '&main_menu=' + main_menu + '&sub_menu=' + sub_menu
                            + '&id=' + id
                            + '&doc_no=' + doc_no
                            + '&doc_date=' + doc_date
                            + '&purpose=' + purpose
                            + '&requester=' + requester
                            + '&supplier_id=' + supplier_id
                            + '&supplier_name=' + supplier_name
                            + '&total_amount=' + total_amount
                            + '&picture_doc=' + picture_doc
                            + '&action=UPDATE';
                        window.open(url, '_blank');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
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
                    url: "model/manage_inventory_detail_process.php",
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