<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Upload a file to Google Drive using OAuth2 or Service Account
 *
 * @param string $filePath Local path to the file
 * @param string $fileName Name for the file on Google Drive
 * @param string $folderId Google Drive Folder ID
 * @param array $config Configuration array from google_drive_config.php
 * @return string Google Drive File ID
 */
function uploadToGoogleDrive($filePath, $fileName, $folderId, $config) {
    $client = new Google\Client();
    
    // Check if using OAuth2 or Service Account (Fallback)
    if (isset($config['auth_type']) && $config['auth_type'] === 'oauth2') {
        $client->setClientId($config['oauth2_config']['client_id']);
        $client->setClientSecret($config['oauth2_config']['client_secret']);
        $client->refreshToken($config['oauth2_config']['refresh_token']);
    } else {
        // Fallback to Service Account (Legacy)
        $auth = isset($config['auth_config']) ? $config['auth_config'] : $config['auth_config_path'];
        $client->setAuthConfig($auth);
        $client->addScope(Google\Service\Drive::DRIVE_FILE);
    }

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
        'fields' => 'id',
        'supportsAllDrives' => true,
    ]);

    return $file->id;
}
