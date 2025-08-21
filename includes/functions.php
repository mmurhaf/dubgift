<?php
/**
 * Common Utility Functions with Security Features
 */

require_once 'input-validator.php';
require_once 'security-logger.php';

/**
 * Safely redirect with validation
 */
function safe_redirect($url, $statusCode = 302) {
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/^\/[a-zA-Z0-9\/_\-\.]*$/', $url)) {
        $url = '/';
    }
    
    // Prevent open redirect attacks
    $parsedUrl = parse_url($url);
    if (isset($parsedUrl['host']) && $parsedUrl['host'] !== $_SERVER['HTTP_HOST']) {
        $url = '/';
    }
    
    http_response_code($statusCode);
    header('Location: ' . $url);
    exit;
}

/**
 * Generate secure random token
 */
function generate_secure_token($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Format price with currency
 */
function format_price($price, $currency = null) {
    $currency = $currency ?? DEFAULT_CURRENCY;
    $symbol = CURRENCY_SYMBOL;
    
    return $symbol . ' ' . number_format($price, 2);
}

/**
 * Calculate tax amount
 */
function calculate_tax($amount, $rate = null) {
    $rate = $rate ?? TAX_RATE;
    return $amount * ($rate / 100);
}

/**
 * Get file size in human readable format
 */
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Safely get POST data with validation
 */
function get_post_data($key, $default = null, $filter = FILTER_SANITIZE_STRING) {
    if (!isset($_POST[$key])) {
        return $default;
    }
    
    $value = $_POST[$key];
    
    if ($filter) {
        $value = filter_var($value, $filter);
    }
    
    return $value;
}

/**
 * Safely get GET data with validation
 */
function get_get_data($key, $default = null, $filter = FILTER_SANITIZE_STRING) {
    if (!isset($_GET[$key])) {
        return $default;
    }
    
    $value = $_GET[$key];
    
    if ($filter) {
        $value = filter_var($value, $filter);
    }
    
    return $value;
}

/**
 * Generate pagination HTML
 */
function generate_pagination($currentPage, $totalPages, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $prevUrl = htmlspecialchars($baseUrl . '?page=' . ($currentPage - 1));
        $html .= '<li class="page-item"><a class="page-link" href="' . $prevUrl . '">Previous</a></li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i === $currentPage) ? ' active' : '';
        $pageUrl = htmlspecialchars($baseUrl . '?page=' . $i);
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $pageUrl . '">' . $i . '</a></li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $nextUrl = htmlspecialchars($baseUrl . '?page=' . ($currentPage + 1));
        $html .= '<li class="page-item"><a class="page-link" href="' . $nextUrl . '">Next</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Check if user has permission
 */
function has_permission($permission, $userType = 'customer') {
    global $auth, $adminAuth;
    
    if ($userType === 'admin') {
        return $adminAuth ? $adminAuth->hasAdminPermission($permission) : false;
    }
    
    return $auth ? $auth->isCustomerLoggedIn() : false;
}

/**
 * Log security event (wrapper function)
 */
function log_security_event($event, $data = []) {
    $logger = SecurityLogger::getInstance();
    $logger->logSecurityEvent($event, $data);
}

/**
 * Validate and sanitize form data
 */
function validate_form_data($data, $rules) {
    return InputValidator::validateAndSanitize($data, $rules);
}

/**
 * Get client IP address
 */
function get_client_ip() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            
            // Handle comma-separated IPs
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Generate breadcrumb navigation
 */
function generate_breadcrumb($items) {
    if (empty($items)) {
        return '';
    }
    
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    $last = count($items) - 1;
    foreach ($items as $index => $item) {
        if ($index === $last) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($item['title']) . '</li>';
        } else {
            $url = htmlspecialchars($item['url']);
            $title = htmlspecialchars($item['title']);
            $html .= '<li class="breadcrumb-item"><a href="' . $url . '">' . $title . '</a></li>';
        }
    }
    
    $html .= '</ol></nav>';
    
    return $html;
}

/**
 * Get meta tags for SEO
 */
function get_meta_tags($title, $description = '', $keywords = '', $image = '') {
    $siteName = SITE_NAME;
    $siteUrl = SITE_URL;
    
    $html = '<title>' . htmlspecialchars($title . ' | ' . $siteName) . '</title>' . "\n";
    
    if ($description) {
        $html .= '<meta name="description" content="' . htmlspecialchars($description) . '">' . "\n";
        $html .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . "\n";
    }
    
    if ($keywords) {
        $html .= '<meta name="keywords" content="' . htmlspecialchars($keywords) . '">' . "\n";
    }
    
    $html .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">' . "\n";
    $html .= '<meta property="og:site_name" content="' . htmlspecialchars($siteName) . '">' . "\n";
    $html .= '<meta property="og:url" content="' . htmlspecialchars($siteUrl . $_SERVER['REQUEST_URI']) . '">' . "\n";
    
    if ($image) {
        $html .= '<meta property="og:image" content="' . htmlspecialchars($image) . '">' . "\n";
    }
    
    return $html;
}

/**
 * Check if site is in maintenance mode
 */
function is_maintenance_mode() {
    return file_exists(dirname(__DIR__) . '/maintenance.flag');
}

/**
 * Enable maintenance mode
 */
function enable_maintenance_mode() {
    file_put_contents(dirname(__DIR__) . '/maintenance.flag', time());
    log_security_event('maintenance_mode_enabled', ['admin_id' => $_SESSION['admin']['id'] ?? null]);
}

/**
 * Disable maintenance mode
 */
function disable_maintenance_mode() {
    $flagFile = dirname(__DIR__) . '/maintenance.flag';
    if (file_exists($flagFile)) {
        unlink($flagFile);
        log_security_event('maintenance_mode_disabled', ['admin_id' => $_SESSION['admin']['id'] ?? null]);
    }
}

/**
 * Time ago function
 */
function time_ago($timestamp) {
    $time = time() - $timestamp;
    
    if ($time < 60) {
        return 'Just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $timestamp);
    }
}
?>
