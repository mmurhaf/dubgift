<?php
// 500 Internal Server Error Page - DubGift E-commerce

// Define access constant for security
define('APP_ACCESS', true);

// Include configuration (with error handling)
try {
    require_once 'config.php';
} catch (Exception $e) {
    // Fallback if config fails
    define('SITE_NAME', 'DubGift');
}

// Set 500 header
http_response_code(500);

$page_title = "Server Error - " . (defined('SITE_NAME') ? SITE_NAME : 'DubGift');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #ff7b7b 0%, #d32f2f 100%);
        }
        .error-content {
            text-align: center;
            color: white;
        }
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }
        .btn-home {
            background: rgba(255,255,255,0.2);
            border: 2px solid white;
            color: white;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            background: white;
            color: #d32f2f;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="error-content">
                        <div class="error-code">500</div>
                        <h1 class="error-message">Internal Server Error</h1>
                        <p class="mb-4">Something went wrong on our end. We're working to fix it!</p>
                        
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="index.php" class="btn btn-home">
                                <i class="fas fa-home me-2"></i>Go to Homepage
                            </a>
                            <a href="pages/contact.php" class="btn btn-home">
                                <i class="fas fa-envelope me-2"></i>Report Issue
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
