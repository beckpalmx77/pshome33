<?php
function converNumberToThaiText($amount)
{
    $txtnum1 = ["", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า"];
    $txtnum2 = ["", "สิบ", "ร้อย", "พัน", "หมื่น", "แสน", "ล้าน"];
    $amount = number_format($amount, 2, ".", "");
    list($number, $fraction) = explode(".", $amount);

    $convert = "";
    $len = strlen($number);
    for ($i = 0; $i < $len; $i++) {
        $n = substr($number, $i, 1);
        if ($n != 0) {
            if ($i == ($len - 1) && $n == 1) {
                $convert .= "เอ็ด";
            } elseif ($i == ($len - 2) && $n == 2) {
                $convert .= "ยี่";
            } elseif ($i == ($len - 2) && $n == 1) {
                $convert .= "";
            } else {
                $convert .= $txtnum1[$n];
            }
            $convert .= $txtnum2[$len - $i - 1];
        }
    }

    $convert .= "บาท";
    if ($fraction == "00") {
        $convert .= "ถ้วน";
    } else {
        $len = strlen($fraction);
        for ($i = 0; $i < $len; $i++) {
            $n = substr($fraction, $i, 1);
            if ($n != 0) {
                if ($i == ($len - 1) && $n == 1) {
                    $convert .= "เอ็ด";
                } elseif ($i == ($len - 2) && $n == 2) {
                    $convert .= "ยี่";
                } elseif ($i == ($len - 2) && $n == 1) {
                    $convert .= "";
                } else {
                    $convert .= $txtnum1[$n];
                }
                $convert .= $txtnum2[$len - $i - 1];
            }
        }
        $convert .= "สตางค์";
    }
    return $convert;
}

