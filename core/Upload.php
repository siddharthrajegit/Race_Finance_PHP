<?php
/**
 * File Upload Handler & Validator
 * Whitelists JPG, PNG, WebP images up to 2MB, and JSON for backups.
 * Prevents MIME spoofing and path traversal.
 */

require_once __DIR__ . '/../config/config.php';

class Upload {
    private const MAX_SIZE = 2 * 1024 * 1024; // 2MB
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'json'];
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/json',
        'text/plain' // Some browsers upload json as text/plain
    ];

    public static function handle(string $fieldName, string $subDir = ''): ?string {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES[$fieldName];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed with error code: " . $file['error']);
        }

        if ($file['size'] > self::MAX_SIZE) {
            throw new Exception("File size exceeds strict 2MB limit.");
        }

        $origName = $file['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new Exception("Invalid file format. Only JPG, PNG, WebP images and JSON files are permitted.");
        }

        // Verify MIME type using finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            throw new Exception("Security Alert: Invalid MIME type detected ($mime).");
        }

        $targetDir = UPLOADS_DIR;
        if (!empty($subDir)) {
            $targetDir .= '/' . trim($subDir, '/');
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $uniqueName = $fieldName . '-' . time() . '-' . mt_rand(100000000, 999999999) . '.' . $ext;
        $destPath = $targetDir . '/' . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new Exception("Failed to save uploaded file.");
        }

        // Return path relative to public directory (e.g. /uploads/filename.png)
        $relPath = '/uploads/' . (!empty($subDir) ? trim($subDir, '/') . '/' : '') . $uniqueName;
        return $relPath;
    }
}
