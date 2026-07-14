<?php 
include('../../config/connect_db.php');
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>ระบบลงประชามติ - PS33 Home System</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon"/>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts - Prompt & Sarabun -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">

    <!-- LIFF SDK -->
    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>

    <style>
        body {
            font-family: 'Sarabun', 'Prompt', sans-serif;
            background-color: #f4f6f9;
        }
        .vote-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            background-color: #ffffff;
        }
        .header-logo img {
            max-width: 100%;
            height: auto;
            display: inline-block;
        }
        .choice-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .choice-item:hover {
            background-color: #f8f9fa;
            border-color: #b0c4de;
        }
        .choice-item.selected {
            background-color: #e8f4fd;
            border-color: #2980b9;
            font-weight: 600;
        }
        .choice-item input[type="radio"] {
            margin-right: 12px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4 mb-5 d-flex justify-content-center align-items-center min-vh-100">
    <div class="card vote-card p-4 p-md-5 text-center" style="width: 100%; max-width: 550px;">
        <!-- Logo -->
        <div class="header-logo mb-4">
            <img src="ps33_logo.png" width="70" height="117" alt="Logo" />
        </div>
        
        <h4 class="mb-2 fw-bold text-dark">ระบบโหวตลงประชามติลูกบ้าน</h4>
        <p class="text-muted small">จำกัดสิทธิ์ 1 โหวต ต่อ 1 บ้านเลขที่ ในแต่ละหัวข้อ</p>
        
        <hr class="mb-4">

        <!-- Unregistered User warning -->
        <div id="unregisteredAlert" class="alert alert-warning d-none text-start" role="alert">
            <h5 class="alert-heading fw-bold"><i class="fas fa-exclamation-triangle"></i> ยังไม่ได้ลงทะเบียนลูกบ้าน!</h5>
            <p class="mb-2">ขออภัยครับ บัญชี LINE นี้ยังไม่ได้ลงทะเบียนลูกบ้านของโครงการ PS33 จึงไม่มีสิทธิ์ร่วมลงประชามติ</p>
            <hr>
            <div class="d-grid">
                <a href="register_house_user.php" class="btn btn-warning"><i class="fas fa-id-card me-1"></i> ไปหน้าลงทะเบียนลูกบ้าน</a>
            </div>
        </div>

        <!-- No Active Topics -->
        <div id="noTopicsAlert" class="alert alert-info d-none text-start" role="alert">
            <i class="fas fa-info-circle"></i> ขณะนี้ไม่มีหัวข้อการลงประชามติเปิดอยู่
        </div>

        <!-- Vote Form -->
        <form id="voteForm" class="text-start d-none" onsubmit="submitVote(event)">
            <!-- User Information Panel (Auto-filled and locked) -->
            <div class="bg-light p-3 rounded mb-4 border text-start">
                <div class="d-flex align-items-center gap-3">
                    <img id="profilePic" src="" class="rounded-circle border" width="55" height="55" alt="Profile Pic">
                    <div>
                        <div class="fw-bold text-dark" id="voterName">กำลังดึงข้อมูล LINE...</div>
                        <div class="text-muted small" id="voterHouseBadge">บ้านเลขที่: -</div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="lineUserId" name="lineUserId">
            <input type="hidden" id="houseNumber" name="house_number">

            <!-- Step 1: Select Topic -->
            <div class="mb-3">
                <label for="topicSelect" class="form-label fw-bold">1. เลือกหัวข้อที่ต้องการลงคะแนน:</label>
                <select class="form-select form-select-lg" id="topicSelect" name="topic_id" required onchange="onTopicChange()">
                    <!-- Options populated via AJAX -->
                </select>
                <div id="topicDescription" class="form-text text-muted mt-2"></div>
            </div>

            <!-- Step 2: Choose Option -->
            <div class="mb-4">
                <label class="form-label fw-bold">2. เลือกคำตอบของท่าน:</label>
                <div id="optionsArea">
                    <!-- Radios populated via AJAX -->
                </div>
                <div class="invalid-feedback d-block" id="optionError"></div>
            </div>

            <!-- Warning block if already voted -->
            <div id="alreadyVotedWarning" class="alert alert-danger d-none mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> <strong>สิทธิ์บ้านเลขที่นี้ถูกใช้งานแล้ว:</strong> <span id="alreadyVotedMessage"></span>
            </div>

            <!-- Submit button -->
            <div class="d-grid mt-4">
                <button type="submit" id="btnSubmit" class="btn btn-success btn-lg disabled" disabled>
                    <i class="fas fa-paper-plane me-2"></i> ส่งคะแนนโหวต
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 1: Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">✅ ลงประชามติเรียบร้อย!</h5>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success fa-3x mb-3 animate__animated animate__bounceIn"></i>
                <p class="mb-2">ระบบได้บันทึกคะแนนโหวตของบ้านเลขที่ <strong id="successHouseNumber"></strong> สำเร็จแล้ว</p>
                <p class="text-muted small">ขอขอบพระคุณลูกบ้านทุกท่านที่ร่วมสร้างหมู่บ้านที่น่าอยู่ไปด้วยกัน</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success w-100" onclick="closeModalAndExit()">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="jsconfig/config_house_register.js"></script>

<script>
    let activeTopicsList = [];
    let currentVoterHouse = "";

    $(document).ready(function () {
        // Initialize LINE LIFF
        liff.init({ liffId: LIFF_ID }).then(() => {
            if (liff.isLoggedIn()) {
                liff.getProfile().then(profile => {
                    $('#lineUserId').val(profile.userId);
                    $('#profilePic').attr('src', profile.pictureUrl || "../img/user-001.png");
                    
                    // ดึงประวัติทะเบียนลูกบ้านว่าผูกสิทธิ์ LINE กับบ้านเลขที่ใด
                    checkVoterRegistration(profile.userId, profile.displayName);
                }).catch(err => {
                    console.error("LIFF Profile Error:", err);
                    alert("ไม่สามารถดึงข้อมูลโปรไฟล์ LINE ได้");
                });
            } else {
                liff.login();
            }
        }).catch(err => {
            console.error("LIFF Init Error:", err);
            // เพื่อทดสอบนอก LINE (Fallback สำหรับ Demo)
            console.log("Fallback to debug mode");
            checkVoterRegistration("U123456789debug", "ทดสอบเบราว์เซอร์");
        });
    });

    // 1. ตรวจสอบการผูกสิทธิ์ของ Line ID
    function checkVoterRegistration(lineUserId, displayName) {
        $.post("register_house_with_line_api.php", {
            action: 'check',
            lineUserId: lineUserId
        }, function(response) {
            if (response.success && response.registered) {
                const user = response.user;
                currentVoterHouse = user.house_number;
                
                // กรอกข้อมูลผู้ใช้บนฟอร์ม
                $("#voterName").text(user.f_name + " " + user.l_name + " (" + displayName + ")");
                $("#voterHouseBadge").html(`🏠 <strong>บ้านเลขที่: ${user.house_number}</strong> (ซอย ${user.line_phone})`);
                $("#houseNumber").val(user.house_number);

                $("#voteForm").removeClass("d-none");
                // ดึงรายการหัวข้อโหวตที่กำลัง Active
                loadActiveTopics();
            } else {
                // ยังไม่ได้ลงทะเบียน
                $("#unregisteredAlert").removeClass("d-none");
            }
        }, "json").fail(function() {
            // Fallback เพื่อให้สามารถทดสอบจำลองได้ (กรณี API ไม่มีในโลคอลขณะนั้น)
            alert("ไม่สามารถตรวจสอบข้อมูลทะเบียนลูกบ้านกับ API ได้ กรุณาลงทะเบียนผูก LINE กับบ้านเลขที่ก่อน");
            $("#unregisteredAlert").removeClass("d-none");
        });
    }

    // 2. ดึงรายการหัวข้อโหวตที่เปิดใช้งาน
    function loadActiveTopics() {
        $.post("vote_submit_api.php", { action: 'get_active_topics' }, function(response) {
            if (response.success) {
                activeTopicsList = response.topics;
                if (activeTopicsList.length === 0) {
                    $("#voteForm").addClass("d-none");
                    $("#noTopicsAlert").removeClass("d-none");
                    return;
                }

                // ใส่หัวข้อใน select
                let selectHtml = "";
                activeTopicsList.forEach(topic => {
                    selectHtml += `<option value="${topic.topic_id}">${topic.title}</option>`;
                });
                $("#topicSelect").html(selectHtml);
                
                onTopicChange();
            } else {
                alert("เกิดข้อผิดพลาดในการโหลดหัวข้อประชามติ");
            }
        }, "json");
    }

    // 3. เมื่อหัวข้อเปลี่ยน: โหลดรายละเอียดตัวเลือก และเช็กว่าเคยโหวตแล้วหรือยัง
    function onTopicChange() {
        const topicId = parseInt($("#topicSelect").value || $("#topicSelect").find(":selected").val());
        if (!topicId) return;

        const topic = activeTopicsList.find(t => t.topic_id === topicId);
        if (!topic) return;

        $("#topicDescription").html(`<i class="fas fa-info-circle text-info"></i> รายละเอียด: ${topic.description || 'ไม่มีรายละเอียดเพิ่มเติม'}`);

        // โหลดตัวเลือกคำตอบ
        let optionsHtml = "";
        topic.options.forEach(opt => {
            optionsHtml += `
                <div class="choice-item" id="item-opt-${opt.option_id}" onclick="selectChoice(${opt.option_id})">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="option_id" id="opt-${opt.option_id}" value="${opt.option_id}" required>
                        <label class="form-check-label w-100" for="opt-${opt.option_id}">
                            ${opt.option_text}
                        </label>
                    </div>
                </div>
            `;
        });
        $("#optionsArea").html(optionsHtml);

        // เช็กสิทธิ์ว่าบ้านเลขที่นี้โหวตหรือยัง
        checkDuplicateVote(topicId);
    }

    function selectChoice(optId) {
        $(".choice-item").removeClass("selected");
        $("#item-opt-" + optId).addClass("selected");
        $("#opt-" + optId).prop("checked", true);
    }

    // 4. เช็กในเซิร์ฟเวอร์ว่าบ้านเลขที่นี้เคยโหวตในหัวข้อนี้หรือยัง
    function checkDuplicateVote(topicId) {
        $.post("vote_submit_api.php", {
            action: 'check_vote',
            topic_id: topicId,
            house_number: currentVoterHouse
        }, function(response) {
            if (response.success) {
                if (response.voted) {
                    const votedTime = new Date(response.record.voted_at).toLocaleString("th-TH");
                    $("#alreadyVotedMessage").html(`ลงคะแนนไปแล้วเมื่อ <strong>${votedTime}</strong> ตัวเลือกที่เลือกคือ: <strong>"${response.record.option_text}"</strong>`);
                    $("#alreadyVotedWarning").removeClass("d-none");
                    $("#btnSubmit").addClass("disabled").prop("disabled", true);
                    $(".choice-item").addClass("opacity-50").css("pointer-events", "none");
                } else {
                    $("#alreadyVotedWarning").addClass("d-none");
                    $("#btnSubmit").removeClass("disabled").prop("disabled", false);
                    $(".choice-item").removeClass("opacity-50").css("pointer-events", "auto");
                }
            }
        }, "json");
    }

    // 5. ส่งฟอร์มลงคะแนนเสียงโหวต
    function submitVote(e) {
        e.preventDefault();
        
        const topicId = $("#topicSelect").val();
        const optionId = $("input[name='option_id']:checked").val();
        const lineUserId = $("#lineUserId").val();
        const houseNo = $("#houseNumber").val();

        if (!optionId) {
            $("#optionError").text("กรุณาเลือกหนึ่งคำตอบเพื่อส่งผลการโหวต");
            return;
        }
        $("#optionError").text("");

        $.post("vote_submit_api.php", {
            action: 'submit_vote',
            topic_id: topicId,
            option_id: optionId,
            lineUserId: lineUserId,
            house_number: houseNo
        }, function(response) {
            if (response.success) {
                $("#successHouseNumber").text(houseNo);
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            } else {
                alert("ไม่สามารถบันทึกได้: " + response.message);
            }
        }, "json").fail(function() {
            alert("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
        });
    }

    function closeModalAndExit() {
        const successModal = bootstrap.Modal.getInstance(document.getElementById('successModal'));
        if (successModal) successModal.hide();
        
        if (liff.isInClient()) {
            liff.closeWindow();
        } else {
            // โหลดหน้าใหม่หากทดสอบบนเบราว์เซอร์
            location.reload();
        }
    }
</script>

</body>
</html>
