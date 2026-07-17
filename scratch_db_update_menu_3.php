<?php
// D:\website\pshome33\scratch_db_update_menu_3.php
require_once 'config/connect_db.php';

try {
    // 1. Insert into menu_sub if it doesn't exist
    $sql_check = "SELECT COUNT(*) FROM menu_sub WHERE sub_menu_id = 'S613'";
    $stmt_check = $conn->query($sql_check);
    if ($stmt_check->fetchColumn() == 0) {
        $sql_insert = "INSERT INTO menu_sub (sub_menu_id, main_menu_id, label, label_en, link, target, icon, sort, privilege, link_target)
                       VALUES ('S613', 'M006', 'คาดการณ์การจัดเก็บค่าส่วนกลาง (ตามปี)', 'คาดการณ์การจัดเก็บค่าส่วนกลาง (ตามปี)', 'report_house_payment_split_monthly_3', '_self', 'fa fa-chart-line', 59, 'User', '_self')";
        $conn->exec($sql_insert);
        echo "Inserted S613 into menu_sub successfully.\n";
    } else {
        echo "S613 already exists in menu_sub.\n";
    }

    // 2. Add S613 to permission roles where S612 is present
    $sql_perms = "SELECT id, sub_menu FROM ims_permission WHERE sub_menu LIKE '%S612,%' AND sub_menu NOT LIKE '%S613,%'";
    $stmt_perms = $conn->query($sql_perms);
    $perms = $stmt_perms->fetchAll(PDO::FETCH_ASSOC);

    foreach ($perms as $p) {
        $new_submenu = str_replace('S612,', 'S612,S613,', $p['sub_menu']);
        $sql_update = "UPDATE ims_permission SET sub_menu = :new_submenu WHERE id = :id";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([':new_submenu' => $new_submenu, ':id' => $p['id']]);
        echo "Updated permissions for ID {$p['id']} to include S613.\n";
    }

    echo "Database menu update S613 completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
