<?php
/**
 * Secure File Serving Script
 * This script serves files from outside the web root with proper security checks
 */

define('APP_ACCESS', true);
require_once '../dubgift-config/config.php';
require_once 'includes/auth.php';
require_once 'includes/security-logger.php';

class SecureFileServer {
    private $secureUploadPath;
    private $logger;
    
    public function __construct() {
        $this->secureUploadPath = dirname(__DIR__) . '/secure_uploads/';
        $this->logger = SecurityLogger::getInstance();
    }
    
    /**
     * Serve file securely
     */
    public function serveFile($filepath, $subfolder = '') {
        try {
            // Sanitize inputs
            $filepath = basename($filepath);
            $subfolder = preg_replace('/[^a-zA-Z0-9\-_]/', '', $subfolder);
            
            // Build full path
            $fullPath = $this->secureUploadPath;
            if ($subfolder) {
                $fullPath .= $subfolder . '/';
            }
            $fullPath .= $filepath;
            
            // Validate file exists
            if (!file_exists($fullPath)) {
                http_response_code(404);
                exit('File not found');
            }
            
            // Validate file is within allowed directory
            $realPath = realpath($fullPath);
            $realBasePath = realpath($this->secureUploadPath);
            
            if (strpos($realPath, $realBasePath) !== 0) {
                $this->logger->logSuspiciousActivity('path_traversal_attempt', [
                    'requested_path' => $filepath,
                    'subfolder' => $subfolder,
                    'real_path' => $realPath
                ]);
                http_response_code(403);
                exit('Access denied');
            }
            
            // Get file info
            $fileInfo = pathinfo($fullPath);
            $extension = strtolower($fileInfo['extension']);
            
            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $allowedTypes)) {
                http_response_code(403);
                exit('File type not allowed');
            }
            
            // Set appropriate headers
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp'
            ];
            
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            
            // Security headers
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($fullPath));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
            header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
            
            // Output file
            readfile($fullPath);
            
        } catch (Exception $e) {
            $this->logger->logSecurityEvent('file_serve_error', [
                'filepath' => $filepath,
                'subfolder' => $subfolder,
                'error' => $e->getMessage()
            ]);
            
            http_response_code(500);
            exit('Internal server error');
        }
    }
}

// Handle file serving
if (isset($_GET['file'])) {
    $fileServer = new SecureFileServer();
    $fileServer->serveFile($_GET['file'], $_GET['folder'] ?? '');
} else {
    http_response_code(400);
    exit('No file specified');
}
?>
