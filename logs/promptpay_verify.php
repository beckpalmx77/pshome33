<?php
session_start();
error_reporting(0);
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!doctype html>
    <html>
    <head lang="en">
        <meta charset="utf-8">
        <title>PromptPay Verification</title>
        <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
        <style>
            #drop-zone, #qr-drop-zone {
                border: 2px dashed #ccc;
                border-radius: 8px;
                padding: 40px;
                text-align: center;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            #drop-zone.dragover, #qr-drop-zone.dragover {
                background-color: #e9f5ff;
                border-color: #007bff;
            }
            #preview, #qr-preview {
                max-width: 100%;
                max-height: 250px;
                margin-top: 15px;
                display: none;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            #progress, #qr-progress {
                display: none;
                margin-top: 15px;
            }
            .verification-card {
                background: #fff;
                border-radius: 12px;
                padding: 20px;
                margin-top: 20px;
                box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            }
            .verification-item {
                display: flex;
                justify-content: space-between;
                padding: 12px 0;
                border-bottom: 1px solid #eee;
            }
            .verification-item:last-child {
                border-bottom: none;
            }
            .verification-label {
                color: #6c757d;
                font-size: 14px;
            }
            .verification-value {
                font-weight: 600;
                color: #212529;
            }
            .verification-value.match {
                color: #28a745;
            }
            .verification-value.mismatch {
                color: #dc3545;
            }
            .status-badge {
                display: inline-block;
                padding: 8px 20px;
                border-radius: 50px;
                font-weight: 600;
                font-size: 16px;
            }
            .status-badge.verified {
                background: #d4edda;
                color: #155724;
            }
            .status-badge.unverified {
                background: #fff3cd;
                color: #856404;
            }
            .status-badge.invalid {
                background: #f8d7da;
                color: #721c24;
            }
            .input-group-verify {
                display: flex;
                gap: 10px;
                margin-top: 15px;
            }
            .input-group-verify input {
                flex: 1;
            }
            canvas#qr-canvas {
                display: none;
            }
        </style>
    </head>
    <body>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">PromptPay Verification</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">PromptPay</li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">1. Upload Slip หรือ QR Code</h6>
                            </div>
                            <div class="card-body">
                                <div id="drop-zone">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-secondary mb-3"></i>
                                    <p class="mb-2">ลากไฟล์ Slip/QR มาวางที่นี่</p>
                                    <p class="text-muted small">รองรับ PNG, JPG, WEBP</p>
                                    <input type="file" id="fileInput" accept="image/*" style="display: none;">
                                </div>
                                
                                <img id="preview" alt="Preview">
                                <canvas id="qr-canvas" style="display: none;"></canvas>
                                
                                <div id="progress">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar"></div>
                                    </div>
                                    <p class="mt-2 text-center" id="progressText">กำลังประมวลผล...</p>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary" id="btnVerify" disabled>
                                        <i class="fas fa-shield-alt mr-1"></i> ตรวจสอบ
                                    </button>
                                    <button type="button" class="btn btn-secondary ml-2" id="btnClear">
                                        <i class="fas fa-trash mr-1"></i> ล้าง
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">2. ผลการตรวจสอบ</h6>
                            </div>
                            <div class="card-body">
                                <div id="result-placeholder" class="text-center text-muted py-5">
                                    <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                                    <p>อัพโหลด Slip เพื่อตรวจสอบ</p>
                                </div>
                                <div id="result-content" style="display: none;">
                                    <div class="text-center mb-3">
                                        <span id="statusBadge" class="status-badge"></span>
                                    </div>
                                    <div id="verification-details"></div>
                                    <div class="mt-3 text-center">
                                        <button type="button" class="btn btn-success" id="btnConfirm">
                                            <i class="fas fa-check mr-1"></i> ยืนยันการชำระเงิน
                                        </button>
                                        <button type="button" class="btn btn-danger ml-2" id="btnReject">
                                            <i class="fas fa-times mr-1"></i> ปฏิเสธ
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('fileInput');
        const preview = document.getElementById('preview');
        const qrCanvas = document.getElementById('qr-canvas');
        const btnVerify = document.getElementById('btnVerify');
        const btnClear = document.getElementById('btnClear');
        const progressDiv = document.getElementById('progress');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const resultPlaceholder = document.getElementById('result-placeholder');
        const resultContent = document.getElementById('result-content');
        const statusBadge = document.getElementById('statusBadge');
        const verificationDetails = document.getElementById('verification-details');

        let selectedFile = null;
        let extractedData = null;

        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('dragover'); });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) handleFile(e.dataTransfer.files[0]);
        });
        fileInput.addEventListener('change', (e) => { if (e.target.files.length > 0) handleFile(e.target.files[0]); });

        function handleFile(file) {
            if (!file.type.startsWith('image/')) { alertify.error('กรุณาเลือกไฟล์รูปภาพ'); return; }
            selectedFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.style.display = 'block';
                btnVerify.disabled = false;
                resultPlaceholder.style.display = 'block';
                resultContent.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        btnVerify.addEventListener('click', async () => {
            if (!selectedFile) return;
            progressDiv.style.display = 'block';
            progressBar.style.width = '0%';
            progressText.textContent = 'กำลังสแกน QR Code...';
            btnVerify.disabled = true;

            try {
                const img = new Image();
                img.onload = async () => {
                    qrCanvas.width = img.width;
                    qrCanvas.height = img.height;
                    const ctx = qrCanvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    const imageData = ctx.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
                    const qrCode = jsQR(imageData.data, imageData.width, imageData.height);

                    if (qrCode) {
                        progressText.textContent = 'กำลังถอดรหัส PromptPay...';
                        progressBar.style.width = '50%';
                        const ppData = parsePromptPayQR(qrCode.data);
                        if (ppData) {
                            progressBar.style.width = '100%';
                            progressText.textContent = 'เสร็จสิ้น';
                            extractedData = ppData;
                            showVerificationResult(ppData);
                        } else {
                            progressText.textContent = 'กำลังใช้ OCR อ่านข้อมูล...';
                            progressBar.style.width = '50%';
                            const ocrData = await runOCR(img);
                            extractedData = ocrData;
                            showVerificationResult(ocrData);
                        }
                    } else {
                        progressText.textContent = 'กำลังใช้ OCR อ่านข้อมูล...';
                        const ocrData = await runOCR(img);
                        extractedData = ocrData;
                        showVerificationResult(ocrData);
                    }
                };
                img.src = URL.createObjectURL(selectedFile);
            } catch (error) {
                console.error(error);
                alertify.error('เกิดข้อผิดพลาด');
                progressDiv.style.display = 'none';
            }
            btnVerify.disabled = false;
        });

        function parsePromptPayQR(data) {
            if (!data || !data.startsWith('0002')) return null;
            const result = { type: '', id: '', name: '', amount: 0 };
            const payload = data.slice(4);
            let pos = 0;
            while (pos < payload.length) {
                if (pos + 4 > payload.length) break;
                const tag = payload.slice(pos, pos + 2);
                const len = parseInt(payload.slice(pos + 2, pos + 4));
                const value = payload.slice(pos + 4, pos + 4 + len);
                pos += 4 + len;

                switch (tag) {
                    case '00': result.type = value; break;
                    case '01': result.id = value; break;
                    case '02': result.name = value; break;
                    case '03': result.id = value; break;
                    case '04': result.name = value; break;
                    case '29': 
                        if (value.length >= 2) {
                            result.amount = parseFloat(value.slice(2)) / 100;
                        }
                        break;
                }
            }
            if (result.id || result.name) {
                return result;
            }
            return null;
        }

        async function runOCR(img) {
            const worker = await Tesseract.createWorker('tha+eng', 1, {
                logger: (m) => {
                    if (m.status === 'recognizing text') {
                        progressBar.style.width = Math.round(m.progress * 100) + '%';
                    }
                }
            });
            const { data: { text } } = await worker.recognize(img);
            await worker.terminate();
            return parseOCRResult(text);
        }

        function parseOCRResult(text) {
            const result = { type: 'ocr', phone: '', idcard: '', amount: '', account: '', name: '' };
            const lines = text.split('\n').map(l => l.trim()).filter(l => l);
            
            const phoneMatch = text.match(/0[0-9]{9}/g);
            const idMatch = text.match(/[0-9]{13}/g);
            const amountMatch = text.match(/(?:ยอดเงิน|จำนวนเงิน|amount)[:\s]*([0-9,]+\.?[0-9]*)/i);
            const nameMatch = text.match(/(?:ชื่อ|name)[:\s]*([ก-๙a-zA-Z\s]+)/i);

            if (phoneMatch) result.phone = phoneMatch[0];
            if (idMatch) result.idcard = idMatch[0];
            if (amountMatch) result.amount = amountMatch[1];
            if (nameMatch) result.name = nameMatch[1].trim();

            return result;
        }

        function showVerificationResult(data) {
            setTimeout(() => {
                progressDiv.style.display = 'none';
                resultPlaceholder.style.display = 'none';
                resultContent.style.display = 'block';

                let html = '';
                if (data.type === 'promptpay') {
                    statusBadge.className = 'status-badge verified';
                    statusBadge.textContent = '✓ พบ PromptPay QR';
                    
                    html = `
                        <div class="verification-item">
                            <span class="verification-label">ประเภท</span>
                            <span class="verification-value">${data.id.length === 10 ? 'เบอร์โทรศัพท์' : 'บัตรประชาชน'}</span>
                        </div>
                        <div class="verification-item">
                            <span class="verification-label">${data.id.length === 10 ? 'เบอร์โทรศัพท์' : 'เลขบัตรประชาชน'}</span>
                            <span class="verification-value">${data.id}</span>
                        </div>
                        ${data.amount ? `
                        <div class="verification-item">
                            <span class="verification-label">จำนวนเงิน</span>
                            <span class="verification-value">฿${data.amount.toLocaleString()}</span>
                        </div>` : ''}
                    `;
                    alertify.success('พบข้อมูล PromptPay');
                } else {
                    statusBadge.className = 'status-badge unverified';
                    statusBadge.textContent = '⚠ ใช้ OCR';
                    
                    if (data.phone) {
                        html += `
                        <div class="verification-item">
                            <span class="verification-label">เบอร์โทรศัพท์</span>
                            <span class="verification-value">${data.phone}</span>
                        </div>`;
                    }
                    if (data.idcard) {
                        html += `
                        <div class="verification-item">
                            <span class="verification-label">เลขบัตรประชาชน</span>
                            <span class="verification-value">${data.idcard}</span>
                        </div>`;
                    }
                    if (data.amount) {
                        html += `
                        <div class="verification-item">
                            <span class="verification-label">จำนวนเงิน</span>
                            <span class="verification-value">฿${data.amount}</span>
                        </div>`;
                    }
                    if (!html) {
                        html = '<p class="text-center text-muted">ไม่สามารถอ่านข้อมูลได้ กรุณาลองใหม่อีกครั้ง</p>';
                        statusBadge.className = 'status-badge invalid';
                        statusBadge.textContent = '✗ ไม่พบข้อมูล';
                    }
                    alertify.warning('อ่านข้อมูลจาก OCR');
                }
                verificationDetails.innerHTML = html;
            }, 300);
        }

        btnClear.addEventListener('click', () => {
            selectedFile = null;
            fileInput.value = '';
            preview.src = '';
            preview.style.display = 'none';
            btnVerify.disabled = true;
            resultPlaceholder.style.display = 'block';
            resultContent.style.display = 'none';
            progressDiv.style.display = 'none';
            extractedData = null;
        });

        document.getElementById('btnConfirm').addEventListener('click', () => {
            alertify.success('ยืนยันการชำระเงินแล้ว');
        });

        document.getElementById('btnReject').addEventListener('click', () => {
            alertify.error('ปฏิเสธการชำระเงิน');
        });
    </script>
    </body>
    </html>
    <?php include('includes/Footer.php'); } ?>