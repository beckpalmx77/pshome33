<?php include('includes/Header.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!--meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"-->
    <!--title>Encrypt/Decrypt with AJAX</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script-->
</head>
<body>

<div class="container-login">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5>Encrypt/Decrypt Data</h5>
        </div>
        <div class="card-body">
            <form id="encryptForm">
                <div class="form-group">
                    <label for="data">Enter Data:</label>
                    <input type="text" class="form-control" id="data" name="data" required>
                </div>
                <div class="form-group">
                    <label for="key">Enter Key:</label>
                    <input type="text" class="form-control" id="key" name="key" required value="Sac168168" readonly>
                </div>
                <div class="form-group">
                    <label for="action">Action:</label>
                    <select class="form-control" id="action" name="action" required>
                        <option value="encrypt">Encrypt</option>
                        <option value="decrypt">Decrypt</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>

            <!-- พื้นที่แสดงผล -->
            <div id="result" class="mt-4"></div>
        </div>
    </div>
</div>

<style>

    body {
        background-color: #f0f2f5;
        padding-top: 50px;
    }

    .container-login {
        max-width: 500px;
        margin: 0 auto;
    }

    .card {
        border-radius: 10px;
    }

    .card-header {
        background-color: #007bff;
        color: white;
        font-size: 1.25rem;
        font-weight: bold;
        text-align: center;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    .form-group label {
        font-weight: 600;
    }

    .btn-primary {
        width: 100%;
    }

    #result {
        padding: 10px;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-top: 20px;
    }

</style>

<script>
    // ฟังก์ชันสำหรับการส่งข้อมูลผ่าน AJAX
    $(document).ready(function () {
        $('#encryptForm').on('submit', function (event) {
            event.preventDefault();  // ป้องกันการ submit แบบปกติ

            // รับค่าจากฟอร์ม
            let data = $('#data').val();
            let action = $('#action').val();
            let key = $('#key').val();

            // ส่งข้อมูลไปยัง server ด้วย AJAX
            $.ajax({
                url: 'util/en-de_encrypt_process.php',  // ชื่อไฟล์ PHP ที่จะประมวลผล
                type: 'POST',
                data: {data: data, action: action, key: key},
                success: function (response) {
                    // แสดงผลลัพธ์ที่ได้
                    $('#result').html('<strong>Result:</strong><pre>' + response + '</pre>');
                },
                error: function (xhr, status, error) {
                    $('#result').html('<strong>Error:</strong> ' + error);
                }
            });
        });
    });
</script>

<!--script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script-->
</body>
</html>
