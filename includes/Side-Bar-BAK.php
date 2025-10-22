<?php
session_start();
error_reporting(0);
include('config/connect_db.php');
?>

<link href="vendor/fontawesome4.7/css/all.css" rel="stylesheet" type="text/css">

<!-- Sidebar -->
<ul class="navbar-nav sidebar sidebar-light accordion" id="accordionSidebar">
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo $_SESSION['dashboard_page']?>">
        <div class="sidebar-brand-icon">
            <img src="img/logo/logo2.png">
        </div>
        <div class="sidebar-brand-text mx-3">ใจพร้อม JaiPrompt</div>
    </a>
    <hr class="sidebar-divider my-0">
    <li class="nav-item active">
        <a class="nav-link" href="<?php echo $_SESSION['dashboard_page']?>">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>
    <hr class="sidebar-divider">

    <?php

    //privilege 1 = admin

    if ($_SESSION['account_type'] != "admin") {
        $where_condM = " where privilege = 'User' ";
        $where_condS = " and privilege = 'User' ";
    }

    $sql = "SELECT * from menu_main " . $where_condM . " order by main_menu_id";
    $query = $conn->prepare($sql);
    //$query->bindParam(':cid', $cid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);


    if ($query->rowCount() > 0) {
        foreach ($results as $result) {
            $main_menu = $_SESSION['lang'] == "th" ? $result->label : $result->label_en;
            ?>

            <li class="nav-item">

                <a class="nav-link collapsed" href="<?php echo $result->link ?>" data-toggle="collapse"
                   data-target="<?php echo $result->data_target ?>"
                   aria-expanded="true"
                   aria-controls="<?php echo $result->aria_controls ?>">
                    <?php echo "<i class='$result->icon'></i>" ?>
                    <span><?php echo $main_menu; ?></span>
                </a>

                <div id="<?php echo $result->aria_controls ?>" class="collapse" aria-labelledby="headingBootstrap"
                     data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <h6 class="collapse-header"><?php echo $main_menu; ?></h6>

                        <?php

                        $sql1 = "SELECT * from menu_sub where main_menu_id =:mid " . $where_condS . " order by main_menu_id,sub_menu_id";
                        $query1 = $conn->prepare($sql1);
                        $query1->bindParam(':mid', $result->main_menu_id, PDO::PARAM_STR);
                        $query1->execute();
                        $results1 = $query1->fetchAll(PDO::FETCH_OBJ);

                        if ($query1->rowCount() > 0) {
                            foreach ($results1 as $result1) {
                                $sub_menu = $_SESSION['lang'] == "th" ? $result1->label : $result1->label_en;
                                ?>
                                <a class="collapse-item"
                                   href="<?php echo $result1->link . '?m=' . urlencode($main_menu) . '&s=' . urlencode($sub_menu) ?>"><?php echo "<i class='$result1->icon'></i>" ?>
                                    <span><?php echo $sub_menu; ?></span>
                                </a>
                            <?php }
                        } ?>

                    </div>
                </div>
            </li>

        <?php }
    } ?>
    <hr class="sidebar-divider">
    <div class="version" id="version-ruangadmin"></div>
</ul>
<!-- Sidebar -->