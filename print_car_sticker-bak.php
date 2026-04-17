<?php
session_start();
require_once __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';
require_once __DIR__ . '/vendor/setasign/fpdi/src/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

function generateCarStickerPdf($house_number, $output = 'I') {
    include 'config/connect_db.php';
    
    $userName = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    
    $sql = "SELECT * FROM ims_house WHERE house_number = :house_number";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':house_number', $house_number, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        die("ไม่พบข้อมูลบ้านเลขที่: " . $house_number);
    }
    
    $carNos = [];
    if (!empty($result['car_no1'])) $carNos[] = $result['car_no1'];
    if (!empty($result['car_no2'])) $carNos[] = $result['car_no2'];
    if (!empty($result['car_no3'])) $carNos[] = $result['car_no3'];
    if (!empty($result['car_no4'])) $carNos[] = $result['car_no4'];
    if (!empty($result['car_no5'])) $carNos[] = $result['car_no5'];
    
    $carCount = count($carNos);
    $extraCarFee = 0;
    if ($carCount > 2) {
        $extraCarFee = ($carCount - 2) * 100;
    }
    
    $pdf = new Fpdi();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    $day = date('j');
    $month = date('n');
    $year = date('Y') + 543;
    
    $monthThai = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $dateText = $day . ' ' . $monthThai[$month] . ' ' . $year;
    
    $pdf->SetFont('THSarabunNew', '', 14);
    
    $pdf->setSourceFile('document/sticker_form_decompressed.pdf');
    $tplId = $pdf->importPage(1);
    
    $pdf->AddPage();
    $pdf->useTemplate($tplId);
    
    $pdf->SetXY(135, 45);
    $pdf->Cell(0, 0, $dateText, 0, 0, 'L');
    
    $pdf->SetXY(90, 60.5);
    $pdf->Cell(0, 0, $result['house_number'], 0, 0, 'L');

    $pdf->SetXY(115, 60.5);
    $pdf->Cell(0, 0, $result['alley'] ?? '', 0, 0, 'L');

    $pdf->SetXY(60, 53);
    $pdf->Cell(0, 0, $result['contact_name'] ?? '', 0, 0, 'L');
    
    $pdf->SetXY(43,60.5);
    $pdf->Cell(0, 0, $result['phone_number'] ?? '', 0, 0, 'L');
    
    $y_car = 135;
    foreach ($carNos as $carNo) {
        $pdf->SetXY(32, $y_car);
        $pdf->Cell(0, 0, $carNo, 0, 0, 'L');
        $y_car += 8;
    }
    
    $pdf->SetXY(100, 252);
    $pdf->Cell(0, 0, $result['contact_name'], 0, 0, 'L');

    $pdf->SetXY(155, 120);
    $displayFee = ($extraCarFee > 0) ? number_format($extraCarFee) : "-";
    $pdf->Cell(0, 0, $displayFee, 0, 0, 'L');

    //$pdf->SetXY(155, 120);
    //$pdf->Cell(0, 0, number_format($extraCarFee), 0, 0, 'L');
    
    $pdf->SetXY(177, 112);
    $pdf->Cell(0, 0, $carCount, 0, 0, 'L');

    $pdf->SetXY(26, 128);
    $pdf->Cell(0, 0, $carCount, 0, 0, 'L');
    
    $pdf->Output('sticker_car_' . $house_number . '.pdf', $output);
}

$house_number = $_GET['house_number'] ?? '';
if (empty($house_number)) {
    die("กรุณาระบุบ้านเลขที่");
}

generateCarStickerPdf($house_number, 'I');
