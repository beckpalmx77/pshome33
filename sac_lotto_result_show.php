<?php include('includes/Header.php'); ?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจรางวัล</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card shadow-sm">
                            <div class="card-header" style="background-color: #28a745; color: white;">
                                ตรวจรางวัล
                            </div>
                            <div class="card-body">
                                <!-- ฟอร์มตรวจรางวัล -->
                                <form method="post" action="">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="period_no">งวดวันที่</label>
                                            <select class="form-control" id="period_no" name="period_no" required>
                                                <option value="">เลือกวันที่</option>
                                                <option value="1">1</option>
                                                <option value="16">16</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-md-4">
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

                                        <div class="form-group col-md-4">
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
                                    </div>

                                    <div class="d-flex">
                                        <button type="button" id="LottoResultCheckBtn"
                                                class="btn btn-outline-primary flex-fill">
                                            <span><i class="fa fa-check-square-o"
                                                     aria-hidden="true"></i> ตรวจรางวัล Lotto</span>
                                        </button>
                                        <button type="button" id="exportExcelBtn"
                                                class="btn btn-outline-success flex-fill">
                                            <span><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export Excel</span>
                                        </button>
                                        <button type="button" id="exportPdfBtn"
                                                class="btn btn-outline-info flex-fill"
                                                style="--bs-btn-hover-color: white; --bs-btn-hover-bg: #17a2b8;">
                                            <span><i class="fa fa-print" aria-hidden="true"></i> Print</span>
                                        </button>
                                        <button type="button" id="backBtn" class="btn btn-outline-danger flex-fill">
                                            <span><i class="fa fa-reply" aria-hidden="true"></i> กลับหน้าแรก</span>
                                        </button>
                                    </div>


                                </form>

                                <div id="result"></div> <!-- แสดงผลการค้นหา -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .card-header {
        text-align: center;
        text-transform: uppercase;
        font-weight: bold;
    }
</style>

<style>
    #winnersTable {
        width: 100%;
        border-collapse: collapse;
    }

    #winnersTable th, #winnersTable td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }

    #winnersTable th {
        background-color: #f2f2f2;
    }
</style>

<style>
    .card-header {
        background-color: #28a745; /* สีเขียว */
        color: white; /* ข้อความเป็นสีขาว */
    }
</style>

<script>
    $("#LottoResultCheckBtn").click(function () {
        // ดึงค่าที่เลือกจากฟอร์ม
        let period_no = $("#period_no").val();
        let period_month = $("#period_month").val();
        let period_year = $("#period_year").val();

        // ตรวจสอบว่าค่าครบถ้วนไหม
        if (period_no == "" || period_month == "" || period_year == "") {
            alert("กรุณากรอกข้อมูลให้ครบ");
            return;
        }

        // ส่งข้อมูลไปยังไฟล์ PHP ด้วย AJAX
        $.ajax({
            url: "search_lotto_results.php", // ไฟล์ PHP ที่จะทำการค้นหาผล
            method: "POST",
            data: {
                period_no: period_no,
                period_month: period_month,
                period_year: period_year
            },
            beforeSend: function () {
                $('#result').html("กำลังค้นหาผล...");
            },
            success: function (response) {
                // แสดงผลลัพธ์ที่ได้รับจาก PHP
                $('#result').html(response);
            },
            error: function () {
                alert("เกิดข้อผิดพลาดในการค้นหา");
            }
        });
    });
</script>

<script>
    $("#exportExcelBtn").click(function () {
        let period_no = $("#period_no").val();
        let period_month = $("#period_month").val();
        let period_year = $("#period_year").val();

        if (!period_no || !period_month || !period_year) {
            alert("กรุณากรอกข้อมูลให้ครบ");
            return;
        }

        // เรียกไฟล์ PHP เพื่อดาวน์โหลด
        const url = `export_process/export_result_lotto.php?period_no=${period_no}&period_month=${period_month}&period_year=${period_year}`;
        window.location.href = url;
    });
</script>

<script>

    $("#exportPdfBtn").click(function () {
        let period_no = $("#period_no").val();
        let period_month = $("#period_month").val();
        let period_year = $("#period_year").val();

        if (!period_no || !period_month || !period_year) {
            alert("กรุณากรอกข้อมูลให้ครบ");
            return;
        }

        // ใช้ Form ชั่วคราวเพื่อส่ง POST
        const form = $('<form action="export_process/lotto_result_pdf.php" method="POST"></form>');
        form.append(`<input type="hidden" name="period_no" value="${period_no}">`);
        form.append(`<input type="hidden" name="period_month" value="${period_month}">`);
        form.append(`<input type="hidden" name="period_year" value="${period_year}">`);
        $('body').append(form);
        form.submit();
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
