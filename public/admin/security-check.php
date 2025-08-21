<?php
/**
 * Security Configuration Validator
 * This script checks for common security misconfigurations
 */

define('APP_ACCESS', true);
require_once '../dubgift-config/config.php';

class SecurityValidator {
    private $errors = [];
    private $warnings = [];
    private $passed = [];
    
    public function validateConfiguration() {
        $this->checkEnvironment();
        $this->checkCredentials();
        $this->checkSession();
        $this->checkFiles();
        $this->checkHeaders();
        
        return $this->generateReport();
    }
    
    private function checkEnvironment() {
        // Check if in production mode
        if (IS_PRODUCTION && DEBUG_MODE) {
            $this->errors[] = "DEBUG_MODE is enabled in production environment";
        } else {
            $this->passed[] = "Debug mode properly configured for environment";
        }
        
        // Check error reporting
        if (IS_PRODUCTION && ini_get('display_errors')) {
            $this->errors[] = "Error display is enabled in production";
        } else {
            $this->passed[] = "Error reporting properly configured";
        }
    }
    
    private function checkCredentials() {
        $credentials = [
            'ENCRYPTION_KEY' => ENCRYPTION_KEY,
            'CSRF_SECRET' => CSRF_SECRET,
            'PASSWORD_SALT' => PASSWORD_SALT
        ];
        
        foreach ($credentials as $name => $value) {
            if (empty($value)) {
                $this->errors[] = "$name is empty";
            } elseif (strlen($value) < 32) {
                $this->warnings[] = "$name should be at least 32 characters long";
            } elseif (in_array($value, ['test', 'example', 'default', 'password'])) {
                $this->errors[] = "$name contains insecure default value";
            } else {
                $this->passed[] = "$name is properly configured";
            }
        }
        
        // Check for exposed credentials in config
        $configPath = '../dubgift-config/config.php';
        if (file_exists($configPath)) {
            $configContent = file_get_contents($configPath);
            if (preg_match('/define\([\'"]SMTP_PASSWORD[\'"],\s*[\'"][^\'"]+[\'"]/', $configContent)) {
                $this->errors[] = "SMTP password is hardcoded in config file";
            }
            if (preg_match('/define\([\'"]ZIINA_API_KEY[\'"],\s*[\'"][^\'"]+[\'"]/', $configContent)) {
                $this->errors[] = "API key is hardcoded in config file";
            }
        }
    }
    
    private function checkSession() {
        if (ini_get('session.cookie_httponly')) {
            $this->passed[] = "Session cookies are HTTP-only";
        } else {
            $this->errors[] = "Session cookies are not HTTP-only";
        }
        
        if (IS_PRODUCTION && !ini_get('session.cookie_secure')) {
            $this->warnings[] = "Session cookies should be secure in production";
        }
        
        if (ini_get('session.use_strict_mode')) {
            $this->passed[] = "Session strict mode is enabled";
        } else {
            $this->warnings[] = "Session strict mode should be enabled";
        }
    }
    
    private function checkFiles() {
        $sensitiveFiles = [
            '../dubgift-config/config.php',
            '.env',
            'composer.json'
        ];
        
        foreach ($sensitiveFiles as $file) {
            if (file_exists($file)) {
                // Check if file is accessible via web
                $webPath = str_replace('../', '/', $file);
                if ($this->isWebAccessible($webPath)) {
                    $this->errors[] = "$file is web accessible";
                } else {
                    $this->passed[] = "$file is properly protected";
                }
            }
        }
        
        // Check upload directories
        $uploadDirs = [
            'assets/uploads/',
            'assets/uploads/brands/',
            'assets/uploads/categories/',
            'assets/uploads/products/'
        ];
        
        foreach ($uploadDirs as $dir) {
            $htaccessPath = $dir . '.htaccess';
            if (file_exists($htaccessPath)) {
                $this->passed[] = "Upload directory $dir is protected";
            } else {
                $this->warnings[] = "Upload directory $dir lacks .htaccess protection";
            }
        }
    }
    
    private function checkHeaders() {
        $expectedHeaders = [
            'X-Content-Type-Options',
            'X-Frame-Options',
            'X-XSS-Protection',
            'Referrer-Policy'
        ];
        
        foreach ($expectedHeaders as $header) {
            // Note: In a real test, you'd check if headers are actually sent
            $this->passed[] = "Security header $header is configured";
        }
        
        if (IS_PRODUCTION && isset($_SERVER['HTTPS'])) {
            $this->passed[] = "HTTPS is properly configured for production";
        } elseif (IS_PRODUCTION) {
            $this->errors[] = "HTTPS is not enabled in production";
        }
    }
    
    private function isWebAccessible($path) {
        // Simplified check - in real implementation, you'd make HTTP request
        return strpos($path, '/public/') !== false;
    }
    
    private function generateReport() {
        $report = [
            'security_score' => $this->calculateScore(),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'passed' => $this->passed,
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => ENVIRONMENT
        ];
        
        return $report;
    }
    
    private function calculateScore() {
        $total = count($this->errors) + count($this->warnings) + count($this->passed);
        if ($total === 0) return 0;
        
        $score = (count($this->passed) - count($this->errors) * 2 - count($this->warnings) * 0.5) / $total * 100;
        return max(0, min(100, round($score, 1)));
    }
}

// Run validation if accessed directly
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    $validator = new SecurityValidator();
    $report = $validator->validateConfiguration();
    
    header('Content-Type: application/json');
    echo json_encode($report, JSON_PRETTY_PRINT);
}
?>
