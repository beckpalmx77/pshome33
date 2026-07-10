<?php
session_start();
$installed_file = __DIR__ . '/config/installed.inc';
$is_installed = file_exists($installed_file);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตั้งระบบ - PS33 Home System</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Prompt', sans-serif; }
        body {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-container {
            width: 100%;
            max-width: 750px;
        }
        .install-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .install-header {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: #fff;
            padding: 30px 40px;
            text-align: center;
        }
        .install-header h1 {
            font-size: 1.6rem;
            font-weight: 600;
            margin: 0 0 5px 0;
        }
        .install-header p {
            font-size: 0.9rem;
            opacity: 0.85;
            margin: 0;
        }
        .install-body {
            padding: 30px 40px 40px;
        }

        /* Stepper */
        .stepper {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            position: relative;
        }
        .stepper::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 15%;
            right: 15%;
            height: 3px;
            background: #e0e0e0;
            z-index: 0;
        }
        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            border: 3px solid #e0e0e0;
        }
        .step-item.active .step-circle {
            background: #1a73e8;
            color: #fff;
            border-color: #1a73e8;
            box-shadow: 0 0 0 4px rgba(26,115,232,0.2);
        }
        .step-item.done .step-circle {
            background: #28a745;
            color: #fff;
            border-color: #28a745;
        }
        .step-label {
            font-size: 0.75rem;
            color: #999;
            margin-top: 8px;
            text-align: center;
            white-space: nowrap;
        }
        .step-item.active .step-label { color: #1a73e8; font-weight: 500; }
        .step-item.done .step-label { color: #28a745; }

        /* Step panels */
        .step-panel { display: none; }
        .step-panel.active { display: block; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .form-group label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 6px;
        }
        .form-group .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 14px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        .form-group .form-control:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26,115,232,0.15);
        }

        .btn-install {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            border: none;
            color: #fff;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s;
            cursor: pointer;
        }
        .btn-install:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(26,115,232,0.4);
            color: #fff;
        }
        .btn-install:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .btn-back {
            background: #6c757d;
            border: none;
            color: #fff;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-back:hover { background: #5a6268; color: #fff; }
        .btn-danger-install {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            color: #fff;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-danger-install:hover { color: #fff; box-shadow: 0 4px 15px rgba(220,53,69,0.4); }

        /* Log area */
        .log-area {
            background: #1a1a2e;
            color: #0f0;
            border-radius: 8px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 15px;
        }
        .log-area .log-line { margin-bottom: 3px; }
        .log-area .log-line.info { color: #00bcd4; }
        .log-area .log-line.success { color: #4caf50; }
        .log-area .log-line.error { color: #f44336; }
        .log-area .log-line.warn { color: #ff9800; }

        /* Progress bar */
        .install-progress { margin-top: 20px; }
        .install-progress .progress {
            height: 8px;
            border-radius: 10px;
            background: #e9ecef;
        }
        .install-progress .progress-bar {
            border-radius: 10px;
            background: linear-gradient(90deg, #1a73e8, #28a745);
            transition: width 0.5s ease;
        }

        /* Welcome */
        .welcome-icon { font-size: 3rem; color: #1a73e8; margin-bottom: 15px; }
        .welcome-title { font-size: 1.3rem; font-weight: 600; color: #333; }
        .welcome-desc { color: #666; font-size: 0.9rem; margin-top: 10px; }

        /* Req check */
        .req-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            background: #f8f9fa;
        }
        .req-item .req-icon { margin-right: 12px; font-size: 1.1rem; }
        .req-item .req-text { flex: 1; font-size: 0.85rem; }
        .req-item.pass .req-icon { color: #28a745; }
        .req-item.fail .req-icon { color: #dc3545; }
        .req-item.pass { border-left: 4px solid #28a745; }
        .req-item.fail { border-left: 4px solid #dc3545; }

        /* Complete */
        .complete-icon { font-size: 4rem; color: #28a745; margin-bottom: 15px; }
        .complete-title { font-size: 1.4rem; font-weight: 600; color: #28a745; }

        /* Reset alert */
        .reset-warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px 20px;
            color: #856404;
            margin-bottom: 20px;
        }

        /* Already installed */
        .already-installed {
            text-align: center;
            padding: 40px 20px;
        }
        .already-installed .icon { font-size: 4rem; color: #ffc107; }
        .already-installed h2 { margin-top: 15px; color: #333; }

        .table-name {
            display: inline-block;
            background: #e3f2fd;
            color: #1565c0;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin: 2px;
        }
    </style>
</head>
<body>
<div class="install-container">
    <div class="install-card">
        <div class="install-header">
            <h1><i class="fas fa-cogs"></i> ระบบติดตั้ง PS33 Home System</h1>
            <p>ระบบบริหารงานนิติบุคคลหมู่บ้าน พฤกษา 33</p>
        </div>
        <div class="install-body">
            <?php if ($is_installed): ?>
            <!-- Already Installed -->
            <div class="already-installed">
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <h2>ระบบถูกติดตั้งแล้ว</h2>
                <p class="text-muted mt-3">ระบบได้รับการติดตั้งเรียบร้อยแล้ว</p>
                <div class="mt-4">
                    <a href="login" class="btn btn-install me-2"><i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ</a>
                    <a href="reset.php" class="btn btn-danger-install"><i class="fas fa-redo"></i> รีเซ็ตและติดตั้งใหม่</a>
                </div>
            </div>
            <?php else: ?>
            <!-- Stepper -->
            <div class="stepper">
                <div class="step-item active" data-step="1">
                    <div class="step-circle">1</div>
                    <div class="step-label">ยินดีต้อนรับ</div>
                </div>
                <div class="step-item" data-step="2">
                    <div class="step-circle">2</div>
                    <div class="step-label">ตั้งค่า DB</div>
                </div>
                <div class="step-item" data-step="3">
                    <div class="step-circle">3</div>
                    <div class="step-label">สร้างตาราง</div>
                </div>
                <div class="step-item" data-step="4">
                    <div class="step-circle">4</div>
                    <div class="step-label">สร้าง Admin</div>
                </div>
                <div class="step-item" data-step="5">
                    <div class="step-circle">5</div>
                    <div class="step-label">เสร็จสิ้น</div>
                </div>
            </div>

            <!-- Step 1: Welcome & Requirements -->
            <div class="step-panel active" id="step1">
                <div class="text-center">
                    <div class="welcome-icon"><i class="fas fa-rocket"></i></div>
                    <div class="welcome-title">ยินดีต้อนรับสู่ระบบติดตั้ง</div>
                    <p class="welcome-desc">ระบบจะทำการตรวจสอบสภาพแวดล้อมและตั้งค่าฐานข้อมูลให้พร้อมใช้งาน</p>
                </div>
                <hr>
                <h6 class="mb-3"><i class="fas fa-clipboard-check"></i> ตรวจสอบสภาพแวดล้อม</h6>
                <div id="requirements-list">
                    <div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> กำลังตรวจสอบ...</div>
                </div>
                <div class="text-center mt-4">
                    <button class="btn btn-install" id="btn-next-step1" disabled onclick="goToStep(2)">
                        <i class="fas fa-arrow-right"></i> ถัดไป
                    </button>
                </div>
            </div>

            <!-- Step 2: Database Config -->
            <div class="step-panel" id="step2">
                <h6 class="mb-3"><i class="fas fa-database"></i> ตั้งค่าฐานข้อมูล MySQL</h6>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>MySQL Host</label>
                            <input type="text" class="form-control" id="db_host" value="localhost" placeholder="localhost">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Port</label>
                            <input type="text" class="form-control" id="db_port" value="3306" placeholder="3306">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>MySQL Username</label>
                    <input type="text" class="form-control" id="db_user" placeholder="root">
                </div>
                <div class="form-group">
                    <label>MySQL Password</label>
                    <input type="password" class="form-control" id="db_pass" placeholder="รหัสผ่าน">
                </div>
                <div class="form-group">
                    <label>ชื่อฐานข้อมูล (Database Name)</label>
                    <input type="text" class="form-control" id="db_name" value="house_dbs" placeholder="house_dbs">
                    <small class="form-text text-muted">หากฐานข้อมูลยังไม่มี ระบบจะสร้างให้อัตโนมัติ</small>
                </div>
                <div class="text-center mt-3">
                    <button class="btn btn-back me-2" onclick="goToStep(1)"><i class="fas fa-arrow-left"></i> ย้อนกลับ</button>
                    <button class="btn btn-install" onclick="testConnection()">
                        <i class="fas fa-plug"></i> ทดสอบการเชื่อมต่อ
                    </button>
                </div>
                <div id="conn-result" class="mt-3"></div>
                <div class="text-center mt-3" id="btn-step2-next" style="display:none;">
                    <button class="btn btn-install" onclick="goToStep(3)">
                        <i class="fas fa-arrow-right"></i> ถัดไป
                    </button>
                </div>
            </div>

            <!-- Step 3: Create Tables -->
            <div class="step-panel" id="step3">
                <h6 class="mb-3"><i class="fas fa-table"></i> สร้างฐานข้อมูลและตาราง</h6>
                <p class="text-muted" style="font-size:0.85rem;">ระบบจะสร้างฐานข้อมูลและ导入ตารางทั้งหมดให้อัตโนมัติ</p>
                <div class="text-center">
                    <button class="btn btn-install btn-lg" id="btn-create-db" onclick="startInstall()">
                        <i class="fas fa-play-circle"></i> เริ่มการติดตั้ง
                    </button>
                </div>
                <div class="install-progress mt-4" id="install-progress" style="display:none;">
                    <div class="d-flex justify-content-between mb-1">
                        <small id="progress-text">กำลังเตรียม...</small>
                        <small id="progress-pct">0%</small>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" id="progress-bar" role="progressbar" style="width:0%"></div>
                    </div>
                </div>
                <div class="log-area mt-3" id="install-log" style="display:none;"></div>
                <div class="text-center mt-4" id="btn-step3-next" style="display:none;">
                    <button class="btn btn-install" onclick="goToStep(4)">
                        <i class="fas fa-arrow-right"></i> ถัดไป
                    </button>
                </div>
            </div>

            <!-- Step 4: Admin Account -->
            <div class="step-panel" id="step4">
                <h6 class="mb-3"><i class="fas fa-user-shield"></i> สร้างบัญชีผู้ดูแลระบบ</h6>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" id="admin_user" value="admin">
                </div>
                <div class="form-group">
                    <label>รหัสผ่าน</label>
                    <input type="password" class="form-control" id="admin_pass" placeholder="รหัสผ่าน">
                </div>
                <div class="form-group">
                    <label>ยืนยันรหัสผ่าน</label>
                    <input type="password" class="form-control" id="admin_pass2" placeholder="ยืนยันรหัสผ่าน">
                </div>
                <div class="form-group">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" class="form-control" id="admin_name" value="ผู้ดูแลระบบ">
                </div>
                <div class="form-group">
                    <label>ชื่อระบบ</label>
                    <input type="text" class="form-control" id="system_name" value="ระบบบริหารงานนิติบุคคลหมู่บ้าน พฤกษา 33">
                </div>
                <div id="admin-result" class="mt-3"></div>
                <div class="text-center mt-3">
                    <button class="btn btn-back me-2" onclick="goToStep(3)"><i class="fas fa-arrow-left"></i> ย้อนกลับ</button>
                    <button class="btn btn-install" onclick="createAdmin()">
                        <i class="fas fa-save"></i> บันทึกและติดตั้ง
                    </button>
                </div>
            </div>

            <!-- Step 5: Complete -->
            <div class="step-panel" id="step5">
                <div class="text-center">
                    <div class="complete-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="complete-title">ติดตั้งระบบสำเร็จ!</div>
                    <p class="text-muted mt-3">ระบบได้รับการติดตั้งเรียบร้อยแล้ว คุณสามารถเข้าสู่ระบบได้ทันที</p>
                    <div class="mt-4">
                        <a href="login" class="btn btn-install btn-lg">
                            <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
var currentStep = 1;

function goToStep(step) {
    if (step < 1 || step > 5) return;
    currentStep = step;
    $('.step-panel').removeClass('active');
    $('#step' + step).addClass('active');
    $('.step-item').each(function() {
        var s = parseInt($(this).data('step'));
        $(this).removeClass('active done');
        if (s === step) $(this).addClass('active');
        else if (s < step) $(this).addClass('done');
    });
}

function checkRequirements() {
    $.ajax({
        url: 'install_process.php',
        type: 'POST',
        data: { action: 'check_requirements' },
        dataType: 'json',
        success: function(data) {
            var html = '';
            var allPass = true;
            $.each(data, function(i, item) {
                var cls = item.pass ? 'pass' : 'fail';
                if (!item.pass) allPass = false;
                html += '<div class="req-item ' + cls + '">';
                html += '<span class="req-icon"><i class="fas ' + (item.pass ? 'fa-check-circle' : 'fa-times-circle') + '"></i></span>';
                html += '<span class="req-text"><strong>' + item.name + '</strong> — ' + item.detail + '</span>';
                html += '</div>';
            });
            $('#requirements-list').html(html);
            if (allPass) {
                $('#btn-next-step1').prop('disabled', false);
            }
        },
        error: function() {
            $('#requirements-list').html('<div class="req-item fail"><span class="req-icon"><i class="fas fa-times-circle"></i></span><span class="req-text">ไม่สามารถตรวจสอบได้ กรุณาลองใหม่</span></div>');
        }
    });
}

function testConnection() {
    var data = {
        action: 'test_connection',
        host: $('#db_host').val(),
        port: $('#db_port').val(),
        user: $('#db_user').val(),
        pass: $('#db_pass').val(),
        dbname: $('#db_name').val()
    };
    $('#conn-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> กำลังทดสอบ...</div>');
    $.ajax({
        url: 'install_process.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#conn-result').html('<div class="alert alert-success py-2"><i class="fas fa-check-circle"></i> ' + res.message + '</div>');
                $('#btn-step2-next').show();
            } else {
                $('#conn-result').html('<div class="alert alert-danger py-2"><i class="fas fa-exclamation-circle"></i> ' + res.message + '</div>');
                $('#btn-step2-next').hide();
            }
        },
        error: function() {
            $('#conn-result').html('<div class="alert alert-danger py-2">เกิดข้อผิดพลาดในการเชื่อมต่อ</div>');
        }
    });
}

function startInstall() {
    $('#btn-create-db').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> กำลังติดตั้ง...');
    $('#install-progress').show();
    $('#install-log').show();
    $('#install-log').html('');

    var config = {
        action: 'install',
        host: $('#db_host').val(),
        port: $('#db_port').val(),
        user: $('#db_user').val(),
        pass: $('#db_pass').val(),
        dbname: $('#db_name').val()
    };

    addLog('info', 'เริ่มกระบวนการติดตั้ง...');
    addLog('info', 'ฐานข้อมูล: ' + config.dbname + ' @ ' + config.host + ':' + config.port);

    $.ajax({
        url: 'install_process.php',
        type: 'POST',
        data: config,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $.each(res.logs, function(i, log) {
                    setTimeout(function() { addLog(log.type, log.msg); }, i * 80);
                });
                updateProgress(100, 'ติดตั้งสำเร็จ!');
                addLog('success', '=== การติดตั้งฐานข้อมูลเสร็จสิ้น ===');
                $('#btn-step3-next').show();
            } else {
                addLog('error', res.message);
                if (res.logs) {
                    $.each(res.logs, function(i, log) {
                        addLog(log.type, log.msg);
                    });
                }
                updateProgress(0, 'เกิดข้อผิดพลาด');
                $('#btn-create-db').prop('disabled', false).html('<i class="fas fa-redo"></i> ลองใหม่');
            }
        },
        error: function(xhr) {
            addLog('error', 'เกิดข้อผิดพลาด: ' + xhr.responseText);
            updateProgress(0, 'เกิดข้อผิดพลาด');
            $('#btn-create-db').prop('disabled', false).html('<i class="fas fa-redo"></i> ลองใหม่');
        }
    });
}

function createAdmin() {
    var pass = $('#admin_pass').val();
    var pass2 = $('#admin_pass2').val();
    if (pass !== pass2) {
        $('#admin-result').html('<div class="alert alert-danger py-2">รหัสผ่านไม่ตรงกัน</div>');
        return;
    }
    $('#admin-result').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...</div>');
    $.ajax({
        url: 'install_process.php',
        type: 'POST',
        data: {
            action: 'create_admin',
            host: $('#db_host').val(),
            port: $('#db_port').val(),
            user: $('#db_user').val(),
            pass: $('#db_pass').val(),
            dbname: $('#db_name').val(),
            admin_user: $('#admin_user').val(),
            admin_pass: pass,
            admin_name: $('#admin_name').val(),
            system_name: $('#system_name').val()
        },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                goToStep(5);
            } else {
                $('#admin-result').html('<div class="alert alert-danger py-2"><i class="fas fa-exclamation-circle"></i> ' + res.message + '</div>');
            }
        },
        error: function() {
            $('#admin-result').html('<div class="alert alert-danger py-2">เกิดข้อผิดพลาด กรุณาลองใหม่</div>');
        }
    });
}

function addLog(type, msg) {
    var time = new Date().toLocaleTimeString('th-TH');
    var prefix = '';
    if (type === 'info') prefix = '[INFO] ';
    else if (type === 'success') prefix = '[OK] ';
    else if (type === 'error') prefix = '[ERR] ';
    else if (type === 'warn') prefix = '[WARN] ';
    var line = '<div class="log-line ' + type + '">' + time + ' ' + prefix + msg + '</div>';
    var el = document.getElementById('install-log');
    el.innerHTML += line;
    el.scrollTop = el.scrollHeight;
}

function updateProgress(pct, text) {
    $('#progress-bar').css('width', pct + '%');
    $('#progress-pct').text(pct + '%');
    if (text) $('#progress-text').text(text);
}

$(document).ready(function() {
    checkRequirements();
});
</script>
</body>
</html>
