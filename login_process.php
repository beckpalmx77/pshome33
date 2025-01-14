<?php
session_start();
error_reporting(0);
include('config/connect_db.php');
include('config/lang.php');
include('util/GetData.php');


if ($_SESSION['alogin'] != '') {
    $_SESSION['alogin'] = '';
}


$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$remember = $_POST['remember'];

$sql = "SELECT iu.*,pm.dashboard_page as dashboard_page
        ,em.sex,em.start_work_date,em.dept_id  
        FROM ims_user iu
        left join ims_permission pm on pm.permission_id = iu.account_type        
        left join memployee em on em.emp_id = iu.emp_id                
        WHERE iu.user_id=:username ";

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
            $_SESSION['sex'] = $result->sex;
            $_SESSION['email'] = $result->email;
            $_SESSION['account_type'] = $result->account_type;
            $_SESSION['user_picture'] = $result->picture;
            $_SESSION['dept_id'] = $result->dept_id;
            $_SESSION['department_id'] = $result->department_id;
            $_SESSION['lang'] = $result->lang;
            $_SESSION['permission_price'] = $result->permission_price;
            $_SESSION['dashboard_page'] = $result->dashboard_page;
            $_SESSION['system_name'] = $system_name;
            $_SESSION['system_name_1'] = $system_name_1;
            $_SESSION['system_name_2'] = $system_name_2;
            $_SESSION['start_work_date'] = $result->start_work_date;
            $_SESSION['approve_permission'] = $result->approve_permission;
            $_SESSION['document_dept_cond'] = $result->document_dept_cond;
            $_SESSION['dept_id_approve'] = $result->dept_id_approve;
            $_SESSION['role'] = $result->role;

            if ($remember == "on") { // ถ้าติ๊กถูก Login ตลอดไป ให้ทำการสร้าง cookie
                setcookie("username", $_POST["username"], time() + (86400 * 10000), "/");
                setcookie("password", $_POST["password"], time() + (86400 * 10000), "/");
                setcookie("remember_chk", "check", time() + (86400 * 10000), "/");
            } else {
                setcookie("username", $_POST["username"], time() + (86400 * 10000), "/");
                setcookie("password", $_POST["password"], time() + (86400 * 10000), "/");
                setcookie("remember_chk", "check", time() + (86400 * 10000), "/");
            }

            echo $result->dashboard_page;

        } else {
            echo 0;
        }
    }
}