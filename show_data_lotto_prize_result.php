<?php

include('includes/Header.php');
include('config/connect_lotto_db.php');

$id = $_GET['id'];
$lotto_name = $_GET['lotto_name'];
$phone = $_GET['phone'];
$lotto_number = $_GET['lotto_number'];

// ดึงข้อมูลรางวัลจากงวดที่ประกาศ
$sql = "SELECT ims_lotto_period.*, prize.prize_id, prize.detail AS prize_detail, prize.prize, 
               ims_lotto_period.period_no, ims_lotto_period.period_month, ims_lotto_period.period_year 
        FROM ims_lotto_period
        LEFT JOIN ims_lotto_prize prize ON prize.prize_id = ims_lotto_period.lotto_type
        WHERE (lotto_number_result = :search_number AND ims_lotto_period.lotto_type = '2') 
              OR (lotto_number_result LIKE :search_number_last_two AND ims_lotto_period.lotto_type = '1')
        ORDER BY ims_lotto_period.period_no, ims_lotto_period.period_month, ims_lotto_period.period_year";

$stmt = $conn->prepare($sql);
$search_number_last_two = '%' . substr($lotto_number, -2); // ใช้ LIKE เฉพาะเลขท้าย 2 ตัว
$stmt->bindParam(':search_number', $lotto_number, PDO::PARAM_STR);
$stmt->bindParam(':search_number_last_two', $search_number_last_two, PDO::PARAM_STR);
$stmt->execute();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
    <title>SAC LOTTO</title>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-xl-12"><br>
            <div><img src="img/logo/logo text-01.png" width="200" height="79"/></div>
            <h6 style="color: blue; display: inline-block; margin-right: 10px;"><b>ข้อมูลรางวัล SAC LOTTO</b></h6>
            <button type="button" id="closeBtn" class="btn btn-danger d-inline-block"><i class="fa fa-times-circle"
                                                                                         aria-hidden="true"></i> ปิด
            </button>
            <br>
            <?php

            if ($stmt->rowCount() > 0) {
                echo "<table id='winnersTable' class='table table-striped table-hover table-responsive table-bordered' style='width: 100%; margin-top: 10px;'>";
                echo "<thead><tr>
                <th style='border: 1px solid #ddd; padding: 8px;'>ลำดับ</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>ผู้ถูกรางวัล</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>เบอร์โทร</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>จังหวัด</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>หมายเลขที่เลือก</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>ชื่อ Sale</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>รางวัลที่ได้รับ</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>หมายเลขที่ออก</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>รางวัล</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>งวดที่</th>
              </tr></thead>";
                echo "<tbody>";

                $rank = 1;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    // ค้นหาผู้ถูกรางวัล
                    $sql_lotto = "SELECT * FROM ims_lotto WHERE lotto_number = :search_number AND id = :id ";
                    /*
                                        $my_file = fopen("sql_getdata.txt", "w") or die("Unable to open file!");
                                        fwrite($my_file, " sql_lotto = " . $sql_lotto . " lotto_number = " . $lotto_number . " id = " . $id);
                                        fclose($my_file);
                    */
                    $stmt_lotto = $conn->prepare($sql_lotto);
                    $stmt_lotto->bindParam(':search_number', $lotto_number, PDO::PARAM_STR);
                    $stmt_lotto->bindParam(':id', $id, PDO::PARAM_STR);
                    $stmt_lotto->execute();

                    if ($stmt_lotto->rowCount() > 0) {
                        while ($lotto = $stmt_lotto->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $rank++ . "</td>";
                            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($lotto['lotto_name']) . "</td>";
                            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($lotto['lotto_phone']) . "</td>";
                            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($lotto['lotto_province']) . "</td>";
                            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($lotto['lotto_number']) . "</td>";
                            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($lotto['sale_name']) . "</td>";
                            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['prize_detail']) . "</td>";
                            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['lotto_number_result']) . "</td>";
                            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['prize']) . " บาท</td>";
                            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" .
                                "งวดที่ " . htmlspecialchars($row['period_no']) .
                                " เดือน " . htmlspecialchars($row['period_month']) .
                                " ปี " . htmlspecialchars($row['period_year']) . "</td>";
                            echo "</tr>";
                        }
                    }
                }
                echo "</tbody></table>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>ไม่พบรายการที่ได้รับรางวัล</p>";
            }
            ?>

        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Initialize DataTable
        $('#winnersTable').DataTable({
            "language": {
                "lengthMenu": "แสดง _MENU_ รายการ",
                "zeroRecords": "ไม่พบข้อมูล",
                "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                "infoEmpty": "แสดง 0 ถึง 0 จาก 0 รายการ",
                "infoFiltered": "(จากทั้งหมด _MAX_ รายการ)",
                "search": "ค้นหา:",
                "paginate": {
                    "first": "แรก",
                    "last": "สุดท้าย",
                    "next": "ถัดไป",
                    "previous": "ก่อนหน้า"
                }
            }
        });

        // Close button functionality
        $('#closeBtn').click(function (event) {
            event.preventDefault();
            close();
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#closeBtn').click(function (event) {
            event.preventDefault(); // ป้องกันการโหลดหน้าใหม่โดยตรงจาก `<a>`
            close();
        });
    });
</script>


</body>
</html>
