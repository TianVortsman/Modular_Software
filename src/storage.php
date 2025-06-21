<?php

namespace App;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class Storage
{
    private static ?string $driver = null;
    private static ?S3Client $s3Client = null;
    private static string $localPath;
    private static string $publicUrl;

    /**
     * Initialize the storage system based on environment configuration
     */
    public static function init(): void
    {
        self::$driver = $_ENV['STORAGE_DRIVER'] ?? 'local';
        self::$localPath = $_ENV['STORAGE_LOCAL_PATH'] ?? 'Uploads';
        self::$publicUrl = $_ENV['STORAGE_PUBLIC_URL'] ?? '';

        if (self::$driver === 's3') {
            self::$s3Client = new S3Client([
                'version' => 'latest',
                'region'  => $_ENV['AWS_REGION'],
                'credentials' => [
                    'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
                    'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
                ]
            ]);
        }
    }

    /**
     * Upload a file to storage
     * 
     * @param string $path The target path in storage
     * @param array $file The uploaded file from $_FILES
     * @return string The path where the file was stored
     * @throws \Exception If upload fails
     */
    public static function upload(string $path, array $file): string
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('Invalid file upload');
        }

        // Ensure path starts with Uploads/
        if (!str_starts_with($path, 'Uploads/')) {
            $path = 'Uploads/' . ltrim($path, '/');
        }

        if (self::$driver === 'local') {
            return self::uploadLocal($path, $file);
        }

        return self::uploadS3($path, $file);
    }

    /**
     * Get the public URL for a stored file
     * 
     * @param string $path The storage path
     * @return string The public URL
     */
    public static function getPublicUrl(string $path): string
    {
        // Ensure path starts with Uploads/
        if (!str_starts_with($path, 'Uploads/')) {
            $path = 'Uploads/' . ltrim($path, '/');
        }

        if (self::$driver === 'local') {
            return rtrim(self::$publicUrl, '/') . '/' . ltrim($path, '/');
        }

        return self::$s3Client->getObjectUrl(
            $_ENV['AWS_BUCKET'],
            $path
        );
    }

    /**
     * Build a storage path from components
     * 
     * @param string $accountNumber The account number
     * @param string $module The module name
     * @param string $section The section name
     * @param string $subSection The subsection name
     * @param string $filename The filename
     * @return string The complete storage path
     */
    public static function buildPath(
        string $accountNumber,
        string $module,
        string $section,
        string $subSection,
        string $filename
    ): string {
        return "Uploads/{$accountNumber}/{$module}/{$section}/{$subSection}/{$filename}";
    }

    /**
     * Upload a file to local storage
     */
    private static function uploadLocal(string $path, array $file): string
    {
        $fullPath = self::$localPath . '/' . $path;
        $directory = dirname($fullPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \Exception('Failed to move uploaded file');
        }

        return $path;
    }

    /**
     * Upload a file to S3
     */
    private static function uploadS3(string $path, array $file): string
    {
        try {
            self::$s3Client->putObject([
                'Bucket' => $_ENV['AWS_BUCKET'],
                'Key'    => $path,
                'SourceFile' => $file['tmp_name'],
                'ContentType' => $file['type'],
                'ACL'    => 'public-read'
            ]);

            return $path;
        } catch (AwsException $e) {
            throw new \Exception('S3 upload failed: ' . $e->getMessage());
        }
    }
} 