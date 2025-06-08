<?php
require_once 'vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php';
$detect = new Mobile_Detect;

include('cockpit_sale_page.php');

/*
if ( $detect->isMobile() ) {
    include('cockpit_sale_page_mob.php');

} else {
    include('cockpit_sale_page.php');
}
*/


