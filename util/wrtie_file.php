<?php

function log($text)
{
    $logfile = fopen("logfile.txt", "w") or die("Unable to open file!");
    fwrite($logfile, $text);
    fclose($logfile);
}
