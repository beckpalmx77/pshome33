<!--?php error_reporting(1);?-->
<?php

include("Calc_Time.php");
$leave_start="2022-10-19 09:00:00";
$leave_end="2022-10-19 12:00:00";
echo $leave_start . "<br>";
echo $leave_end . "<br>";
echo calc_leave($leave_start,$leave_end);

?>
