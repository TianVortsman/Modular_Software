<?php
namespace App\modules\product\controllers;

use PDO;
use Exception;


function moveProductImageToNewTypeFolder(int $productId, string $oldTypeName, string $newTypeName): void {
    $accountNumber = $_SESSION['account_number'] ?? 'ACC002';
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
    $oldFolder = "$docRoot/Uploads/$accountNumber/products/" . strtolower($oldTypeName);
    $newFolder = "$docRoot/Uploads/$accountNumber/products/" . strtolower($newTypeName);
    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    foreach ($extensions as $ext) {
        $oldPath = "$oldFolder/$productId.$ext";
        if (file_exists($oldPath)) {
            if (!is_dir($newFolder)) {
                mkdir($newFolder, 0777, true);
            }
            $newPath = "$newFolder/$productId.$ext";
            rename($oldPath, $newPath);
            break;
        }
    }
}

function handleImageUpload(): array {
    try {
        $productId = $_POST['product_id'] ?? null;
        if (!$productId || !is_numeric($productId)) {
            return error('No valid product_id provided');
        }

        $file = $_FILES['image'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return error('No image file uploaded or upload error', $_FILES);
        }

        // Max file size: 5MB
        if ($file['size'] > 5 * 1024 * 1024) {
            return error('File size exceeds 5MB limit');
        }

        $accountNumber = $_SESSION['account_number'] ?? 'ACC002';
        $category = strtolower(trim($_POST['category'] ?? 'product')) ?: 'product';

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowedExts, true)) {
            return error("Unsupported image type: $ext");
        }

        // MIME type check
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp'
        ];
        if (!in_array($mimeType, $allowedMimes, true)) {
            return error("Unsupported MIME type: $mimeType");
        }

        $uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . "/Uploads/{$accountNumber}/products/{$category}/";
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            return error("Failed to create directory: $uploadDir");
        }

        $filename = $productId . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return error('Failed to save uploaded file');
        }

        $relativeUrl = "Uploads/{$accountNumber}/products/{$category}/$filename";

        return [
            'success' => true,
            'message' => 'Image uploaded successfully',
            'url' => $relativeUrl,
            'path' => $relativeUrl
        ];
    } catch (\Throwable $e) {
        return error('Exception occurred: ' . $e->getMessage());
    }
}

// Optional helper to avoid repetition
function error(string $message, $context = null): array {
    if ($context) {
        error_log("[ImageUpload] $message | Context: " . print_r($context, true));
    } else {
        error_log("[ImageUpload] $message");
    }

    return [
        'success' => false,
        'message' => $message,
        'data' => null
    ];
}