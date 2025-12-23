<?php
session_start();
include('config/connect_db.php');

// ตรวจสอบ Login
if (strlen($_SESSION['alogin']) == "") {
    echo "<script>window.close();</script>";
    exit();
}

$meeting_year = isset($_GET['meeting_year']) ? $_GET['meeting_year'] : '';

// สร้างเงื่อนไข SQL
$condition = "";
$params = [];
if ($meeting_year != "") {
    $condition = " AND meeting_year = :meeting_year ";
    $params[':meeting_year'] = $meeting_year;
}

// ดึงข้อมูลจาก View v_ims_house_meeting
$sql = "SELECT * FROM v_ims_house_meeting 
        WHERE 1=1 $condition 
        ORDER BY CAST(alley AS UNSIGNED) ASC, house_number ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// จัดกลุ่มข้อมูลตาม Alley (ซอย)
$dataByAlley = [];
foreach ($results as $row) {
    $alley = $row['alley'];
    if ($alley == '' || $alley == null) $alley = 'ไม่ระบุซอย';
    $dataByAlley[$alley][] = $row;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบลงทะเบียนเข้าร่วมประชุม</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* ตั้งค่าหน้ากระดาษ A4 แนวตั้ง */
        @page {
            size: A4 portrait;
            margin: 1; /* ซ่อน Header/Footer ของ Browser */
        }

        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 14pt;
            margin: 0;
            padding: 1.5cm; /* ระยะขอบกระดาษจริง */
            background: white;
        }

        /* --- CSS สำหรับ Header แบบชิดซ้าย-ขวา --- */
        .header-container {
            display: flex; /* ใช้ Flexbox จัดวาง */
            justify-content: space-between; /* แยกซ้ายขวา */
            align-items: flex-end; /* จัดให้ฐานด้านล่างเท่ากัน (หรือ center ถ้าต้องการกึ่งกลาง) */
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000; /* เส้นคั่นใต้ Header */
        }

        .header-left {
            text-align: left;
        }

        .header-right {
            text-align: right;
        }

        /* ปรับขนาด Logo */
        .logo {
            width: 120px; /* ปรับขนาดตามความเหมาะสม */
            height: auto;
        }

        /* ปรับระยะห่างบรรทัดใน Header ฝั่งขวา */
        .header-right h2, .header-right h3, .header-right p {
            margin: 2px 0;
            line-height: 1.2;
        }
        .header-right h3 { font-size: 16pt; font-weight: bold; }
        .header-right p { font-size: 14pt; }
        .project-name { font-size: 16pt; font-weight: bold; }

        /* ตารางข้อมูล */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px 5px;
            vertical-align: middle;
            font-size: 14pt;
            word-wrap: break-word;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            height: 35px;
        }
        td {
            height: 30px;
        }

        .text-center { text-align: center; }

        .page-break {
            page-break-before: always;
            display: block;
        }

        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; padding: 1.5cm; }
            thead { display: table-header-group; }
        }

        /* ปุ่ม Print */
        .print-btn-container {
            text-align: right;
            padding: 10px;
            background: #eee;
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
            margin-top: -1.5cm;
            margin-left: -1.5cm;
            margin-right: -1.5cm;
        }
        .btn-print {
            background-color: #4e73df;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body onload="setTimeout(function(){ window.print(); }, 500);">

<div class="no-print print-btn-container">
    <button class="btn-print" onclick="window.print()">🖨️ พิมพ์เอกสาร (Print)</button>
</div>

<?php
$firstGroup = true;
foreach ($dataByAlley as $alleyName => $rows) {

    if (!$firstGroup) {
        echo '<div class="page-break"></div>';
    }
    $firstGroup = false;

    $m_year = isset($rows[0]['meeting_year']) ? $rows[0]['meeting_year'] : '-';
    $m_date = isset($rows[0]['meeting_date']) ? $rows[0]['meeting_date'] : '-';
    $m_name = isset($rows[0]['meeting_name']) ? $rows[0]['meeting_name'] : '-';
    ?>

    <div class="content-page">

        <div class="header-container">
            <div class="header-left">
                <img src="img/header/niti_ps33_header.png" alt="Logo" class="logo">
            </div>
            <div class="header-right">
                <div class="project-name">หมู่บ้านพฤกษา 33</div>
                <p><strong>การประชุม:</strong> <?php echo $m_name; ?> (<?php echo $m_year; ?>)</p>
                <h3>ซอย: <?php echo $alleyName; ?></h3>
                <p><strong>วันที่:</strong> <?php echo $m_date; ?></p>
            </div>
        </div>

        <table>
            <thead>
            <tr>
                <th style="width: 8%;">ลำดับ</th>
                <th style="width: 14%;">บ้านเลขที่</th>
                <th style="width: 8%;">ซอย</th>
                <th style="width: 45%;">ลงชื่อผู้เข้าร่วมประชุม</th>
                <th style="width: 22%;">หมายเหตุ</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            foreach ($rows as $row) {
                ?>
                <tr>
                    <td class="text-center"><?php echo $i++; ?></td>
                    <td class="text-center"><?php echo $row['house_number']; ?></td>
                    <td class="text-center"><?php echo $row['alley']; ?></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; font-size: 10pt; text-align: right;">
            <i>พิมพ์ข้อมูลเมื่อ: <?php echo date('d/m/Y H:i'); ?> | หน้าสำหรับซอย <?php echo $alleyName; ?></i>
        </div>
    </div>

<?php } ?>

<?php if (empty($results)) { ?>
    <div style="text-align: center; margin-top: 50px; color: red;" class="no-print">
        <h3>ไม่พบข้อมูลสำหรับการพิมพ์</h3>
        <p>กรุณาตรวจสอบว่าได้เลือกปีที่มีข้อมูลการประชุมแล้วหรือไม่</p>
    </div>
<?php } ?>

</body>
</html>