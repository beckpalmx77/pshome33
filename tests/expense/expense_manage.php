<?php
include '../../config/connect_db.php';
?>

<!-- expenses_form.php -->
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>บันทึกค่าใช้จ่าย</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
</head>
<body>
<h2>บันทึกค่าใช้จ่าย</h2>

<form id="formMaster">
    <input type="hidden" name="id" id="id">
    <div><label>เลขที่เอกสาร:</label> <input type="text" name="doc_id" id="doc_id" readonly></div>
    <div><label>ชื่อผู้รับ:</label> <input type="text" name="receipt_name" id="receipt_name"></div>
    <div><label>วันที่:</label> <input type="date" name="expense_date" id="expense_date"></div>
    <div>
        <label>เดือน:</label> <input type="text" name="exp_month" id="exp_month">
        <label>ปี:</label> <input type="text" name="exp_year" id="exp_year">
    </div>
    <div><label>ใบเสร็จ:</label> <input type="text" name="inv" id="inv"></div>
    <div><label>หมายเหตุ:</label><textarea name="remark" id="remark"></textarea></div>
</form>

<h3>รายละเอียด</h3>
<table id="tableDetail" class="display">
    <thead>
    <tr>
        <th>หมวดหมู่</th><th>คำอธิบาย</th><th>จำนวน</th><th>หน่วย</th>
        <th>ราคา/หน่วย</th><th>รวม</th><th>หมายเหตุ</th><th>จัดการ</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>
<button id="btnAddDetail">+ เพิ่มรายการ</button><br><br>

<button id="btnSave">💾 บันทึกข้อมูล</button>

<script>
    $(document).ready(function() {
        $('#tableDetail').DataTable();

        $('#btnAddDetail').click(function() {
            let row = `
      <tr>
        <td><input class="category_id"></td>
        <td><input class="description"></td>
        <td><input class="qty" type="number"></td>
        <td><input class="unit_id"></td>
        <td><input class="price_per_unit" type="number"></td>
        <td class="amount">0</td>
        <td><input class="remark"></td>
        <td><button class="btnRemove">ลบ</button></td>
      </tr>`;
            $('#tableDetail tbody').append(row);
        });

        $(document).on('click', '.btnRemove', function () {
            $(this).closest('tr').remove();
        });

        $('#btnSave').click(function() {
            let masterData = $('#formMaster').serialize();
            $.post('save_master.php', masterData, function(doc_id) {
                let detailData = [];
                $('#tableDetail tbody tr').each(function() {
                    let row = {
                        doc_id: doc_id,
                        category_id: $(this).find('.category_id').val(),
                        description: $(this).find('.description').val(),
                        qty: $(this).find('.qty').val(),
                        price_per_unit: $(this).find('.price_per_unit').val(),
                        unit_id: $(this).find('.unit_id').val(),
                        amount: parseFloat($(this).find('.qty').val() || 0) * parseFloat($(this).find('.price_per_unit').val() || 0),
                        remark: $(this).find('.remark').val()
                    };
                    detailData.push(row);
                });

                $.post('save_detail.php', {details: JSON.stringify(detailData)}, function(res) {
                    alert('บันทึกเรียบร้อย');
                });
            });
        });
    });
</script>
</body>
</html>

<?php

$id = $_POST['id'] ?? 0;
$doc_id = $_POST['doc_id'] ?: uniqid("EXP");
$receipt_name = $_POST['receipt_name'];
$expense_date = $_POST['expense_date'];
$exp_month = $_POST['exp_month'];
$exp_year = $_POST['exp_year'];
$inv = $_POST['inv'];
$remark = $_POST['remark'];

if ($id == 0) {
    $stmt = $conn->prepare("INSERT INTO ims_expenses_master (doc_id, receipt_name, expense_date, exp_month, exp_year, inv, remark) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$doc_id, $receipt_name, $expense_date, $exp_month, $exp_year, $inv, $remark]);
} else {
    $stmt = $conn->prepare("UPDATE ims_expenses_master SET receipt_name=?, expense_date=?, exp_month=?, exp_year=?, inv=?, remark=? WHERE id=?");
    $stmt->execute([$receipt_name, $expense_date, $exp_month, $exp_year, $inv, $remark, $id]);
}
echo $doc_id;
?>

<?php
// save_detail.php
$details = json_decode($_POST['details'], true);

foreach ($details as $row) {
    $stmt = $conn->prepare("INSERT INTO ims_expenses (doc_id, category_id, description, qty, price_per_unit, unit_id, amount, remark) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $row['doc_id'], $row['category_id'], $row['description'], $row['qty'],
        $row['price_per_unit'], $row['unit_id'], $row['amount'], $row['remark']
    ]);
}
echo "success";
?>

