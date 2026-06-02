<?php
session_start();
error_reporting(0);
include('../config/connect_db.php');

if (isset($_SESSION['alogin']) && $_SESSION['alogin'] != "") {
    $user_id = $_SESSION['alogin'];
    $theme_topbar = $_POST['theme_topbar'];
    $theme_sidebar = $_POST['theme_sidebar'];
    $theme_sidebar_color = $_POST['theme_sidebar_color'];

    $sql = "UPDATE ims_user SET theme_topbar = :theme_topbar, theme_sidebar = :theme_sidebar, theme_sidebar_color = :theme_sidebar_color WHERE user_id = :user_id";
    $query = $conn->prepare($sql);
    $query->bindParam(':theme_topbar', $theme_topbar, PDO::PARAM_STR);
    $query->bindParam(':theme_sidebar', $theme_sidebar, PDO::PARAM_STR);
    $query->bindParam(':theme_sidebar_color', $theme_sidebar_color, PDO::PARAM_STR);
    $query->bindParam(':user_id', $user_id, PDO::PARAM_STR);

    if ($query->execute()) {
        $_SESSION['theme_topbar'] = $theme_topbar;
        $_SESSION['theme_sidebar'] = $theme_sidebar;
        $_SESSION['theme_sidebar_color'] = $theme_sidebar_color;
        echo 1;
    } else {
        echo 0;
    }
} else {
    echo 0;
}
?>
