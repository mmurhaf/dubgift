<?php
/**
 * Admin Authentication Functions
 */

// Include main auth system
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/csrf-protection.php';

class AdminAuth extends Auth {
    
    /**
     * Require admin login
     */
    public function requireLogin($redirectUrl = '/admin/') {
        if (!$this->isAdminLoggedIn()) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Require specific admin role
     */
    public function requireRole($role = 'admin') {
        $this->requireLogin();
        
        if (!$this->hasAdminPermission($role)) {
            http_response_code(403);
            include dirname(__DIR__) . '/pages/403.php';
            exit;
        }
    }
    
    /**
     * Check CSRF token for admin forms
     */
    public function requireCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRFProtection::validatePost()) {
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }
    }
    
    /**
     * Get admin navigation based on role
     */
    public function getAdminNavigation() {
        $admin = $this->getCurrentAdmin();
        if (!$admin) {
            return [];
        }
        
        $navigation = [
            'dashboard' => ['title' => 'Dashboard', 'url' => '/admin/dashboard.php', 'icon' => 'dashboard'],
            'products' => ['title' => 'Products', 'url' => '/admin/pages/products.php', 'icon' => 'inventory'],
            'categories' => ['title' => 'Categories', 'url' => '/admin/pages/categories.php', 'icon' => 'category'],
            'brands' => ['title' => 'Brands', 'url' => '/admin/pages/brands.php', 'icon' => 'brand'],
            'orders' => ['title' => 'Orders', 'url' => '/admin/pages/orders.php', 'icon' => 'orders'],
            'customers' => ['title' => 'Customers', 'url' => '/admin/pages/customers.php', 'icon' => 'people']
        ];
        
        // Admin and Super Admin get additional menu items
        if ($this->hasAdminPermission('admin')) {
            $navigation['reports'] = ['title' => 'Reports', 'url' => '/admin/pages/reports.php', 'icon' => 'analytics'];
        }
        
        // Super Admin gets settings
        if ($this->hasAdminPermission('super_admin')) {
            $navigation['settings'] = ['title' => 'Settings', 'url' => '/admin/pages/settings.php', 'icon' => 'settings'];
        }
        
        return $navigation;
    }
    
    /**
     * Log admin action
     */
    public function logAdminAction($action, $details = []) {
        $admin = $this->getCurrentAdmin();
        if ($admin) {
            $logger = SecurityLogger::getInstance();
            $logger->logSecurityEvent('admin_action', [
                'admin_id' => $admin['id'],
                'username' => $admin['username'],
                'role' => $admin['role'],
                'action' => $action,
                'details' => $details
            ]);
        }
    }
}

// Create global admin auth instance
$adminAuth = new AdminAuth();
?>
