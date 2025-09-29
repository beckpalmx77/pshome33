<?php
// *** START: EDITED FOR PAYROLL UPLOAD ***
// 1. Changed target directory to uploads/payroll/
$targetDir = "uploads/payroll/";

// 2. Create the directory if it doesn't exist
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}
// *** END: EDITED FOR PAYROLL UPLOAD ***

// Initialize the response with a success status and an empty filenames array
$response = ['status' => 'success', 'message' => 'Upload successful', 'filenames' => []];

// Check if files were actually uploaded
if (empty($_FILES['images']['name'][0])) { // Check if the first file name is empty (no files selected)
    $response['status'] = 'error';
    $response['message'] = 'No files selected for upload.';
} else {
    foreach ($_FILES['images']['name'] as $index => $name) {
        $tmpName = $_FILES['images']['tmp_name'][$index];
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $newName = uniqid('payroll_', true) . '.' . $ext; // Generate a unique filename
        $targetFile = $targetDir . $newName;

        // Attempt to move the uploaded file
        if (move_uploaded_file($tmpName, $targetFile)) {
            $response['filenames'][] = $newName; // Add filename to the response array
        } else {
            // If any single file fails to move, mark the overall status as error
            $response['status'] = 'error';
            $response['message'] = 'One or more files failed to move to the target directory.';
            // You might want to log the specific file that failed or collect error messages for each file
        }
    }

    // If overall status is still success but no files were added (e.g., all failed silently without `move_uploaded_file` returning false)
    if ($response['status'] === 'success' && empty($response['filenames']) && !empty($_FILES['images']['name'])) {
        $response['status'] = 'error';
        $response['message'] = 'Files received but none were successfully uploaded (possible permission issue or invalid files).';
    }
}


header('Content-Type: application/json');
echo json_encode($response);