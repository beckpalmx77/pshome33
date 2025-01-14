<?php
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => 'https://www.gpsiam.net/api/v2/?TOKEN=[YOUR_TOKEN]'
));
$result = $curl->exec();

print_r($result);