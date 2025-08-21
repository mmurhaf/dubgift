<?php
/**
 * Database Backup System
 */

require_once 'database.php';
require_once 'security-logger.php';

class BackupSystem {
    private $db;
    private $logger;
    private $backupPath;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = SecurityLogger::getInstance();
        $this->backupPath = dirname(__DIR__) . '/backups/';
        
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
        
        // Create .htaccess to protect backup directory
        $htaccessFile = $this->backupPath . '/.htaccess';
        if (!file_exists($htaccessFile)) {
            file_put_contents($htaccessFile, "Order Allow,Deny\nDeny from all");
        }
    }
    
    /**
     * Create database backup
     */
    public function createDatabaseBackup() {
        try {
            $filename = 'dubgift_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->backupPath . $filename;
            
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg(DB_USERNAME),
                escapeshellarg(DB_PASSWORD),
                escapeshellarg(DB_HOST),
                escapeshellarg(DB_DATABASE),
                escapeshellarg($filepath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($filepath)) {
                // Compress backup
                $compressedFile = $filepath . '.gz';
                $gzFile = gzopen($compressedFile, 'wb9');
                $sqlFile = fopen($filepath, 'rb');
                
                while (!feof($sqlFile)) {
                    gzwrite($gzFile, fread($sqlFile, 1024));
                }
                
                fclose($sqlFile);
                gzclose($gzFile);
                
                // Remove uncompressed file
                unlink($filepath);
                
                $this->logger->logSecurityEvent('database_backup_created', [
                    'filename' => basename($compressedFile),
                    'size' => filesize($compressedFile)
                ]);
                
                // Clean old backups (keep last 10)
                $this->cleanOldBackups();
                
                return [
                    'success' => true,
                    'filename' => basename($compressedFile),
                    'size' => filesize($compressedFile)
                ];
            } else {
                throw new Exception('Backup command failed');
            }
            
        } catch (Exception $e) {
            $this->logger->logSecurityEvent('database_backup_failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create files backup
     */
    public function createFilesBackup() {
        try {
            $filename = 'dubgift_files_backup_' . date('Y-m-d_H-i-s') . '.tar.gz';
            $filepath = $this->backupPath . $filename;
            
            $sourceDir = dirname(__DIR__);
            $excludeFiles = [
                '--exclude=backups',
                '--exclude=logs',
                '--exclude=.git',
                '--exclude=vendor/bin',
                '--exclude=.env'
            ];
            
            $command = sprintf(
                'tar -czf %s %s -C %s .',
                escapeshellarg($filepath),
                implode(' ', $excludeFiles),
                escapeshellarg($sourceDir)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($filepath)) {
                $this->logger->logSecurityEvent('files_backup_created', [
                    'filename' => basename($filepath),
                    'size' => filesize($filepath)
                ]);
                
                return [
                    'success' => true,
                    'filename' => basename($filepath),
                    'size' => filesize($filepath)
                ];
            } else {
                throw new Exception('Files backup command failed');
            }
            
        } catch (Exception $e) {
            $this->logger->logSecurityEvent('files_backup_failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Clean old backups
     */
    private function cleanOldBackups() {
        $backups = glob($this->backupPath . 'dubgift_backup_*.sql.gz');
        if (count($backups) > 10) {
            // Sort by modification time
            usort($backups, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Remove oldest backups
            for ($i = 0; $i < count($backups) - 10; $i++) {
                unlink($backups[$i]);
            }
        }
        
        // Clean old file backups
        $fileBackups = glob($this->backupPath . 'dubgift_files_backup_*.tar.gz');
        if (count($fileBackups) > 5) {
            usort($fileBackups, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            for ($i = 0; $i < count($fileBackups) - 5; $i++) {
                unlink($fileBackups[$i]);
            }
        }
    }
    
    /**
     * List available backups
     */
    public function listBackups() {
        $backups = [];
        
        // Database backups
        $dbBackups = glob($this->backupPath . 'dubgift_backup_*.sql.gz');
        foreach ($dbBackups as $backup) {
            $backups[] = [
                'type' => 'database',
                'filename' => basename($backup),
                'size' => filesize($backup),
                'created' => filemtime($backup)
            ];
        }
        
        // File backups
        $fileBackups = glob($this->backupPath . 'dubgift_files_backup_*.tar.gz');
        foreach ($fileBackups as $backup) {
            $backups[] = [
                'type' => 'files',
                'filename' => basename($backup),
                'size' => filesize($backup),
                'created' => filemtime($backup)
            ];
        }
        
        // Sort by creation time (newest first)
        usort($backups, function($a, $b) {
            return $b['created'] - $a['created'];
        });
        
        return $backups;
    }
    
    /**
     * Schedule automatic backups
     */
    public function scheduleAutoBackup() {
        // This would typically be called from a cron job
        $lastBackup = $this->getLastBackupTime();
        $backupInterval = 24 * 60 * 60; // 24 hours
        
        if (time() - $lastBackup > $backupInterval) {
            $result = $this->createDatabaseBackup();
            if ($result['success']) {
                $this->logger->logSecurityEvent('auto_backup_completed', $result);
            }
        }
    }
    
    /**
     * Get last backup time
     */
    private function getLastBackupTime() {
        $backups = glob($this->backupPath . 'dubgift_backup_*.sql.gz');
        if (empty($backups)) {
            return 0;
        }
        
        $lastBackup = max(array_map('filemtime', $backups));
        return $lastBackup;
    }
}
?>
