<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
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
                        <h1 class="h3 mb-0 text-gray-800">ประวัติข้อความจาก LINE Webhook</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">ประวัติข้อความ LINE</li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <button type="button" id="btnReload" class="btn btn-outline-success btn-sm mb-3">
                                        <i class="fa fa-refresh"></i> Reload
                                    </button>
                                    <table id='TableRecordList' class='display dataTable table table-bordered' width="100%">
                                        <thead>
                                        <tr>
                                            <th>วันที่-เวลา (นาที)</th>
                                            <th>ชื่อผู้ส่ง (LINE)</th>
                                            <th>ข้อความ</th>
                                            <th>รูปที่ 1</th>
                                            <th>รูปที่ 2</th>
                                            <th>รูปที่ 3</th>
                                            <th>รูปที่ 4</th>
                                            <th>รูปที่ 5</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal สำหรับแสดงรูปขยาย -->
                <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content bg-dark">
                            <div class="modal-header border-0 text-white">
                                <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="modalImage" src="" class="img-fluid rounded">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>

    <script>
        $(document).ready(function () {
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'pageLength': 5,
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_line_webhook_messages_process.php',
                    'data': { action: "GET_MESSAGES" }
                },
                'columns': [
                    { data: 'minute_group' },
                    { data: 'line_display_name' },
                    { data: 'texts' },
                    { data: 'image1' },
                    { data: 'image2' },
                    { data: 'image3' },
                    { data: 'image4' },
                    { data: 'image5' }
                ],
                'order': [[0, 'desc']],
                'language': {
                    search: 'ค้นหา',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'แสดงหน้า _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่พบข้อมูล",
                    paginate: {
                        previous: 'ก่อนหน้า',
                        next: 'ต่อไป'
                    }
                }
            });

            $('#btnReload').on('click', function() {
                dataRecords.ajax.reload();
            });

            // เมื่อคลิกที่รูปภาพเพื่อขยาย
            $(document).on('click', '.img-preview', function () {
                let imgSrc = $(this).data('img');
                $('#modalImage').attr('src', imgSrc);
                $('#imageModal').modal('show');
            });
        });
    </script>
    </body>
    </html>
<?php } ?>