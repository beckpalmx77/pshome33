<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <link rel="stylesheet" href="css/spin_datatables.css"/>
    <style>
        .dataTables_wrapper {
            overflow-x: auto;
        }
        /* CSS ที่เพิ่มใหม่สำหรับลดช่องว่าง */
        .card-body {
            padding-bottom: 0 !important;
        }

        #content-wrapper {
            padding-bottom: 0 !important;
        }

        #TableRecordList_wrapper {
            margin-bottom: 0 !important;
        }

        footer {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('includes/Top-Bar.php'); ?>

            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <input type="hidden" id="user_type" name="user_type" value="<?= $_SESSION['account_type'] ?>">
                    <h1 class="h3 mb-0 text-gray-800"><?= urldecode($_GET['s']) ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item"><?= urldecode($_GET['m']) ?></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= urldecode($_GET['s']) ?></li>
                    </ol>
                </div>

                <div class="card mb-0"> <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="TableRecordList" class="display nowrap table table-hover" style="width:100%;">
                                <thead>
                                <tr>
                                    <th>วันที่เอกสาร</th>
                                    <th>บ้านเลขที่</th>
                                    <th>ซอย</th>
                                    <th>ผู้ชำระ</th>
                                    <th>รูปโปรไฟล์</th>
                                    <th>งวดเดือน</th>
                                    <th>ปี</th>
                                    <th>ค่าส่วนกลาง</th>
                                    <th>จำนวนงวด</th>
                                    <th>ยอดชำระ</th>
                                    <th>Slip</th>
                                    <th>สถานะ</th>
                                    <th>Action</th>
                                    <th>ใบเสร็จ</th>
                                    <th>ขนาด ตร.ว.</th>
                                    <th>ค่าเก็บขยะ</th>
                                    <th>ลบข้อมูล</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                        <div id="result"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="recordModal">
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
</div>

<?php
include('includes/Modal-Logout.php');
include('includes/Footer.php');
?>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="js/util/calculate_datetime.js"></script>
<script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
<script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

<script>
    // Your existing JavaScript code for DataTable and modals
    $(document).ready(function () {
        let dataRecords = $('#TableRecordList').DataTable({
            'lengthMenu': [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
            'language': {
                search: 'ค้นหา บ้านเลขที่',
                lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    last: 'สุดท้าย',
                    next: 'ต่อไป'
                },
                processing: '<div class="custom-spinner"></div>'
            },
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'scrollX': true,
            'ajax': {
                'url': 'model/manage_common_fee_payment_process.php',
                'type': 'POST',
                'data': function (d) {
                    d.action = 'GET_COMMON_FEE';
                    d.sub_action = 'GET_MASTER';
                    d.page_manage = 'ADMIN';
                }
            },
            'columns': [
                {data: 'payment_date'},
                {data: 'house_number'},
                {data: 'alley'},
                {data: 'detail'},
                {data: 'line_picture_profile'},
                {data: 'month_name_period'},
                {data: 'period_year'},
                {data: 'common_fee', className: 'dt-body-right'},
                {data: 'payment_type', className: 'dt-body-center'},
                {data: 'amount', className: 'dt-body-right'},
                {data: 'slip'},
                {data: 'payment_status_desc'},
                {data: 'update'},
                {data: 'print'},
                {data: 'area_size', className: 'dt-body-right'},
                {data: 'garbage_collection_fee', className: 'dt-body-right'},
                {data: 'delete'}
            ],
            'autoWidth': false,
            'initComplete': function (settings, json) {
                // Initializations
            }
        });

        // Function to handle form submission via AJAX
        $('#recordForm').on('submit', function (event) {
            event.preventDefault();
            const formData = $(this).serialize();
            $('#saveButton').attr('disabled', true);
            $.ajax({
                url: 'model/manage_common_fee_payment_process.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    alert(data);
                    $('#recordForm')[0].reset();
                    $('#recordModal').modal('hide');
                    $('#saveButton').attr('disabled', false);
                    $('#TableRecordList').DataTable().ajax.reload();
                },
                error: function (xhr, status, error) {
                    alert("Error: " + error);
                    $('#saveButton').attr('disabled', false);
                }
            });
        });

        // Event handler for "Update" button click
        $("#TableRecordList").on('click', '.update', function () {
            const id = $(this).attr("id");
            $.ajax({
                type: "POST",
                url: 'model/manage_common_fee_payment_process.php',
                dataType: "json",
                data: {action: "GET_DATA", id: id},
                success: function (data) {
                    if (data && data.length > 0) {
                        const rowData = data[0];
                        $('#recordModal').modal('show');
                        $('#id').val(rowData.id);
                        $('#doc_id').val(rowData.doc_id);
                        $('#detail').val(rowData.detail);
                        $('#payment_date').val(rowData.payment_date);
                        $('#house_number').val(rowData.house_number);
                        $('#period_month_start').val(rowData.period_month_start);
                        $('#period_month_to').val(rowData.period_month_to);
                        $('#period_year').val(rowData.period_year);
                        $('#amount').val(rowData.amount);
                        $('#payment_method').val(rowData.payment_method);
                        $('#payment_status_desc').val((rowData.payment_status === "Y") ? "ชำระเรียบร้อยแล้ว" : "ยังไม่ยืนยันการชำระ");
                        $('input[name="payment_status"][value="' + rowData.payment_status + '"]').prop('checked', true);
                        $('#create_by').val(rowData.create_by);
                        $('#created_at').val(rowData.created_at);
                        $('#approve_by').val(rowData.approve_by);
                        $('#action').val('UPDATE');
                        $('.modal-title').html("แก้ไขข้อมูลการชำระเงิน");
                        const image_path = 'uploads/slips/' + rowData.picture_payment;
                        if (rowData.picture_payment) {
                            $('#preview_image').attr('src', image_path).show();
                        } else {
                            $('#preview_image').hide();
                        }
                    }
                }
            });
        });

        // Function to open image in new window
        function openImageInNewWindow(imageSrc) {
            if (imageSrc && imageSrc !== "#") {
                window.open(imageSrc, '_blank');
            } else {
                alert('ไม่พบรูปภาพ');
            }
        }

        // Event handler for "Print" button click
        $("#TableRecordList").on('click', '.print', function () {
            const id = $(this).attr("id");
            const user_type = $('#user_type').val();
            const url = (user_type === 'user') ? `print_pdf_smart.php?id=${id}` : `print_pdf.php?id=${id}`;
            window.open(url, "_blank");
        });

        // Event handler for "Slip" button click
        $("#TableRecordList").on('click', '.slip', function () {
            const id = $(this).attr("id");
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
                }
            });
        });

        // Event handler for "Delete" button click
        let deleteId = null;
        $("#TableRecordList").on('click', '.delete', function () {
            deleteId = $(this).attr("id");
            $("#confirmDeleteModal").modal("show");
        });

        // Event handler for confirm delete
        $("#confirmDeleteBtn").on("click", function () {
            if (deleteId) {
                $.ajax({
                    url: "model/manage_common_fee_payment_process.php",
                    method: "POST",
                    data: {id: deleteId, action: "DELETE"},
                    success: function (response) {
                        $("#confirmDeleteModal").modal("hide");
                        $('#TableRecordList').DataTable().ajax.reload();
                        alert("ลบข้อมูลเรียบร้อยแล้ว");
                    }
                });
            }
        });
    });
</script>

</body>
</html>