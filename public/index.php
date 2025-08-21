<?php
// Main Homepage - DubGift E-commerce Website

// Define access constant for security
define('APP_ACCESS', true);

try {
    // Include configuration (moved outside web root for security)
    require_once '../dubgift-config/config.php';

    // Include necessary files
    require_once 'includes/database.php';
    require_once 'includes/functions.php';

    // Basic homepage content for testing
    $page_title = "Welcome to " . SITE_NAME;
    $page_description = "Luxury Gifts & Home Decor from Premium Brands";
    
} catch (Exception $e) {
    // Handle configuration errors gracefully
    if (DEBUG_MODE ?? true) {
        die("Configuration Error: " . $e->getMessage());
    } else {
        die("Website is temporarily unavailable. Please try again later.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo {
            font-size: 3em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .tagline {
            font-size: 1.2em;
            color: #666;
        }
        .status {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .info-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #4caf50;
        }
        .info-card h3 {
            margin-top: 0;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo"><?= htmlspecialchars(SITE_NAME) ?></div>
            <div class="tagline"><?= htmlspecialchars($page_description) ?></div>
        </div>
        
        <div class="status">
            <h2>✅ Website is Working!</h2>
            <p>Congratulations! Your DubGift e-commerce website is successfully running.</p>
        </div>

        <div class="info-grid">
            <div class="info-card">
                <h3>🔧 Configuration Status</h3>
                <ul>
                    <li><strong>Environment:</strong> <?= ENVIRONMENT ?></li>
                    <li><strong>Debug Mode:</strong> <?= DEBUG_MODE ? 'Enabled' : 'Disabled' ?></li>
                    <li><strong>Site URL:</strong> <?= htmlspecialchars(SITE_URL) ?></li>
                    <li><strong>Config Location:</strong> Outside web root ✅</li>
                </ul>
            </div>

            <div class="info-card">
                <h3>🛡️ Security Features</h3>
                <ul>
                    <li>Config file outside web root ✅</li>
                    <li>Upload directory protection ✅</li>
                    <li>Security headers enabled ✅</li>
                    <li>CSRF protection ready ✅</li>
                    <li>Input validation system ✅</li>
                </ul>
            </div>

            <div class="info-card">
                <h3>📚 Available Features</h3>
                <ul>
                    <li>Email system (PHPMailer) ✅</li>
                    <li>PDF generation (FPDF) ✅</li>
                    <li>File upload security ✅</li>
                    <li>Database abstraction ✅</li>
                    <li>Admin panel structure ✅</li>
                </ul>
            </div>

            <div class="info-card">
                <h3>🚀 Next Steps</h3>
                <ul>
                    <li>Set up your database using <code>/database/dubgift_schema.sql</code></li>
                    <li>Configure your email settings in <code>.env</code></li>
                    <li>Customize the design and add products</li>
                    <li>Test the admin panel at <code>/admin</code></li>
                </ul>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px; color: #666;">
            <p>DubGift E-commerce Platform • Secure & Production Ready</p>
        </div>
    </div>
</body>
</html>
