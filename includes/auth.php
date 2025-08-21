<?php
/**
 * Authentication and Session Management System
 */

require_once 'database.php';
require_once 'csrf-protection.php';
require_once 'security-logger.php';

class Auth {
    private $db;
    private $logger;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = SecurityLogger::getInstance();
        $this->initSession();
    }
    
    /**
     * Initialize secure session
     */
    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Secure session configuration
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.cookie_samesite', 'Lax');
            
            session_start();
            
            // Regenerate session ID for new sessions
            if (!isset($_SESSION['initiated'])) {
                session_regenerate_id(true);
                $_SESSION['initiated'] = true;
            }
        }
    }
    
    /**
     * Customer login with rate limiting
     */
    public function customerLogin($email, $password, $remember = false) {
        // Check rate limiting
        if (!$this->checkRateLimit($email, 'customer_login')) {
            $this->logger->logSecurityEvent('login_rate_limit_exceeded', [
                'email' => $email,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            throw new Exception('Too many login attempts. Please try again later.');
        }
        
        // Validate input
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Fetch customer from database
        $sql = "SELECT id, email, password, status, failed_attempts, locked_until 
                FROM customers 
                WHERE email = ? AND status = 'active'";
        
        $customer = $this->db->fetchRow($sql, [$email]);
        
        if (!$customer) {
            $this->recordFailedAttempt($email, 'customer_login');
            throw new Exception('Invalid credentials');
        }
        
        // Check if account is locked
        if ($customer['locked_until'] && strtotime($customer['locked_until']) > time()) {
            throw new Exception('Account is temporarily locked');
        }
        
        // Verify password
        if (!password_verify($password, $customer['password'])) {
            $this->recordFailedAttempt($email, 'customer_login');
            $this->incrementFailedAttempts($customer['id'], 'customers');
            throw new Exception('Invalid credentials');
        }
        
        // Reset failed attempts
        $this->resetFailedAttempts($customer['id'], 'customers');
        $this->clearRateLimit($email, 'customer_login');
        
        // Create session
        $this->createCustomerSession($customer);
        
        // Update last login
        $this->updateLastLogin($customer['id'], 'customers');
        
        // Log successful login
        $this->logger->logSecurityEvent('customer_login_success', [
            'customer_id' => $customer['id'],
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        return true;
    }
    
    /**
     * Admin login with enhanced security
     */
    public function adminLogin($username, $password) {
        // Check rate limiting
        if (!$this->checkRateLimit($username, 'admin_login')) {
            $this->logger->logSecurityEvent('admin_login_rate_limit_exceeded', [
                'username' => $username,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            throw new Exception('Too many login attempts. Please try again later.');
        }
        
        // Fetch admin from database
        $sql = "SELECT id, username, email, password, role, status, failed_attempts, locked_until 
                FROM admin_users 
                WHERE username = ? AND status = 'active'";
        
        $admin = $this->db->fetchRow($sql, [$username]);
        
        if (!$admin) {
            $this->recordFailedAttempt($username, 'admin_login');
            throw new Exception('Invalid credentials');
        }
        
        // Check if account is locked
        if ($admin['locked_until'] && strtotime($admin['locked_until']) > time()) {
            throw new Exception('Account is temporarily locked');
        }
        
        // Verify password
        if (!password_verify($password, $admin['password'])) {
            $this->recordFailedAttempt($username, 'admin_login');
            $this->incrementFailedAttempts($admin['id'], 'admin_users');
            throw new Exception('Invalid credentials');
        }
        
        // Reset failed attempts
        $this->resetFailedAttempts($admin['id'], 'admin_users');
        $this->clearRateLimit($username, 'admin_login');
        
        // Create session
        $this->createAdminSession($admin);
        
        // Update last login
        $this->updateLastLogin($admin['id'], 'admin_users');
        
        // Log successful login
        $this->logger->logSecurityEvent('admin_login_success', [
            'admin_id' => $admin['id'],
            'username' => $username,
            'role' => $admin['role'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        return true;
    }
    
    /**
     * Create customer session
     */
    private function createCustomerSession($customer) {
        session_regenerate_id(true);
        
        $_SESSION['customer'] = [
            'id' => $customer['id'],
            'email' => $customer['email'],
            'logged_in' => true,
            'login_time' => time(),
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ];
    }
    
    /**
     * Create admin session
     */
    private function createAdminSession($admin) {
        session_regenerate_id(true);
        
        $_SESSION['admin'] = [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'role' => $admin['role'],
            'logged_in' => true,
            'login_time' => time(),
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ];
    }
    
    /**
     * Check if customer is logged in
     */
    public function isCustomerLoggedIn() {
        return isset($_SESSION['customer']) && 
               $_SESSION['customer']['logged_in'] === true &&
               $this->validateSession('customer');
    }
    
    /**
     * Check if admin is logged in
     */
    public function isAdminLoggedIn() {
        return isset($_SESSION['admin']) && 
               $_SESSION['admin']['logged_in'] === true &&
               $this->validateSession('admin');
    }
    
    /**
     * Validate session
     */
    private function validateSession($type) {
        if (!isset($_SESSION[$type])) {
            return false;
        }
        
        $session = $_SESSION[$type];
        
        // Check session timeout
        if (time() - $session['login_time'] > SESSION_LIFETIME) {
            $this->logout($type);
            return false;
        }
        
        // Check IP address change (security measure)
        if ($session['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            $this->logger->logSecurityEvent('session_ip_mismatch', [
                'type' => $type,
                'user_id' => $session['id'],
                'original_ip' => $session['ip_address'],
                'current_ip' => $_SERVER['REMOTE_ADDR']
            ]);
            $this->logout($type);
            return false;
        }
        
        return true;
    }
    
    /**
     * Logout user
     */
    public function logout($type = 'customer') {
        if (isset($_SESSION[$type])) {
            $this->logger->logSecurityEvent($type . '_logout', [
                'user_id' => $_SESSION[$type]['id'],
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);
            
            unset($_SESSION[$type]);
        }
        
        // If no other user types are logged in, destroy session
        if (!isset($_SESSION['customer']) && !isset($_SESSION['admin'])) {
            session_destroy();
        }
    }
    
    /**
     * Rate limiting check
     */
    private function checkRateLimit($identifier, $action) {
        $key = $action . '_' . $identifier . '_' . $_SERVER['REMOTE_ADDR'];
        $attempts = $_SESSION['rate_limit'][$key] ?? [];
        
        // Clean old attempts
        $attempts = array_filter($attempts, function($time) {
            return (time() - $time) < LOGIN_RATE_WINDOW;
        });
        
        return count($attempts) < LOGIN_RATE_LIMIT;
    }
    
    /**
     * Record failed attempt for rate limiting
     */
    private function recordFailedAttempt($identifier, $action) {
        $key = $action . '_' . $identifier . '_' . $_SERVER['REMOTE_ADDR'];
        $_SESSION['rate_limit'][$key][] = time();
    }
    
    /**
     * Clear rate limit
     */
    private function clearRateLimit($identifier, $action) {
        $key = $action . '_' . $identifier . '_' . $_SERVER['REMOTE_ADDR'];
        unset($_SESSION['rate_limit'][$key]);
    }
    
    /**
     * Increment failed login attempts in database
     */
    private function incrementFailedAttempts($userId, $table) {
        $sql = "UPDATE $table SET 
                failed_attempts = failed_attempts + 1,
                locked_until = CASE 
                    WHEN failed_attempts >= 4 THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                    ELSE locked_until 
                END
                WHERE id = ?";
        
        $this->db->execute($sql, [$userId]);
    }
    
    /**
     * Reset failed login attempts
     */
    private function resetFailedAttempts($userId, $table) {
        $sql = "UPDATE $table SET failed_attempts = 0, locked_until = NULL WHERE id = ?";
        $this->db->execute($sql, [$userId]);
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin($userId, $table) {
        $sql = "UPDATE $table SET last_login = NOW() WHERE id = ?";
        $this->db->execute($sql, [$userId]);
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Get current customer
     */
    public function getCurrentCustomer() {
        if ($this->isCustomerLoggedIn()) {
            return $_SESSION['customer'];
        }
        return null;
    }
    
    /**
     * Get current admin
     */
    public function getCurrentAdmin() {
        if ($this->isAdminLoggedIn()) {
            return $_SESSION['admin'];
        }
        return null;
    }
    
    /**
     * Check admin permission
     */
    public function hasAdminPermission($requiredRole = 'admin') {
        $admin = $this->getCurrentAdmin();
        if (!$admin) {
            return false;
        }
        
        $roles = ['manager' => 1, 'admin' => 2, 'super_admin' => 3];
        $userLevel = $roles[$admin['role']] ?? 0;
        $requiredLevel = $roles[$requiredRole] ?? 3;
        
        return $userLevel >= $requiredLevel;
    }
}
?>
