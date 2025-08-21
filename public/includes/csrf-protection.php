<?php
/**
 * CSRF Protection Class
 */

class CSRFProtection {
    const TOKEN_NAME = 'csrf_token';
    const TOKEN_LIFETIME = 3600; // 1 hour
    
    /**
     * Generate CSRF token
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $timestamp = time();
        
        $_SESSION[self::TOKEN_NAME] = [
            'token' => $token,
            'timestamp' => $timestamp
        ];
        
        return $token;
    }
    
    /**
     * Get current CSRF token
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if token exists and is not expired
        if (isset($_SESSION[self::TOKEN_NAME])) {
            $tokenData = $_SESSION[self::TOKEN_NAME];
            
            if (time() - $tokenData['timestamp'] <= self::TOKEN_LIFETIME) {
                return $tokenData['token'];
            } else {
                // Token expired, generate new one
                unset($_SESSION[self::TOKEN_NAME]);
            }
        }
        
        return self::generateToken();
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }
        
        $sessionData = $_SESSION[self::TOKEN_NAME];
        
        // Check token validity and expiration
        if (hash_equals($sessionData['token'], $token) && 
            (time() - $sessionData['timestamp']) <= self::TOKEN_LIFETIME) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate hidden input field for forms
     */
    public static function getFormField() {
        $token = self::getToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Validate CSRF token from POST request
     */
    public static function validatePost() {
        $token = $_POST[self::TOKEN_NAME] ?? '';
        return self::validateToken($token);
    }
    
    /**
     * Clear CSRF token (after successful validation)
     */
    public static function clearToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION[self::TOKEN_NAME]);
    }
}
?>
