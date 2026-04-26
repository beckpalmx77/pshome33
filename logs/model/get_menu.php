<?php
session_start();
error_reporting(E_ALL); // เปิดไว้เพื่อตรวจสอบ error ระหว่างพัฒนา
include('../config/connect_db.php');
include('../config/lang.php');

$permission_id = $_SESSION['account_type'] ?? '';
$lang = $_SESSION['lang'] ?? 'en';

if ($permission_id !== "") {
    // 1. ดึงข้อมูล Permission และ Join กับ Menu ทั้งหมดใน Query เดียว
    // วิธีนี้เรียกว่า "Eager Loading" ช่วยลดภาระ Database ได้มหาศาล
    $sql = "SELECT 
                m.main_menu_id, m.label as m_label, m.label_en as m_label_en, 
                m.link as m_link, m.icon as m_icon, m.data_target, m.aria_controls,
                s.sub_menu_id, s.label as s_label, s.label_en as s_label_en, 
                s.link as s_link, s.icon as s_icon, s.target as s_target
            FROM ims_permission p
            JOIN menu_main m ON FIND_IN_SET(m.main_menu_id, p.main_menu)
            LEFT JOIN menu_sub s ON m.main_menu_id = s.main_menu_id AND FIND_IN_SET(s.sub_menu_id, p.sub_menu)
            WHERE p.permission_id = :pid
            ORDER BY m.main_menu_id, s.sub_menu_id";

    $query = $conn->prepare($sql);
    $query->execute(['pid' => $permission_id]);
    $rows = $query->fetchAll(PDO::FETCH_OBJ);

    // 2. จัดกลุ่มข้อมูล (Grouping) เพื่อให้แสดงผลง่าย
    $menus = [];
    foreach ($rows as $row) {
        $m_id = $row->main_menu_id;
        if (!isset($menus[$m_id])) {
            $menus[$m_id] = [
                'label' => ($lang == 'th') ? $row->m_label : $row->m_label_en,
                'link' => $row->m_link,
                'icon' => $row->m_icon,
                'target' => $row->data_target,
                'controls' => $row->aria_controls,
                'subs' => []
            ];
        }
        if ($row->sub_menu_id) {
            $menus[$m_id]['subs'][] = [
                'label' => ($lang == 'th') ? $row->s_label : $row->s_label_en,
                'link' => $row->s_link,
                'icon' => $row->s_icon,
                'target' => $row->s_target == "_blank" ? "target='_blank'" : ""
            ];
        }
    }
}
?>