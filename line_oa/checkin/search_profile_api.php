<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$accessToken = 'j5zwyVzjucFBCOkUBsn2O9TRv8D+kZz3xFTveCT4EgHB7Hca24vmdJXtG0ckOb6m1lf9shpLJcoLZqV3OkV0ewdPEq+sQ6e8D7MuRhnIpqbdFpgBY7aJ3tHq8Y/JPiudr4TWqn1IgZFIsqPPrUyR0QdB04t89/1O/w1cDnyilFU=';

$line_id = $_GET['line_id'] ?? '';

if (empty($line_id)) {
    echo json_encode(['success' => false, 'message' => 'Missing line_id']);
    exit;
}

$url = "https://api.line.me/v2/bot/profile/" . $line_id;

$headers = [
    'Authorization: Bearer ' . $accessToken,
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
} else {
    $errorData = json_decode($response, true);
    echo json_encode([
        'success' => false,
        'message' => $errorData['message'] ?? 'User not found or API error',
        'http_code' => $httpCode
    ]);
}
