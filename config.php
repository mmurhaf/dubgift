<?php
// Database Configuration and Global Settings
// Security: This file should be protected by .htaccess or moved outside web root

// Prevent direct access
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'your_db_username');
define('DB_PASSWORD', 'your_db_password');
define('DB_DATABASE', 'dubgift_ecommerce');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'DubGift');
define('SITE_URL', 'https://dubgift.com');
define('SITE_EMAIL', 'info@dubgift.com');
define('ADMIN_EMAIL', 'admin@dubgift.com');

// Security Settings
define('ENCRYPTION_KEY', 'your-32-character-secret-key-here');
define('SESSION_LIFETIME', 7200); // 2 hours
define('PASSWORD_SALT', 'your-unique-salt-here');

// Payment Configuration
define('ZIINA_API_KEY', 'your_ziina_api_key');
define('ZIINA_SECRET_KEY', 'your_ziina_secret_key');
define('ZIINA_SANDBOX', true); // Set to false for production

// Email Configuration (SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_email_password');
define('SMTP_ENCRYPTION', 'tls');

// WhatsApp Configuration
define('WHATSAPP_API_URL', 'your_whatsapp_api_endpoint');
define('WHATSAPP_API_KEY', 'your_whatsapp_api_key');
define('WHATSAPP_ADMIN_NUMBER', '+971XXXXXXXXX');

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_PATH', './assets/uploads/');

// Currency Settings
define('DEFAULT_CURRENCY', 'AED');
define('CURRENCY_SYMBOL', 'د.إ');
define('TAX_RATE', 5); // 5% VAT
define('FREE_SHIPPING_THRESHOLD', 200); // Free shipping over 200 AED

// Pagination Settings
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 20);
define('CUSTOMERS_PER_PAGE', 25);

// Error Reporting (Set to false for production)
define('DEBUG_MODE', true);
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Dubai');

// Session Configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}
?>
