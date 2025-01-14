<?php
include('../config/connect_db.php');
include ('../util/record_util.php');

$last_id = LAST_ID($conn,"ims_unit",'id');

echo "<br>" . "Return" . $last_id;