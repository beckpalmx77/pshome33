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
        .progress {
            border-radius: 10px;
        }
        .progress-bar {
            transition: width 0.6s ease;
        }
        .topic-list .topic-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
        }
        .topic-list .topic-item:hover {
            background-color: #f8f9fa;
            border-color: #b0c4de;
        }
        .topic-list .topic-item.selected {
            background-color: #e8f4fd;
            border-color: #2980b9;
            font-weight: 600;
        }
        .topic-list .topic-item .topic-title {
            font-weight: 600;
            color: #333;
            word-wrap: break-word;
        }
        .topic-list .topic-item .topic-desc {
            font-size: 0.85em;
            color: #888;
            margin-top: 4px;
            word-wrap: break-word;
        }
        .topic-list .topic-item .topic-badge {
            font-size: 0.75em;
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
            <div class="mt-3">
                <button type="button" class="btn btn-outline-primary" onclick="showVoteResults()">
                    <i class="fas fa-chart-bar me-2"></i> ดูผลโหวตทั้งหมด
                </button>
            </div>
        </div>

        <!-- Standalone Results Button (shown when user is registered but no active topics or always) -->
        <div id="standaloneResultsBtn" class="d-none text-center mt-3">
            <button type="button" class="btn btn-outline-primary btn-lg" onclick="showVoteResults()">
                <i class="fas fa-chart-bar me-2"></i> ดูผลโหวตทั้งหมด
            </button>
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
                <label class="form-label fw-bold">1. เลือกหัวข้อที่ต้องการลงคะแนน:</label>
                <div id="topicList" class="topic-list">
                    <!-- Topics populated via AJAX as cards -->
                </div>
                <input type="hidden" id="topicSelect" name="topic_id">
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

            <!-- Show Results button -->
            <div class="d-grid mt-3">
                <button type="button" id="btnShowResults" class="btn btn-outline-primary btn-lg" onclick="showVoteResults()">
                    <i class="fas fa-chart-bar me-2"></i> ดูผลโหวตทั้งหมด
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

<!-- Modal 2: Vote Results Modal -->
<div class="modal fade" id="resultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 600px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-chart-bar me-2"></i> ผลโหวตทั้งหมด</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultsBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <p class="mt-2 text-muted">กำลังโหลดข้อมูล...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="jsconfig/config_vote.js"></script>

<script>
    let activeTopicsList = [];
    let currentVoterHouse = "";
    const API_BASE = window.location.origin;
    let voteAppStarted = false;

    function startVoteApp(displayName, lineUserId, houseNumber, profilePic) {
        if (voteAppStarted) return;
        voteAppStarted = true;

        if (!lineUserId) {
            startVoteAppFallback(displayName, houseNumber);
            return;
        }

        // ตรวจสอบ LINE User ID กับฐานข้อมูล
        $.post(API_BASE + "/line_oa/house/vote_submit_api.php", {
            action: 'check_line_user',
            lineUserId: lineUserId
        }, function(response) {
            if (response.success && response.registered) {
                const user = response.user;
                const fullName = user.f_name + ' ' + user.l_name;
                const house = user.house_number || houseNumber || "ไม่ระบุ";
                const pic = user.line_picture_profile || profilePic || "../img/user-001.png";

                currentVoterHouse = house;
                $("#lineUserId").val(lineUserId);
                $("#voterName").text(fullName);
                $("#profilePic").attr('src', pic);
                $("#voterHouseBadge").html(`🏠 <strong>บ้านเลขที่: ${currentVoterHouse}</strong>`);
                $("#houseNumber").val(currentVoterHouse);
                $("#voteForm").removeClass("d-none");
                $("#standaloneResultsBtn").removeClass("d-none");
                loadActiveTopics();
            } else {
                // ไม่ได้ลงทะเบียน
                $("#unregisteredAlert").removeClass("d-none");
            }
        }, "json").fail(function() {
            startVoteAppFallback(displayName, houseNumber);
        });
    }

    function startVoteAppFallback(displayName, houseNumber) {
        currentVoterHouse = houseNumber || "TEST";
        $("#lineUserId").val("");
        $("#voterName").text(displayName);
        $("#voterHouseBadge").html(`🏠 <strong>บ้านเลขที่: ${currentVoterHouse}</strong>`);
        $("#houseNumber").val(currentVoterHouse);
        $("#voteForm").removeClass("d-none");
        $("#standaloneResultsBtn").removeClass("d-none");
        loadActiveTopics();
    }

    $(document).ready(function () {
        const urlParams = new URLSearchParams(window.location.search);
        const urlHouseNumber = urlParams.get('house_number') || '';

        const fallbackTimer = setTimeout(function() {
            console.warn("LIFF init timeout, using fallback mode");
            startVoteApp("ทดสอบเบราว์เซอร์", "", urlHouseNumber);
        }, 5000);

        try {
            if (typeof liff === 'undefined' || typeof LIFF_ID === 'undefined' || !LIFF_ID) {
                console.warn("LIFF not available, using fallback mode");
                clearTimeout(fallbackTimer);
                startVoteApp("ทดสอบเบราว์เซอร์", "", urlHouseNumber);
                return;
            }

            liff.init({ liffId: LIFF_ID }).then(() => {
                clearTimeout(fallbackTimer);
                if (liff.isLoggedIn()) {
                    liff.getProfile().then(profile => {
                        startVoteApp(profile.displayName, profile.userId, urlHouseNumber, profile.pictureUrl);
                        $('#profilePic').attr('src', profile.pictureUrl || "../img/user-001.png");
                    }).catch(err => {
                        console.error("LIFF Profile Error:", err);
                        startVoteApp("ไม่ทราบชื่อ", "", urlHouseNumber);
                    });
                } else {
                    liff.login();
                }
            }).catch(err => {
                clearTimeout(fallbackTimer);
                console.error("LIFF Init Error:", err);
                startVoteApp("ทดสอบเบราว์เซอร์", "", urlHouseNumber);
            });
        } catch (e) {
            clearTimeout(fallbackTimer);
            console.error("LIFF exception:", e);
            startVoteApp("ทดสอบเบราว์เซอร์", "", urlHouseNumber);
        }
    });

    // 2. ดึงรายการหัวข้อโหวตที่เปิดใช้งาน
    function loadActiveTopics() {
        $.post(API_BASE + "/line_oa/house/vote_submit_api.php", { action: 'get_active_topics' }, function(response) {
            if (response.success) {
                activeTopicsList = response.topics;
                if (activeTopicsList.length === 0) {
                    $("#voteForm").addClass("d-none");
                    $("#noTopicsAlert").removeClass("d-none");
                    return;
                }

                // ดึงสถานะโหวตทั้งหมดของบ้านเลขที่นี้
                fetchVotedStatusAndRender();
            } else {
                alert("เกิดข้อผิดพลาดในการโหลดหัวข้อประชามติ");
            }
        }, "json");
    }

    function fetchVotedStatusAndRender() {
        $.post(API_BASE + "/line_oa/house/vote_submit_api.php", {
            action: 'get_voted_status',
            house_number: currentVoterHouse
        }, function(voteResponse) {
            let votedMap = {};
            if (voteResponse.success && voteResponse.voted_topics) {
                voteResponse.voted_topics.forEach(function(v) {
                    votedMap[v.topic_id] = { voted_at: v.voted_at, option_text: v.option_text };
                });
            }

            renderTopicList(votedMap);
        }, "json").fail(function() {
            renderTopicList({});
        });
    }

    function renderTopicList(votedMap) {
        let html = "";
        activeTopicsList.forEach((topic, index) => {
            let selectedClass = index === 0 ? "selected" : "";
            let badge = "";
            if (votedMap[topic.topic_id]) {
                badge = '<span class="badge bg-success topic-badge ms-2">✓ ลงแล้ว</span>';
            }
            html += `
                <div class="topic-item ${selectedClass}" data-topic-id="${topic.topic_id}" onclick="selectTopic(${topic.topic_id})">
                    <div class="topic-title">${topic.title}${badge}</div>
                    ${topic.description ? '<div class="topic-desc">' + topic.description + '</div>' : ''}
                </div>
            `;
        });
        $("#topicList").html(html);
        
        if (activeTopicsList.length > 0) {
            $("#topicSelect").val(activeTopicsList[0].topic_id);
        }
        
        onTopicChange();
    }

    function selectTopic(topicId) {
        $(".topic-item").removeClass("selected");
        $(`.topic-item[data-topic-id="${topicId}"]`).addClass("selected");
        $("#topicSelect").val(topicId);
        onTopicChange();
    }

    // 3. เมื่อหัวข้อเปลี่ยน: โหลดรายละเอียดตัวเลือก และเช็กว่าเคยโหวตแล้วหรือยัง
    function onTopicChange() {
        const topicId = parseInt($("#topicSelect").val());
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
        $.post(API_BASE + "/line_oa/house/vote_submit_api.php", {
            action: 'check_vote',
            topic_id: topicId,
            house_number: currentVoterHouse
        }, function(response) {
            if (response.success) {
                if (response.voted) {
                    const votedTime = new Date(response.record.voted_at).toLocaleString("th-TH");
                    $("#alreadyVotedMessage").html(
                        `บ้านเลขที่ <strong>${currentVoterHouse}</strong> ได้ลงคะแนนในหัวข้อนี้ไปแล้ว<br>` +
                        `<small>🕐 ลงคะแนนเมื่อ: <strong>${votedTime}</strong></small><br>` +
                        `<small>📝 คำตอบที่เลือก: <strong>"${response.record.option_text}"</strong></small>`
                    );
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

        $.post(API_BASE + "/line_oa/house/vote_submit_api.php", {
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
        
        if (typeof liff !== 'undefined' && liff.isInClient && liff.isInClient()) {
            liff.closeWindow();
        } else {
            // รีเฟรชสถานะโหวตใน dropdown แทนการ reload ทั้งหน้า
            fetchVotedStatusAndRender();
        }
    }

    // 6. แสดงผลโหวตทั้งหมด
    function showVoteResults() {
        const resultsModal = new bootstrap.Modal(document.getElementById('resultsModal'));
        resultsModal.show();

        $.post(API_BASE + "/line_oa/house/vote_submit_api.php", {
            action: 'get_vote_results'
        }, function(response) {
            if (response.success && response.results) {
                renderVoteResults(response.results);
            } else {
                $("#resultsBody").html('<div class="alert alert-danger">ไม่สามารถโหลดข้อมูลผลโหวตได้</div>');
            }
        }, "json").fail(function() {
            $("#resultsBody").html('<div class="alert alert-danger">เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์</div>');
        });
    }

    function renderVoteResults(results) {
        if (!results || results.length === 0) {
            $("#resultsBody").html('<div class="alert alert-info">ยังไม่มีข้อมูลผลโหวต</div>');
            return;
        }

        let html = '';

        // สรุปจำนวนหัวข้อ
        const activeCount = results.filter(t => t.status === 'active').length;
        const closedCount = results.filter(t => t.status !== 'active').length;
        html += `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <small class="text-muted">ทั้งหมด <strong>${results.length}</strong> หัวข้อ</small>
                <div>
                    <span class="badge bg-success me-1">เปิดรับโหวต ${activeCount}</span>
                    <span class="badge bg-secondary">ปิดโหวตแล้ว ${closedCount}</span>
                </div>
            </div>
        `;

        // Accordion
        html += '<div class="accordion" id="resultsAccordion">';
        results.forEach(function(topic, index) {
            const statusClass = topic.status === 'active' ? 'success' : 'secondary';
            const collapseId = 'collapse_topic_' + index;
            const headerId = 'header_topic_' + index;
            const isWinner = topic.total_votes > 0 ? getWinnerText(topic) : '';

            html += `
                <div class="accordion-item border-0 mb-2 shadow-sm">
                    <h2 class="accordion-header" id="${headerId}">
                        <button class="accordion-button collapsed" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#${collapseId}" 
                                aria-expanded="false" aria-controls="${collapseId}">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <div class="text-start">
                                    <div class="fw-bold">${topic.title}</div>
                                    <small class="text-muted">${topic.total_votes} ผู้โหวต</small>
                                    ${isWinner ? '<br><small class="text-success fw-bold">' + isWinner + '</small>' : ''}
                                </div>
                                <span class="badge bg-${statusClass}">${topic.status_text}</span>
                            </div>
                        </button>
                    </h2>
                    <div id="${collapseId}" class="accordion-collapse collapse" 
                         aria-labelledby="${headerId}" data-bs-parent="#resultsAccordion">
                        <div class="accordion-body">
                            <p class="text-muted small mb-3">${topic.description || 'ไม่มีรายละเอียด'}`;
            
            if (topic.total_votes > 0) {
                topic.options.forEach(function(opt) {
                    const barColor = opt.percent >= 50 ? 'bg-success' : (opt.percent >= 30 ? 'bg-info' : 'bg-secondary');
                    const isMax = opt.vote_count === Math.max(...topic.options.map(o => o.vote_count));
                    html += `
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-bold ${isMax ? 'text-success' : ''}">${opt.option_text} ${isMax ? '🏆' : ''}</span>
                                    <span class="small text-muted">${opt.vote_count} คะแนน (${opt.percent}%)</span>
                                </div>
                                <div class="progress" style="height: 22px;">
                                    <div class="progress-bar ${barColor}" role="progressbar" 
                                         style="width: ${opt.percent}%;" 
                                         aria-valuenow="${opt.percent}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                    `;
                });
                html += `
                            <div class="text-end mt-2">
                                <small class="text-muted">ผู้โหวตทั้งหมด: <strong>${topic.total_votes}</strong> ราย</small>
                            </div>
                `;
            } else {
                html += '<div class="alert alert-light text-center mb-0">ยังไม่มีผู้โหวต</div>';
            }

            html += `
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        $("#resultsBody").html(html);
    }

    function getWinnerText(topic) {
        if (!topic.options || topic.options.length === 0) return '';
        const maxVote = Math.max(...topic.options.map(o => o.vote_count));
        if (maxVote === 0) return '';
        const winner = topic.options.find(o => o.vote_count === maxVote);
        return '🏆 ' + winner.option_text;
    }
</script>

</body>
</html>
