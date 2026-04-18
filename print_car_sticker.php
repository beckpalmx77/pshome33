<?php
session_start();

// Prevent output buffer issues
ob_clean();

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
    $carData = [];
    for ($i = 1; $i <= 5; $i++) {
        $carNo = $result['car_no' . $i] ?? '';
        if (!empty($carNo)) {
            $carNos[] = $carNo;
            $carData[] = [
                'no' => $carNo,
                'brand' => $result['car_no' . $i . '_brand'] ?? '',
                'color' => $result['car_no' . $i . '_color'] ?? '',
                'province' => $result['car_no' . $i . '_province'] ?? '',
                'type' => $result['car_no' . $i . '_type'] ?? ''
            ];
        }
    }
    
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
    
    $pdf->SetFont('thsarabunnew', '', 14);
    
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
    $x_car = 32;
    $x_province = $x_car + 35;
    $x_brand = $x_province + 35;
    $x_color = $x_brand + 40;
    $x_type = $x_color + 35;
    
    foreach ($carData as $car) {
        $pdf->SetXY($x_car, $y_car);
        $pdf->Cell(0, 0, $car['no'], 0, 0, 'L');
        $pdf->SetXY($x_province, $y_car);
        $pdf->Cell(0, 0, $car['province'], 0, 0, 'L');
        $pdf->SetXY($x_brand, $y_car);
        $pdf->Cell(0, 0, $car['brand'], 0, 0, 'L');
        $pdf->SetXY($x_color, $y_car);
        $pdf->Cell(0, 0, $car['color'], 0, 0, 'L');
        $pdf->SetXY($x_type, $y_car);
        $pdf->Cell(0, 0, $car['type'], 0, 0, 'L');
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
