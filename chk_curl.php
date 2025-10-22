<?php
var_dump(function_exists('curl_init'));

if (!defined('CURLOPT_CONNECTTIMEOUT')) {
    define('CURLOPT_CONNECTTIMEOUT', 78); // This is the constant's typical value for cURL
}

echo "<br>";


echo "curl version " . curl_version()['version'];


echo "<br>";

if (defined('CURLOPT_CONNECTTIMEOUT')) {
    echo 'CURLOPT_CONNECTTIMEOUT is defined';
} else {
    echo 'CURLOPT_CONNECTTIMEOUT is NOT defined';
}