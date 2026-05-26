<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>จัดการผังบัญชี (Chart of Accounts)</title>
        <style>
            #TableCOA th, #TableCOA td { white-space: nowrap; }
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
                        <h1 class="h4 mb-0 text-gray-800">จัดการผังบัญชี</h1>
                        <button type="button" class="btn btn-primary" id="btnAdd">เพิ่มผังบัญชี <i class="fa fa-plus"></i></button>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="table-responsive p-3">
                                        <table class="table align-items-center table-flush table-hover" id="TableCOA">
                                            <thead class="thead-light">
                                            <tr>
                                                <th>รหัสบัญชี</th>
                                                <th>ชื่อบัญชี</th>
                                                <th>หมวดบัญชี</th>
                                                <th>สถานะ</th>
                                                <th>แก้ไข</th>
                                                <th>ลบ</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Add/Update -->
                    <div class="modal fade" id="COAModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitle">เพิ่มผังบัญชี</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="COAForm">
                                        <input type="hidden" name="action" id="action" value="ADD">
                                        <div class="form-group">
                                            <label>รหัสบัญชี :</label>
                                            <input type="text" name="acc_code" id="acc_code" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>ชื่อบัญชี :</label>
                                            <input type="text" name="acc_name" id="acc_name" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>หมวดบัญชี :</label>
                                            <select name="acc_group" id="acc_group" class="form-control" required>
                                                <option value="Asset">Asset (สินทรัพย์)</option>
                                                <option value="Liability">Liability (หนี้สิน)</option>
                                                <option value="Equity">Equity (ทุน)</option>
                                                <option value="Revenue">Revenue (รายได้)</option>
                                                <option value="Expense">Expense (ค่าใช้จ่าย)</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                                    <button type="button" class="btn btn-primary" id="btnSave">บันทึก</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php include('includes/Modal-Logout.php');
                include('includes/Footer.php'); ?>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        $(document).ready(function () {
            let table = $('#TableCOA').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "model/manage_coa_process.php",
                    "type": "POST",
                    "data": { "action": "GET_COA" }
                },
                "columns": [
                    { "data": "acc_code" },
                    { "data": "acc_name" },
                    { "data": "acc_group" },
                    { "data": "status" },
                    { "data": "update", "orderable": false },
                    { "data": "delete", "orderable": false }
                ],
                "order": [[0, "asc"]]
            });

            $('#btnAdd').click(function () {
                $('#COAForm')[0].reset();
                $('#acc_code').prop('readonly', false);
                $('#action').val('ADD');
                $('#modalTitle').text('เพิ่มผังบัญชี');
                $('#COAModal').modal('show');
            });

            $('#btnSave').click(function () {
                $.ajax({
                    url: 'model/manage_coa_process.php',
                    method: 'POST',
                    data: $('#COAForm').serialize(),
                    success: function (response) {
                        alert(response);
                        $('#COAModal').modal('hide');
                        table.draw();
                    }
                });
            });

            $(document).on('click', '.update', function () {
                let id = $(this).attr('id');
                $.ajax({
                    url: 'model/manage_coa_process.php',
                    method: 'POST',
                    data: { action: 'GET_DATA', id: id },
                    dataType: 'json',
                    success: function (data) {
                        $('#acc_code').val(data.acc_code).prop('readonly', true);
                        $('#acc_name').val(data.acc_name);
                        $('#acc_group').val(data.acc_group);
                        $('#action').val('UPDATE');
                        $('#modalTitle').text('แก้ไขผังบัญชี');
                        $('#COAModal').modal('show');
                    }
                });
            });

            $(document).on('click', '.delete', function () {
                let id = $(this).attr('id');
                if (confirm('คุณต้องการลบผังบัญชีรหัส ' + id + ' หรือไม่?')) {
                    $.ajax({
                        url: 'model/manage_coa_process.php',
                        method: 'POST',
                        data: { action: 'DELETE', id: id },
                        success: function (response) {
                            alert(response);
                            table.draw();
                        }
                    });
                }
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
