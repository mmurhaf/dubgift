<?php
/**
 * Secure File Upload Handler
 */

require_once 'input-validator.php';
require_once 'security-logger.php';

class SecureFileUpload {
    private $uploadPath;
    private $allowedTypes;
    private $maxFileSize;
    private $logger;
    
    public function __construct($uploadPath = null, $allowedTypes = null, $maxFileSize = null) {
        $this->uploadPath = $uploadPath ?? UPLOAD_PATH;
        $this->allowedTypes = $allowedTypes ?? ALLOWED_IMAGE_TYPES;
        $this->maxFileSize = $maxFileSize ?? MAX_FILE_SIZE;
        $this->logger = SecurityLogger::getInstance();
        
        $this->ensureUploadDirectory();
    }
    
    /**
     * Ensure upload directory exists and is secure
     */
    private function ensureUploadDirectory() {
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
        
        // Create .htaccess to prevent execution
        $htaccessFile = $this->uploadPath . '/.htaccess';
        if (!file_exists($htaccessFile)) {
            $htaccessContent = "Options -ExecCGI\n";
            $htaccessContent .= "AddHandler cgi-script .php .pl .py .jsp .asp .sh .cgi\n";
            $htaccessContent .= "Options -Indexes\n";
            $htaccessContent .= "Order Allow,Deny\n";
            $htaccessContent .= "Allow from all\n";
            $htaccessContent .= "<FilesMatch \"\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$\">\n";
            $htaccessContent .= "    Order Allow,Deny\n";
            $htaccessContent .= "    Deny from all\n";
            $htaccessContent .= "</FilesMatch>\n";
            
            file_put_contents($htaccessFile, $htaccessContent);
        }
    }
    
    /**
     * Upload file securely
     */
    public function upload($file, $subfolder = '', $customName = null) {
        try {
            // Validate file
            $errors = InputValidator::validateFileUpload($file, $this->allowedTypes, $this->maxFileSize);
            
            if (!empty($errors)) {
                $this->logger->logFileUpload($file['name'], false, ['errors' => $errors]);
                throw new Exception(implode(', ', $errors));
            }
            
            // Additional security checks
            $this->performSecurityChecks($file);
            
            // Generate secure filename
            $filename = $this->generateSecureFilename($file['name'], $customName);
            
            // Create subfolder if specified
            $targetDir = $this->uploadPath;
            if ($subfolder) {
                $targetDir .= '/' . $this->sanitizeSubfolder($subfolder);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
            }
            
            $targetPath = $targetDir . '/' . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Set proper permissions
                chmod($targetPath, 0644);
                
                $this->logger->logFileUpload($filename, true, [
                    'original_name' => $file['name'],
                    'size' => $file['size'],
                    'type' => $file['type'],
                    'subfolder' => $subfolder
                ]);
                
                return [
                    'success' => true,
                    'filename' => $filename,
                    'path' => $targetPath,
                    'relative_path' => str_replace($this->uploadPath, '', $targetPath),
                    'size' => $file['size']
                ];
            } else {
                throw new Exception('Failed to move uploaded file');
            }
            
        } catch (Exception $e) {
            $this->logger->logFileUpload($file['name'] ?? 'unknown', false, ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Perform additional security checks
     */
    private function performSecurityChecks($file) {
        // Check for double extensions
        if (preg_match('/\.(php|phtml|php3|php4|php5|pl|py|jsp|asp|sh|cgi)\./i', $file['name'])) {
            throw new Exception('Suspicious file extension detected');
        }
        
        // Check file signature (magic bytes)
        $handle = fopen($file['tmp_name'], 'rb');
        $header = fread($handle, 10);
        fclose($handle);
        
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        // Validate file signatures
        $signatures = [
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47"],
            'gif' => ["\x47\x49\x46\x38"],
            'webp' => ["\x52\x49\x46\x46"]
        ];
        
        if (isset($signatures[$extension])) {
            $validSignature = false;
            foreach ($signatures[$extension] as $signature) {
                if (strpos($header, $signature) === 0) {
                    $validSignature = true;
                    break;
                }
            }
            
            if (!$validSignature) {
                throw new Exception('File signature does not match extension');
            }
        }
        
        // Check for embedded PHP code in images
        $content = file_get_contents($file['tmp_name']);
        if (preg_match('/<\?php|<\?=|<script/i', $content)) {
            throw new Exception('Suspicious content detected in file');
        }
    }
    
    /**
     * Generate secure filename
     */
    private function generateSecureFilename($originalName, $customName = null) {
        $fileInfo = pathinfo($originalName);
        $extension = strtolower($fileInfo['extension']);
        
        if ($customName) {
            $name = preg_replace('/[^a-zA-Z0-9\-_]/', '', $customName);
        } else {
            $name = preg_replace('/[^a-zA-Z0-9\-_]/', '', $fileInfo['filename']);
            if (empty($name)) {
                $name = 'file';
            }
        }
        
        // Add timestamp for uniqueness
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        
        return $name . '_' . $timestamp . '_' . $random . '.' . $extension;
    }
    
    /**
     * Sanitize subfolder name
     */
    private function sanitizeSubfolder($subfolder) {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $subfolder);
    }
    
    /**
     * Delete uploaded file
     */
    public function delete($filename, $subfolder = '') {
        $targetDir = $this->uploadPath;
        if ($subfolder) {
            $targetDir .= '/' . $this->sanitizeSubfolder($subfolder);
        }
        
        $filePath = $targetDir . '/' . $filename;
        
        if (file_exists($filePath)) {
            unlink($filePath);
            
            $this->logger->logSecurityEvent('file_deleted', [
                'filename' => $filename,
                'subfolder' => $subfolder,
                'path' => $filePath
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get file URL
     */
    public function getFileUrl($filename, $subfolder = '') {
        $baseUrl = rtrim(SITE_URL, '/');
        $uploadUrl = str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->uploadPath);
        
        if ($subfolder) {
            $uploadUrl .= '/' . $subfolder;
        }
        
        return $baseUrl . $uploadUrl . '/' . $filename;
    }
    
    /**
     * Resize image (basic implementation)
     */
    public function resizeImage($filename, $width, $height, $subfolder = '') {
        $targetDir = $this->uploadPath;
        if ($subfolder) {
            $targetDir .= '/' . $this->sanitizeSubfolder($subfolder);
        }
        
        $filePath = $targetDir . '/' . $filename;
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }
        
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $imageType = $imageInfo[2];
        
        // Calculate new dimensions maintaining aspect ratio
        $aspectRatio = $sourceWidth / $sourceHeight;
        if ($width / $height > $aspectRatio) {
            $width = $height * $aspectRatio;
        } else {
            $height = $width / $aspectRatio;
        }
        
        // Create image resource based on type
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($filePath);
                break;
            default:
                return false;
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNG and GIF
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
        }
        
        // Resize image
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
        
        // Generate new filename
        $fileInfo = pathinfo($filename);
        $newFilename = $fileInfo['filename'] . '_' . $width . 'x' . $height . '.' . $fileInfo['extension'];
        $newFilePath = $targetDir . '/' . $newFilename;
        
        // Save resized image
        $saved = false;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $saved = imagejpeg($newImage, $newFilePath, 90);
                break;
            case IMAGETYPE_PNG:
                $saved = imagepng($newImage, $newFilePath, 9);
                break;
            case IMAGETYPE_GIF:
                $saved = imagegif($newImage, $newFilePath);
                break;
        }
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return $saved ? $newFilename : false;
    }
}
?>
