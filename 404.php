<?php
// 404 Error Page - DubGift E-commerce

// Define access constant for security
define('APP_ACCESS', true);

// Include configuration
require_once 'config.php';
require_once 'includes/functions.php';

// Set 404 header
http_response_code(404);

// Page title and meta
$page_title = "Page Not Found - " . SITE_NAME;
$meta_description = "Sorry, the page you are looking for could not be found.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_description; ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .error-content {
            text-align: center;
            color: white;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 1rem;
        }
        .error-message {
            font-size: 1.5rem;
            margin-bottom: 2rem;
        }
        .error-description {
            font-size: 1.1rem;
            margin-bottom: 3rem;
            opacity: 0.9;
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
            color: #667eea;
            transform: translateY(-2px);
        }
        .search-box {
            max-width: 500px;
            margin: 0 auto 2rem;
        }
        .search-input {
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            font-size: 1.1rem;
        }
        .btn-search {
            border-radius: 50px;
            padding: 12px 25px;
            background: #ff6b6b;
            border: none;
        }
        .btn-search:hover {
            background: #ff5252;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="error-content">
                        <!-- Error Code -->
                        <div class="error-code">404</div>
                        
                        <!-- Error Message -->
                        <h1 class="error-message">Oops! Page Not Found</h1>
                        
                        <!-- Error Description -->
                        <p class="error-description">
                            The page you are looking for might have been removed, had its name changed, 
                            or is temporarily unavailable.
                        </p>
                        
                        <!-- Search Box -->
                        <div class="search-box">
                            <form action="pages/products.php" method="GET" class="d-flex gap-2">
                                <input type="text" name="search" class="form-control search-input" 
                                       placeholder="Search for products..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                <button type="submit" class="btn btn-search">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="index.php" class="btn btn-home">
                                <i class="fas fa-home me-2"></i>Go to Homepage
                            </a>
                            <a href="pages/products.php" class="btn btn-home">
                                <i class="fas fa-shopping-bag me-2"></i>Browse Products
                            </a>
                            <a href="pages/contact.php" class="btn btn-home">
                                <i class="fas fa-envelope me-2"></i>Contact Us
                            </a>
                        </div>
                        
                        <!-- Popular Categories -->
                        <div class="mt-5">
                            <h4 class="mb-3">Popular Categories</h4>
                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="pages/categories.php?category=electronics" class="badge bg-light text-dark text-decoration-none p-2">
                                    <i class="fas fa-laptop me-1"></i>Electronics
                                </a>
                                <a href="pages/categories.php?category=fashion" class="badge bg-light text-dark text-decoration-none p-2">
                                    <i class="fas fa-tshirt me-1"></i>Fashion
                                </a>
                                <a href="pages/categories.php?category=home-garden" class="badge bg-light text-dark text-decoration-none p-2">
                                    <i class="fas fa-home me-1"></i>Home & Garden
                                </a>
                                <a href="pages/categories.php?category=sports" class="badge bg-light text-dark text-decoration-none p-2">
                                    <i class="fas fa-dumbbell me-1"></i>Sports
                                </a>
                            </div>
                        </div>
                        
                        <!-- Help Text -->
                        <div class="mt-4">
                            <small class="opacity-75">
                                If you think this is a mistake, please 
                                <a href="pages/contact.php" class="text-white"><u>contact our support team</u></a>.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        // Auto-focus search input
        document.querySelector('.search-input').focus();
        
        // Log 404 error for analytics (optional)
        console.log('404 Error - Page not found:', window.location.href);
        
        // Optional: Send 404 error to analytics
        // gtag('event', 'exception', {
        //     'description': '404 - Page not found: ' + window.location.pathname,
        //     'fatal': false
        // });
    </script>
</body>
</html>
