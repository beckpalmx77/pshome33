<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    include("config/connect_db.php");
    $main_menu_name = isset($_GET['m']) ? urldecode($_GET['m']) : 'การประชุมหมู่บ้าน';
    $sub_menu_name = isset($_GET['s']) ? urldecode($_GET['s']) : 'รายการประชุม (Register)';
    $dashboard_page = isset($_SESSION['dashboard_page']) ? $_SESSION['dashboard_page'] : 'dashboard.php';
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <link rel="stylesheet" href="css/spin_datatables.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
        <style>
            .card-body { padding: 1rem; }
            .dataTables_wrapper { overflow-x: auto; }
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $sub_menu_name; ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $dashboard_page ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo $main_menu_name; ?></li>
                            <li class="breadcrumb-item active"><?php echo $sub_menu_name; ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">รายการข้อมูลการประชุม (Meeting Config List)</h6>
                                    <div class="d-flex" style="gap:6px;">
                                        <a href="generate_house_meeting_year.php?m=<?php echo urlencode($main_menu_name); ?>&s=<?php echo urlencode('สร้างข้อมูลการประชุม'); ?>" class="btn btn-success btn-sm">
                                            <i class="fas fa-plus"></i> สร้างข้อมูลการประชุม
                                        </a>
                                        <!--a href="manage_house_meeting_checkin.php?m=<?php echo urlencode($main_menu_name); ?>&s=<?php echo urlencode('ตรวจสอบการลงทะเบียน'); ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-check-circle"></i> ตรวจสอบการลงทะเบียน
                                        </a-->
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped" id="meetingSummaryTable" width="100%" cellspacing="0">
                                            <thead>
                                            <tr>
                                                <th>ชื่อการประชุม</th>
                                                <th>ปี</th>
                                                <th>วันที่ประชุม</th>
                                                <th>เวลา</th>
                                                <th>สถานที่</th>
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
            </div>
            <?php include('includes/Modal-Logout.php'); include('includes/Footer.php'); ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#meetingSummaryTable').DataTable({
                "processing": true,
                "serverSide": true,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]],
                "pageLength": 25,
                "ajax": {
                    "url": "model/get_meeting_register_summary.php",
                    "type": "POST"
                },
                "columns": [
                    { "data": "topic"},
                    { "data": "meeting_year", "className": "text-center" },
                    { "data": "meeting_date", "className": "text-center" },
                    { "data": "meeting_time", "defaultContent": "-" },
                    { "data": "meeting_location", "defaultContent": "-" },
                    {
                        "data": null,
                        "className": "text-center",
                        "render": function (data) {
                            var year = data.meeting_year || '';
                            var date = encodeURIComponent(data.meeting_date || '');
                            var url = 'manage_house_meeting_record.php?year=' + year + '&date=' + date
                                + '&m=<?php echo urlencode($main_menu_name); ?>&s=จัดการข้อมูลการประชุม';
                            return '<div class="d-flex justify-content-center" style="gap:8px;">'
                                + '<a href="' + url + '" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> แก้ไข-ดู ข้อมูล</a>'
                                + '<button type="button" class="btn btn-danger btn-sm btn-delete" data-year="' + year + '" data-date="' + data.meeting_date + '"><i class="fas fa-trash"></i> ลบ</button>'
                                + '</div>';
                        }
                    }
                ],
                "order": [[0, 'desc']],
                "language": {
                    "search": "ค้นหา:",
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                    "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                    "zeroRecords": "ไม่พบข้อมูล",
                    "emptyTable": "ไม่มีข้อมูลในตาราง",
                    "paginate": { "first": "แรก", "last": "สุดท้าย", "next": "ถัดไป", "previous": "ก่อนหน้า" }
                }
            });

            // ปุ่มลบ
            $(document).on('click', '.btn-delete', function(){
                var year = $(this).data('year');
                var date = $(this).data('date');
                alertify.confirm(
                    'ยืนยันการลบ',
                    'ลบข้อมูลการประชุมปี <b>' + year + '</b> วันที่ <b>' + date + '</b> ทั้งหมด (header + รายละเอียดบ้าน)?',
                    function(){
                        $.ajax({
                            type: 'POST',
                            url: 'model/delete_meeting_all.php',
                            data: { meeting_year: year, meeting_date: date },
                            dataType: 'json',
                            success: function(res){
                                if (res.status === 'success') {
                                    alertify.success(res.message);
                                    $('#meetingSummaryTable').DataTable().ajax.reload();
                                } else {
                                    alertify.error(res.message);
                                }
                            },
                            error: function(){
                                alertify.error('เกิดข้อผิดพลาดในการลบข้อมูล');
                            }
                        });
                    },
                    function(){}
                ).set('labels', {ok: 'ยืนยัน', cancel: 'ยกเลิก'});
            });
        });
    </script>
    </body>
    </html>
<?php } ?>
