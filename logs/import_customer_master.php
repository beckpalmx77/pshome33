<?php
include('includes/Header.php');
?>
<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-body">
                                <div class="text-center">
                                    <div>
                                        <img src="img/logo/logo text-01.png" width="200" height="79"/>
                                    </div>
                                </div>
                                <section class="container-fluid">
                                    <form id="uploadForm" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="excelFile" class="form-label">Select Excel File</label>
                                            <input class="form-control" type="file" id="excelFile" name="excelFile"
                                                   accept=".xlsx, .xls">
                                        </div>
                                        <div class="mb-12">
                                            <button type="button" class="btn btn-primary" id="uploadBtn"
                                                    name="uploadBtn">Import
                                            </button>
                                            <button type="button" id="showImageBtn" class="btn btn-success">ตัวอย่างไฟล์
                                                Excel Format Data สำหรับนำเข้า
                                            </button>
                                            <button type="button" class="btn btn-danger" id="backBtn"
                                                    name="backBtn">กลับหน้าแรก
                                            </button>
                                        </div>
                                        <div class="mb-12">
                                            <span>
                                                <div id="input_text" style="white-space: pre-wrap;"></div>
                                                <!-- เพิ่ม style white-space -->
                                            </span>
                                        </div>
                                    </form>
                                    <br>
                                    <div id="spinner" class="text-center my-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>ชื่อร้านค้า</th>
                                                <th>จังหวัด</th>
                                                <th>ชื่อSale</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>ชื่อร้านค้า</th>
                                                <th>จังหวัด</th>
                                                <th>ชื่อSale</th>
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

<?php
include('includes/Modal-Logout.php');
include('includes/Footer.php');
?>

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Vendor Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<!-- Datatables Scripts -->
<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<!-- Custom Style -->
<link href="css/spinner_over.css" rel="stylesheet"/>

<!-- Custom Script -->
<script>
    $(document).ready(function () {

        $('#TableRecordList').DataTable({
            "lengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
            "ajax": "model/fetch_data_customer_sac.php",
            "order": [[0, 'desc']],
            "columns": [
                {"data": "customer_name"},
                {"data": "sale_name"},
                {"data": "province"}
            ]
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#showImageBtn').on('click', function () {
            window.open("import_process/template/ims_data_customer_master.xlsx", "_blank");
        });
    });
</script>

<script>
    $(document).ready(function () {
        let formData = {screen_name: "ims_customer_master", table_name: "log_import_data"};
        $.ajax({
            type: "POST",
            url: 'model/get_last_import.php',
            data: formData,
            success: function (response) {
                $('#input_text').html(response);  // ใส่ response ลงใน div
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#uploadBtn').on('click', function () {
            $("#spinner").show(); // แสดง spinner ขณะทำการอัปโหลด

            let formData = new FormData($('#uploadForm')[0]); // เก็บข้อมูลฟอร์ม
            //alert(formData);
            $.ajax({
                url: 'import_process/import_data_customer_process.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    $('#spinner').hide();
                    alertify.alert(response);
                    $('#TableRecordList').DataTable().ajax.reload();
                    alertify.alert("Notification", "Data imported successfully.");
                },
                error: function (xhr, status, error) {
                    $('#spinner').hide();
                    alertify.alert("Notification", "An error occurred: " + error);
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {
        $("#backBtn").click(function () {
            window.location.href = "sac_lotto_dashboard";
        });
    });
</script>

</body>
</html>