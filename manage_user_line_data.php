<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
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
                            <div class="card mb-4">
                                <div class="card-body">
                                    <table id='TableRecordList' class='table table-bordered table-striped display' width="100%">
                                        <thead>
                                        <tr>
                                            <th>ลำดับ</th>
                                            <th>เบอร์โทร</th>
                                            <th>ชื่อสมาชิก</th>
                                            <th>เลขที่บ้าน</th>
                                            <th>ชื่อใน LINE</th>
                                            <th>จัดการ</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">ตรวจสอบข้อมูลทั้งหมดก่อนลบ (จะลบทั้ง 3 ตาราง)</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="allFieldsContent">
                    </div>
                </div>
                <div class="modal-footer">
                    <form id="confirmDeleteForm">
                        <input type="hidden" id="target_id" name="id">
                        <input type="hidden" name="action" value="DELETE">
                        <button type="submit" class="btn btn-danger btn-lg">ยืนยันลบข้อมูลถาวร</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>

    <script>
        $(document).ready(function () {
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[7, 10, 20, 50, 100], [7, 10, 20, 50, 100]],
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': { 'url': 'model/manage_user_line_data_process.php', 'data': { action: "GET_USER_LINE_DATA" } },
                'columns': [
                    { data: 'no' }, { data: 'phone_number' }, { data: 'member_name' },
                    { data: 'house_number' }, { data: 'line_user_name' }, { data: 'delete' }
                ],
                'columnDefs': [{ "orderable": false, "targets": [0, 5] }]
            });

            // เมื่อคลิกปุ่มลบ ดึงข้อมูล 3 ตารางมาโชว์
            $('#TableRecordList').on('click', '.delete', function () {
                let id = $(this).attr("id");
                $('#target_id').val(id);
                $('#allFieldsContent').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> กำลังโหลดข้อมูล...</div>');
                $('#deleteModal').modal('show');

                $.ajax({
                    url: 'model/manage_user_line_data_process.php',
                    method: 'POST',
                    data: { action: 'GET_ALL_FIELDS', id: id },
                    dataType: 'json',
                    success: function (res) {
                        if(res.length > 0) {
                            let d = res[0];
                            let html = `
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card border-primary mb-3">
                                            <div class="card-header bg-primary text-white text-center"><b>ตาราง: ims_user</b></div>
                                            <div class="card-body">
                                                <p><b>User ID:</b> ${d.user_id || '-'}</p>
                                                <p><b>ชื่อ-นามสกุล:</b> ${d.first_name} ${d.last_name}</p>
                                                <p><b>Email:</b> ${d.email || '-'}</p>
                                                <p><b>Status:</b> ${d.status || '-'}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-success mb-3">
                                            <div class="card-header bg-success text-white text-center"><b>ตาราง: ims_house</b></div>
                                            <div class="card-body">
                                                <p><b>เลขที่บ้าน:</b> ${d.house_number}</p>
                                                <p><b>เบอร์โทร:</b> ${d.phone_number}</p>
                                                <p><b>ผู้ติดต่อ:</b> ${d.contact_name || '-'}</p>
                                                <p><b>สถานะบ้าน:</b> ${d.house_status || '-'}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-info mb-3">
                                            <div class="card-header bg-info text-white text-center"><b>ตาราง: ims_house_line_user</b></div>
                                            <div class="card-body text-center">
                                                <img src="${d.line_picture_profile || 'img/no-image.png'}" class="rounded-circle mb-2" width="60">
                                                <p><b>ชื่อ LINE:</b> ${d.line_user_name}</p>
                                                <p><b>Line ID:</b> ${d.line_user_id || '-'}</p>
                                                <p><b>วันที่ผูกบัญชี:</b> ${d.create_date || '-'}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-warning text-center">
                                    <h5><i class="fa fa-exclamation-triangle"></i> ยืนยันการลบข้อมูลถาวรด้วย Key: <b>${d.line_phone}</b></h5>
                                    <small>ข้อมูลทั้งหมดที่แสดงด้านบนจะถูกลบออกจากฐานข้อมูลทันที</small>
                                </div>`;
                            $('#allFieldsContent').html(html);
                        }
                    }
                });
            });

            // กดยืนยันการลบ
            $('#confirmDeleteForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'model/manage_user_line_data_process.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        if(response === 'success') {
                            alertify.success("ลบข้อมูลทั้ง 3 ตารางเรียบร้อยแล้ว");
                            $('#deleteModal').modal('hide');
                            dataRecords.ajax.reload(null, false);
                        } else {
                            alertify.error(response);
                        }
                    }
                });
            });
        });
    </script>
    </body>
    </html>
<?php } ?>