<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");
    $sql_acc = "SELECT acc_code, acc_name FROM ims_chart_of_accounts ORDER BY acc_code ASC";
    $accounts = $conn->query($sql_acc)->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>จัดการสมุดรายวัน (Journal Manager)</title>
        <style>
            #TableGL td { white-space: nowrap; }
            .row-deleted { text-decoration: line-through; color: red; }
            @media (min-width: 1200px) {
                .modal-xl {
                    max-width: 95%;
                }
            }
        </style>
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid">
                    <h1 class="h4 mb-4 text-gray-800">จัดการสมุดรายวันทั่วไป (แก้ไข Dr/Cr)</h1>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <table class="table table-hover" id="TableGL" width="100%">
                                <thead>
                                    <tr>
                                        <th>วันที่</th>
                                        <th>เลขที่เอกสาร</th>
                                        <th>รายการ</th>
                                        <th class="text-right">ยอดรวม</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="EditGLModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">แก้ไขรายการบัญชี</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="GLForm">
                                    <input type="hidden" id="edit_gl_id">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label>วันที่ :</label>
                                            <input type="date" id="edit_gl_date" class="form-control">
                                        </div>
                                        <div class="col-md-8">
                                            <label>คำอธิบาย :</label>
                                            <input type="text" id="edit_description" class="form-control">
                                        </div>
                                    </div>
                                    <hr>
                                    <h6>รายการบันทึกบัญชี (Dr/Cr)</h6>
                                    <table class="table table-sm" id="TableEditDetails">
                                        <thead>
                                            <tr>
                                                <th>บัญชี</th>
                                                <th width="150">Debit</th>
                                                <th width="150">Credit</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <div class="mr-auto">
                                    <span id="balance_check" class="font-weight-bold"></span>
                                </div>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                                <button type="button" class="btn btn-primary" id="btnSaveGL">บันทึกการแก้ไข</button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include('includes/Footer.php'); ?>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        $(document).ready(function () {
            let table = $('#TableGL').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": { 
                    "url": "model/get_gl_report.php", 
                    "type": "POST", 
                    "data": {} 
                },
                "columns": [
                    { "data": "gl_date" },
                    { "data": "doc_no" },
                    { "data": "description" },
                    { "data": "total_amount", "className": "text-right" },
                    { 
                        "data": null,
                        "render": function(data, type, row) {
                            return `<button class="btn btn-warning btn-xs btn-edit" data-id="${row.gl_id}">แก้ไข</button>`;
                        }
                    }
                ],
                "order": [[0, "desc"]],
                "pageLength": 5,
                "lengthMenu": [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]],
                "language": {
                    "search": "ค้นหารวดเร็ว:",
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    "paginate": {
                        "previous": "ก่อนหน้า",
                        "next": "ถัดไป"
                    }
                }
            });

            $(document).on('click', '.btn-edit', function() {
                let gl_id = $(this).data('id');
                loadGLEntry(gl_id);
            });

            function loadGLEntry(gl_id) {
                $.ajax({
                    url: 'model/manage_gl_process.php',
                    method: 'POST',
                    data: { action: 'GET_GL_DATA', gl_id: gl_id },
                    dataType: 'json',
                    success: function(data) {
                        $('#edit_gl_id').val(data.header.gl_id);
                        $('#edit_gl_date').val(data.header.gl_date);
                        $('#edit_description').val(data.header.description);
                        
                        let html = '';
                        data.details.forEach((d, i) => {
                            html += `<tr>
                                <td>
                                    <select class="form-control form-control-sm acc-select" data-val="${d.acc_code}">
                                        <?php foreach($accounts as $a){ ?>
                                            <option value="<?php echo $a['acc_code']; ?>">
                                                <?php echo $a['acc_code'].' - '.$a['acc_name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm text-right dr-input" value="${d.dr_amount}"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm text-right cr-input" value="${d.cr_amount}"></td>
                            </tr>`;
                        });
                        $('#TableEditDetails tbody').html(html);
                        
                        $('#TableEditDetails .acc-select').each(function() {
                            $(this).val($(this).data('val'));
                        });

                        $('#EditGLModal').modal('show');
                        checkBalance();
                    }
                });
            }

            function checkBalance() {
                let totalDr = 0, totalCr = 0;
                $('.dr-input').each(function() { totalDr += parseFloat($(this).val() || 0); });
                $('.cr-input').each(function() { totalCr += parseFloat($(this).val() || 0); });
                let diff = Math.abs(totalDr - totalCr);
                if(diff < 0.01) {
                    $('#balance_check').html(`<span class="text-success">สมดุล (Total: ${totalDr.toFixed(2)})</span>`);
                    $('#btnSaveGL').prop('disabled', false);
                } else {
                    $('#balance_check').html(`<span class="text-danger">ไม่สมดุล (Diff: ${diff.toFixed(2)})</span>`);
                    $('#btnSaveGL').prop('disabled', true);
                }
            }

            $(document).on('input', '.dr-input, .cr-input', checkBalance);

            $('#btnSaveGL').click(function() {
                let details = [];
                $('#TableEditDetails tbody tr').each(function() {
                    details.push({
                        acc_code: $(this).find('.acc-select').val(),
                        dr: $(this).find('.dr-input').val(),
                        cr: $(this).find('.cr-input').val()
                    });
                });

                $.ajax({
                    url: 'model/manage_gl_process.php',
                    method: 'POST',
                    data: {
                        action: 'UPDATE_GL_ENTRY',
                        gl_id: $('#edit_gl_id').val(),
                        gl_date: $('#edit_gl_date').val(),
                        description: $('#edit_description').val(),
                        details: details
                    },
                    success: function(msg) {
                        alert(msg);
                        $('#EditGLModal').modal('hide');
                        table.ajax.reload();
                    }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
