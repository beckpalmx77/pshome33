<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");
    
    // Fetch accounts for the dropdown
    $sql_acc = "SELECT acc_code, acc_name FROM ims_chart_of_accounts ORDER BY acc_code ASC";
    $stmt_acc = $conn->prepare($sql_acc);
    $stmt_acc->execute();
    $accounts = $stmt_acc->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>ผูกผังบัญชี (Account Mapping)</title>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800">ผูกประเภทรายการกับผังบัญชี</h1>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <ul class="nav nav-tabs" id="mappingTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="expense-tab" data-toggle="tab" href="#expense" role="tab" aria-controls="expense" aria-selected="true">ประเภทค่าใช้จ่าย (Expense)</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="income-tab" data-toggle="tab" href="#income" role="tab" aria-controls="income" aria-selected="false">ประเภทรายได้ (Income)</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="mappingTabContent">
                                <!-- Expense Tab -->
                                <div class="tab-pane fade show active p-3" id="expense" role="tabpanel" aria-labelledby="expense-tab">
                                    <div class="card">
                                        <div class="card-body">
                                            <table class="table table-bordered" id="TableExpenseMapping" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>รหัสกลุ่ม</th>
                                                        <th>ชื่อประเภทค่าใช้จ่าย</th>
                                                        <th>รหัสบัญชีที่ผูก</th>
                                                        <th>จัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- Income Tab -->
                                <div class="tab-pane fade p-3" id="income" role="tabpanel" aria-labelledby="income-tab">
                                    <div class="card">
                                        <div class="card-body">
                                            <table class="table table-bordered" id="TableIncomeMapping" width="100%">
                                                <thead>
                                                    <tr>
                                                        <th>รหัสกลุ่ม</th>
                                                        <th>ชื่อประเภทรายได้</th>
                                                        <th>รหัสบัญชีที่ผูก</th>
                                                        <th>จัดการ</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="MappingModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">ผูกรหัสบัญชี</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="MappingForm">
                                        <input type="hidden" name="action" value="UPDATE_MAPPING">
                                        <input type="hidden" name="type" id="map_type">
                                        <input type="hidden" name="id" id="map_id">
                                        <div class="form-group">
                                            <label>ชื่อรายการ :</label>
                                            <input type="text" id="map_name" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label>เลือกบัญชีที่จะผูก :</label>
                                            <select name="acc_code" id="acc_code_select" class="form-control" required>
                                                <option value="">-- กรุณาเลือกบัญชี --</option>
                                                <?php foreach ($accounts as $acc) { ?>
                                                    <option value="<?php echo $acc['acc_code']; ?>">
                                                        <?php echo $acc['acc_code'] . " - " . $acc['acc_name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                                    <button type="button" class="btn btn-primary" id="btnSaveMapping">บันทึกการผูกบัญชี</button>
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
            let tableExp = $('#TableExpenseMapping').DataTable({
                "ajax": { "url": "model/manage_account_mapping_process.php", "type": "POST", "data": { "action": "GET_MAPPINGS", "type": "expense" } },
                "columns": [{ "data": "id" }, { "data": "name" }, { "data": "acc_code" }, { "data": "action" }]
            });

            let tableInc = $('#TableIncomeMapping').DataTable({
                "ajax": { "url": "model/manage_account_mapping_process.php", "type": "POST", "data": { "action": "GET_MAPPINGS", "type": "income" } },
                "columns": [{ "data": "id" }, { "data": "name" }, { "data": "acc_code" }, { "data": "action" }]
            });

            $(document).on('click', '.edit-mapping', function() {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let acc = $(this).data('acc');
                let type = $(this).closest('table').attr('id') === 'TableExpenseMapping' ? 'expense' : 'income';

                $('#map_id').val(id);
                $('#map_name').val(name);
                $('#map_type').val(type);
                $('#acc_code_select').val(acc);
                $('#MappingModal').modal('show');
            });

            $('#btnSaveMapping').click(function() {
                $.ajax({
                    url: 'model/manage_account_mapping_process.php',
                    method: 'POST',
                    data: $('#MappingForm').serialize(),
                    success: function(response) {
                        alert(response);
                        $('#MappingModal').modal('hide');
                        tableExp.ajax.reload();
                        tableInc.ajax.reload();
                    }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
