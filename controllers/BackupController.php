<?php
/**
 * Backup Controller
 * Handles local JSON exports/imports and Google Drive Cloud Sync via Google REST API
 * RACE FINANCE - Billing & Inventory System
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Upload.php';
require_once __DIR__ . '/../models/Backup.php';

class BackupController {
    public function getIndex(): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        $googleToken = Backup::getGoogleToken((int)$user['id']);

        $googleClientId = env('GOOGLE_CLIENT_ID');
        $hasGoogleAuth = !empty($googleClientId) && $googleClientId !== 'your_google_client_id_here';

        view('backup/index', [
            'title' => 'Data Backup & Google Drive Cloud Sync',
            'googleToken' => $googleToken,
            'hasGoogleAuth' => $hasGoogleAuth,
            'activeMenu' => 'backup'
        ]);
    }

    public function exportJson(): void {
        Auth::requireUserOnly();
        $user = Auth::user();

        try {
            $backupData = Backup::exportFullBackup((int)$user['id']);
            $dateStr = date('Y-m-d');
            $filename = "RaceFinance_Backup_{$dateStr}_" . time() . ".json";

            header('Content-Type: application/json');
            header("Content-Disposition: attachment; filename=\"{$filename}\"");
            echo json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to export backup.'));
            header('Location: /backup');
            exit;
        }
    }

    public function restoreJson(): void {
        Auth::requireUserOnly();
        $user = Auth::user();

        try {
            if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
                Flash::set('error_msg', 'Please select a valid JSON backup file.');
                header('Location: /backup');
                exit;
            }

            $tmpPath = $_FILES['backup_file']['tmp_name'];
            $content = file_get_contents($tmpPath);
            $backupData = json_decode($content, true);

            if (!is_array($backupData) || !isset($backupData['firms'])) {
                Flash::set('error_msg', 'Uploaded file is not a valid RACE FINANCE backup document.');
                header('Location: /backup');
                exit;
            }

            Backup::restoreFullBackup((int)$user['id'], $backupData);

            Flash::set('success_msg', 'Data restored successfully from backup! All firms, items, and bills imported.');
            header('Location: /dashboard');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Failed to restore backup.'));
            header('Location: /backup');
            exit;
        }
    }

    public function uploadGoogleDrive(): void {
        Auth::requireUserOnly();
        $user = Auth::user();
        $userId = (int)$user['id'];

        try {
            $tokenRecord = Backup::getGoogleToken($userId);
            if (!$tokenRecord || empty($tokenRecord['access_token'])) {
                Flash::set('error_msg', 'Google Drive is not connected. Please connect your Google account below first.');
                header('Location: /backup');
                exit;
            }

            $accessToken = $tokenRecord['access_token'];

            // Check if token needs refresh
            $clientId = env('GOOGLE_CLIENT_ID');
            $clientSecret = env('GOOGLE_CLIENT_SECRET');

            if (!empty($tokenRecord['refresh_token']) && $clientId && $clientSecret) {
                $ch = curl_init('https://oauth2.googleapis.com/token');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'refresh_token' => $tokenRecord['refresh_token'],
                    'grant_type' => 'refresh_token'
                ]));
                $refreshRes = curl_exec($ch);
                curl_close($ch);

                $refData = json_decode((string)$refreshRes, true);
                if (!empty($refData['access_token'])) {
                    $accessToken = $refData['access_token'];
                    Backup::saveGoogleToken($userId, [
                        'access_token' => $accessToken,
                        'refresh_token' => $tokenRecord['refresh_token'],
                        'scope' => $tokenRecord['scope'] ?? 'drive.file',
                        'token_type' => 'Bearer',
                        'expiry_date' => isset($refData['expires_in']) ? (time() + $refData['expires_in']) * 1000 : null,
                        'email' => $tokenRecord['email']
                    ]);
                }
            }

            // 1. Search or create "RACE FINANCE Backups" folder
            $folderId = null;
            $searchUrl = "https://www.googleapis.com/drive/v3/files?q=" . urlencode("mimeType='application/vnd.google-apps.folder' and name='RACE FINANCE Backups' and trashed=false") . "&fields=" . urlencode("files(id, name)");

            $ch = curl_init($searchUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
            $searchRes = curl_exec($ch);
            curl_close($ch);

            $searchData = json_decode((string)$searchRes, true);
            if (!empty($searchData['files'][0]['id'])) {
                $folderId = $searchData['files'][0]['id'];
            } else {
                // Create folder
                $ch = curl_init('https://www.googleapis.com/drive/v3/files');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                    'name' => 'RACE FINANCE Backups',
                    'mimeType' => 'application/vnd.google-apps.folder'
                ]));
                $folderRes = curl_exec($ch);
                curl_close($ch);

                $folderData = json_decode((string)$folderRes, true);
                if (!empty($folderData['id'])) {
                    $folderId = $folderData['id'];
                }
            }

            // 2. Prepare backup content
            $backupData = Backup::exportFullBackup($userId);
            $dateStr = date('Y-m-d_H-i-s');
            $filename = "RaceFinance_Backup_{$dateStr}.json";
            $fileContent = json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            // 3. Upload file (multipart)
            $boundary = '-------314159265358979323846';
            $delimiter = "\r\n--" . $boundary . "\r\n";
            $closeDelimiter = "\r\n--" . $boundary . "--";

            $metadata = [
                'name' => $filename,
                'mimeType' => 'application/json'
            ];
            if ($folderId) {
                $metadata['parents'] = [$folderId];
            }

            $multipartBody = $delimiter;
            $multipartBody .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
            $multipartBody .= json_encode($metadata);
            $multipartBody .= $delimiter;
            $multipartBody .= "Content-Type: application/json\r\n\r\n";
            $multipartBody .= $fileContent;
            $multipartBody .= $closeDelimiter;

            $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: multipart/related; boundary=' . $boundary,
                'Content-Length: ' . strlen($multipartBody)
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $multipartBody);
            $uploadRes = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $uploadData = json_decode((string)$uploadRes, true);

            if ($httpCode >= 200 && $httpCode < 300 && !empty($uploadData['name'])) {
                Flash::set('success_msg', "Backup successfully uploaded to Google Drive! File: \"{$uploadData['name']}\".");
            } else {
                throw new Exception("Google Drive API response: " . ($uploadData['error']['message'] ?? 'Unknown error'));
            }

            header('Location: /backup');
            exit;
        } catch (Throwable $e) {
            Flash::set('error_msg', Security::getSafeErrorMessage($e, 'Google Drive upload failed.'));
            header('Location: /backup');
            exit;
        }
    }
}
