<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ร้านค้าลงทะเบียน</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- ใช้ SweetAlert2 สำหรับแสดงผลลัพธ์ -->
</head>
<body>

<div class="container mt-5">
    <h2>ลงทะเบียนร้านค้า</h2>
    <form id="shop-form">
        <div class="form-group">
            <label for="name">ชื่อร้าน:</label>
            <input type="text" class="form-control" id="name" required>
        </div>
        <div class="form-group">
            <label for="phone_number">เบอร์โทร:</label>
            <input type="text" class="form-control" id="phone_number" required>
        </div>
        <div class="form-group">
            <label for="address">ที่อยู่:</label>
            <input type="text" class="form-control" id="address" required>
        </div>
        <div class="form-group">
            <label for="contact_person">ผู้ติดต่อ:</label>
            <input type="text" class="form-control" id="contact_person" required>
        </div>
        <button type="submit" class="btn btn-primary">ลงทะเบียน</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        $("#shop-form").on("submit", function(event) {
            event.preventDefault(); // ป้องกันการโหลดหน้าซ้ำ

            let name = $("#name").val();
            let phone_number = $("#phone_number").val();
            let address = $("#address").val();
            let contact_person = $("#contact_person").val();

            // ส่งข้อมูลไปยังไฟล์ PHP ที่จะจัดการการส่งข้อมูลไปยัง API
            $.ajax({
                url: "api/register_shop_handler.php", // ไฟล์ PHP ที่ใช้รับข้อมูลจากฟอร์มและส่งไปยัง API
                type: "POST",
                data: {
                    name: name,
                    phone_number: phone_number,
                    address: address,
                    contact_person: contact_person
                },
                success: function(response) {
                    // แสดงผลลัพธ์
                    let data = JSON.parse(response);
                    if (data.message == "Shop was registered.") {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: 'ร้านค้าลงทะเบียนสำเร็จ!'
                        });
                        // ล้างฟอร์มหลังจากสำเร็จ
                        $("#shop-form")[0].reset();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: data.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถติดต่อ API ได้'
                    });
                }
            });
        });
    });
</script>

</body>
</html>
