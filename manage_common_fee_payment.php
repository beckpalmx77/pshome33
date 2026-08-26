<?php
session_start();
error_reporting(0);
include('includes/Header.php');
include('config/connect_db.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['house_number']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <link rel="stylesheet" href="css/spin_datatables_v2.css"/>
        <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
        <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
        <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
        <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.dataTables.min.css"/>

        <style>
            /* ปรับให้หน้าเพจกระชับขึ้น */
            .card-body {
                padding: 1rem;
            }

            /* CSS สำหรับ FixedHeader background */
            #TableRecordList thead th {
                background-color: #f8f9fc;
            }
            
            .fixedHeader-floating {
                background-color: white !important;
                z-index: 1000;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }

            .modal-body {
                padding: 1rem;
            }

            .modal-footer {
                padding: 0.75rem 1rem;
            }

            .form-group.row {
                margin-bottom: 0.5rem;
            }

            /* CSS สำหรับ Footer */
            .sticky-footer.bg-white {
                padding: 1rem 0;
            }

            /* CSS สำหรับตาราง */
            .dataTables_wrapper {
                overflow-x: auto;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0.3em 0.6em;
            }

            .zoom-container {
                position: relative;
                overflow: hidden;
                display: inline-block;
            }

            .zoom-container img {
                transition: transform 0.3s ease;
            }

            .zoom-container:hover img {
                transform: scale(1.5);
                cursor: zoom-out;
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
                        <input type="hidden" id="user_type" name="user_type"
                               value="<?php echo $_SESSION['account_type'] ?>">
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
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-start flex-wrap">
                                    <button type="button" id="btnReload" class="btn btn-outline-success btn-xs" data-toggle="tooltip" title="Reload Data">
                                        <i class="fa fa-refresh"></i> Reload Data
                                    </button>
                                    <div class="input-group input-group-sm ml-2" style="width: 250px;">
                                        <input type="text" id="search_house_number" class="form-control" placeholder="ค้นหา บ้านเลขที่ (ระบุตรงตัว)">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button" id="btnSearch">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <a href="manage_common_fee_payment_not_confirm.php?m=ข้อมูลเกี่ยวกับการเงิน&s=ค่าส่วนกลางที่ยังไม่ยืนยันการชำระ" class="btn btn-outline-danger ml-2">
                                        <i class="fas fa-search-dollar"></i> ค้นหายังไม่ยืนยันการชำระ
                                    </a>
                                    <a href="show_duplicate_payments.php?m=ข้อมูลเกี่ยวกับการเงิน&s=ตรวจสอบรายการชำระค่าส่วนกลางบันทึกซ้ำ" class="btn btn-outline-warning ml-2">
                                        <i class="fas fa-copy"></i> ตรวจสอบบันทึกซ้ำ
                                    </a>
                                    <button type="button" id="btnQueryProfiler" class="btn btn-outline-info btn-xs ml-2" data-toggle="tooltip" title="Query Performance Profiler & EXPLAIN ANALYZE">
                                        <i class="fas fa-chart-line"></i> Query Profiler
                                    </button>
                                    <span id="perfBadge" class="badge badge-success ml-2" style="font-size: 12px; padding: 6px 10px; cursor: pointer; display: none;" data-toggle="tooltip" title="คลิกเพื่อดู Query Performance & EXPLAIN ANALYZE">
                                        <i class="fas fa-bolt"></i> <span id="perfTimeText">0.0 ms</span>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id="TableRecordList" class="display nowrap" style="width:100%;">
                                                <thead>
                                                <tr>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>บ้านเลขที่</th>
                                                    <th>ซอย</th>
                                                    <th>ผู้ชำระ</th>
                                                    <th>picture</th>
                                                    <th>งวดเดือน</th>
                                                    <th>ปี</th>
                                                    <th>ค่าส่วนกลาง</th>
                                                    <th>จำนวนงวด</th>
                                                    <th>ยอดชำระ</th>
                                                    <th>Slip</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                    <th>ใบเสร็จ</th>
                                                    <th>ขนาดพื้นที่ ตรว</th>
                                                    <th>ค่าเก็บขยะ</th>
                                                    <th>ลบข้อมูล</th>
                                                </tr>
                                                </thead>
                                            </table>
                                            <div id="result"></div>
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
                                                                        <label for="doc_id"
                                                                               class="control-label">เลขที่เอกสาร</label>
                                                                        <input type="text" class="form-control"
                                                                               id="doc_id"
                                                                               name="doc_id"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="payment_date"
                                                                               class="control-label">วันที่เอกสาร</label>
                                                                        <input type="text" class="form-control"
                                                                               id="payment_date"
                                                                               name="payment_date"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-6">
                                                                        <label for="house_number"
                                                                               class="control-label">บ้านเลขที่</label>
                                                                        <input type="text" class="form-control"
                                                                               id="house_number"
                                                                               name="house_number"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label for="detail"
                                                                               class="control-label">ผู้ชำระ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="detail"
                                                                               name="detail"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-md-4">
                                                                        <label for="period_month_start">เริ่มงวดเดือน</label>
                                                                        <select name="period_month_start"
                                                                                id="period_month_start"
                                                                                class="form-control" required>
                                                                            <option value="">เลือก</option>
                                                                            <?php
                                                                            $months = [
                                                                                1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                                                                                5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                                                                                9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                                                                            ];
                                                                            foreach ($months as $val => $name) {
                                                                                echo "<option value='$val'>$name</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label for="period_month_to">ถึงงวดเดือน</label>
                                                                        <select name="period_month_to"
                                                                                id="period_month_to"
                                                                                class="form-control"
                                                                                required>
                                                                            <option value="">เลือก</option>
                                                                            <?php
                                                                            foreach ($months as $val => $name) {
                                                                                echo "<option value='$val'>$name</option>";
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label for="period_year">งวดปี</label>
                                                                        <input type="number" name="period_year"
                                                                               id="period_year"
                                                                               class="form-control" required
                                                                               value="<?php echo date('Y'); ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="amount"
                                                                               class="control-label">จำนวนเงิน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="amount"
                                                                               name="amount"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="payment_method"
                                                                               class="control-label">วิธีการชำระ</label>
                                                                        <select class="form-control"
                                                                                id="payment_method"
                                                                                name="payment_method"
                                                                                required>
                                                                            <option value="โอนเงิน">โอนเงิน</option>
                                                                            <option value="เงินสด">เงินสด</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="payment_status_desc"
                                                                               class="control-label">สถานะ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="payment_status_desc"
                                                                               name="payment_status_desc"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-6 zoom-container">
                                                                        <img id="preview_image" src="#"
                                                                             alt="Preview Image"
                                                                             style="display: none; margin-top: 10px; max-width: 200px; cursor: pointer;"
                                                                             onclick="openImageInNewWindow(this.src)"/>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <label>สถานะการอนุมัติ</label><br>
                                                                        <input type="radio" id="approved"
                                                                               name="payment_status" value="Y">
                                                                        <label for="approved" class="btn btn-success">ยืนยันการชำระ</label>
                                                                        <input type="radio" id="rejected"
                                                                               name="payment_status" value="N">
                                                                        <label for="rejected" class="btn btn-danger">ยังไม่ยืนยันการชำระ</label>
                                                                    </div>
                                                                </div>

                                                                <!--div class="form-group row">
                                                                    <div class="col-sm-12">
                                                                        <button type="button" class="btn btn-info" id="scanQRBtn">
                                                                            <i class="fas fa-qrcode"></i> Scan QR Code เพื่อ Verify
                                                                        </button>
                                                                    </div>
                                                                </div-->

                                                                <div class="form-group row" id="qrScannerSection" style="display: none;">
                                                                    <div class="col-sm-12">
                                                                        <div id="reader" style="width: 100%;"></div>
                                                                        <button type="button" class="btn btn-secondary btn-sm mt-2" id="closeScannerBtn">ปิดกล้อง</button>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row" id="qrVerifyResult" style="display: none;">
                                                                    <div class="col-sm-12">
                                                                        <div class="alert" id="qrResultAlert"></div>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="create_by"
                                                                               class="control-label">สร้างรายการ
                                                                            โดย</label>
                                                                        <input type="text" class="form-control"
                                                                               id="create_by"
                                                                               name="create_by"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="created_at"
                                                                               class="control-label">วัน-เวลา
                                                                            สร้างรายการ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="created_at"
                                                                               name="created_at"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="remark"
                                                                               class="control-label">หมายเหตุ
                                                                        </label>
                                                                        <input type="text" class="form-control"
                                                                               id="remark"
                                                                               name="remark"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="approve_by"
                                                                               class="control-label">ปรับปรุงข้อมูล/อนุมัติ
                                                                            โดย</label>
                                                                        <input type="text" class="form-control"
                                                                               id="approve_by"
                                                                               name="approve_by"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="updated_at"
                                                                               class="control-label">วัน-เวลา
                                                                            ปรับปรุงข้อมูล</label>
                                                                        <input type="text" class="form-control"
                                                                               id="updated_at"
                                                                               name="updated_at"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <button type="button" class="btn btn-primary"
                                                                    id="saveButton">Save <i
                                                                        class="fa fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="slipModal" tabindex="-1" role="dialog"
                                             aria-labelledby="slipModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content text-center">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="slipModalLabel">
                                                            หลักฐานการโอนเงิน</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img id="slipImage" src="" alt="Slip Image"
                                                             class="img-fluid rounded shadow-sm">
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <button type="button" class="btn btn-primary"
                                                                id="printSlipButton">
                                                            <i class="fa fa-print"></i> พิมพ์สลิป
                                                        </button>
                                                        <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">ปิด
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                                             aria-labelledby="confirmDeleteLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title" id="confirmDeleteLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="close text-white"
                                                                data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณต้องการลบข้อมูลนี้ใช่หรือไม่?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">ยกเลิก
                                                        </button>
                                                        <button type="button" class="btn btn-danger"
                                                                id="confirmDeleteBtn">ลบข้อมูล
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Query Performance Profiler & EXPLAIN ANALYZE -->
                                        <div class="modal fade" id="queryProfilerModal" tabindex="-1" role="dialog" aria-labelledby="queryProfilerModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-xl" role="document" style="max-width: 90%;">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-dark text-white py-2">
                                                        <h5 class="modal-title d-flex align-items-center" id="queryProfilerModalLabel">
                                                            <i class="fas fa-tachometer-alt text-warning mr-2"></i> Query Performance Profiler & EXPLAIN ANALYZE
                                                            <span id="modalMysqlVersionBadge" class="badge badge-info ml-2" style="font-size: 11px;">MySQL</span>
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body p-3">
                                                        <!-- Nav Tabs -->
                                                        <ul class="nav nav-tabs mb-3" id="profilerTabs" role="tablist">
                                                            <li class="nav-item">
                                                                <a class="nav-link active" id="tab-overview-link" data-toggle="tab" href="#tab-overview" role="tab">
                                                                    <i class="fas fa-bolt text-warning"></i> ภาพรวมประสิทธิภาพ (Live Profiler)
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="tab-analyze-link" data-toggle="tab" href="#tab-analyze" role="tab">
                                                                    <i class="fas fa-project-diagram text-info"></i> EXPLAIN ANALYZE (Execution Tree)
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="tab-explain-link" data-toggle="tab" href="#tab-explain" role="tab">
                                                                    <i class="fas fa-table text-success"></i> ตาราง Execution Plan (EXPLAIN)
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="tab-tester-link" data-toggle="tab" href="#tab-tester" role="tab">
                                                                    <i class="fas fa-vial text-primary"></i> เครื่องมือทดสอบ Query (Live Tester)
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" id="tab-insights-link" data-toggle="tab" href="#tab-insights" role="tab">
                                                                    <i class="fas fa-lightbulb text-warning"></i> คำแนะนำและข้อสังเกต (Insights)
                                                                </a>
                                                            </li>
                                                        </ul>

                                                        <!-- Tab Contents -->
                                                        <div class="tab-content" id="profilerTabContent">
                                                            <!-- Tab 1: Overview & KPI Cards -->
                                                            <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                                                                <div class="row mb-3">
                                                                    <div class="col-md-3 col-sm-6 mb-2">
                                                                        <div class="card border-left-success shadow-sm h-100 py-2">
                                                                            <div class="card-body py-1">
                                                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Execution Time</div>
                                                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiTotalTime">0.00 ms</div>
                                                                                <small class="text-muted" id="kpiDataQueryTime">Data Query: 0.00 ms</small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-6 mb-2">
                                                                        <div class="card border-left-primary shadow-sm h-100 py-2">
                                                                            <div class="card-body py-1">
                                                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Peak Memory Usage</div>
                                                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiMemory">0.00 MB</div>
                                                                                <small class="text-muted">PHP Engine Peak RAM</small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-6 mb-2">
                                                                        <div class="card border-left-info shadow-sm h-100 py-2">
                                                                            <div class="card-body py-1">
                                                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Queries Executed</div>
                                                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiQueriesCount">3 queries</div>
                                                                                <small class="text-muted">Total + Filter + Data</small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3 col-sm-6 mb-2">
                                                                        <div class="card border-left-warning shadow-sm h-100 py-2">
                                                                            <div class="card-body py-1">
                                                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Records Processed</div>
                                                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="kpiRecordsInfo">0 / 0</div>
                                                                                <small class="text-muted" id="kpiReturnedRows">Returned: 0 rows</small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <h6 class="font-weight-bold text-gray-800 mb-2"><i class="fas fa-list-ol mr-1"></i> รายการ Query ที่ทำงานในคำขอนี้ (Query Breakdown):</h6>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-bordered table-striped" id="queriesBreakdownTable">
                                                                        <thead class="thead-light">
                                                                            <tr>
                                                                                <th style="width: 15%;">ประเภท Query</th>
                                                                                <th style="width: 12%;">เวลาที่ใช้</th>
                                                                                <th style="width: 10%;">จำนวนแถว</th>
                                                                                <th style="width: 55%;">คำสั่ง SQL ที่ประมวลผล</th>
                                                                                <th style="width: 8%;">Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="queriesBreakdownTbody">
                                                                            <tr><td colspan="5" class="text-center text-muted">กำลังโหลดข้อมูล...</td></tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>

                                                            <!-- Tab 2: EXPLAIN ANALYZE Execution Tree -->
                                                            <div class="tab-pane fade" id="tab-analyze" role="tabpanel">
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span class="text-muted small">
                                                                        <i class="fas fa-info-circle text-info"></i> ผลลัพธ์จริงจากคำสั่ง <code>EXPLAIN ANALYZE</code> (Actual Time, Row Counts, Cost & Loops per Step)
                                                                    </span>
                                                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCopyExplainAnalyze">
                                                                        <i class="fas fa-copy"></i> คัดลอก Tree
                                                                    </button>
                                                                </div>
                                                                <div style="background-color: #1a202c; color: #68d391; border-radius: 6px; padding: 15px; font-family: Consolas, 'Courier New', monospace; font-size: 13px; line-height: 1.5; max-height: 480px; overflow-y: auto;">
                                                                    <pre id="explainAnalyzeContent" style="color: #68d391; margin: 0; white-space: pre-wrap; font-family: inherit;">ไม่มีข้อมูล EXPLAIN ANALYZE</pre>
                                                                </div>
                                                            </div>

                                                            <!-- Tab 3: EXPLAIN Plan Table -->
                                                            <div class="tab-pane fade" id="tab-explain" role="tabpanel">
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span class="text-muted small">
                                                                        <i class="fas fa-info-circle text-info"></i> การเข้าถึงข้อมูลของตาราง (Table Access Methods & Key Selection)
                                                                    </span>
                                                                </div>
                                                                <div class="table-responsive">
                                                                    <table class="table table-sm table-bordered table-hover" id="explainPlanTable">
                                                                        <thead class="thead-dark">
                                                                            <tr>
                                                                                <th>id</th>
                                                                                <th>select_type</th>
                                                                                <th>table</th>
                                                                                <th>type</th>
                                                                                <th>possible_keys</th>
                                                                                <th>key (Index Used)</th>
                                                                                <th>key_len</th>
                                                                                <th>ref</th>
                                                                                <th>rows</th>
                                                                                <th>filtered %</th>
                                                                                <th>Extra</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="explainPlanTbody">
                                                                            <tr><td colspan="11" class="text-center text-muted">กำลังโหลดข้อมูล...</td></tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>

                                                            <!-- Tab 4: Live Query Tester -->
                                                            <div class="tab-pane fade" id="tab-tester" role="tabpanel">
                                                                <div class="card bg-light mb-3">
                                                                    <div class="card-body py-2">
                                                                        <form id="formQueryTester" class="form-inline d-flex flex-wrap align-items-center">
                                                                            <label class="mr-2 font-weight-bold">บ้านเลขที่ (Exact):</label>
                                                                            <input type="text" id="testerHouseNumber" class="form-control form-control-sm mr-3 mb-2" placeholder="เช่น 99/1" style="width: 150px;">

                                                                            <label class="mr-2 font-weight-bold">คำค้นหาทั่วไป:</label>
                                                                            <input type="text" id="testerSearchValue" class="form-control form-control-sm mr-3 mb-2" placeholder="ค้นหาชื่อ/ซอย/ข้อความ" style="width: 180px;">

                                                                            <label class="mr-2 font-weight-bold">จำนวนแถว (Limit):</label>
                                                                            <select id="testerLength" class="form-control form-control-sm mr-3 mb-2" style="width: 100px;">
                                                                                <option value="10" selected>10 แถว</option>
                                                                                <option value="20">20 แถว</option>
                                                                                <option value="50">50 แถว</option>
                                                                                <option value="100">100 แถว</option>
                                                                            </select>

                                                                            <button type="button" id="btnRunTester" class="btn btn-primary btn-sm mb-2">
                                                                                <i class="fas fa-play mr-1"></i> Run EXPLAIN ANALYZE
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                                <div id="testerResultContainer">
                                                                    <div class="alert alert-info">
                                                                        <i class="fas fa-info-circle mr-1"></i> กรอกเงื่อนไขด้านบนแล้วคลิก <strong>"Run EXPLAIN ANALYZE"</strong> เพื่อทดสอบประสิทธิภาพและการทำงานของ Index ในแบบ Real-time
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Tab 5: Insights & Recommendations -->
                                                            <div class="tab-pane fade" id="tab-insights" role="tabpanel">
                                                                <div id="insightsContainer">
                                                                    <div class="alert alert-success">
                                                                        <h6 class="font-weight-bold mb-1"><i class="fas fa-check-circle mr-1"></i> ⚡ ประสิทธิภาพยอดเยี่ยม (Optimal Query Performance)</h6>
                                                                        <p class="mb-0">Query ได้รับการปรับแต่งให้ใช้ Index Scan บน PRIMARY KEY และ idx_house_number หลีกเลี่ยง Cartesian join ทำให้ความเร็วการตอบสนองอยู่ในระดับมิลลิวินาที</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer py-2 justify-content-between">
                                                        <span class="text-muted small" id="profilerTimestamp"><i class="far fa-clock mr-1"></i> Last updated: -</span>
                                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                                                            <i class="fas fa-times mr-1"></i> ปิด
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
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

    <?php
    include('includes/Modal-Logout.php');
    ?>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="js/util/calculate_datetime.js"></script>
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js"></script>

    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });

            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "ทั้งหมด"]],
                'fixedHeader': true,
                'language': {
                    search: 'ค้นหาข้อมูล',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    },
                    processing: '<div class="custom-spinner"></div>',
                    loadingRecords: '<div class="text-center p-3"><span class="spinner-border spinner-border-sm text-primary" role="status"></span> กำลังโหลด...</div>'
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'scrollX': true,
                'ajax': {
                    'url': 'model/manage_common_fee_payment_process.php',
                    'type': 'POST',
                    'data': function (d) {
                        d.action = 'GET_COMMON_FEE';
                        d.sub_action = 'GET_MASTER';
                        d.page_manage = 'ADMIN';
                        d.searchHouseNumber = $('#search_house_number').val();
                        return d;
                    }
                },
                'columns': [
                    {data: 'payment_date', width: '200px'},
                    {data: 'house_number', width: '100px'},
                    {data: 'alley', width: '100px'},
                    {data: 'detail', width: '200px'},
                    {data: 'line_picture_profile', width: '200px'},
                    {data: 'month_name_period', width: '120px'},
                    {data: 'period_year', width: '100px'},
                    {data: 'common_fee', className: 'dt-body-right', width: '120px'},
                    {data: 'payment_type', className: 'dt-body-center', width: '100px'},
                    {data: 'amount', className: 'dt-body-right', width: '120px'},
                    {data: 'slip', width: '80px'},
                    {data: 'payment_status_desc', width: '100px'},
                    {data: 'update', width: '80px'},
                    {data: 'print', width: '80px'},
                    {data: 'area_size', className: 'dt-body-right', width: '100px'},
                    {data: 'garbage_collection_fee', className: 'dt-body-right', width: '120px'},
                    {data: 'delete', width: '80px'},
                ],
                'autoWidth': false,
                'preXhr': function (xhr, data) {
                },
                'xhr': function (data) {
                    if (data && data.profiler) {
                        window.lastProfilerData = data.profiler;
                        updateProfilerBadge(data.profiler);
                    }
                },
                'initComplete': function (settings, json) {
                    if (json && json.profiler) {
                        window.lastProfilerData = json.profiler;
                        updateProfilerBadge(json.profiler);
                    }
                }
            });

            // Auto-refresh every 5 seconds (no page flicker)
            //setInterval(function() {
                //dataRecords.ajax.reload(null, false);
            //}, 5000);

            $('#saveButton').on('click', function (event) {
                event.preventDefault();

                // ----------------------------------------------------------------
                // START : ส่วนที่เพิ่มตรวจสอบ Period Month Start > Period Month To
                // ----------------------------------------------------------------
                let startMonth = parseInt($('#period_month_start').val());
                let toMonth = parseInt($('#period_month_to').val());

                // ตรวจสอบว่ามีการเลือกทั้งคู่และเป็นตัวเลข
                if (!isNaN(startMonth) && !isNaN(toMonth)) {
                    if (startMonth > toMonth) {
                        // แจ้งเตือนผู้ใช้
                        if (typeof alertify !== 'undefined') {
                            alertify.error("งวดเดือนเริ่มต้น ต้องไม่มากกว่า งวดเดือนสิ้นสุด");
                        } else {
                            alert("งวดเดือนเริ่มต้น ต้องไม่มากกว่า งวดเดือนสิ้นสุด");
                        }
                        // โฟกัสไปที่ช่องที่ผิดเพื่อให้แก้ไข และหยุดการทำงาน
                        $('#period_month_to').focus();
                        return;
                    }
                }
                // ----------------------------------------------------------------
                // END : ส่วนที่เพิ่มตรวจสอบ
                // ----------------------------------------------------------------

                let recordForm = $('#recordForm');
                let formData = recordForm.serialize();
                $(this).attr('disabled', true);
                $.ajax({
                    url: 'model/manage_common_fee_payment_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        recordForm[0].reset();
                        $('#recordModal').modal('hide');
                        $('#saveButton').attr('disabled', false);
                        $('#TableRecordList').DataTable().ajax.reload();
                    },
                    error: function (xhr, status, error) {
                        alertify.error("Error: " + error);
                        $('#saveButton').attr('disabled', false);
                    }
                });
            });

            // Reload Data button click
            $('#btnReload').on('click', function() {
                $('#TableRecordList').DataTable().ajax.reload();
            });

            // House Number Search button click
            $('#btnSearch').on('click', function() {
                $('#TableRecordList').DataTable().ajax.reload();
            });

            // House Number Search on Enter key
            $('#search_house_number').on('keyup', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    $('#TableRecordList').DataTable().ajax.reload();
                }
            });

            $("#TableRecordList").on('click', '.print', function () {
                let id = $(this).attr("id");
                let url = "";
                let user_type = $('#user_type').val();
                if (user_type === 'user') {
                    url = "print_pdf_smart.php?id=" + encodeURIComponent(id);
                } else {
                    url = "print_pdf.php?id=" + encodeURIComponent(id);
                }
                window.open(url, "_blank");
            });

            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            $("#TableRecordList").on('click', '.slip', function () {
                let id = $(this).attr("id");
                $.ajax({
                    url: "display_slip.php",
                    type: "GET",
                    data: {id: id},
                    dataType: "json",
                    success: function (response) {
                        if (response.status === 1) {
                            $("#slipImage").attr("src", response.image_url);
                            $("#slipModal").modal('show');
                        } else {
                            alert("ไม่พบรูปภาพ");
                        }
                    },
                    error: function () {
                        alert("เกิดข้อผิดพลาดในการโหลดรูปภาพ");
                    }
                });
            });

            // **เพิ่มฟังก์ชันสำหรับปุ่มพิมพ์สลิป**
            $("#printSlipButton").on('click', function () {
                let imageUrl = $("#slipImage").attr("src");

                // ตรวจสอบว่ามี URL รูปภาพหรือไม่
                if (!imageUrl || imageUrl === '#') {
                    alert('ไม่พบรูปภาพสลิปที่จะพิมพ์');
                    return;
                }

                // สร้างหน้าต่างใหม่เพื่อแสดงรูปภาพและสั่งพิมพ์
                let printWindow = window.open('', '_blank');
                printWindow.document.write('<html><head><title>พิมพ์สลิป</title>');
                // CSS สำหรับรูปภาพเพื่อให้แสดงผลดีเมื่อพิมพ์
                // printWindow.document.write('<style>body{margin: 0; padding: 0;} img{width: 100%; height: auto; display: block;}</style>');
                printWindow.document.write('<style>body{margin: 0; padding: 0;} img{max-width: 100mm; height: auto; display: block; margin: 20px auto;}</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write('<img src="' + imageUrl + '" alt="Slip Image for Print">');
                printWindow.document.write('</body></html>');

                printWindow.document.close();

                // สั่งพิมพ์เมื่อหน้าต่างโหลดเสร็จ
                printWindow.onload = function () {
                    printWindow.print();
                    // printWindow.close(); // เลือกว่าจะปิดหน้าต่างทันทีหรือไม่
                };
            });

            let deleteId = null;
            $("#TableRecordList").on('click', '.delete', function () {
                deleteId = $(this).attr("id");
                $("#confirmDeleteModal").modal("show");
            });

            $("#confirmDeleteBtn").on("click", function () {
                if (deleteId) {
                    $.ajax({
                        url: "model/manage_common_fee_payment_process.php",
                        method: "POST",
                        data: {id: deleteId, action: "DELETE"},
                        success: function (response) {
                            $("#confirmDeleteModal").modal("hide");
                            $('#TableRecordList').DataTable().ajax.reload();
                            alertify.success("ลบข้อมูลเรียบร้อยแล้ว");
                        },
                        error: function () {
                            alertify.error("เกิดข้อผิดพลาดในการลบข้อมูล");
                        }
                    });
                }
            });

            $("#TableRecordList").on('click', '.update', function () {
                let id = $(this).attr("id");
                let formData = {action: "GET_DATA", id: id};

                $.ajax({
                    type: "POST",
                    url: 'model/manage_common_fee_payment_process.php',
                    dataType: "json",
                    data: formData,
                    success: function (response) {
                        if (response && response.length > 0) {
                            let data = response[0];

                            let id = data.id;
                            let doc_id = data.doc_id;
                            let detail = data.detail;
                            let payment_date = data.payment_date;
                            let house_number = data.house_number;
                            let period_month_start = data.period_month_start;
                            let period_month_to = data.period_month_to;
                            let period_year = data.period_year;
                            let amount = data.amount;
                            let remark = data.remark;
                            let picture_payment = data.picture_payment;
                            let payment_status = data.payment_status;
                            let payment_method = data.payment_method;
                            let payment_status_desc = (payment_status === "Y") ? "ชำระเรียบร้อยแล้ว" : "ยังไม่ยืนยันการชำระ";

                            if (payment_status === "Y") {
                                $('input[name="payment_status"][value="Y"]').prop('checked', true);
                                $('#saveButton').attr('disabled', false);
                            } else {
                                $('input[name="payment_status"][value="N"]').prop('checked', true);
                                $('#saveButton').attr('disabled', false);
                            }

                            if (picture_payment && picture_payment !== "") {
                                $("#preview_image").attr("src", "uploads/slips/" + picture_payment);
                                $("#preview_image").show();
                            } else {
                                $("#preview_image").attr("src", "#");
                                $("#preview_image").hide();
                            }

                            $("#recordModal .modal-title").text("แก้ไขข้อมูลการชำระค่าส่วนกลาง");
                            $("#id").val(id);
                            $("#doc_id").val(doc_id);
                            $("#payment_date").val(payment_date);
                            $("#house_number").val(house_number);
                            $("#detail").val(detail);
                            $("#period_month_start").val(period_month_start);
                            $("#period_month_to").val(period_month_to);
                            $("#period_year").val(period_year);
                            $("#amount").val(amount);
                            $("#payment_method").val(payment_method);
                            $("#payment_status_desc").val(payment_status_desc);
                            $("#remark").val(remark);
                            $("#create_by").val(data.create_by);
                            $("#created_at").val(data.created_at);
                            $("#approve_by").val(data.approve_by);
                            $("#updated_at").val(data.updated_at);
                            $("#action").val("UPDATE");

                            $("#recordModal").modal("show");
                        }
                    },
                    error: function () {
                        alertify.error("เกิดข้อผิดพลาดในการดึงข้อมูล");
                    }
                });
            });
        });

        // ฟังก์ชันเปิดรูปในหน้าต่างใหม่
        function openImageInNewWindow(imageSrc) {
            if (imageSrc && imageSrc !== "#") {
                window.open(imageSrc, '_blank');
            } else {
                alert('ไม่มีรูปภาพที่จะแสดง');
            }
        }

        // ============================================
        // QR Code Scanner for Bank Slip Verify (from uploaded image)
        // ============================================
        let html5QrCode = null;

        $("#scanQRBtn").on("click", function() {
            // Get the image src from preview or slip
            let imageSrc = $("#preview_image").attr("src");
            if (!imageSrc || imageSrc === "#") {
                // Try slip image in modal
                imageSrc = $("#slipImage").attr("src");
            }
            
            if (!imageSrc || imageSrc === "#" || imageSrc === "") {
                alert("ไม่พบรูปภาพสำหรับ scan QR Code");
                return;
            }

            $("#qrVerifyResult").show();
            $("#qrResultAlert").attr("class", "alert alert-info");
            $("#qrResultAlert").html("กำลังอ่าน QR Code...");

            scanQRFromImage(imageSrc);
        });

        function scanQRFromImage(imageSrc) {
            $("#qrResultAlert").attr("class", "alert alert-info");
            $("#qrResultAlert").html("กำลังอ่าน QR Code...");

            let img = new Image();
            img.crossOrigin = "Anonymous";
            img.onload = function() {
                let canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;
                let ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0);
                
                let imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                let code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert"
                });
                
                if (code) {
                    verifyQRCode(code.data);
                } else {
                    // Try with inversion
                    let codeInvert = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "invertFirst"
                    });
                    if (codeInvert) {
                        verifyQRCode(codeInvert.data);
                    } else {
                        $("#qrResultAlert").attr("class", "alert alert-danger");
                        $("#qrResultAlert").html("<strong>ไม่พบ QR Code</strong><br>ไม่สามารถอ่าน QR Code จากรูปภาพ กรุณาตรวจสอบว่ารูปภาพมี QR Code ที่ชัดเจน");
                    }
                }
            };
            img.onerror = function() {
                $("#qrResultAlert").attr("class", "alert alert-danger");
                $("#qrResultAlert").html("<strong>Error</strong>: ไม่สามารถโหลดรูปภาพได้");
            };
            img.src = imageSrc;
        }

        function verifyQRCode(qrString) {
            $("#qrVerifyResult").show();
            
            try {
                let qrData = parseThaiQR(qrString);
                let inputAmount = parseFloat($("#amount").val().replace(/,/g, '')) || 0;
                
                let alertClass = "alert-danger";
                let alertMessage = "<strong>QR Code ไม่ถูกต้อง</strong>";
                let isValid = false;

                // ตรวจสอบ Amount
                if (qrData.amount === inputAmount) {
                    isValid = true;
                    alertClass = "alert-success";
                    alertMessage = "<strong>✓ จำนวนเงินตรงกัน</strong><br>" +
                                "จำนวนเงิน: " + qrData.amount.toLocaleString() + " บาท<br>" +
                                "ธนาคาร: " + qrData.senderBank + "<br>" +
                                "บัญชีผู้รับ: " + qrData.receiverAccount;
                } else {
                    alertClass = "alert-warning";
                    alertMessage = "<strong>⚠ จำนวนเงินไม่ตรงกัน</strong><br>" +
                                "จาก QR: " + qrData.amount.toLocaleString() + " บาท<br>" +
                                "จากฟอร์ม: " + inputAmount.toLocaleString() + " บาท";
                }

                $("#qrResultAlert").attr("class", "alert " + alertClass);
                $("#qrResultAlert").html(alertMessage);

            } catch (e) {
                $("#qrResultAlert").attr("class", "alert alert-danger");
                $("#qrResultAlert").html("<strong>Error:</strong> ไม่สามารถอ่าน QR Code ได้: " + e.message);
            }
        }

        function parseThaiQR(qrString) {
            // Thai QR Payment standard (PromptPay)
            // Format: 000201010211...
            
            let result = {
                amount: 0,
                receiverAccount: "",
                senderBank: "",
                timestamp: ""
            };

            if (!qrString || qrString.length < 50) {
                throw new Error("Invalid QR Code format");
            }

            // Parse using character-based extraction
            // EMVCo Thai QR Code standard
            
            let pos = 0;
            let data = qrString;
            
            // Find Amount (54)
            let amountMatch = data.match(/54[0-9]{2}(\d{2})/);
            if (amountMatch) {
                let amountLen = parseInt(amountMatch[1]);
                let amountStart = data.indexOf("54") + 4;
                let amountEnd = amountStart + amountLen;
                if (amountEnd <= data.length) {
                    result.amount = parseFloat(data.substring(amountStart, amountEnd)) || 0;
                }
            }

            // Find Receiver Account (38)
            let accMatch = data.match(/38[0-9]{2}(\d{2})/);
            if (accMatch) {
                let accLen = parseInt(accMatch[1]);
                let accStart = data.indexOf("38") + 4;
                let accEnd = accStart + accLen;
                if (accEnd <= data.length) {
                    result.receiverAccount = data.substring(accStart, accEnd);
                }
            }

            // Find Sender Bank (30)
            let bankMatch = data.match(/30[0-9]{2}(\d{2})/);
            if (bankMatch) {
                let bankLen = parseInt(bankMatch[1]);
                let bankStart = data.indexOf("30") + 4;
                let bankEnd = bankStart + bankLen;
                if (bankEnd <= data.length) {
                    result.senderBank = data.substring(bankStart, bankEnd);
                }
            }

            if (result.amount === 0) {
                // Try alternative parsing - look for specific value after ID
                let parts = data.split("5802TH");
                if (parts.length > 1) {
                    // Try to extract amount from hex or numeric
                    let amountPattern = /54(\d{2})(\d+)/;
                    let match = data.match(amountPattern);
                    if (match) {
                        result.amount = parseFloat(match[2]) / 100;
                    }
                }
            }

            return result;
        }

        // =======================================================
        // Query Performance Profiler & EXPLAIN ANALYZE Logic
        // =======================================================
        window.lastProfilerData = null;

        function updateProfilerBadge(profiler) {
            if (!profiler) return;
            let totalTime = parseFloat(profiler.total_time_ms) || 0;
            let badge = $('#perfBadge');
            let timeText = $('#perfTimeText');

            timeText.text(totalTime.toFixed(1) + ' ms');

            badge.removeClass('badge-success badge-warning badge-danger');
            if (totalTime < 50) {
                badge.addClass('badge-success');
            } else if (totalTime < 200) {
                badge.addClass('badge-warning');
            } else {
                badge.addClass('badge-danger');
            }
            badge.fadeIn();
        }

        function renderProfilerModal(data) {
            if (!data) return;

            if (data.mysql_version) {
                $('#modalMysqlVersionBadge').text('MySQL ' + data.mysql_version);
            }

            let totalTime = parseFloat(data.total_time_ms) || 0;
            let dataTime = parseFloat(data.data_query_time_ms) || 0;
            $('#kpiTotalTime').text(totalTime.toFixed(2) + ' ms');
            $('#kpiDataQueryTime').text('Data Query: ' + dataTime.toFixed(2) + ' ms');
            $('#kpiMemory').text((data.memory_peak_mb || 0) + ' MB');
            $('#kpiQueriesCount').text((data.total_queries || 0) + ' queries');
            
            let filteredRec = data.filtered_records !== undefined ? data.filtered_records : (data.filtered_count !== undefined ? data.filtered_count : 0);
            let totalRec = data.total_records !== undefined ? data.total_records : 0;
            let retRows = data.returned_rows !== undefined ? data.returned_rows : (data.row_count !== undefined ? data.row_count : 0);
            
            $('#kpiRecordsInfo').text(filteredRec + ' / ' + totalRec);
            $('#kpiReturnedRows').text('Returned: ' + retRows + ' rows');

            // 1. Queries Breakdown
            let queriesHtml = '';
            if (data.queries && data.queries.length > 0) {
                data.queries.forEach(function(q, idx) {
                    let timeMs = parseFloat(q.time_ms) || 0;
                    let timeColor = timeMs < 10 ? 'text-success font-weight-bold' : (timeMs < 50 ? 'text-warning font-weight-bold' : 'text-danger font-weight-bold');
                    queriesHtml += '<tr>';
                    queriesHtml += '<td><span class="badge badge-secondary py-1 px-2">' + (q.type || 'Query #' + (idx+1)) + '</span></td>';
                    queriesHtml += '<td class="' + timeColor + '">' + timeMs.toFixed(2) + ' ms</td>';
                    queriesHtml += '<td>' + (q.rows !== undefined ? q.rows : '-') + '</td>';
                    queriesHtml += '<td><code style="font-size: 11px; white-space: pre-wrap; word-break: break-all; color: #2b6cb0;">' + escapeHtml(q.sql) + '</code></td>';
                    queriesHtml += '<td class="text-center"><button type="button" class="btn btn-outline-primary btn-xs btnCopySql" data-sql="' + encodeURIComponent(q.sql) + '" title="คัดลอก SQL"><i class="fas fa-copy"></i></button></td>';
                    queriesHtml += '</tr>';
                });
            } else {
                queriesHtml = '<tr><td colspan="5" class="text-center text-muted">ไม่มีข้อมูล Query</td></tr>';
            }
            $('#queriesBreakdownTbody').html(queriesHtml);

            // 2. EXPLAIN ANALYZE Content
            if (data.explain_analyze) {
                $('#explainAnalyzeContent').text(data.explain_analyze);
            } else {
                $('#explainAnalyzeContent').text('ไม่มีข้อมูล EXPLAIN ANALYZE สำหรับคำขอนี้');
            }

            // 3. EXPLAIN Plan Table
            let planHtml = '';
            if (data.explain_table && data.explain_table.length > 0) {
                data.explain_table.forEach(function(row) {
                    let typeBadge = 'badge-secondary';
                    let t = (row.type || '').toLowerCase();
                    if (t === 'const' || t === 'eq_ref' || t === 'ref') {
                        typeBadge = 'badge-success';
                    } else if (t === 'range' || t === 'index') {
                        typeBadge = 'badge-info';
                    } else if (t === 'all') {
                        typeBadge = 'badge-danger';
                    }

                    planHtml += '<tr>';
                    planHtml += '<td>' + (row.id || '-') + '</td>';
                    planHtml += '<td>' + (row.select_type || '-') + '</td>';
                    planHtml += '<td><strong>' + (row.table || '-') + '</strong></td>';
                    planHtml += '<td><span class="badge ' + typeBadge + '">' + (row.type || '-') + '</span></td>';
                    planHtml += '<td><small>' + (row.possible_keys || '-') + '</small></td>';
                    planHtml += '<td><strong class="text-primary">' + (row.key || '-') + '</strong></td>';
                    planHtml += '<td>' + (row.key_len || '-') + '</td>';
                    planHtml += '<td><small>' + (row.ref || '-') + '</small></td>';
                    planHtml += '<td>' + (row.rows || '-') + '</td>';
                    planHtml += '<td>' + (row.filtered !== undefined ? row.filtered + '%' : '-') + '</td>';
                    planHtml += '<td><small>' + (row.Extra || '-') + '</small></td>';
                    planHtml += '</tr>';
                });
            } else {
                planHtml = '<tr><td colspan="11" class="text-center text-muted">ไม่มีข้อมูล Execution Plan</td></tr>';
            }
            $('#explainPlanTbody').html(planHtml);

            // 4. Insights & Recommendations
            let insightsHtml = '';
            if (data.recommendations && data.recommendations.length > 0) {
                data.recommendations.forEach(function(rec) {
                    let alertClass = 'alert-info';
                    let icon = 'fa-info-circle';
                    if (rec.level === 'success') {
                        alertClass = 'alert-success';
                        icon = 'fa-check-circle';
                    } else if (rec.level === 'warning') {
                        alertClass = 'alert-warning';
                        icon = 'fa-exclamation-triangle';
                    } else if (rec.level === 'danger') {
                        alertClass = 'alert-danger';
                        icon = 'fa-times-circle';
                    }

                    insightsHtml += '<div class="alert ' + alertClass + ' mb-2">';
                    insightsHtml += '<h6 class="font-weight-bold mb-1"><i class="fas ' + icon + ' mr-1"></i> ' + escapeHtml(rec.title) + '</h6>';
                    insightsHtml += '<p class="mb-0 small">' + escapeHtml(rec.detail) + '</p>';
                    insightsHtml += '</div>';
                });
            } else {
                insightsHtml = '<div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> ไม่พบปัญหาคอขวดด้านประสิทธิภาพของ Query</div>';
            }
            $('#insightsContainer').html(insightsHtml);

            let d = new Date();
            $('#profilerTimestamp').html('<i class="far fa-clock mr-1"></i> Last updated: ' + d.toLocaleTimeString());
        }

        function runLiveExplainAnalyze(params) {
            let houseNumber = params && params.searchHouseNumber !== undefined ? params.searchHouseNumber : ($('#search_house_number').val() || '');
            let searchVal = params && params.searchValue !== undefined ? params.searchValue : ($('#TableRecordList_filter input').val() || '');
            let length = params && params.length ? params.length : 10;

            $('#testerResultContainer').html('<div class="text-center p-3"><span class="spinner-border spinner-border-sm text-primary" role="status"></span> กำลังประมวลผล EXPLAIN ANALYZE...</div>');

            $.ajax({
                url: 'model/manage_common_fee_payment_process.php',
                type: 'POST',
                data: {
                    action: 'EXPLAIN_ANALYZE_PROFILE',
                    searchHouseNumber: houseNumber,
                    searchValue: searchVal,
                    start: 0,
                    length: length
                },
                dataType: 'json',
                success: function(response) {
                    renderProfilerModal(response);
                    $('#testerResultContainer').html('<div class="alert alert-success py-2"><i class="fas fa-check-circle mr-1"></i> วิเคราะห์ผลสำเร็จในเวลา <strong>' + response.total_time_ms + ' ms</strong> (Data Query: ' + response.data_query_time_ms + ' ms) ดูรายละเอียดได้ในแท็บต่างๆ</div>');
                },
                error: function(xhr, status, error) {
                    $('#testerResultContainer').html('<div class="alert alert-danger py-2"><i class="fas fa-exclamation-triangle mr-1"></i> เกิดข้อผิดพลาดในการรัน EXPLAIN ANALYZE: ' + error + '</div>');
                }
            });
        }

        function copyTextToClipboard(text, successMsg) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    if (typeof alertify !== 'undefined') {
                        alertify.success(successMsg || "คัดลอกเรียบร้อยแล้ว");
                    } else {
                        alert(successMsg || "คัดลอกเรียบร้อยแล้ว");
                    }
                }).catch(function() {
                    fallbackCopyText(text, successMsg);
                });
            } else {
                fallbackCopyText(text, successMsg);
            }
        }

        function fallbackCopyText(text, successMsg) {
            let textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.top = "0";
            textArea.style.left = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                if (typeof alertify !== 'undefined') {
                    alertify.success(successMsg || "คัดลอกเรียบร้อยแล้ว");
                } else {
                    alert(successMsg || "คัดลอกเรียบร้อยแล้ว");
                }
            } catch (err) {
                alert("ไม่สามารถคัดลอกได้");
            }
            document.body.removeChild(textArea);
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Open Query Profiler Modal from button or badge
        $('#btnQueryProfiler, #perfBadge').on('click', function() {
            let currentHouseNumber = $('#search_house_number').val() || '';
            let currentSearch = $('#TableRecordList_filter input').val() || '';
            $('#testerHouseNumber').val(currentHouseNumber);
            $('#testerSearchValue').val(currentSearch);

            $('#queryProfilerModal').modal('show');

            if (window.lastProfilerData) {
                renderProfilerModal(window.lastProfilerData);
            }
            runLiveExplainAnalyze({
                searchHouseNumber: currentHouseNumber,
                searchValue: currentSearch,
                length: 10
            });
        });

        // Run live tester button
        $('#btnRunTester').on('click', function() {
            let hNumber = $('#testerHouseNumber').val();
            let sValue = $('#testerSearchValue').val();
            let len = $('#testerLength').val();
            runLiveExplainAnalyze({
                searchHouseNumber: hNumber,
                searchValue: sValue,
                length: len
            });
        });

        // Copy SQL handler
        $('#queriesBreakdownTable').on('click', '.btnCopySql', function() {
            let sql = decodeURIComponent($(this).data('sql') || '');
            copyTextToClipboard(sql, "คัดลอก SQL แล้ว");
        });

        // Copy EXPLAIN ANALYZE Tree
        $('#btnCopyExplainAnalyze').on('click', function() {
            let content = $('#explainAnalyzeContent').text();
            copyTextToClipboard(content, "คัดลอก EXPLAIN ANALYZE Tree แล้ว");
        });
    </script>
    </body>
    </html>

<?php } ?>