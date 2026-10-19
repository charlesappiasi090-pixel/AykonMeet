<?php

namespace App\Services\Document;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentValidationService
{
    /**
     * Allowed MIME types with their corresponding file extensions.
     */
    private const ALLOWED_MIME_TYPES = [
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.ms-powerpoint' => ['ppt'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        'text/plain' => ['txt'],
        'text/csv' => ['csv'],
        'application/csv' => ['csv'],
    ];

    /**
     * Maximum file size in bytes (50 MB).
     */
    private const MAX_FILE_SIZE = 52_428_800;

    /**
     * Magic bytes signatures for file type verification.
     * Key = MIME type, Value = array of byte signatures (offset => hex string)
     */
    private const MAGIC_BYTES = [
        'application/pdf' => [0 => '25504446'], // %PDF
        'application/msword' => [0 => 'd0cf11e0a1b11ae1'], // OLE compound document
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => [0 => '504b0304'], // ZIP-based (docx)
        'application/vnd.ms-excel' => [0 => 'd0cf11e0a1b11ae1'], // OLE compound document
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => [0 => '504b0304'], // ZIP-based (xlsx)
        'application/vnd.ms-powerpoint' => [0 => 'd0cf11e0a1b11ae1'], // OLE compound document
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => [0 => '504b0304'], // ZIP-based (pptx)
        'text/plain' => [], // No magic bytes for plain text
        'text/csv' => [], // No magic bytes for CSV
        'application/csv' => [],
    ];

    /**
     * Validate an uploaded file.
     *
     * @throws \InvalidArgumentException if validation fails
     */
    public function validate(UploadedFile $file): array
    {
        // 1. Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new \InvalidArgumentException(
                'File size exceeds maximum allowed size of 50 MB.'
            );
        }

        // 2. Get MIME type from file content (not just extension)
        $detectedMimeType = $this->detectMimeType($file);
        $originalMimeType = $file->getMimeType();

        // 3. Verify MIME type is allowed
        if (! isset(self::ALLOWED_MIME_TYPES[$detectedMimeType])) {
            throw new \InvalidArgumentException(
                'File type not allowed. Detected: '.$detectedMimeType
            );
        }

        // 4. Verify extension matches MIME type
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = self::ALLOWED_MIME_TYPES[$detectedMimeType];

        if (! in_array($extension, $allowedExtensions, true)) {
            throw new \InvalidArgumentException(
                'File extension does not match file content. Expected one of: '.implode(', ', $allowedExtensions)
            );
        }

        // 5. Verify magic bytes (file signature)
        $this->verifyMagicBytes($file, $detectedMimeType);

        // 6. Malware scanning (placeholder for integration with ClamAV or similar)
        $this->scanForMalware($file);

        // 7. Return validated file info
        return [
            'mime_type' => $detectedMimeType,
            'extension' => $extension,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'stored_name' => Str::random(40).'.'.$extension,
        ];
    }

    /**
     * Detect MIME type from file content using finfo.
     */
    private function detectMimeType(UploadedFile $file): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        return $mimeType ?: 'application/octet-stream';
    }

    /**
     * Verify file magic bytes match expected signature.
     */
    private function verifyMagicBytes(UploadedFile $file, string $mimeType): void
    {
        $signatures = self::MAGIC_BYTES[$mimeType] ?? [];

        if (empty($signatures)) {
            // No magic bytes defined for this type (e.g., text files)
            return;
        }

        $handle = fopen($file->getRealPath(), 'rb');
        if (! $handle) {
            throw new \InvalidArgumentException('Unable to read file for signature verification.');
        }

        $header = fread($handle, 8);
        fclose($handle);

        if ($header === false) {
            throw new \InvalidArgumentException('Unable to read file header.');
        }

        $headerHex = bin2hex($header);
        $matched = false;

        foreach ($signatures as $offset => $signature) {
            $length = strlen($signature) / 2;
            $bytes = substr($headerHex, $offset * 2, $length * 2);

            if (strcasecmp($bytes, $signature) === 0) {
                $matched = true;
                break;
            }
        }

        if (! $matched) {
            throw new \InvalidArgumentException(
                'File signature does not match declared type. Possible file corruption or malicious content.'
            );
        }
    }

    /**
     * Scan file for malware.
     * This is a placeholder - in production, integrate with ClamAV, VirusTotal API, etc.
     */
    private function scanForMalware(UploadedFile $file): void
    {
        // TODO: Integrate with actual malware scanning service
        // Options:
        // 1. ClamAV daemon (clamdscan)
        // 2. VirusTotal API
        // 3. AWS Lambda with ClamAV
        // 4. Google Cloud VirusTotal integration

        // For now, log the scan attempt
        Log::info('Malware scan placeholder executed', [
            'file' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);

        // Example ClamAV integration (uncomment and configure when ClamAV is available):
        /*
        $clamdSocket = config('services.clamd.socket', 'unix:///var/run/clamav/clamd.ctl');
        $result = $this->scanWithClamAV($file->getRealPath(), $clamdSocket);

        if ($result !== 'OK') {
            Log::warning('Malware detected in uploaded file', [
                'file' => $file->getClientOriginalName(),
                'result' => $result,
            ]);
            throw new \InvalidArgumentException('File failed malware scan.');
        }
        */
    }

    /**
     * Scan file using ClamAV daemon.
     */
    private function scanWithClamAV(string $filePath, string $socket): string
    {
        $socket = stream_socket_client($socket, $errno, $errstr, 30);

        if (! $socket) {
            Log::warning('Could not connect to ClamAV daemon: '.$errstr);

            return 'SKIPPED';
        }

        stream_set_timeout($socket, 30);

        $command = "nSCAN {$filePath}\0";
        fwrite($socket, $command);

        $response = '';
        while (! feof($socket)) {
            $response .= fread($socket, 8192);
        }

        fclose($socket);

        return trim($response);
    }

    /**
     * Get list of allowed MIME types for frontend validation.
     */
    public static function getAllowedMimeTypes(): array
    {
        return array_keys(self::ALLOWED_MIME_TYPES);
    }

    /**
     * Get list of allowed extensions for frontend validation.
     */
    public static function getAllowedExtensions(): array
    {
        return array_unique(array_merge(...array_values(self::ALLOWED_MIME_TYPES)));
    }

    /**
     * Get maximum file size in bytes.
     */
    public static function getMaxFileSize(): int
    {
        return self::MAX_FILE_SIZE;
    }

    /**
     * Get maximum file size in human-readable format.
     */
    public static function getMaxFileSizeHuman(): string
    {
        $bytes = self::MAX_FILE_SIZE;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
