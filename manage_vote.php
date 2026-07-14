<?php
include('includes/Header.php');
if (empty($_SESSION['alogin'])) {
    header("Location: index.php");
    exit;
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
                <!-- Page Title -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">ตั้งค่าหัวข้อโหวตประชามติ</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item active">ตั้งค่าหัวข้อโหวตประชามติ</li>
                    </ol>
                </div>

                <!-- Main Section -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">รายการหัวข้อประชามติทั้งหมดในฐานข้อมูล</h6>
                                <div>
                                    <button type="button" id="btnAdd" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus"></i> เพิ่มหัวข้อใหม่
                                    </button>
                                    <button type="button" id="btnReload" class="btn btn-outline-success btn-sm">
                                        <i class="fa fa-refresh"></i> รีโหลดข้อมูล
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="TableVoteList">
                                        <thead>
                                            <tr>
                                                <th style="width: 8%;">ID</th>
                                                <th>หัวข้อโหวต/ประชามติ</th>
                                                <th style="width: 15%;">สถานะ</th>
                                                <th style="width: 15%;">จำนวนผู้โหวต</th>
                                                <th style="width: 25%;">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data populated dynamically via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('includes/Footer.php'); ?>
    </div>
</div>

<?php include('includes/Modal-Logout.php'); ?>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Modal 1: Add New Topic -->
<div class="modal fade" id="recordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-plus"></i> เพิ่มหัวข้อการโหวตใหม่</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="voteTopicForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="topicTitle" class="font-weight-bold">หัวข้อโหวต / ประเด็นประชามติ:</label>
                        <input type="text" class="form-control" id="topicTitle" name="title" required placeholder="เช่น ปรับปรุงการวางระบบกล้องวงจรปิดในหมู่บ้าน">
                    </div>
                    <div class="form-group">
                        <label for="topicDescription" class="font-weight-bold">รายละเอียดประกอบ (คำอธิบายเพิ่มเติม):</label>
                        <textarea class="form-control" id="topicDescription" name="description" rows="3" placeholder="กรอกข้อมูลประกอบการตัดสินใจของลูกบ้าน เช่น ขอบเขต งบประมาณ หรือตัวเลือกต่างๆ"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold d-flex justify-content-between align-items-center">
                            <span>รายการตัวเลือกการโหวต (ตัวเลือกข้อความ):</span>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnAddOption">
                                <i class="fa fa-plus"></i> เพิ่มตัวเลือก
                            </button>
                        </label>
                        <div id="optionsContainer">
                            <div class="input-group mb-2 option-group">
                                <input type="text" class="form-control option-text-field" name="options[]" placeholder="ตัวเลือกที่ 1" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-danger btn-remove-option" type="button"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                            <div class="input-group mb-2 option-group">
                                <input type="text" class="form-control option-text-field" name="options[]" placeholder="ตัวเลือกที่ 2" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-danger btn-remove-option" type="button"><i class="fa fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-danger">* การโหวตจำเป็นต้องมีอย่างน้อย 2 ตัวเลือกขึ้นไป</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success" id="btnSaveTopic">บันทึกและเปิดโหวต</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: View Results -->
<div class="modal fade" id="resultModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fa fa-chart-bar"></i> ผลคะแนนการลงประชามติ</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="bg-light p-3 rounded mb-3 border">
                    <h5 id="resultTopicTitle" class="font-weight-bold text-dark mb-1">หัวข้อโหวต: -</h5>
                    <p id="resultTopicDesc" class="text-muted small mb-2">-</p>
                    <span class="badge badge-primary px-3 py-2" id="resultTotalVotes" style="font-size: 0.9rem;">โหวตทั้งหมด: 0 หลัง</span>
                </div>

                <h6 class="font-weight-bold text-secondary mb-3">ผลคะแนนแต่ละตัวเลือก:</h6>
                <div id="resultsBarsArea" class="mb-4">
                    <!-- Dynamic Progress Bars populated by JS -->
                </div>

                <hr>

                <h6 class="font-weight-bold text-secondary mb-3"><i class="fa fa-history"></i> ประวัติการโหวตแยกตามบ้านเลขที่ (Audit Log):</h6>
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-sm table-striped table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 10%;" class="text-center">ลำดับ</th>
                                <th style="width: 25%;" class="text-center">บ้านเลขที่</th>
                                <th>คำตอบที่โหวต</th>
                                <th style="width: 30%;" class="text-center">วันเวลาที่โหวต</th>
                            </tr>
                        </thead>
                        <tbody id="resultLogsTableBody">
                            <!-- Dynamic rows populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {
        loadVoteTopics();

        // 1. เพิ่ม/ลด ช่องกรอกตัวเลือกโหวตแบบไดนามิก
        let optionIndex = 2;
        $("#btnAddOption").click(function () {
            optionIndex++;
            let fieldHtml = `
                <div class="input-group mb-2 option-group animate__animated animate__fadeIn">
                    <input type="text" class="form-control option-text-field" name="options[]" placeholder="ตัวเลือกที่ ${optionIndex}" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-danger btn-remove-option" type="button"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            `;
            $("#optionsContainer").append(fieldHtml);
            renumberOptionPlaceholders();
        });

        $(document).on("click", ".btn-remove-option", function () {
            let container = $("#optionsContainer");
            if (container.children().length <= 2) {
                alertify.error("จำเป็นต้องมีอย่างน้อย 2 ตัวเลือกสำหรับการโหวต");
                return;
            }
            $(this).closest(".option-group").remove();
            renumberOptionPlaceholders();
        });

        function renumberOptionPlaceholders() {
            let index = 1;
            $(".option-text-field").each(function () {
                $(this).attr("placeholder", "ตัวเลือกที่ " + index);
                index++;
            });
            optionIndex = index - 1;
        }

        // 2. ดึงหัวข้อประชามติมาแสดงในตาราง
        function loadVoteTopics() {
            $.ajax({
                type: "POST",
                url: "model/manage_vote_process.php",
                data: { action: "GET_ALL" },
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        let rows = "";
                        response.data.forEach(function (topic) {
                            let statusBadge = "";
                            let toggleBtn = "";

                            if (topic.status === "active") {
                                statusBadge = `<span class="badge badge-success px-3 py-2"><i class="fa fa-check-circle"></i> เปิดโหวต (Active)</span>`;
                                toggleBtn = `<button class="btn btn-warning btn-sm toggle-status" id="${topic.topic_id}" status="inactive" title="ปิดรับโหวต"><i class="fa fa-ban"></i> ปิดโหวต</button>`;
                            } else {
                                statusBadge = `<span class="badge badge-secondary px-3 py-2"><i class="fa fa-times-circle"></i> ปิดโหวต (Inactive)</span>`;
                                toggleBtn = `<button class="btn btn-success btn-sm toggle-status" id="${topic.topic_id}" status="active" title="เปิดรับโหวต"><i class="fa fa-play"></i> เปิดโหวต</button>`;
                            }

                            rows += `
                                <tr>
                                    <td class="text-center">${topic.topic_id}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark">${topic.title}</div>
                                        <div class="text-muted small">${topic.description || 'ไม่มีรายละเอียดเพิ่มเติม'}</div>
                                    </td>
                                    <td class="text-center">${statusBadge}</td>
                                    <td class="text-center"><strong>${topic.total_votes}</strong> หลัง</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-info btn-sm view-results" id="${topic.topic_id}" title="ดูผลลัพธ์และสถิติ"><i class="fa fa-bar-chart"></i> รายงานผล</button>
                                            ${toggleBtn}
                                            <button class="btn btn-danger btn-sm delete-topic" id="${topic.topic_id}" title="ลบหัวข้อ"><i class="fa fa-trash"></i> ลบ</button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        if (rows === "") {
                            rows = `<tr><td colspan="5" class="text-center text-muted py-4">ไม่พบหัวข้อโหวตประชามติในฐานข้อมูล</td></tr>`;
                        }
                        $("#TableVoteList tbody").html(rows);
                    } else {
                        alertify.error(response.message);
                    }
                },
                error: function () {
                    alertify.error("เกิดข้อผิดพลาดในการดึงข้อมูลจากเซิร์ฟเวอร์");
                }
            });
        }

        $("#btnReload").click(function () {
            loadVoteTopics();
            alertify.success("รีโหลดข้อมูลปัจจุบันเรียบร้อย");
        });

        // 3. แสดง Modal เพิ่มหัวข้อโหวต
        $("#btnAdd").click(function () {
            $("#voteTopicForm")[0].reset();
            $("#optionsContainer").html(`
                <div class="input-group mb-2 option-group">
                    <input type="text" class="form-control option-text-field" name="options[]" placeholder="ตัวเลือกที่ 1" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-danger btn-remove-option" type="button"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
                <div class="input-group mb-2 option-group">
                    <input type="text" class="form-control option-text-field" name="options[]" placeholder="ตัวเลือกที่ 2" required>
                    <div class="input-group-append">
                        <button class="btn btn-outline-danger btn-remove-option" type="button"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            `);
            optionIndex = 2;
            $("#recordModal").modal("show");
        });

        // 4. ส่งฟอร์มสร้างหัวข้อโหวตใหม่
        $("#voteTopicForm").on("submit", function (e) {
            e.preventDefault();
            let formData = $(this).serialize() + "&action=ADD";
            $.ajax({
                type: "POST",
                url: "model/manage_vote_process.php",
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        alertify.success(response.message);
                        $("#recordModal").modal("hide");
                        loadVoteTopics();
                    } else {
                        alertify.error(response.message);
                    }
                },
                error: function () {
                    alertify.error("เกิดข้อผิดพลาดในระบบส่งฟอร์ม");
                }
            });
        });

        // 5. สลับสถานะเปิด/ปิดโหวต
        $(document).on("click", ".toggle-status", function () {
            let id = $(this).attr("id");
            let status = $(this).attr("status");
            let confirmMsg = status === "active" ? "ยืนยันการเปิดระบบโหวตประชามติ?" : "ยืนยันการปิดรับโหวตในหัวข้อนี้?";

            alertify.confirm(confirmMsg, function () {
                $.ajax({
                    type: "POST",
                    url: "model/manage_vote_process.php",
                    data: { action: "TOGGLE_STATUS", id: id, status: status },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            alertify.success(response.message);
                            loadVoteTopics();
                        } else {
                            alertify.error(response.message);
                        }
                    }
                });
            });
        });

        // 6. ลบหัวข้อโหวต
        $(document).on("click", ".delete-topic", function () {
            let id = $(this).attr("id");
            alertify.confirm("คุณต้องการลบหัวข้อโหวตนี้จริงหรือไม่? ข้อมูลประวัติการลงคะแนนทั้งหมดของหัวข้อนี้จะถูกลบไปด้วย", function () {
                $.ajax({
                    type: "POST",
                    url: "model/manage_vote_process.php",
                    data: { action: "DELETE", id: id },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === "success") {
                            alertify.success(response.message);
                            loadVoteTopics();
                        } else {
                            alertify.error(response.message);
                        }
                    }
                });
            });
        });

        // 7. ดูผลคะแนนประชามติและประวัติ (Report & Logs)
        $(document).on("click", ".view-results", function () {
            let id = $(this).attr("id");
            $.ajax({
                type: "POST",
                url: "model/manage_vote_process.php",
                data: { action: "GET_RESULTS", id: id },
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        // อัปเดตข้อมูลหัวข้อโหวต
                        $("#resultTopicTitle").text(response.topic.title);
                        $("#resultTopicDesc").text(response.topic.description || "ไม่มีรายละเอียดเพิ่มเติม");
                        $("#resultTotalVotes").text("โหวตทั้งหมด: " + response.total_votes + " หลัง");

                        // เรนเดอร์กราฟแท่งความคืบหน้า (Bootstrap Progress Bars)
                        let total = response.total_votes;
                        let barsHtml = "";
                        let colors = ["bg-primary", "bg-success", "bg-info", "bg-warning", "bg-secondary"];

                        response.options.forEach(function (opt, idx) {
                            let count = opt.vote_count;
                            let percent = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
                            let barColor = colors[idx % colors.length];

                            barsHtml += `
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="font-weight-bold text-dark">${opt.option_text}</span>
                                        <span class="badge badge-secondary px-2">${count} หลัง (${percent}%)</span>
                                    </div>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar ${barColor} progress-bar-striped progress-bar-animated" role="progressbar" style="width: ${percent}%;" aria-valuenow="${percent}" aria-valuemin="0" aria-valuemax="100">${percent}%</div>
                                    </div>
                                </div>
                            `;
                        });
                        $("#resultsBarsArea").html(barsHtml);

                        // เรนเดอร์ตารางประวัติ (Logs)
                        let logsHtml = "";
                        response.logs.forEach(function (log, idx) {
                            let time = new Date(log.voted_at).toLocaleString("th-TH");
                            logsHtml += `
                                <tr>
                                    <td class="text-center">${idx + 1}</td>
                                    <td class="text-center text-primary font-weight-bold">${log.house_number}</td>
                                    <td>${log.option_text}</td>
                                    <td class="text-center">${time}</td>
                                </tr>
                            `;
                        });
                        if (logsHtml === "") {
                            logsHtml = `<tr><td colspan="4" class="text-center text-muted">ยังไม่เคยมีบ้านเลขที่ใดร่วมลงคะแนนเสียง</td></tr>`;
                        }
                        $("#resultLogsTableBody").html(logsHtml);

                        $("#resultModal").modal("show");
                    } else {
                        alertify.error(response.message);
                    }
                }
            });
        });
    });
</script>
</body>
</html>
<?php } ?>
