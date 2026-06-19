<?php
session_start();
if (empty($_SESSION['alogin'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานเปรียบเทียบข้อมูลใบเสร็จรับเงินอื่นๆ กับ บัญชีแยกประเภททั่วไป (GL)</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- jQuery (Needed for DataTables) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <!-- DataTables Local Assets -->
    <link rel="stylesheet" type="text/css" href="css/datatables1115.css">
    <script type="text/javascript" charset="utf8" src="js/datatables1115.js"></script>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 20px;
            color: #2e384d;
        }
        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header-panel {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            padding: 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header-info h1 {
            margin: 0 0 10px 0;
            color: #4e73df;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        .header-info h1 i {
            margin-right: 12px;
        }
        .header-info p {
            color: #858796;
            margin: 0;
            font-size: 14px;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid transparent;
        }
        .btn-outline-primary {
            background-color: transparent;
            border-color: #4e73df;
            color: #4e73df;
        }
        .btn-outline-primary:hover {
            background-color: #4e73df;
            color: #fff;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.08);
            border-left: 4px solid #e3e6f0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.3rem 2rem 0 rgba(58, 59, 69, 0.12);
        }
        .card-primary { border-left-color: #4e73df; }
        .card-success { border-left-color: #1cc88a; }
        .card-info { border-left-color: #36b9cc; }
        .card-danger { border-left-color: #e74a3b; }
        
        .card-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #858796;
            margin-bottom: 5px;
        }
        .card-value {
            font-size: 22px;
            font-weight: 700;
            color: #5a5c69;
        }
        .card-value.text-primary { color: #4e73df; }
        .card-value.text-success { color: #1cc88a; }
        .card-value.text-info { color: #36b9cc; }
        .card-value.text-danger { color: #e74a3b; }

        .data-panel {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            padding: 24px;
        }
        .search-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
            min-width: 280px;
        }
        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #a3a6b5;
        }
        .search-input {
            width: 100%;
            padding: 10px 10px 10px 40px;
            font-family: inherit;
            font-size: 14px;
            border: 1px solid #d1d3e2;
            border-radius: 6px;
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        .search-input:focus {
            border-color: #4e73df;
        }
        .filter-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-filter {
            background-color: #fff;
            border: 1px solid #d1d3e2;
            color: #5a5c69;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-filter:hover {
            background-color: #f8f9fc;
            border-color: #b7b9cc;
        }
        .btn-filter.active[data-filter="all"] {
            background-color: #4e73df;
            border-color: #4e73df;
            color: #fff;
        }
        .btn-filter.active[data-filter="match"] {
            background-color: #1cc88a;
            border-color: #1cc88a;
            color: #fff;
        }
        .btn-filter.active[data-filter="mismatch"] {
            background-color: #e74a3b;
            border-color: #e74a3b;
            color: #fff;
        }
        .status-container {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: #858796;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
            transition: all 0.3s ease;
        }
        .status-badge.syncing {
            background-color: #e8f0fe;
            color: #1a73e8;
        }
        .status-badge.success {
            background-color: #e6f4ea;
            color: #137333;
        }
        .status-badge.error {
            background-color: #fce8e6;
            color: #c5221f;
        }
        .last-updated {
            font-size: 12px;
            color: #858796;
        }
        .table-responsive {
            overflow-x: auto;
            border: 1px solid #e3e6f0;
            border-radius: 6px;
            padding: 10px;
        }
        
        /* Hide Default DataTables Search Box to use our custom one */
        .dataTables_filter {
            display: none !important;
        }
        
        /* Premium DataTables Styling Alignments */
        table.dataTable {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 13px !important;
        }
        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #e3e6f0 !important;
            padding: 12px 15px !important;
        }
        table.dataTable tbody td {
            padding: 12px 15px !important;
            border-bottom: 1px solid #e3e6f0 !important;
            color: #5a5c69 !important;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-weight-bold { font-weight: 600 !important; }
        .text-primary { color: #4e73df !important; }
        .text-success { color: #1cc88a !important; }
        
        .badge {
            display: inline-block;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .badge-success {
            color: #fff;
            background-color: #1cc88a;
        }
        .badge-danger {
            color: #fff;
            background-color: #e74a3b;
        }
        .badge-secondary {
            color: #fff;
            background-color: #858796;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <div class="header-panel">
            <div class="header-info">
                <h1><i class="fas fa-exchange-alt"></i> รายงานเปรียบเทียบข้อมูลใบเสร็จรับเงินอื่นๆ กับ บัญชีแยกประเภททั่วไป (GL)</h1>
                <p>ตรวจสอบและจับคู่ข้อมูลของตาราง ims_reciepts กับรายการสมุดรายวันทั่วไป ims_gl_header และ ims_gl_details</p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn btn-outline-primary" onclick="forceReload()">
                    <i class="fas fa-sync-alt" id="reloadBtnIcon"></i> รีโหลดข้อมูล
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card card-primary">
                <span class="card-label">จำนวนเอกสารทั้งหมด (Total Documents)</span>
                <span class="card-value text-primary" id="totalDocs">- รายการ</span>
            </div>
            <div class="card card-success">
                <span class="card-label">ยอดเงินรวมในใบเสร็จ (Total Receipt Amount)</span>
                <span class="card-value text-success" id="totalRecAmt">- บาท</span>
            </div>
            <div class="card card-info">
                <span class="card-label">ยอดเงินรวมลงสมุดบัญชี (Total GL Amount)</span>
                <span class="card-value text-info" id="totalGLAmt">- บาท</span>
            </div>
            <div class="card card-success" id="discrepancyCard">
                <span class="card-label">ส่วนต่างผลกระทบ (Discrepancy)</span>
                <span class="card-value text-success" id="discrepancyVal">- บาท</span>
            </div>
        </div>

        <!-- Data Panel -->
        <div class="data-panel">
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="ค้นหาด้วย เลขที่เอกสาร, รายการ, หรือวันที่...">
                </div>
                
                <div class="filter-group">
                    <button class="btn-filter active" data-filter="all">ทั้งหมด</button>
                    <button class="btn-filter" data-filter="match" style="border-color: #1cc88a; color: #1cc88a;"><i class="fas fa-check-circle"></i> ตรงกัน</button>
                    <button class="btn-filter" data-filter="mismatch" style="border-color: #e74a3b; color: #e74a3b;"><i class="fas fa-exclamation-circle"></i> ต่างกัน</button>
                </div>

                <div class="status-container">
                    <span id="updateStatus" class="status-badge syncing"><i class="fas fa-sync fa-spin"></i> กำลังโหลดข้อมูล...</span>
                    <span id="lastUpdated" class="last-updated">อัปเดตล่าสุด: -</span>
                </div>
            </div>

            <div class="table-responsive">
                <table id="compTable" class="display nowrap dataTable" width="100%">
                    <thead>
                        <tr>
                            <th class="text-center" width="50">ลำดับ</th>
                            <th width="150">เลขที่เอกสาร (Receipt)</th>
                            <th class="text-center" width="100">วันที่ใบเสร็จ</th>
                            <th>รายละเอียดใบเสร็จ (Description)</th>
                            <th class="text-right" width="120">ยอดเงินใบเสร็จ</th>
                            <th class="text-center" width="100">วันที่ลงบัญชี (GL)</th>
                            <th class="text-center" width="80">รหัส GL ID</th>
                            <th>รายละเอียดบัญชี (GL Description)</th>
                            <th class="text-right" width="120">ยอดเดบิตใน GL</th>
                            <th class="text-center" width="150">สถานะการตรวจสอบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- ดึงข้อมูลแบบไดนามิกโดย DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let table = null;
        let currentFilter = "all";
        let autoPollInterval = null;

        // ฟังก์ชันช่วยหลีกเลี่ยงอักขระพิเศษ HTML
        function escapeHtml(str) {
            if (!str) return '';
            return str.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // ฟอร์แมตจำนวนเงิน
        function formatMoney(amount) {
            return new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount);
        }

        // ตัวกรอง DataTables แบบ Custom
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                if (currentFilter === 'all') return true;
                
                // ใช้ข้อมูลจาก internal settings เพื่อป้องกันปัญหาตัวแปร table เป็น null ขณะเริ่มทำงาน
                const rowData = settings.aoData && settings.aoData[dataIndex] ? settings.aoData[dataIndex]._aData : null;
                if (!rowData) return true;
                
                if (currentFilter === 'match') {
                    return rowData.is_match === true;
                }
                if (currentFilter === 'mismatch') {
                    return rowData.is_match === false;
                }
                return true;
            }
        );

        $(document).ready(function() {
            // เริ่มต้นใช้งาน jQuery DataTables
            table = $('#compTable').DataTable({
                'processing': false, // ปิด processing overlay ทั่วไปเนื่องจากมี syncing badge สวยงามอยู่แล้ว
                'serverSide': false, // ดึงข้อมูลทั้งหมดในครั้งเดียวเพื่อความเร็วในการจัดเรียงและค้นหาฝั่งไคลเอนต์
                'scrollX': true,
                'pageLength': 20,
                'lengthMenu': [[10, 20, 50, 100, -1], [10, 20, 50, 100, "ทั้งหมด"]],
                'ajax': {
                    'url': 'api/get_data_comp.php',
                    'dataSrc': 'data'
                },
                'order': [[1, 'desc']], // เรียงลำดับเลขที่เอกสารจากใหม่ไปเก่าเป็นค่าเริ่มต้น
                'language': {
                    search: 'ค้นหา',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'แสดงรายการที่ _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ',
                    infoEmpty: 'ไม่มีข้อมูลแสดง',
                    zeroRecords: "ไม่พบข้อมูลที่ต้องการ",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    }
                },
                'columns': [
                    { 
                        data: null, 
                        className: 'text-center',
                        orderable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: 'doc_id', className: 'font-weight-bold text-primary' },
                    { data: 'reciept_date', className: 'text-center' },
                    { 
                        data: 'rec_desc',
                        render: function(data) { return escapeHtml(data); }
                    },
                    { 
                        data: 'rec_amount', 
                        className: 'text-right font-weight-bold text-primary',
                        render: function (data) { return formatMoney(data); }
                    },
                    { 
                        data: 'gl_date', 
                        className: 'text-center',
                        render: function (data) { return data ? escapeHtml(data) : '-'; }
                    },
                    { 
                        data: 'gl_id', 
                        className: 'text-center',
                        render: function (data) { 
                            return data ? `<span class="badge badge-secondary">#${escapeHtml(data)}</span>` : '-'; 
                        }
                    },
                    { 
                        data: 'gl_desc',
                        render: function (data) { return data ? escapeHtml(data) : '-'; }
                    },
                    { 
                        data: 'gl_amount', 
                        className: 'text-right font-weight-bold text-success',
                        render: function (data) { return formatMoney(data); }
                    },
                    { 
                        data: 'is_match', 
                        className: 'text-center',
                        render: function (data) { 
                            return data 
                                ? '<span class="badge badge-success"><i class="fas fa-check-circle"></i> ตรงกัน (Match)</span>' 
                                : '<span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> ต่างกัน (Mismatch)</span>';
                        }
                    }
                ]
            });

            // ตรวจจับเมื่อดึงข้อมูลผ่าน AJAX เสร็จสิ้น เพื่ออัปเดตการ์ดสรุปยอด
            table.on('xhr.dt', function(e, settings, json, xhr) {
                if (json && json.success) {
                    // อัปเดต Summary Cards
                    document.getElementById('totalDocs').textContent = json.summary.total_docs.toLocaleString('th-TH') + ' รายการ';
                    document.getElementById('totalRecAmt').textContent = formatMoney(json.summary.total_rec_amount) + ' บาท';
                    document.getElementById('totalGLAmt').textContent = formatMoney(json.summary.total_gl_amount) + ' บาท';
                    
                    const discrepancyCard = document.getElementById('discrepancyCard');
                    const discrepancyVal = document.getElementById('discrepancyVal');
                    
                    if (json.summary.discrepancy > 0.01) {
                        discrepancyCard.className = 'card card-danger';
                        discrepancyVal.className = 'card-value text-danger';
                        discrepancyVal.innerHTML = formatMoney(json.summary.discrepancy) + ' บาท (พบผลต่าง)';
                    } else {
                        discrepancyCard.className = 'card card-success';
                        discrepancyVal.className = 'card-value text-success';
                        discrepancyVal.innerHTML = '0.00 บาท (สมบูรณ์ 100%)';
                    }
                    
                    // แสดงเวลาอัปเดตล่าสุด
                    const now = new Date();
                    const timeString = now.toLocaleTimeString('th-TH');
                    document.getElementById('lastUpdated').textContent = 'ตรวจสอบล่าสุดเมื่อ: ' + timeString;
                    
                    const statusEl = document.getElementById('updateStatus');
                    statusEl.innerHTML = '<i class="fas fa-check-circle"></i> ข้อมูลเป็นปัจจุบัน';
                    statusEl.className = 'status-badge success';
                }
            });

            // ตรวจจับข้อผิดพลาดการโหลด AJAX
            table.on('error.dt', function(e, settings, techNote, message) {
                console.error('DataTables error:', message);
                const statusEl = document.getElementById('updateStatus');
                statusEl.innerHTML = '<i class="fas fa-exclamation-triangle"></i> อัปเดตล้มเหลว';
                statusEl.className = 'status-badge error';
            });

            // ซิงค์ข้อมูลกับฟิลด์ค้นหาหลัก
            $('#searchInput').on('input', function() {
                table.search(this.value).draw();
            });

            // ตั้งค่าตัวเลือกปุ่มฟิลเตอร์
            $('.filter-group .btn-filter').on('click', function() {
                $('.filter-group .btn-filter').removeClass('active');
                $(this).addClass('active');
                currentFilter = $(this).attr('data-filter');
                
                // เปลี่ยนรูปแบบสีปุ่มที่กำลังใช้งาน (Active)
                if (currentFilter === 'all') {
                    $(this).css({'background-color': '#4e73df', 'color': '#fff'});
                } else if (currentFilter === 'match') {
                    $(this).css({'background-color': '#1cc88a', 'color': '#fff'});
                } else if (currentFilter === 'mismatch') {
                    $(this).css({'background-color': '#e74a3b', 'color': '#fff'});
                }
                
                // รีเซ็ตปุ่มอื่นให้กลับสู่โครงร่างเดิม
                $('.filter-group .btn-filter').not(this).each(function() {
                    $(this).css('background-color', '#fff');
                    const filterVal = $(this).attr('data-filter');
                    if (filterVal === 'match') $(this).css('color', '#1cc88a');
                    else if (filterVal === 'mismatch') $(this).css('color', '#e74a3b');
                    else $(this).css('color', '#5a5c69');
                });
                
                table.draw();
            });

            // ตั้งระบบตรวจสอบฐานข้อมูลอัตโนมัติ (Auto-Polling) ทุกๆ 10 วินาที
            autoPollInterval = setInterval(() => {
                reloadTableQuietly();
            }, 10000);
        });

        // ฟังก์ชันโหลดข้อมูลใหม่แบบเงียบๆ (ไม่กระโดดหน้า หรือขัดจังหวะการค้นหาของผู้ใช้)
        function reloadTableQuietly() {
            const statusEl = document.getElementById('updateStatus');
            statusEl.innerHTML = '<i class="fas fa-sync fa-spin"></i> กำลังตรวจสอบฐานข้อมูล...';
            statusEl.className = 'status-badge syncing';
            
            // สั่งโหลด DataTables ใหม่ โดยรักษาหน้าปัจจุบัน (Paging) และคำค้นหาไว้
            table.ajax.reload(null, false);
        }

        // รีโหลดบัญชีแยกประเภทและข้อมูลทั้งหมดแบบบังคับซิงค์
        function forceReload() {
            const icon = document.getElementById('reloadBtnIcon');
            icon.classList.add('fa-spin');
            
            reloadTableQuietly();
            
            setTimeout(() => {
                icon.classList.remove('fa-spin');
            }, 1000);
        }
    </script>
</body>
</html>
