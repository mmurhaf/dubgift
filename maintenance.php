<?php
// Maintenance Mode Page - DubGift E-commerce

// Define access constant for security
define('APP_ACCESS', true);

// Check if maintenance mode is enabled
$maintenance_enabled = false; // Set to true to enable maintenance mode

if (!$maintenance_enabled && !isset($_GET['preview'])) {
    // Redirect to homepage if maintenance is not enabled
    header('Location: index.php');
    exit;
}

// Set 503 header for maintenance
http_response_code(503);
header('Retry-After: 3600'); // Try again in 1 hour

$page_title = "Under Maintenance - DubGift";
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
        .maintenance-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .maintenance-content {
            text-align: center;
            color: white;
        }
        .maintenance-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            animation: spin 3s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .maintenance-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .maintenance-message {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .countdown {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            backdrop-filter: blur(10px);
        }
        .countdown-item {
            display: inline-block;
            margin: 0 1rem;
        }
        .countdown-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }
        .countdown-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .social-links a {
            color: white;
            font-size: 1.5rem;
            margin: 0 0.5rem;
            transition: transform 0.3s ease;
        }
        .social-links a:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <div class="maintenance-page">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="maintenance-content">
                        <!-- Maintenance Icon -->
                        <div class="maintenance-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        
                        <!-- Title -->
                        <h1 class="maintenance-title">We'll Be Back Soon!</h1>
                        
                        <!-- Message -->
                        <p class="maintenance-message">
                            We're currently performing scheduled maintenance to improve your shopping experience.<br>
                            Thank you for your patience!
                        </p>
                        
                        <!-- Countdown Timer -->
                        <div class="countdown">
                            <h4 class="mb-3">Estimated Time Remaining</h4>
                            <div id="countdown-timer">
                                <div class="countdown-item">
                                    <span class="countdown-number" id="hours">02</span>
                                    <span class="countdown-label">Hours</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-number" id="minutes">30</span>
                                    <span class="countdown-label">Minutes</span>
                                </div>
                                <div class="countdown-item">
                                    <span class="countdown-number" id="seconds">00</span>
                                    <span class="countdown-label">Seconds</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Contact Info -->
                        <div class="mb-4">
                            <p class="mb-2">Need immediate assistance?</p>
                            <a href="mailto:support@dubgift.com" class="text-white">
                                <i class="fas fa-envelope me-2"></i>support@dubgift.com
                            </a>
                            <span class="mx-3">|</span>
                            <a href="tel:+971XXXXXXXXX" class="text-white">
                                <i class="fas fa-phone me-2"></i>+971-XX-XXX-XXXX
                            </a>
                        </div>
                        
                        <!-- Social Links -->
                        <div class="social-links">
                            <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Countdown timer (example: 2.5 hours from now)
        let endTime = new Date().getTime() + (2.5 * 60 * 60 * 1000);
        
        function updateCountdown() {
            let now = new Date().getTime();
            let timeLeft = endTime - now;
            
            if (timeLeft > 0) {
                let hours = Math.floor(timeLeft / (1000 * 60 * 60));
                let minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
                
                document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            } else {
                document.getElementById('countdown-timer').innerHTML = '<p class="h4">Maintenance Complete!</p>';
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        }
        
        // Update countdown every second
        setInterval(updateCountdown, 1000);
        updateCountdown(); // Initial call
    </script>
</body>
</html>
