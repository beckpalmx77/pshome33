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
// เรียงตาม Alley (แปลงเป็นตัวเลขเพื่อการเรียงที่ถูกต้อง) และ บ้านเลขที่
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
            /* สำคัญ: ตั้งเป็น 0 เพื่อซ่อน Header/Footer (วันที่/URL) ของ Browser */
            margin: 1;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 14pt;
            margin: 0;
            /* ใช้ Padding ดันเนื้อหาเข้ามาแทน Margin ที่เราลบไป */
            padding: 1.5cm;
            background: white;
        }

        /* ส่วนหัวกระดาษ */
        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        /* CSS สำหรับ Logo */
        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 10px;
        }

        .header h2 {
            font-size: 18pt;
            font-weight: bold;
            margin: 0 0 10px 0;
        }
        .header h3 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        .header p {
            font-size: 14pt;
            margin: 0;
        }

        /* ตารางข้อมูล */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px 5px;
            vertical-align: middle;
            font-size: 14pt;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            height: 40px;
        }
        td {
            height: 35px;
        }

        /* จัดตำแหน่งข้อความในตาราง */
        .text-center { text-align: center; }
        .text-left { text-align: left; padding-left: 10px; }

        /* คำสั่งขึ้นหน้าใหม่ */
        .page-break {
            page-break-before: always;
            display: block;
        }

        /* ซ่อนปุ่มเมื่อสั่งพิมพ์ */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                -webkit-print-color-adjust: exact;
                /* ปรับ padding ตอน print ให้พอดี ถ้าหน้าจอดูเยอะไป */
                padding: 1.5cm;
            }
            thead { display: table-header-group; }
        }

        .print-btn-container {
            text-align: right;
            padding: 10px;
            background: #eee;
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
            /* เนื่องจาก body มี padding เราอาจต้องปรับ margin ลบ เพื่อให้แถบปุ่มชิดขอบจอ */
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
        .btn-print:hover {
            background-color: #2e59d9;
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

    // ถ้าไม่ใช่กลุ่มแรก ให้สั่งขึ้นหน้าใหม่
    if (!$firstGroup) {
        echo '<div class="page-break"></div>';
    }
    $firstGroup = false;

    // ดึงข้อมูลหัวกระดาษจาก Record แรกของกลุ่ม
    $m_year = isset($rows[0]['meeting_year']) ? $rows[0]['meeting_year'] : '-';
    $m_date = isset($rows[0]['meeting_date']) ? $rows[0]['meeting_date'] : '-';
    $m_name = isset($rows[0]['meeting_name']) ? $rows[0]['meeting_name'] : '-';
    ?>

    <div class="content-page">
        <div class="header">
            <img src="img/header/niti_ps33_header.png" alt="Logo" class="logo">

            <p><strong>การประชุม:</strong> <?php echo $m_name; ?> (<?php echo $m_year; ?>)</p>
            <p><strong>หมู่บ้านพฤกษา 33</strong></p>
            <h3>ซอย: <?php echo $alleyName; ?></h3>
            <p><strong>วันที่:</strong> <?php echo $m_date; ?></p>
        </div>

        <table>
            <thead>
            <tr>
                <th style="width: 8%;">ลำดับ</th>
                <th style="width: 15%;">บ้านเลขที่</th>
                <th style="width: 12%;">ซอย</th>
                <th style="width: 35%;">ลงชื่อผู้เข้าร่วมประชุม</th>
                <th style="width: 30%;">หมายเหตุ</th>
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
                    <td></td> <td></td> </tr>
            <?php } ?>
            </tbody>
        </table>

        <div style="margin-top: 20px; font-size: 10pt; text-align: right;">
            <i>พิมพ์ข้อมูลเมื่อ: <?php echo date('d/m/Y H:i'); ?> | หน้าสำหรับซอย <?php echo $alleyName; ?></i>
        </div>
    </div>

<?php } // จบ Foreach กลุ่มซอย ?>

<?php if (empty($results)) { ?>
    <div style="text-align: center; margin-top: 50px; color: red;" class="no-print">
        <h3>ไม่พบข้อมูลสำหรับการพิมพ์</h3>
        <p>กรุณาตรวจสอบว่าได้เลือกปีที่มีข้อมูลการประชุมแล้วหรือไม่</p>
    </div>
<?php } ?>

</body>
</html>