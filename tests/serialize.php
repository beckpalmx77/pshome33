<?php
if( $_REQUEST["name"] ) {
    $first_name = $_REQUEST['first_name'];
    echo "Welcome ". $first_name;
    $last_name = $_REQUEST['last_name'];
    echo "<br />Your Last Name : ". $last_name;
}
?>
