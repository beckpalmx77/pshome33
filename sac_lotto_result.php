<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฟอร์มบันทึกข้อมูลสลากและตรวจสอบผู้ถูกรางวัล</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>ฟอร์มบันทึกข้อมูลสลากและตรวจสอบผู้ถูกรางวัล</h2>

    <!-- ฟอร์มบันทึกข้อมูล -->
    <form id="lotto-form" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="lotto_name" class="form-label">ชื่อผู้ซื้อ</label>
            <input type="text" class="form-control" id="lotto_name" required>
        </div>
        <div class="mb-3">
            <label for="lotto_number" class="form-label">เลขสลาก</label>
            <input type="text" class="form-control" id="lotto_number" required>
        </div>
        <div class="mb-3">
            <label for="lotto_phone" class="form-label">เบอร์โทรศัพท์</label>
            <input type="text" class="form-control" id="lotto_phone">
        </div>
        <div class="mb-3">
            <label for="lotto_province" class="form-label">จังหวัด</label>
            <input type="text" class="form-control" id="lotto_province">
        </div>
        <div class="mb-3">
            <label for="lotto_file" class="form-label">ไฟล์แนบ 1</label>
            <input type="file" class="form-control" id="lotto_file">
        </div>
        <button type="submit" class="btn btn-primary">บันทึกข้อมูลผู้ซื้อ</button>
    </form>

    <!-- ฟอร์มเลือกงวด -->
    <div class="mt-5">
        <h3>เลือกงวด</h3>
        <select id="lotto-period" class="form-select" onchange="loadWinners()">
            <option value="">เลือกงวด</option>
            <!-- รายการงวดจะถูกดึงมาจากฐานข้อมูล -->
        </select>
    </div>

    <!-- แสดงผู้ถูกรางวัล -->
    <div id="winners-list" class="mt-5">
        <h3>ผู้ถูกรางวัลในงวดที่เลือก</h3>
        <ul id="winners" class="list-group">
            <!-- รายชื่อผู้ถูกรางวัลจะถูกแสดงที่นี่ -->
        </ul>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById("lotto-form").addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        fetch('save_lotto.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadPeriods();  // โหลดงวดใหม่
                    alert('บันทึกข้อมูลผู้ซื้อเรียบร้อย');
                }
            });
    });

    // ฟังก์ชันโหลดงวด
    function loadPeriods() {
        fetch('get_lotto_periods.php')
            .then(response => response.json())
            .then(data => {
                const periodSelect = document.getElementById('lotto-period');
                periodSelect.innerHTML = '<option value="">เลือกงวด</option>';
                data.periods.forEach(period => {
                    let option = document.createElement('option');
                    option.value = period.id;
                    option.textContent = `งวดที่ ${period.period_no} (${period.period_year})`;
                    periodSelect.appendChild(option);
                });
            });
    }

    // ฟังก์ชันโหลดผู้ถูกรางวัลของงวดที่เลือก
    function loadWinners() {
        const periodId = document.getElementById('lotto-period').value;
        if (!periodId) return;

        fetch(`get_lotto_winners.php?period_id=${periodId}`)
            .then(response => response.json())
            .then(data => {
                const winnersList = document.getElementById('winners');
                winnersList.innerHTML = '';  // ล้างรายการเดิม
                data.winners.forEach(winner => {
                    let li = document.createElement('li');
                    li.classList.add('list-group-item');
                    li.textContent = `${winner.lotto_name} - ${winner.lotto_number} - รางวัล: ${winner.prize}`;
                    winnersList.appendChild(li);
                });
            });
    }

    // โหลดงวดเมื่อเริ่มต้น
    loadPeriods();
</script>
</body>
</html>
