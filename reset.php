<?php
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รีเซ็ตระบบ - PS33 Home System</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Prompt', sans-serif; }
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-container { width: 100%; max-width: 700px; }
        .reset-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .reset-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: #fff;
            padding: 30px 40px;
            text-align: center;
        }
        .reset-header h1 { font-size: 1.5rem; font-weight: 600; margin: 0 0 5px 0; }
        .reset-header p { font-size: 0.9rem; opacity: 0.85; margin: 0; }
        .reset-body { padding: 30px 40px 40px; }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .warning-box h5 { color: #856404; font-weight: 600; margin-bottom: 10px; }
        .warning-box ul { margin: 0; padding-left: 20px; color: #856404; font-size: 0.85rem; }
        .warning-box ul li { margin-bottom: 5px; }

        .danger-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .danger-box h5 { color: #721c24; font-weight: 600; margin-bottom: 10px; }
        .danger-box p { color: #721c24; font-size: 0.85rem; margin: 0; }

        .backup-option {
            background: #e8f5e9;
            border: 2px solid #4caf50;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .backup-option:hover { background: #c8e6c9; }
        .backup-option.selected { background: #a5d6a7; border-color: #2e7d32; }
        .backup-option h6 { color: #2e7d32; margin-bottom: 5px; }
        .backup-option p { color: #333; font-size: 0.85rem; margin: 0; }

        .form-group label { font-weight: 500; font-size: 0.9rem; color: #333; margin-bottom: 6px; }
        .form-group .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 14px;
            font-size: 0.9rem;
        }
        .form-group .form-control:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220,53,69,0.15);
        }

        .btn-reset {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
            color: #fff;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        .btn-reset:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(220,53,69,0.4); color: #fff; }
        .btn-reset:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-back {
            background: #6c757d;
            border: none;
            color: #fff;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-back:hover { background: #5a6268; color: #fff; }

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

        .progress { height: 8px; border-radius: 10px; background: #e9ecef; }
        .progress-bar { border-radius: 10px; background: linear-gradient(90deg, #dc3545, #ff6b6b); transition: width 0.5s ease; }

        .complete-icon { font-size: 4rem; color: #28a745; margin-bottom: 15px; }
        .complete-title { font-size: 1.4rem; font-weight: 600; color: #28a745; }

        .step-panel { display: none; }
        .step-panel.active { display: block; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .confirm-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .confirm-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #dc3545;
        }
        .confirm-checkbox label { margin: 0; font-size: 0.9rem; cursor: pointer; }
    </style>
</head>
<body>
<div class="reset-container">
    <div class="reset-card">
        <div class="reset-header">
            <h1><i class="fas fa-exclamation-triangle"></i> รีเซ็ตและติดตั้งระบบใหม่</h1>
            <p>PS33 Home System - ระบบบริหารงานนิติบุคคลหมู่บ้าน</p>
        </div>
        <div class="reset-body">

            <!-- Step 1: Confirm Reset -->
            <div class="step-panel active" id="reset-step1">
                <div class="warning-box">
                    <h5><i class="fas fa-exclamation-triangle"></i> คำเตือน</h5>
                    <ul>
                        <li>การรีเซ็ตจะ<strong>ลบทุกข้อมูล</strong>ในฐานข้อมูลปัจจุบัน</li>
                        <li>ข้อมูลทั้งหมดจะถูก<strong>สำรองไว้</strong>ก่อนรีเซ็ต (ถ้าเลือก)</li>
                        <li>หลังรีเซ็ต ระบบจะกลับสู่ขั้นตอนการติดตั้ง</li>
                        <li><strong>ไม่สามารถย้อนกลับได้</strong>หลังทำการรีเซ็ตแล้ว</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label>MySQL Host</label>
                    <input type="text" class="form-control" id="r_db_host" value="localhost">
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>MySQL Username</label>
                            <input type="text" class="form-control" id="r_db_user" placeholder="root">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Port</label>
                            <input type="text" class="form-control" id="r_db_port" value="3306">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>MySQL Password</label>
                    <input type="password" class="form-control" id="r_db_pass" placeholder="รหัสผ่าน">
                </div>
                <div class="form-group">
                    <label>ชื่อฐานข้อมูล</label>
                    <input type="text" class="form-control" id="r_db_name" value="house_dbs">
                </div>

                <div class="backup-option selected" id="backup-option" onclick="toggleBackup()">
                    <h6><i class="fas fa-shield-alt"></i> สำรองข้อมูลก่อนรีเซ็ต (แนะนำ)</h6>
                    <p>สร้างไฟล์ SQL backup อัตโนมัติเก็บไว้ในโฟลเดอร์ backups/ ก่อนดำเนินการรีเซ็ต</p>
                </div>

                <div class="confirm-checkbox">
                    <input type="checkbox" id="confirm-reset">
                    <label for="confirm-reset">ฉันเข้าใจว่าการรีเซ็ตจะ<strong>ลบข้อมูลทั้งหมด</strong>และไม่สามารถย้อนกลับได้</label>
                </div>

                <div id="reset-step1-result"></div>

                <div class="text-center">
                    <a href="install.php" class="btn btn-back me-2"><i class="fas fa-arrow-left"></i> ย้อนกลับ</a>
                    <button class="btn btn-reset" id="btn-reset" onclick="startReset()" disabled>
                        <i class="fas fa-trash-alt"></i> รีเซ็ตและติดตั้งใหม่
                    </button>
                </div>
            </div>

            <!-- Step 2: Reset Progress -->
            <div class="step-panel" id="reset-step2">
                <h6 class="mb-3"><i class="fas fa-sync-alt"></i> กำลังรีเซ็ตระบบ...</h6>
                <div class="d-flex justify-content-between mb-1">
                    <small id="reset-progress-text">กำลังเตรียม...</small>
                    <small id="reset-progress-pct">0%</small>
                </div>
                <div class="progress">
                    <div class="progress-bar" id="reset-progress-bar" role="progressbar" style="width:0%"></div>
                </div>
                <div class="log-area mt-3" id="reset-log"></div>
            </div>

            <!-- Step 3: Complete -->
            <div class="step-panel" id="reset-step3">
                <div class="text-center">
                    <div class="complete-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="complete-title">รีเซ็ตระบบสำเร็จ!</div>
                    <p class="text-muted mt-3">ระบบได้รับการรีเซ็ตและพร้อมสำหรับการติดตั้งใหม่</p>
                    <div class="mt-4">
                        <a href="install.php" class="btn btn-reset btn-lg">
                            <i class="fas fa-cogs"></i> ติดตั้งระบบใหม่
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
var backupEnabled = true;

$('#confirm-reset').on('change', function() {
    $('#btn-reset').prop('disabled', !this.checked);
});

function toggleBackup() {
    backupEnabled = !backupEnabled;
    $('#backup-option').toggleClass('selected', backupEnabled);
}

function addResetLog(type, msg) {
    var time = new Date().toLocaleTimeString('th-TH');
    var prefix = '';
    if (type === 'info') prefix = '[INFO] ';
    else if (type === 'success') prefix = '[OK] ';
    else if (type === 'error') prefix = '[ERR] ';
    else if (type === 'warn') prefix = '[WARN] ';
    var line = '<div class="log-line ' + type + '">' + time + ' ' + prefix + msg + '</div>';
    var el = document.getElementById('reset-log');
    el.innerHTML += line;
    el.scrollTop = el.scrollHeight;
}

function updateResetProgress(pct, text) {
    $('#reset-progress-bar').css('width', pct + '%');
    $('#reset-progress-pct').text(pct + '%');
    if (text) $('#reset-progress-text').text(text);
}

function startReset() {
    if (!$('#confirm-reset').is(':checked')) return;

    if (!confirm('คุณแน่ใจหรือไม่ที่จะรีเซ็ตระบบทั้งหมด? ข้อมูลจะถูกลบและไม่สามารถย้อนกลับได้')) {
        return;
    }

    $('#reset-step1').removeClass('active');
    $('#reset-step2').addClass('active');
    $('#reset-log').html('');

    addResetLog('info', 'เริ่มกระบวนการรีเซ็ตระบบ...');

    var config = {
        action: 'backup',
        host: $('#r_db_host').val(),
        port: $('#r_db_port').val(),
        user: $('#r_db_user').val(),
        pass: $('#r_db_pass').val(),
        dbname: $('#r_db_name').val(),
        backup: backupEnabled ? '1' : '0'
    };

    updateResetProgress(10, 'กำลังสำรองข้อมูล...');
    addResetLog('info', 'ตรวจสอบการเชื่อมต่อฐานข้อมูล...');

    $.ajax({
        url: 'reset_process.php',
        type: 'POST',
        data: config,
        dataType: 'json',
        success: function(res) {
            if (res.backup_success) {
                $.each(res.backup_logs || [], function(i, log) {
                    addResetLog(log.type, log.msg);
                });
                updateResetProgress(40, 'สำรองข้อมูลเสร็จสิ้น กำลังรีเซ็ต...');
                addResetLog('info', 'ดำเนินการรีเซ็ตฐานข้อมูล...');
                runReset(config);
            } else if (res.backup_success === false) {
                $.each(res.backup_logs || [], function(i, log) {
                    addResetLog(log.type, log.msg);
                });
                if (!backupEnabled) {
                    updateResetProgress(40, 'ข้ามการสำรอง กำลังรีเซ็ต...');
                    runReset(config);
                } else {
                    updateResetProgress(0, 'เกิดข้อผิดพลาด');
                    addResetLog('error', 'ไม่สามารถสำรองข้อมูลได้');
                }
            } else {
                $.each(res.logs || [], function(i, log) {
                    addResetLog(log.type, log.msg);
                });
                updateResetProgress(0, 'เกิดข้อผิดพลาด');
            }
        },
        error: function(xhr) {
            addResetLog('error', 'เกิดข้อผิดพลาด: ' + xhr.responseText);
            updateResetProgress(0, 'เกิดข้อผิดพลาด');
        }
    });
}

function runReset(config) {
    config.action = 'reset';
    $.ajax({
        url: 'reset_process.php',
        type: 'POST',
        data: config,
        dataType: 'json',
        success: function(res) {
            $.each(res.logs || [], function(i, log) {
                addResetLog(log.type, log.msg);
            });
            if (res.success) {
                updateResetProgress(100, 'รีเซ็ตสำเร็จ!');
                addResetLog('success', '=== การรีเซ็ตเสร็จสิ้น ===');
                setTimeout(function() {
                    $('#reset-step2').removeClass('active');
                    $('#reset-step3').addClass('active');
                }, 1000);
            } else {
                updateResetProgress(0, 'เกิดข้อผิดพลาด');
            }
        },
        error: function(xhr) {
            addResetLog('error', 'เกิดข้อผิดพลาด: ' + xhr.responseText);
            updateResetProgress(0, 'เกิดข้อผิดพลาด');
        }
    });
}
</script>
</body>
</html>
