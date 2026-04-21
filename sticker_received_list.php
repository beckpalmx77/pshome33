<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');

if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
    header("Location: index.php");
} else {

    $sql_car_data = "SELECT 
    SUM(
        (CASE WHEN car_no1 IS NOT NULL AND car_no1 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no2 IS NOT NULL AND car_no2 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no3 IS NOT NULL AND car_no3 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no4 IS NOT NULL AND car_no4 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no5 IS NOT NULL AND car_no5 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no6 IS NOT NULL AND car_no6 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no7 IS NOT NULL AND car_no7 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no8 IS NOT NULL AND car_no8 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no9 IS NOT NULL AND car_no9 <> '' THEN 1 ELSE 0 END) +
        (CASE WHEN car_no10 IS NOT NULL AND car_no10 <> '' THEN 1 ELSE 0 END) 
    ) AS total_cars_count
FROM ims_house;";

    $query_house_data = $conn->prepare($sql_car_data);
    $query_house_data->execute();
    $results_house_data = $query_house_data->fetch(PDO::FETCH_OBJ);

    $total_cars_count = $results_house_data->total_cars_count ?? 0;

    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <link rel="stylesheet" href="css/spin_datatables.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

        <style>
            .card-body {
                padding: 1rem;
            }
            .dataTables_wrapper {
                overflow-x: auto;
            }
            /* ตกแต่งระยะห่างของเมนูเลือกจำนวนแถวและปุ่ม Export */
            .dataTables_length {
                margin-top: 10px;
                margin-right: 20px;
                float: left;
            }
            .dt-buttons {
                margin-top: 10px;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.3em 0.6em;
            }
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
    </head>
    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">รายการบ้านที่รับสติกเกอร์</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item">ข้อมูลสติกเกอร์</li>
                            <li class="breadcrumb-item active" aria-current="page">รายการรับสติกเกอร์</li>
                        </ol>
                    </div>

                    <div class="row mb-3 flex-nowrap overflow-auto py-2">
                        <div class="col" style="min-width: 180px;">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="text-xl font-weight-bold text-uppercase mb-1">จำนวนบ้านที่ลงทะเบียน (หลัง)</div>
                                    <div class="h4 mb-0 font-weight-bold text-end text-right" id="totalHouse">0 หลัง</div>
                                </div>
                            </div>
                        </div>

                        <div class="col" style="min-width: 180px;">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="text-xl font-weight-bold text-uppercase mb-1">จำนวนรถที่ลงทะเบียน (คัน)</div>
                                    <div class="h4 mb-0 font-weight-bold text-end text-right" id="totalCars">0 คัน</div>
                                </div>
                            </div>
                        </div>

                        <div class="col" style="min-width: 220px;">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="text-xl font-weight-bold text-uppercase mb-1" style="line-height: 1.2;">
                                        จำนวนรถทั้งหมด  (คัน)
                                    </div>
                                    <div class="h4 mb-0 font-weight-bold text-end text-right" id="total_cars_count"><?= number_format($total_cars_count) ?></div>
                                    <div class="text-xl font-weight-bold text-uppercase mb-1" style="line-height: 1.2;">
                                        (รับ + ยังไม่ได้รับ สติกเกอร์)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col" style="min-width: 180px;">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="text-xl font-weight-bold text-uppercase mb-1">จำนวนเงินค่าสติกเกอร์รถเพิ่ม  (บาท)</div>
                                    <div class="h4 mb-0 font-weight-bold text-end text-right" id="totalExtraFee">0 บาท</div>
                                </div>
                            </div>
                        </div>

                        <div class="col" style="min-width: 150px;">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body d-flex justify-content-center align-items-center">
                                    <button type="button" class="btn btn-info btn-lg border-white w-100" id="btnExportCsv">
                                        <i class="fas fa-file-csv text-white"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id="TableRecordList" class="display nowrap" style="width:100%;">
                                                <thead>
                                                <tr>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ทะเบียนรถ 1</th>
                                                    <th>ทะเบียนรถ 2</th>
                                                    <th>ทะเบียนรถ 3</th>
                                                    <th>ทะเบียนรถ 4</th>
                                                    <th>ทะเบียนรถ 5</th>
                                                    <th>ทะเบียนรถ 6</th>
                                                    <th>ทะเบียนรถ 7</th>
                                                    <th>จำนวนรถ</th>
                                                    <th>ค่าสติกเกอร์(บาท)</th>
                                                    <th>วันที่รับสติกเกอร์</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
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
            </div>
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade" id="houseDetailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">รายละเอียดบ้านเลขที่: <span id="detailHouseNumber"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detailContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editStickerModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info-subtle">
                    <h5 class="modal-title">แก้ไขข้อมูลสติกเกอร์ บ้านเลขที่: <span id="editHouseNumber"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editStickerForm">
                        <input type="hidden" id="edit_house_number" name="house_number">

                        <h6 class="text-primary font-weight-bold mb-3">ข้อมูลรถ</h6>

                        <div class="row mb-2">
                            <div class="col-md-2"><strong>รถคันที่ 1</strong></div>
                            <div class="col-md-2">ทะเบียน</div>
                            <div class="col-md-2">จังหวัด</div>
                            <div class="col-md-2">ประเภท</div>
                            <div class="col-md-2">ยี่ห้อ-รุ่น</div>
                            <div class="col-md-2">สี</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no1" name="car_no1" placeholder="ทะเบียน">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no1_province" name="car_no1_province" placeholder="จังหวัด">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="edit_car_no1_type" name="car_no1_type">
                                    <option value="">-- เลือก --</option>
                                    <option value="รถยนต์">รถยนต์</option>
                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no1_brand" name="car_no1_brand" placeholder="ยี่ห้อ-รุ่น">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no1_color" name="car_no1_color" placeholder="สี">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-2"><strong>รถคันที่ 2</strong></div>
                            <div class="col-md-2">ทะเบียน</div>
                            <div class="col-md-2">จังหวัด</div>
                            <div class="col-md-2">ประเภท</div>
                            <div class="col-md-2">ยี่ห้อ-รุ่น</div>
                            <div class="col-md-2">สี</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no2" name="car_no2" placeholder="ทะเบียน">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no2_province" name="car_no2_province" placeholder="จังหวัด">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="edit_car_no2_type" name="car_no2_type">
                                    <option value="">-- เลือก --</option>
                                    <option value="รถยนต์">รถยนต์</option>
                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no2_brand" name="car_no2_brand" placeholder="ยี่ห้อ-รุ่น">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no2_color" name="car_no2_color" placeholder="สี">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-2"><strong>รถคันที่ 3</strong></div>
                            <div class="col-md-2">ทะเบียน</div>
                            <div class="col-md-2">จังหวัด</div>
                            <div class="col-md-2">ประเภท</div>
                            <div class="col-md-2">ยี่ห้อ-รุ่น</div>
                            <div class="col-md-2">สี</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no3" name="car_no3" placeholder="ทะเบียน">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no3_province" name="car_no3_province" placeholder="จังหวัด">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="edit_car_no3_type" name="car_no3_type">
                                    <option value="">-- เลือก --</option>
                                    <option value="รถยนต์">รถยนต์</option>
                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no3_brand" name="car_no3_brand" placeholder="ยี่ห้อ-รุ่น">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no3_color" name="car_no3_color" placeholder="สี">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-2"><strong>รถคันที่ 4</strong></div>
                            <div class="col-md-2">ทะเบียน</div>
                            <div class="col-md-2">จังหวัด</div>
                            <div class="col-md-2">ประเภท</div>
                            <div class="col-md-2">ยี่ห้อ-รุ่น</div>
                            <div class="col-md-2">สี</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no4" name="car_no4" placeholder="ทะเบียน">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no4_province" name="car_no4_province" placeholder="จังหวัด">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="edit_car_no4_type" name="car_no4_type">
                                    <option value="">-- เลือก --</option>
                                    <option value="รถยนต์">รถยนต์</option>
                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no4_brand" name="car_no4_brand" placeholder="ยี่ห้อ-รุ่น">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no4_color" name="car_no4_color" placeholder="สี">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-2"><strong>รถคันที่ 5</strong></div>
                            <div class="col-md-2">ทะเบียน</div>
                            <div class="col-md-2">จังหวัด</div>
                            <div class="col-md-2">ประเภท</div>
                            <div class="col-md-2">ยี่ห้อ-รุ่น</div>
                            <div class="col-md-2">สี</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no5" name="car_no5" placeholder="ทะเบียน">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no5_province" name="car_no5_province" placeholder="จังหวัด">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="edit_car_no5_type" name="car_no5_type">
                                    <option value="">-- เลือก --</option>
                                    <option value="รถยนต์">รถยนต์</option>
                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no5_brand" name="car_no5_brand" placeholder="ยี่ห้อ-รุ่น">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no5_color" name="car_no5_color" placeholder="สี">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-2"><strong>รถคันที่ 6</strong></div>
                            <div class="col-md-2">ทะเบียน</div>
                            <div class="col-md-2">จังหวัด</div>
                            <div class="col-md-2">ประเภท</div>
                            <div class="col-md-2">ยี่ห้อ-รุ่น</div>
                            <div class="col-md-2">สี</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no6" name="car_no6" placeholder="ทะเบียน">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no6_province" name="car_no6_province" placeholder="จังหวัด">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="edit_car_no6_type" name="car_no6_type">
                                    <option value="">-- เลือก --</option>
                                    <option value="รถยนต์">รถยนต์</option>
                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no6_brand" name="car_no6_brand" placeholder="ยี่ห้อ-รุ่น">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no6_color" name="car_no6_color" placeholder="สี">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-2"><strong>รถคันที่ 7</strong></div>
                            <div class="col-md-2">ทะเบียน</div>
                            <div class="col-md-2">จังหวัด</div>
                            <div class="col-md-2">ประเภท</div>
                            <div class="col-md-2">ยี่ห้อ-รุ่น</div>
                            <div class="col-md-2">สี</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no7" name="car_no7" placeholder="ทะเบียน">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no7_province" name="car_no7_province" placeholder="จังหวัด">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="edit_car_no7_type" name="car_no7_type">
                                    <option value="">-- เลือก --</option>
                                    <option value="รถยนต์">รถยนต์</option>
                                    <option value="จักรยานยนต์">จักรยานยนต์</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no7_brand" name="car_no7_brand" placeholder="ยี่ห้อ-รุ่น">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="edit_car_no7_color" name="car_no7_color" placeholder="สี">
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btnSaveEdit">บันทึก</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <!--script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script-->
    <!--script src="vendor/jquery-easing/jquery.easing.min.js"></script-->
    <!--script src="js/sb-admin-2.min.js"></script-->

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables/v11/dataTables.buttons.min.js"></script>
    <script src="vendor/datatables/v11/jszip.min.js"></script>
    <script src="vendor/datatables/v11/pdfmake.min.js"></script>
    <script src="vendor/datatables/v11/vfs_fonts.js"></script>
    <script src="vendor/datatables/v11/buttons.html5.min.js"></script>
    <script src="vendor/datatables/v11/buttons.print.min.js"></script>


    <script>
        $(document).ready(function() {
            let table = $('#TableRecordList').DataTable({
                "processing": true,
                "serverSide": false,
                "ajax": {
                    "url": "model/get_sticker_received_list.php",
                    "type": "POST",
                    "dataSrc": function(json) {
                        if (json.summary) {
                            $('#totalHouse').text(json.summary.total_house);
                            $('#totalCars').text(json.summary.total_cars);
                            $('#totalExtraFee').text(json.summary.total_extra_fee.toLocaleString());
                        }
                        return json.data;
                    }
                },
                "columns": [
                    { "data": "house_number" },
                    { "data": "car_no1" },
                    { "data": "car_no2" },
                    { "data": "car_no3" },
                    { "data": "car_no4" },
                    { "data": "car_no5" },
                    { "data": "car_no6" },
                    { "data": "car_no7" },
                    { "data": "car_count", "className": "text-right" },
                    { "data": "extra_car_fee", "className": "text-right" },
                    { "data": "sticker_receive_date" },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            return '<button type="button" class="btn btn-sm btn-info btn-detail" data-house="' + row.house_number + '"><i class="fas fa-eye"></i> ดู</button>';
                        },
                        "orderable": false
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            return '<button type="button" class="btn btn-sm btn-warning btn-edit" data-house="' + row.house_number + '"><i class="fas fa-edit"></i> แก้ไข</button>';
                        },
                        "orderable": false
                    }
                ],
                "language": {
                    "emptyTable": "ไม่พบข้อมูล",
                    "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                    "infoFiltered": "(กรองจาก _MAX_ รายการ)",
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "loadingRecords": "กำลังโหลด...",
                    "processing": "กำลังประมวลผล...",
                    "search": "ค้นหา:",
                    "zeroRecords": "ไม่พบรายการที่ตรงกัน",
                    "paginate": {
                        "first": "หน้าแรก",
                        "last": "หน้าสุดท้าย",
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    }
                },
                "dom": 'Blfrtip', // l คือตัวเลือกความยาวหน้า (length changing input)
                "buttons": [
                    'copy', 'excel', 'print'
                ],
                "order": [[10, "desc"]],
                "lengthMenu": [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "All"]], // กำหนดเมนูเลือกจำนวนแถว
                "pageLength": 5, // ตั้งค่าเริ่มต้นให้แสดง 10 แถว
                "retrieve": true
            });

            // Export CSV button click
            $('#btnExportCsv').on('click', function() {
                let data = table.rows().data();
                if (data.length === 0) {
                    alert('ไม่มีข้อมูลสำหรับ Export');
                    return;
                }

                let csvContent = "\uFEFF"; // BOM for UTF-8
                csvContent += "ลำดับ,บ้านเลขที่,ทะเบียนรถ 1,ทะเบียนรถ 2,ทะเบียนรถ 3,ทะเบียนรถ 4,ทะเบียนรถ 5,ทะเบียนรถ 6,ทะเบียนรถ 7,จำนวนรถ,ค่าสติกเกอร์รถเพิ่ม (บาท),วันที่รับสติกเกอร์\n";

                let totalCars = 0;
                let totalExtraFee = 0;
                let rowNum = 1;

                data.each(function(row) {
                    csvContent += rowNum + ',';
                    csvContent += '"' + row.house_number + '",';
                    csvContent += '"' + row.car_no1 + '",';
                    csvContent += '"' + row.car_no2 + '",';
                    csvContent += '"' + row.car_no3 + '",';
                    csvContent += '"' + row.car_no4 + '",';
                    csvContent += '"' + row.car_no5 + '",';
                    csvContent += '"' + row.car_no6 + '",';
                    csvContent += '"' + row.car_no7 + '",';
                    csvContent += row.car_count + ',';
                    csvContent += row.extra_car_fee + ',';
                    csvContent += '"' + row.sticker_receive_date + '"\n';

                    totalCars += row.car_count;
                    totalExtraFee += row.extra_car_fee;
                    rowNum++;
                });

                // Add total row
                csvContent += '"รวมทั้งหมด","","","","","","","",' + totalCars + ',' + totalExtraFee + ',""\n';

                let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                let link = document.createElement("a");
                let url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", "sticker_received_list.csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Detail button click
            $('#TableRecordList').on('click', '.btn-detail', function() {
                let houseNumber = $(this).data('house');
                let rowData = table.row($(this).closest('tr')).data();

                $('#detailHouseNumber').text(houseNumber);

                let detailHtml = '<table class="table table-bordered"><thead><tr><th>ลำดับ</th><th>ทะเบียนรถ</th><th>จังหวัด</th><th>ยี่ห้อ-รุ่น</th><th>สี</th><th>ประเภท</th></tr></thead><tbody>';

                let cars = [];
                if (rowData.car_no1) cars.push({no: 1, car_no: rowData.car_no1, province: rowData.car_no1_province, brand: rowData.car_no1_brand, color: rowData.car_no1_color, type: rowData.car_no1_type});
                if (rowData.car_no2) cars.push({no: 2, car_no: rowData.car_no2, province: rowData.car_no2_province, brand: rowData.car_no2_brand, color: rowData.car_no2_color, type: rowData.car_no2_type});
                if (rowData.car_no3) cars.push({no: 3, car_no: rowData.car_no3, province: rowData.car_no3_province, brand: rowData.car_no3_brand, color: rowData.car_no3_color, type: rowData.car_no3_type});
                if (rowData.car_no4) cars.push({no: 4, car_no: rowData.car_no4, province: rowData.car_no4_province, brand: rowData.car_no4_brand, color: rowData.car_no4_color, type: rowData.car_no4_type});
                if (rowData.car_no5) cars.push({no: 5, car_no: rowData.car_no5, province: rowData.car_no5_province, brand: rowData.car_no5_brand, color: rowData.car_no5_color, type: rowData.car_no5_type});
                if (rowData.car_no6) cars.push({no: 6, car_no: rowData.car_no6, province: rowData.car_no6_province, brand: rowData.car_no6_brand, color: rowData.car_no6_color, type: rowData.car_no6_type});
                if (rowData.car_no7) cars.push({no: 7, car_no: rowData.car_no7, province: rowData.car_no7_province, brand: rowData.car_no7_brand, color: rowData.car_no7_color, type: rowData.car_no7_type});

                for (let i = 0; i < cars.length; i++) {
                    detailHtml += '<tr><td>' + cars[i].no + '</td><td>' + cars[i].car_no + '</td><td>' + cars[i].province + '</td><td>' + cars[i].brand + '</td><td>' + cars[i].color + '</td><td>' + cars[i].type + '</td></tr>';
                }

                detailHtml += '</tbody></table>';
                detailHtml += '<div class="mt-3"><strong>จำนวนรถ: </strong>' + rowData.car_count + ' คัน</div>';
                detailHtml += '<div><strong>ค่าสติกเกอร์รถเพิ่ม: </strong>' + rowData.extra_car_fee + ' บาท</div>';
                detailHtml += '<div><strong>วันที่รับสติกเกอร์: </strong>' + rowData.sticker_receive_date + '</div>';

                $('#detailContent').html(detailHtml);
                $('#houseDetailModal').modal('show');
            });

            // Edit button click
            $('#TableRecordList').on('click', '.btn-edit', function() {
                let houseNumber = $(this).data('house');
                let rowData = table.row($(this).closest('tr')).data();

                $('#editHouseNumber').text(houseNumber);
                $('#edit_house_number').val(houseNumber);

                // Clear all fields
                for (let i = 1; i <= 7; i++) {
                    $('#edit_car_no' + i).val('');
                    $('#edit_car_no' + i + '_province').val('');
                    $('#edit_car_no' + i + '_type').val('');
                    $('#edit_car_no' + i + '_brand').val('');
                    $('#edit_car_no' + i + '_color').val('');
                }

                // Fill data
                if (rowData.car_no1) {
                    $('#edit_car_no1').val(rowData.car_no1);
                    $('#edit_car_no1_province').val(rowData.car_no1_province || '');
                    $('#edit_car_no1_type').val(rowData.car_no1_type || '');
                    $('#edit_car_no1_brand').val(rowData.car_no1_brand || '');
                    $('#edit_car_no1_color').val(rowData.car_no1_color || '');
                }
                if (rowData.car_no2) {
                    $('#edit_car_no2').val(rowData.car_no2);
                    $('#edit_car_no2_province').val(rowData.car_no2_province || '');
                    $('#edit_car_no2_type').val(rowData.car_no2_type || '');
                    $('#edit_car_no2_brand').val(rowData.car_no2_brand || '');
                    $('#edit_car_no2_color').val(rowData.car_no2_color || '');
                }
                if (rowData.car_no3) {
                    $('#edit_car_no3').val(rowData.car_no3);
                    $('#edit_car_no3_province').val(rowData.car_no3_province || '');
                    $('#edit_car_no3_type').val(rowData.car_no3_type || '');
                    $('#edit_car_no3_brand').val(rowData.car_no3_brand || '');
                    $('#edit_car_no3_color').val(rowData.car_no3_color || '');
                }
                if (rowData.car_no4) {
                    $('#edit_car_no4').val(rowData.car_no4);
                    $('#edit_car_no4_province').val(rowData.car_no4_province || '');
                    $('#edit_car_no4_type').val(rowData.car_no4_type || '');
                    $('#edit_car_no4_brand').val(rowData.car_no4_brand || '');
                    $('#edit_car_no4_color').val(rowData.car_no4_color || '');
                }
                if (rowData.car_no5) {
                    $('#edit_car_no5').val(rowData.car_no5);
                    $('#edit_car_no5_province').val(rowData.car_no5_province || '');
                    $('#edit_car_no5_type').val(rowData.car_no5_type || '');
                    $('#edit_car_no5_brand').val(rowData.car_no5_brand || '');
                    $('#edit_car_no5_color').val(rowData.car_no5_color || '');
                }
                if (rowData.car_no6) {
                    $('#edit_car_no6').val(rowData.car_no6);
                    $('#edit_car_no6_province').val(rowData.car_no6_province || '');
                    $('#edit_car_no6_type').val(rowData.car_no6_type || '');
                    $('#edit_car_no6_brand').val(rowData.car_no6_brand || '');
                    $('#edit_car_no6_color').val(rowData.car_no6_color || '');
                }
                if (rowData.car_no7) {
                    $('#edit_car_no7').val(rowData.car_no7);
                    $('#edit_car_no7_province').val(rowData.car_no7_province || '');
                    $('#edit_car_no7_type').val(rowData.car_no7_type || '');
                    $('#edit_car_no7_brand').val(rowData.car_no7_brand || '');
                    $('#edit_car_no7_color').val(rowData.car_no7_color || '');
                }

                $('#editStickerModal').modal('show');
                
                // Initialize autocomplete after modal is shown
                for (let i = 1; i <= 7; i++) {
                    initProvinceAutocomplete("edit_car_no" + i + "_province");
                    initColorAutocomplete("edit_car_no" + i + "_color");
                    initBrandAutocomplete("edit_car_no" + i + "_brand");
                }
            });

            // Save edit button click
            $('#btnSaveEdit').on('click', function() {
                let formData = $('#editStickerForm').serialize();

                $.ajax({
                    url: 'model/update_sticker_received.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        try {
                            let result = typeof response === 'string' ? JSON.parse(response) : response;
                            if (result.status === 'success') {
                                alertify.success('บันทึกสำเร็จ');
                                $('#editStickerModal').modal('hide');
                                table.ajax.reload();
                            } else {
                                alertify.error('เกิดข้อผิดพลาด: ' + result.message);
                            }
                        } catch(e) {
                            alertify.error('เกิดข้อผิดพลาด: ' + response);
                        }
                    },
                    error: function(xhr, status, error) {
                        alertify.error('เกิดข้อผิดพลาด: ' + error);
                    }
                });
            });

            function initProvinceAutocomplete(inputId) {
                $("#" + inputId).autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            type: "POST",
                            url: 'model/get_autocomplete.php',
                            data: {action: "GET_PROVINCE_AUTOCOMPLETE", search: request.term},
                            dataType: "json",
                            success: function (data) {
                                if (Array.isArray(data)) {
                                    response(data.map(function(item) {
                                        return {label: item, value: item};
                                    }));
                                } else {
                                    response([]);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.log('Province autocomplete error:', status, error);
                                response([]);
                            }
                        });
                    },
                    minLength: 0,
                    appendTo: "#editStickerModal"
                });
            }

            function initColorAutocomplete(inputId) {
                $("#" + inputId).autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            type: "POST",
                            url: 'model/get_autocomplete.php',
                            data: {action: "GET_COLOR_AUTOCOMPLETE", search: request.term},
                            dataType: "json",
                            success: function (data) {
                                if (Array.isArray(data)) {
                                    response(data.map(function(item) {
                                        return {label: item, value: item};
                                    }));
                                } else {
                                    response([]);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.log('Color autocomplete error:', status, error);
                                response([]);
                            }
                        });
                    },
                    minLength: 0,
                    appendTo: "#editStickerModal"
                });
            }

            function initBrandAutocomplete(inputId) {
                $("#" + inputId).autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            type: "POST",
                            url: 'model/get_autocomplete.php',
                            data: {action: "GET_BRAND_AUTOCOMPLETE", search: request.term},
                            dataType: "json",
                            success: function (data) {
                                if (Array.isArray(data)) {
                                    response(data.map(function(item) {
                                        return {label: item, value: item};
                                    }));
                                } else {
                                    response([]);
                                }
                            },
                            error: function (xhr, status, error) {
                                console.log('Brand autocomplete error:', status, error);
                                response([]);
                            }
                        });
                    },
                    minLength: 0,
                    appendTo: "#editStickerModal"
                });
            }

        });
    </script>
    </body>
    </html>

<?php } ?>