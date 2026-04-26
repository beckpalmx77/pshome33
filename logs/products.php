<?php
require_once 'Mobile_Detect.php';
$detect = new Mobile_Detect;

if ( $detect->isMobile() ) {
    include('cockpit_sale_page_mob.php');
} else {
    include('cockpit_sale_page.php');
}

?>
