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
        <title>OCR & QR Reader</title>
        <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
        <style>
            #drop-zone {
                border: 2px dashed #ccc;
                border-radius: 8px;
                padding: 40px;
                text-align: center;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            #drop-zone.dragover {
                background-color: #e9f5ff;
                border-color: #007bff;
            }
            #preview {
                max-width: 100%;
                max-height: 300px;
                margin-top: 20px;
                display: none;
            }
            #progress {
                display: none;
                margin-top: 20px;
            }
            #result, #qr-result {
                margin-top: 20px;
                white-space: pre-wrap;
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                border: 1px solid #dee2e6;
            }
            .btn-group-tabs {
                display: flex;
                gap: 5px;
                margin-bottom: 20px;
            }
            .btn-group-tabs .btn {
                flex: 1;
            }
            .btn-group-tabs .btn.active {
                background-color: #007bff;
                color: white;
            }
            .tab-content {
                display: none;
            }
            .tab-content.active {
                display: block;
            }
        </style>
    </head>
    <body>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">OCR & QR Reader</h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">OCR/QR</li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Upload Image</h6>
                            </div>
                            <div class="card-body">
                                <div class="btn-group-tabs">
                                    <button type="button" class="btn btn-outline-primary active" data-tab="ocr">
                                        <i class="fas fa-font mr-1"></i> OCR (Text)
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" data-tab="qr">
                                        <i class="fas fa-qrcode mr-1"></i> QR Code
                                    </button>
                                </div>

                                <div id="tab-ocr" class="tab-content active">
                                    <div id="drop-zone">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-secondary mb-3"></i>
                                        <p class="mb-2">ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</p>
                                        <p class="text-muted small">รองรับไฟล์ PNG, JPG, JPEG, BMP, WEBP</p>
                                        <input type="file" id="fileInput" accept="image/*" style="display: none;">
                                    </div>
                                    
                                    <img id="preview" alt="Preview">
                                    
                                    <div id="progress">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 0%" id="progressBar"></div>
                                        </div>
                                        <p class="mt-2 text-center" id="progressText">กำลังประมวลผล...</p>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary" id="btnOCR" disabled>
                                            <i class="fas fa-search mr-1"></i> อ่านข้อความ
                                        </button>
                                        <button type="button" class="btn btn-secondary ml-2" id="btnClear">
                                            <i class="fas fa-trash mr-1"></i> ล้าง
                                        </button>
                                        <button type="button" class="btn btn-success ml-2" id="btnCopy" style="display: none;">
                                            <i class="fas fa-copy mr-1"></i> คัดลอกข้อความ
                                        </button>
                                    </div>
                                    
                                    <div id="result" style="display: none;"></div>
                                </div>

                                <div id="tab-qr" class="tab-content">
                                    <div id="qr-drop-zone">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-secondary mb-3"></i>
                                        <p class="mb-2">ลากไฟล์ QR Code มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</p>
                                        <p class="text-muted small">รองรับไฟล์ PNG, JPG, JPEG, BMP, WEBP</p>
                                        <input type="file" id="qrFileInput" accept="image/*" style="display: none;">
                                    </div>
                                    
                                    <img id="qr-preview" alt="QR Preview" style="max-width: 100%; max-height: 300px; margin-top: 20px; display: none;">
                                    <canvas id="qr-canvas" style="display: none;"></canvas>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary" id="btnQR" disabled>
                                            <i class="fas fa-qrcode mr-1"></i> อ่าน QR Code
                                        </button>
                                        <button type="button" class="btn btn-secondary ml-2" id="btnQRClear">
                                            <i class="fas fa-trash mr-1"></i> ล้าง
                                        </button>
                                        <button type="button" class="btn btn-success ml-2" id="btnQRCopy" style="display: none;">
                                            <i class="fas fa-copy mr-1"></i> คัดลอก
                                        </button>
                                    </div>
                                    
                                    <div id="qr-result" style="display: none;"></div>
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
        const btnOCR = document.getElementById('btnOCR');
        const btnClear = document.getElementById('btnClear');
        const btnCopy = document.getElementById('btnCopy');
        const progressDiv = document.getElementById('progress');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const resultDiv = document.getElementById('result');

        const qrDropZone = document.getElementById('qr-drop-zone');
        const qrFileInput = document.getElementById('qrFileInput');
        const qrPreview = document.getElementById('qr-preview');
        const qrCanvas = document.getElementById('qr-canvas');
        const btnQR = document.getElementById('btnQR');
        const btnQRClear = document.getElementById('btnQRClear');
        const btnQRCopy = document.getElementById('btnQRCopy');
        const qrResultDiv = document.getElementById('qr-result');

        let selectedFile = null;
        let qrSelectedFile = null;
        let ocrResult = '';
        let qrResultData = '';

        document.querySelectorAll('.btn-group-tabs .btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.btn-group-tabs .btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
            });
        });

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
                btnOCR.disabled = false;
                resultDiv.style.display = 'none';
                btnCopy.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        btnOCR.addEventListener('click', async () => {
            if (!selectedFile) return;
            progressDiv.style.display = 'block';
            progressBar.style.width = '0%';
            progressText.textContent = 'กำลังโหลดโมเดล...';
            btnOCR.disabled = true;

            try {
                const worker = await Tesseract.createWorker('tha+eng', 1, {
                    logger: (m) => {
                        if (m.status === 'recognizing text') {
                            progressBar.style.width = Math.round(m.progress * 100) + '%';
                            progressText.textContent = `กำลังอ่านข้อความ... ${Math.round(m.progress * 100)}%`;
                        }
                    }
                });
                const { data: { text } } = await worker.recognize(selectedFile);
                await worker.terminate();

                ocrResult = text;
                resultDiv.textContent = text;
                resultDiv.style.display = 'block';
                btnCopy.style.display = 'inline-block';
                progressDiv.style.display = 'none';
                alertify.success('อ่านข้อความเสร็จสิ้น');
            } catch (error) {
                console.error(error);
                alertify.error('เกิดข้อผิดพลาดในการอ่านข้อความ');
                progressDiv.style.display = 'none';
            }
            btnOCR.disabled = false;
        });

        btnClear.addEventListener('click', () => {
            selectedFile = null;
            fileInput.value = '';
            preview.src = '';
            preview.style.display = 'none';
            btnOCR.disabled = true;
            resultDiv.style.display = 'none';
            btnCopy.style.display = 'none';
            progressDiv.style.display = 'none';
            ocrResult = '';
        });

        btnCopy.addEventListener('click', () => {
            navigator.clipboard.writeText(ocrResult).then(() => alertify.success('คัดลอกข้อความแล้ว'))
                .catch(() => alertify.error('ไม่สามารถคัดลอกข้อความได้'));
        });

        qrDropZone.addEventListener('click', () => qrFileInput.click());
        qrDropZone.addEventListener('dragover', (e) => { e.preventDefault(); qrDropZone.classList.add('dragover'); });
        qrDropZone.addEventListener('dragleave', () => { qrDropZone.classList.remove('dragover'); });
        qrDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            qrDropZone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) handleQRFile(e.dataTransfer.files[0]);
        });
        qrFileInput.addEventListener('change', (e) => { if (e.target.files.length > 0) handleQRFile(e.target.files[0]); });

        function handleQRFile(file) {
            if (!file.type.startsWith('image/')) { alertify.error('กรุณาเลือกไฟล์รูปภาพ'); return; }
            qrSelectedFile = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                qrPreview.src = e.target.result;
                qrPreview.style.display = 'block';
                btnQR.disabled = false;
                qrResultDiv.style.display = 'none';
                btnQRCopy.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        btnQR.addEventListener('click', () => {
            if (!qrSelectedFile) return;
            btnQR.disabled = true;

            const img = new Image();
            img.onload = () => {
                qrCanvas.width = img.width;
                qrCanvas.height = img.height;
                const ctx = qrCanvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
                const imageData = ctx.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code) {
                    qrResultData = code.data;
                    qrResultDiv.textContent = qrResultData;
                    qrResultDiv.style.display = 'block';
                    btnQRCopy.style.display = 'inline-block';
                    alertify.success('อ่าน QR Code เสร็จสิ้น');
                } else {
                    qrResultDiv.textContent = 'ไม่พบ QR Code ในรูปภาพ';
                    qrResultDiv.style.display = 'block';
                    alertify.warning('ไม่พบ QR Code ในรูปภาพ');
                }
                btnQR.disabled = false;
            };
            img.src = URL.createObjectURL(qrSelectedFile);
        });

        btnQRClear.addEventListener('click', () => {
            qrSelectedFile = null;
            qrFileInput.value = '';
            qrPreview.src = '';
            qrPreview.style.display = 'none';
            btnQR.disabled = true;
            qrResultDiv.style.display = 'none';
            btnQRCopy.style.display = 'none';
            qrResultData = '';
        });

        btnQRCopy.addEventListener('click', () => {
            navigator.clipboard.writeText(qrResultData).then(() => alertify.success('คัดลอกแล้ว'))
                .catch(() => alertify.error('ไม่สามารถคัดลอกได้'));
        });
    </script>
    </body>
    </html>
    <?php include('includes/Footer.php'); } ?>