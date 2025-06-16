<?php include('../includes/Header.php'); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ดาวน์โหลดเอกสาร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .logo-header {
            max-width: 860px;
            height: auto;
            display: block;
            margin: 0 auto 20px auto;
        }
        .list-group-item {
            background-color: #6a6868;
            border-color: #000000;
            color: #fff;
        }
        .list-group-item a.btn {
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-secondary text-white p-4">
<div class="container">
    <img src="niti_ps_33_header_tran.png" alt="โลโก้ พฤกษา 33" class="logo-header">
    <h2 class="mb-4 text-left">📄 ดาวน์โหลดเอกสาร พฤกษา 33</h2>

    <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between align-items-center">
            หนังสือเชิญประชุม พฤกษา33 วันที่ 29 มิถุนายน 2568
            <a href="download.php?file=meeting_invite.pdf" class="btn btn-success btn-sm">ดาวน์โหลด</a>
        </li>
        <br>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            ใบมอบฉันทะ
            <a href="download.php?file=give_me_a_mandate.pdf" class="btn btn-success btn-sm">ดาวน์โหลด</a>
        </li>
    </ul>
</div>
</body>
</html>
