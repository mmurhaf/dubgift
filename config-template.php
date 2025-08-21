<?php
/**
 * DubGift E-commerce Configuration Template - Secure Production Ready
 * Last Updated: August 21, 2025
 * 
 * INSTRUCTIONS:
 * 1. Copy this file to ../dubgift-config/config.php (outside web root)
 * 2. Create a .env file based on .env.example
 * 3. Update environment variables with your actual values
 * 4. Ensure proper file permissions (600 for config.php)
 */

// Prevent direct access
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Load environment configuration
require_once __DIR__ . '/../public/includes/env-loader.php';
EnvLoader::load();

// Environment Detection
define('ENVIRONMENT', EnvLoader::get('ENVIRONMENT', 'development'));
define('IS_PRODUCTION', ENVIRONMENT === 'production');
define('DEBUG_MODE', EnvLoader::getBool('DEBUG_MODE', false) && !IS_PRODUCTION);

// Database Configuration
define('DB_HOST', EnvLoader::getRequired('DB_HOST'));
define('DB_USERNAME', EnvLoader::getRequired('DB_USERNAME'));
define('DB_PASSWORD', EnvLoader::getRequired('DB_PASSWORD'));
define('DB_DATABASE', EnvLoader::getRequired('DB_DATABASE'));
define('DB_CHARSET', EnvLoader::get('DB_CHARSET', 'utf8mb4'));

// Site Configuration
define('SITE_NAME', EnvLoader::get('SITE_NAME', 'DubGift'));
define('SITE_URL', EnvLoader::getRequired('SITE_URL'));
define('SITE_EMAIL', EnvLoader::getRequired('SITE_EMAIL'));
define('ADMIN_EMAIL', EnvLoader::getRequired('ADMIN_EMAIL'));
define('SITE_DESCRIPTION', EnvLoader::get('SITE_DESCRIPTION', 'Luxury Gifts & Home Decor from Premium Brands'));

// Security Settings
define('ENCRYPTION_KEY', EnvLoader::getRequired('ENCRYPTION_KEY'));
define('SESSION_LIFETIME', EnvLoader::getInt('SESSION_LIFETIME', 7200));
define('SESSION_TIMEOUT', SESSION_LIFETIME); // Alias for compatibility
define('PASSWORD_SALT', EnvLoader::getRequired('PASSWORD_SALT'));
define('CSRF_SECRET', EnvLoader::getRequired('CSRF_SECRET'));
define('CSRF_TOKEN_EXPIRY', EnvLoader::getInt('CSRF_TOKEN_EXPIRY', 3600));

// Enhanced Security Settings
define('MAX_LOGIN_ATTEMPTS', EnvLoader::getInt('MAX_LOGIN_ATTEMPTS', 5));
define('LOGIN_LOCKOUT_TIME', EnvLoader::getInt('LOGIN_LOCKOUT_TIME', 900)); // 15 minutes
define('LOGIN_RATE_LIMIT', EnvLoader::getInt('LOGIN_RATE_LIMIT', 5));
define('LOGIN_RATE_WINDOW', EnvLoader::getInt('LOGIN_RATE_WINDOW', 900));

// Payment Configuration - Ziina
define('ZIINA_API_URL', 'https://api-v2.ziina.com/api/payment_intent');
define('ZIINA_API_KEY', EnvLoader::get('ZIINA_API_KEY'));
define('ZIINA_SECRET_KEY', EnvLoader::get('ZIINA_SECRET_KEY'));
define('ZIINA_SANDBOX', EnvLoader::getBool('ZIINA_SANDBOX', true));
define('ZIINA_TEST_MODE', EnvLoader::getBool('ZIINA_TEST_MODE', true));

// Email Configuration (SMTP) - Secure Implementation
define('EMAIL_FROM', EnvLoader::get('EMAIL_FROM', EnvLoader::get('SITE_EMAIL')));
define('EMAIL_FROM_NAME', EnvLoader::get('EMAIL_FROM_NAME', SITE_NAME));
define('SMTP_HOST', EnvLoader::get('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', EnvLoader::getInt('SMTP_PORT', 587));
define('SMTP_USERNAME', EnvLoader::get('SMTP_USERNAME'));
define('SMTP_PASSWORD', EnvLoader::get('SMTP_PASSWORD'));
define('SMTP_ENCRYPTION', EnvLoader::get('SMTP_ENCRYPTION', 'tls'));

// WhatsApp Configuration
define('WHATSAPP_API_URL', EnvLoader::get('WHATSAPP_API_URL'));
define('WHATSAPP_API_KEY', EnvLoader::get('WHATSAPP_API_KEY'));
define('WHATSAPP_ADMIN_NUMBER', EnvLoader::get('WHATSAPP_ADMIN_NUMBER'));

// File Upload Settings
define('MAX_FILE_SIZE', EnvLoader::getInt('MAX_FILE_SIZE', 5 * 1024 * 1024)); // 5MB
define('ALLOWED_IMAGE_TYPES', explode(',', EnvLoader::get('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp')));
define('ALLOWED_FILE_TYPES', explode(',', EnvLoader::get('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf')));
define('UPLOAD_PATH', EnvLoader::get('UPLOAD_PATH', '../secure_uploads/'));
define('MAX_CART_ITEMS', EnvLoader::getInt('MAX_CART_ITEMS', 50));

// Currency and Commerce Settings
define('CURRENCY', EnvLoader::get('CURRENCY', 'AED'));
define('DEFAULT_CURRENCY', CURRENCY); // Alias for compatibility
define('CURRENCY_SYMBOL', EnvLoader::get('CURRENCY_SYMBOL', 'د.إ'));
define('TAX_RATE', EnvLoader::getFloat('TAX_RATE', 5.0)); // 5% VAT
define('DEFAULT_SHIPPING_COST', EnvLoader::getFloat('DEFAULT_SHIPPING_COST', 25.0));
define('FREE_SHIPPING_THRESHOLD', EnvLoader::getFloat('FREE_SHIPPING_THRESHOLD', 500.0));

// Pagination Settings
define('PRODUCTS_PER_PAGE', EnvLoader::getInt('PRODUCTS_PER_PAGE', 12));
define('ORDERS_PER_PAGE', EnvLoader::getInt('ORDERS_PER_PAGE', 20));
define('CUSTOMERS_PER_PAGE', EnvLoader::getInt('CUSTOMERS_PER_PAGE', 25));

// Error Reporting Configuration
if (DEBUG_MODE && !IS_PRODUCTION) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Error logging
ini_set('log_errors', 1);
$logsDir = __DIR__ . '/../logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
    file_put_contents($logsDir . '/.htaccess', "Order Deny,Allow\nDeny from all\n");
}
ini_set('error_log', $logsDir . '/error.log');

// Timezone Configuration
date_default_timezone_set('Asia/Dubai');

// Security Headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Powered-By: ' . SITE_NAME);
    
    if (IS_PRODUCTION && isset($_SERVER['HTTPS'])) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com; font-src \'self\' https://fonts.gstatic.com; img-src \'self\' data: https:; connect-src \'self\';');
    }
}

// Session Security Configuration (must be set before session_start)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', IS_PRODUCTION ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
    ini_set('session.name', 'DUBGIFT_SESSION');
    
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => IS_PRODUCTION,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

// Production Security Validation
if (IS_PRODUCTION) {
    $critical_configs = [
        'ZIINA_API_KEY' => ZIINA_API_KEY,
        'SMTP_PASSWORD' => SMTP_PASSWORD,
        'ENCRYPTION_KEY' => ENCRYPTION_KEY,
        'CSRF_SECRET' => CSRF_SECRET,
        'PASSWORD_SALT' => PASSWORD_SALT
    ];
    
    foreach ($critical_configs as $key => $value) {
        if (empty($value) || 
            $value === 'test_key_placeholder' || 
            $value === 'your_secure_password_here' ||
            strlen($value) < 16) {
            error_log("SECURITY CRITICAL: $key is not properly configured for production!");
            // Don't expose which config is missing in production
            if (DEBUG_MODE) {
                die("Critical configuration error: $key must be set for production use!");
            }
        }
    }
    
    // Ensure HTTPS in production
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        if (!headers_sent()) {
            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
            exit();
        }
    }
}

// Application Constants
define('APP_VERSION', '2.0.0');
define('CONFIG_LOADED', true);
define('CONFIG_LOAD_TIME', microtime(true));

// Development helper (only in debug mode)
if (DEBUG_MODE && !IS_PRODUCTION) {
    define('CONFIG_DEBUG_INFO', [
        'environment' => ENVIRONMENT,
        'debug_mode' => DEBUG_MODE,
        'is_production' => IS_PRODUCTION,
        'config_loaded_at' => date('Y-m-d H:i:s'),
        'session_lifetime' => SESSION_LIFETIME,
        'timezone' => date_default_timezone_get()
    ]);
}
?>
