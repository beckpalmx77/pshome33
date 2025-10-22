<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อความติดต่อ</title>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
    <style>
        :root {
            --pastel-blue: #a4d8f0;
            --pastel-blue-dark: #7cbcd4;
            --text-color: #354a5f;
            --background-light: #f5fbff;
            --button-success: #a2d5ac;
            --button-danger: #f7a1a1;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: var(--background-light);
            color: var(--text-color);
        }

        #wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: white;
            max-width: 1200px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(164, 216, 240, 0.25);
            padding: 20px;
        }

        .card {
            background: white;
            border: 1px solid var(--pastel-blue);
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(164, 216, 240, 0.1);
            margin-bottom: 1rem;
        }

        .card-header {
            background-color: var(--pastel-blue);
            color: white;
            font-weight: 600;
            border-radius: 8px 8px 0 0;
            padding: 1rem 1.5rem;
        }

        .card-body {
            padding: 1rem 1.5rem;
        }

        h1.h3, .breadcrumb-item, .text-sm {
            color: var(--pastel-blue-dark);
        }

        table.dataTable th,
        table.dataTable td {
            border: 1px solid var(--pastel-blue);
            text-align: center;
        }

        table.dataTable thead th, table.dataTable tfoot th {
            background-color: var(--pastel-blue);
            color: white;
        }

        .form-control {
            border: 1px solid var(--pastel-blue);
            background-color: #f0faff;
            color: var(--text-color);
        }

        .btn-success {
            background-color: var(--button-success);
            color: #2e662d;
        }

        .btn-danger {
            background-color: var(--button-danger);
            color: #7a2323;
        }

        .modal-content {
            border: 1px solid var(--pastel-blue);
            box-shadow: 0 6px 15px rgba(164, 216, 240, 0.3);
        }

        .modal-header h4,
        .modal-header h5,
        .close {
            color: var(--pastel-blue-dark);
        }

        .zoom-container img {
            max-width: 200px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(164, 216, 240, 0.5);
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .zoom-container:hover img {
            transform: scale(1.5);
        }

        /* ทำให้ตาราง scroll แนวนอนบนมือถือได้ */
        .table-responsive-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-responsive-wrapper table {
            min-width: 600px; /* ปรับตามจำนวนคอลัมน์ */
        }

        @media (max-width: 768px) {
            .col-sm-4, .col-sm-6 {
                width: 100%;
            }

            .d-sm-flex {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h5 mb-0 text-gray-800">กระดานข้อความ</h1>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-body">
                                <section class="container-fluid">
                                    <div class="col-md-12 col-md-offset-2 table-responsive-wrapper">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>ลำดับ</th>
                                                <th>ข้อความ</th>
                                                <th>ชื่อ</th>
                                                <th>วันที่</th>
                                                <th>action</th>
                                            </tr>
                                            </thead>
                                        </table>
                                        <div id="result"></div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/Modal-Logout.php'); ?>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <div class="modal fade" id="recordModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Modal title</h4>
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">×
                    </button>
                </div>
                <form method="post" id="recordForm">
                    <div class="modal-body">
                        <div class="modal-body">

                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="f_name" class="control-label">ชื่อ</label>
                                    <input type="f_name" class="form-control"
                                           id="f_name" name="f_name"
                                           readonly="true"
                                           placeholder="ชื่อ">
                                </div>

                                <div class="col-sm-6">
                                    <label for="l_name"
                                           class="control-label">นามสกุล</label>
                                    <input type="text" class="form-control"
                                           id="l_name" name="l_name"
                                           readonly="true"
                                           placeholder="นามสกุล">
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="house_number"
                                           class="control-label">บ้านเลขที่</label>
                                    <input type="text" class="form-control"
                                           id="house_number" name="house_number"
                                           readonly="true"
                                           placeholder="บ้านเลขที่">
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label for="images">ภาพ: (Cick ที่รูปเพื่อขยาย)</label>
                                    <div id="imagePreview" class="d-flex flex-wrap gap-2"></div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="remark" class="control-label">ข้อความ</label>
                                    <textarea class="form-control" id="remark" name="remark" rows="4"
                                              placeholder="" readonly="true"></textarea>
                                </div>

                                <div class="col-sm-6">
                                    <label for="answer" class="control-label">คำตอบ</label>
                                    <textarea class="form-control" id="answer" name="answer" rows="4"
                                              placeholder="" readonly="true"></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-6">
                                    <label for="update_date"
                                           class="control-label">วันที่ข้อความ</label>
                                    <input type="text" class="form-control"
                                           id="create_date" name="create_date"
                                           readonly="true"
                                           placeholder="">
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="id" id="id"/>
                        <input type="hidden" name="line_user_id" id="line_user_id" value=""/>
                        <input type="hidden" name="action" id="action" value=""/>
                        <button type="button" class="btn btn-danger"
                                data-dismiss="modal">Close <i
                                    class="fa fa-times"></i>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body text-center">
                    <!-- ภาพที่จะแสดงใน modal -->
                    <img id="modalImage" src="" class="img-fluid rounded" style="max-height: 80vh;">
                </div>
            </div>
        </div>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
<script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>

<script>
        let dataRecords = $('#TableRecordList').DataTable({
            lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
            language: {
                search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล', zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                paginate: { previous: 'ก่อนหน้า', last: 'สุดท้าย', next: 'ต่อไป' }
            },
            processing: true,
            serverSide: true,
            searching: false,
            serverMethod: 'post',
            ajax: {
                url: 'model/manage_message_smart_process.php',
                data: { action: "GET_MESSAGE", sub_action: "GET_MASTER" }
            },
            columns: [
                {data: 'line_no'},
                {data: 'remark'},
                {data: 'f_name'},
                {data: 'create_date'},
                {data: 'detail'}
            ]
        });
</script>

<script>

    $("#TableRecordList").on('click', '.detail', function () {
        let id = $(this).attr("id");
        //alert(id);
        let formData = {action: "GET_DATA", id: id};
        $.ajax({
            type: "POST",
            url: 'model/manage_message_process.php',
            dataType: "json",
            data: formData,
            success: function (response) {
                let len = response.length;
                for (let i = 0; i < len; i++) {
                    let id = response[i].id;
                    let f_name = response[i].f_name;
                    let l_name = response[i].l_name;
                    let house_number = response[i].house_number;
                    let remark = response[i].remark;
                    let answer = response[i].answer;
                    let create_date = response[i].create_date;
                    let line_user_id = response[i].line_user_id;
                    let images = response[i].images;

                    $('#recordModal').modal('show');
                    $('#id').val(id);
                    $('#f_name').val(f_name);
                    $('#l_name').val(l_name);
                    $('#house_number').val(house_number);
                    $('#remark').val(remark);
                    $('#answer').val(answer);
                    $('#create_date').val(create_date);
                    $('#line_user_id').val(line_user_id);
                    $('.modal-title').html("<i class='fa fa-plus'></i> รายละเอียด");

                    $('#imagePreview').html('');

                    if (images && images.trim() !== "") {
                        let filenames = images.split(',');
                        filenames.forEach(filename => {
                            filename = filename.trim();
                            if (filename !== "") {
                                let imgTag = `<img src="line_oa/house/uploads/${filename}" class="img-thumbnail m-1 img-preview" style="height: 100px; cursor: pointer;">`;
                                $('#imagePreview').append(imgTag);
                            }
                        });
                    }


                }
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });
    });

</script>



</body>
</html>
