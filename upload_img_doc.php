<?php
$targetDir = "uploads/files/";
$response = ['filenames' => []];

foreach ($_FILES['images']['name'] as $index => $name) {
    $tmpName = $_FILES['images']['tmp_name'][$index];
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $newName = uniqid('img_', true) . '.' . $ext;
    $targetFile = $targetDir . $newName;

    if (move_uploaded_file($tmpName, $targetFile)) {
        $response['filenames'][] = $newName;
    }
}

header('Content-Type: application/json');
echo json_encode($response);