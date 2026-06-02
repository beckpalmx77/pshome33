<?php
session_start();
error_reporting(0);
include('config/connect_db.php');
include('config/lang.php');
include('util/GetData.php');
include('includes/CheckDevice.php');


if ($_SESSION['alogin'] != '') {
    $_SESSION['alogin'] = '';
}

$username = $_POST['username'];
//$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$remember = $_POST['remember'];

/*
$myfile = fopen("login-a.txt", "w") or die("Unable to open file!");
fwrite($myfile, $username . " | " . $password);
fclose($myfile);
*/

$sql = "SELECT iu.*, ih.house_number, ih.contact_name, pm.dashboard_page, ihu.line_picture_profile,ihu.line_phone
        FROM ims_user iu
        LEFT JOIN ims_permission pm ON pm.permission_id = iu.account_type
        LEFT JOIN ims_house ih ON ih.phone_number = iu.user_id
        LEFT JOIN ims_house_line_user ihu ON ihu.line_phone = iu.user_id
        WHERE iu.user_id = :username";

$query = $conn->prepare($sql);
$query->bindParam(':username', $username, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() == 1) {
    foreach ($results as $result) {
        if (password_verify($_POST['password'], $result->password)) {
            $_SESSION['alogin'] = $result->user_id;
            $_SESSION['login_id'] = $result->id;
            $_SESSION['username'] = $result->email;
            $_SESSION['emp_id'] = $result->emp_id;
            $_SESSION['first_name'] = $result->first_name;
            $_SESSION['last_name'] = $result->last_name;
            $_SESSION['email'] = $result->email;
            $_SESSION['account_type'] = $result->account_type;
            $_SESSION['user_picture'] = $result->picture;
            $_SESSION['lang'] = $result->lang;
            $_SESSION['dashboard_page'] = $result->dashboard_page;
            $_SESSION['system_name'] = $system_name;
            $_SESSION['system_name_1'] = $system_name_1;
            $_SESSION['system_name_2'] = $system_name_2;
            $_SESSION['approve_permission'] = $result->approve_permission;
            $_SESSION['role'] = $result->role;
            $_SESSION['house_number'] = $result->house_number;
            $_SESSION['line_picture_profile'] = $result->line_picture_profile;
            $_SESSION['phone_number'] = $result->line_phone;
            $_SESSION['user_signature'] = $result->user_signature;
            $_SESSION['theme_topbar'] = $result->theme_topbar;
            $_SESSION['theme_sidebar'] = $result->theme_sidebar;
            $_SESSION['theme_sidebar_color'] = $result->theme_sidebar_color;

/*
            $myfile = fopen("login.txt", "w") or die("Unable to open file!");
            fwrite($myfile, $_SESSION['house_number']);
            fclose($myfile);
*/

            if ($remember == "on") { // ถ้าติ๊กถูก Login ตลอดไป ให้ทำการสร้าง cookie
                setcookie("username", $_POST["username"], time() + (86400 * 10000), "/");
                setcookie("password", $_POST["password"], time() + (86400 * 10000), "/");
                setcookie("remember_chk", "check", time() + (86400 * 10000), "/");
            } else {
                setcookie("username", $_POST["username"], time() + (86400 * 10000), "/");
                setcookie("password", $_POST["password"], time() + (86400 * 10000), "/");
                setcookie("remember_chk", "check", time() + (86400 * 10000), "/");
            }

            $sql = "INSERT INTO ims_user_login_logs (user_id) VALUES (:user_id)";
            $query = $conn->prepare($sql);
            $query->bindParam(':user_id', $result->user_id, PDO::PARAM_STR);
            $query->execute();
            //$lastInsertId = $conn->lastInsertId();

            //echo $result->dashboard_page;

            if ($_SESSION['deviceType']==='computer' || $_SESSION['deviceType']==='tablet') {
                echo $result->dashboard_page;
            } else {
                //echo "payment_transfer_smart";
                echo $result->dashboard_page;
            }

        } else {
            echo 0;
        }
    }
}