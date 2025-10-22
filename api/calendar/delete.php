<?php
include("../config.php");

if (isset($_POST["id"])) {
    $db->deleteById('ims_event',$_POST['id']);
}
