<?php
/**
 * Security Events Logger
 */

class SecurityLogger {
    private static $instance = null;
    private $logFile;
    private $maxLogSize = 10485760; // 10MB
    
    private function __construct() {
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $this->logFile = $logDir . '/security.log';
        
        // Create .htaccess to protect log directory
        $htaccessFile = $logDir . '/.htaccess';
        if (!file_exists($htaccessFile)) {
            file_put_contents($htaccessFile, "Order Allow,Deny\nDeny from all");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent($event, $data = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'data' => $data
        ];
        
        $logLine = json_encode($logEntry) . "\n";
        
        // Rotate log if too large
        if (file_exists($this->logFile) && filesize($this->logFile) > $this->maxLogSize) {
            $this->rotateLog();
        }
        
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Rotate log file
     */
    private function rotateLog() {
        $backupFile = $this->logFile . '.' . date('Y-m-d-H-i-s');
        rename($this->logFile, $backupFile);
        
        // Keep only last 5 backup files
        $logDir = dirname($this->logFile);
        $backups = glob($logDir . '/security.log.*');
        if (count($backups) > 5) {
            sort($backups);
            for ($i = 0; $i < count($backups) - 5; $i++) {
                unlink($backups[$i]);
            }
        }
    }
    
    /**
     * Log authentication attempt
     */
    public function logAuthAttempt($type, $identifier, $success, $details = []) {
        $this->logSecurityEvent('auth_attempt', [
            'type' => $type,
            'identifier' => $identifier,
            'success' => $success,
            'details' => $details
        ]);
    }
    
    /**
     * Log file upload attempt
     */
    public function logFileUpload($filename, $success, $details = []) {
        $this->logSecurityEvent('file_upload', [
            'filename' => $filename,
            'success' => $success,
            'details' => $details
        ]);
    }
    
    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity($activity, $details = []) {
        $this->logSecurityEvent('suspicious_activity', [
            'activity' => $activity,
            'details' => $details
        ]);
    }
    
    /**
     * Get recent security events
     */
    public function getRecentEvents($limit = 100) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES);
        $events = [];
        
        // Get last N lines
        $lines = array_slice($lines, -$limit);
        
        foreach ($lines as $line) {
            $event = json_decode($line, true);
            if ($event) {
                $events[] = $event;
            }
        }
        
        return array_reverse($events);
    }
}
?>
