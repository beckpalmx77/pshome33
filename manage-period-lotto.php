<?php include('includes/Header.php'); ?>

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
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
                                <h6 class="m-0 font-weight-bold text-primary">จัดการเลขรางวัล Lotto</h6>
                            </div>
                            <div class="card-body">
                                <section class="container-fluid">
                                    <!-- แสดงปุ่ม Add ใหม่ -->
                                    <div class="col-md-12 col-md-offset-2 mb-4">
                                        <button type="button" name="btnAdd" id="btnAdd" class="btn btn-primary btn-sm">
                                            <i class="fa fa-plus"></i> เพิ่มข้อมูล
                                        </button>
                                        <button type="button" name="backBtn" id="backBtn" class="btn btn-danger btn-sm">
                                            <i class="fa fa-reply"></i> กลับหน้าแรก
                                        </button>
                                    </div>

                                    <!-- ตารางแสดงข้อมูล -->
                                    <div class="col-md-12 col-md-offset-2">
                                        <div class="table-responsive">
                                            <table id="TableRecordList" class="table table-bordered table-striped">
                                                <thead>
                                                <tr>
                                                    <th>งวดวันที่</th>
                                                    <th>เดือน</th>
                                                    <th>ปีงวด</th>
                                                    <th>ประเภทรางวัล</th>
                                                    <th>เลขรางวัล</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <!-- Modal for Add/Edit -->
    <div class="modal fade" id="recordModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">เพิ่ม/แก้ไขข้อมูล</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <form method="post" id="recordForm">
                    <div class="modal-body">
                        <!-- ฟอร์มข้อมูล -->
                        <div class="form-group">
                            <label for="period_no">งวดวันที่</label>
                            <select class="form-control" id="period_no" name="period_no" required>
                                <option value="">เลือกวันที่</option>
                                <option value="1">1</option>
                                <option value="16">16</option>
                            </select>
                        </div>


                        <div class="form-group">
                            <label for="period_month">เดือน</label>
                            <select class="form-control" id="period_month" name="period_month" required>
                                <option value="">เลือกเดือน</option>
                                <?php
                                $months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
                                foreach ($months as $index => $month) {
                                    echo "<option value='" . ($index + 1) . "'>$month</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="period_year">ปีงวด</label>
                            <select class="form-control" id="period_year" name="period_year" required>
                                <?php
                                $currentYear = date("Y");
                                for ($year = $currentYear; $year >= $currentYear - 0; $year--) {
                                    echo "<option value='$year'>$year</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="lotto_type">ประเภทเลขท้าย</label>
                            <select class="form-control" id="lotto_type" name="lotto_type" required>
                                <option value="">เลือกประเภทรางวัล</option>
                                <option value="2">เลขท้าย 3 ตัว</option>
                                <option value="1">เลขท้าย 2 ตัว</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="lotto_number_result">เลขรางวัล</label>
                            <input type="text" class="form-control" id="lotto_number_result" name="lotto_number_result"
                                   required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="id" id="id"/>
                        <input type="hidden" name="action" id="action" value=""/>
                        <input type="submit" name="save" id="save" class="btn btn-primary" value="บันทึก"/>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">ปิด <i
                                    class="fa fa-window-close"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/buttons.dataTables.min.js"></script>

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
        div.dataTables_filter, div.dataTables_length {
            display: inline-block;
            margin-right: 10px;
        }
    </style>

    <style>
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>

    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#period_no').val("");
                $('#period_month').val("");
                $('#period_year').val("");
                $('#lotto_type').val("");
                $('#lotto_number_result').val("");
                $('.modal-title').html("<i class='fa fa-plus'></i> เพิ่มข้อมูล");
                $('#action').val('ADD');
                $('#save').val('บันทึก');
            });

            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                let formData = {action: "GET_DATA", id: id};
                $.ajax({
                    type: "POST",
                    url: 'model/manage_period_lotto_process.php',
                    dataType: "json",
                    data: formData,
                    success: function (response) {
                        let len = response.length;
                        for (let i = 0; i < len; i++) {
                            let id = response[i].id;
                            let period_no = response[i].period_no;
                            let period_month = response[i].period_month;
                            let period_year = response[i].period_year;
                            let lotto_type = response[i].lotto_type;
                            let lotto_number_result = response[i].lotto_number_result;

                            $('#recordModal').modal('show');
                            $('#id').val(id);
                            $('#period_no').val(period_no);
                            $('#period_month').val(period_month);
                            $('#period_year').val(period_year);
                            $('#lotto_type').val(lotto_type);
                            $('#lotto_number_result').val(lotto_number_result);
                            $('.modal-title').html("<i class='fa fa-edit'></i> แก้ไขข้อมูล");
                            $('#action').val('UPDATE');
                            $('#save').val('บันทึก');
                        }
                    }
                });
            });

            $("#TableRecordList").on('click', '.delete', function () {
                let id = $(this).attr("id");
                let formData = {action: "GET_DATA", id: id};
                $.ajax({
                    type: "POST",
                    url: 'model/manage_period_lotto_process.php',
                    dataType: "json",
                    data: formData,
                    success: function (response) {
                        let len = response.length;
                        for (let i = 0; i < len; i++) {
                            let id = response[i].id;
                            let period_no = response[i].period_no;
                            let period_month = response[i].period_month;
                            let period_year = response[i].period_year;
                            let lotto_type = response[i].lotto_type;
                            let lotto_number_result = response[i].lotto_number_result;

                            $('#recordModal').modal('show');
                            $('#id').val(id);
                            $('#period_no').val(period_no);
                            $('#period_month').val(period_month);
                            $('#period_year').val(period_year);
                            $('#lotto_type').val(lotto_type);
                            $('#lotto_number_result').val(lotto_number_result);
                            $('.modal-title').html("<i class='fa fa-trash'></i> ลบข้อมูล");
                            $('#action').val('DELETE');
                            $('#save').val('ยืนยีนการลบข้อมูล');
                        }
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_LOTTO_PERIOD_RESULT", sub_action: "GET_MASTER"};
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
                    'url': 'model/manage_period_lotto_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'period_no'},
                    {data: 'period_month'},
                    {data: 'period_year'},
                    {data: 'lotto_type'},
                    {data: 'lotto_number_result'},
                    {data: 'update'},
                    {data: 'delete'},
                    {data: 'check'}
                ]
            });

            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_period_lotto_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    }
                })
            });
        });

    </script>

    <script>
        // เปิดหน้าต่าง report.php เมื่อคลิกที่ check
        $("#TableRecordList").on('click', '.check', function () {
            let id = $(this).attr("id");
            window.location.href = "sac_lotto_result_show_data?id=" + id;
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
