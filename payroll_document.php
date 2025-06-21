<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เลือกเดือนจาก DB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="p-5 bg-light">
<div class="container">
    <h4 class="mb-3">📅 เลือกเดือน / ปี (ดึงจากฐานข้อมูล)</h4>

    <div class="row g-3">
        <div class="col-md-3">
            <label>เดือน:</label>
            <select id="month" class="form-select"></select>
        </div>

        <div class="col-md-3">
            <label>ปี:</label>
            <select id="year" class="form-select">
                <script>
                    const currentYear = new Date().getFullYear();
                    for (let i = currentYear - 5; i <= currentYear + 5; i++) {
                        document.write(`<option value="${i}">${i}</option>`);
                    }
                </script>
            </select>
        </div>

        <div class="col-md-6 d-flex align-items-end">
            <div id="result" class="alert alert-info w-100 mb-0">จำนวนวันจะปรากฏตรงนี้</div>
        </div>
    </div>
</div>

<script>
    function fetchDays() {
        const month = $('#month').val();
        const year = $('#year').val();

        if (month && year) {
            $.post('model/get_days.php', { month: month, year: year }, function(data) {
                $('#result').html(data);
            });
        }
    }

    $(document).ready(function() {
        // โหลดรายชื่อเดือนจากฐานข้อมูล
        $.get('model/get_month.php', function(data) {
            $('#month').html(data);
            fetchDays(); // คำนวณทันทีเมื่อโหลด
        });

        $('#month, #year').on('change', fetchDays);
    });
</script>

</body>
</html>
