<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-size: 12px;
            font-family: 'THSarabunNew', sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .bordered {
            border: 1px solid black;
        }
        .footer-signature {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row header">
        <div class="col-2">
            <img src="logo.png" alt="Company Logo" height="60">
        </div>
        <div class="col-10 text-start">
            <h5>บริษัท เอ็กซามเปิ้ล จำกัด</h5>
            <p>เลขที่ 253 ชั้น 17 ถนนสุขุมวิท 21 (อโศก) เขตวัฒนา กรุงเทพฯ 10110</p>
            <p>โทร: 02-0266231 เลขประจำตัวผู้เสียภาษี: 05465465462295</p>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-6 bordered p-2">
            <b>เลขที่บ้าน:</b> {house_number}<br>
            <b>รายละเอียด:</b> {detail}<br>
            <b>ประเภทการชำระ:</b> {payment_type}
        </div>
        <div class="col-6 bordered p-2">
            <b>วันที่ชำระ:</b> {payment_date}<br>
            <b>เลขที่เอกสาร:</b> {doc_id}<br>
        </div>
    </div>
    <table class="table table-bordered mt-3">
        <thead>
        <tr>
            <th>งวดปี</th>
            <th>เดือนเริ่มต้น</th>
            <th>เดือนสิ้นสุด</th>
            <th>จำนวนเงิน (บาท)</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{period_year}</td>
            <td>{period_month_start}</td>
            <td>{period_month_to}</td>
            <td>{amount}</td>
        </tr>
        </tbody>
    </table>
    <div class="row">
        <div class="col-6">
            <p><b>หมายเหตุ:</b> {remark}</p>
        </div>
        <div class="col-6">
            <table class="table table-bordered">
                <tr>
                    <td><b>รวมทั้งสิ้น</b></td>
                    <td>{amount} บาท</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="footer-signature">
        <table class="table table-bordered">
            <tr>
                <td>ผู้รับเงิน</td>
                <td>ผู้อนุมัติ</td>
            </tr>
            <tr>
                <td>_____________</td>
                <td>_____________</td>
            </tr>
        </table>
    </div>
</div>
</body>
</html>
