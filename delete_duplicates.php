
<?php
include('config/connect_db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $house_number = $_POST['house_number'];
    $period_year = $_POST['period_year'];
    $period_month_start = $_POST['period_month_start'];

    // ดึงรายการซ้ำ
    $sql = "SELECT id FROM ims_house_payment
            WHERE house_number = :house_number AND period_year = :period_year AND period_month_start = :period_month_start
            ORDER BY id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':house_number' => $house_number,
        ':period_year' => $period_year,
        ':period_month_start' => $period_month_start
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) <= 1) {
        echo json_encode(['success' => false, 'message' => 'ไม่มีรายการซ้ำให้ลบ']);
        exit;
    }

    // ลบรายการที่ 2 ขึ้นไป
    $idsToDelete = array_column(array_slice($rows, 1), 'id');
    $inQuery = implode(',', array_fill(0, count($idsToDelete), '?'));
    $deleteStmt = $conn->prepare("DELETE FROM ims_house_payment WHERE id IN ($inQuery)");
    $deleteStmt->execute($idsToDelete);

    echo json_encode(['success' => true, 'message' => 'ลบรายการซ้ำเรียบร้อยแล้ว (' . count($idsToDelete) . ' รายการ)']);
    exit;
}
?>
