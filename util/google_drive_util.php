<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Upload a file to Google Drive
 *
 * @param string $filePath Local path to the file
 * @param string $fileName Name for the file on Google Drive
 * @param string $folderId Google Drive Folder ID
 * @param string $authConfigPath Path to the Service Account JSON key file
 * @return string Google Drive File ID
 */
function uploadToGoogleDrive($filePath, $fileName, $folderId, $authConfigPath) {
    $client = new Google\Client();
    $client->setAuthConfig($authConfigPath);
    $client->addScope(Google\Service\Drive::DRIVE_FILE);

    $service = new Google\Service\Drive($client);

    $fileMetadata = new Google\Service\Drive\DriveFile([
        'name' => $fileName,
        'parents' => [$folderId]
    ]);

    $content = file_get_contents($filePath);
    $mimeType = mime_content_type($filePath);

    $file = $service->files->create($fileMetadata, [
        'data' => $content,
        'mimeType' => $mimeType,
        'uploadType' => 'multipart',
        'fields' => 'id'
    ]);

    return $file->id;
}
