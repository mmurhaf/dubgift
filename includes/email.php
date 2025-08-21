<?php
/**
 * Email System using PHPMailer
 * Secure email sending with SMTP support
 */

// Prevent direct access
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Load PHPMailer
require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailSystem {
    private $mailer;
    private $logger;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->logger = SecurityLogger::getInstance();
        $this->configureSMTP();
    }
    
    /**
     * Configure SMTP settings
     */
    private function configureSMTP() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host       = SMTP_HOST;
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = SMTP_USERNAME;
            $this->mailer->Password   = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = SMTP_ENCRYPTION;
            $this->mailer->Port       = SMTP_PORT;
            
            // Security settings
            $this->mailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            // Default settings
            $this->mailer->setFrom(SITE_EMAIL, SITE_NAME);
            $this->mailer->isHTML(true);
            
        } catch (Exception $e) {
            $this->logger->logSecurityEvent('email_config_error', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Email configuration failed');
        }
    }
    
    /**
     * Send email with security logging
     */
    public function sendEmail($to, $subject, $body, $attachments = []) {
        try {
            // Input validation
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email address');
            }
            
            // Sanitize inputs
            $subject = InputValidator::sanitizeString($subject, 200);
            $body = InputValidator::sanitizeHtml($body);
            
            // Set recipient
            $this->mailer->addAddress($to);
            
            // Set content
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            
            // Add attachments if any
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $this->mailer->addAttachment($attachment);
                }
            }
            
            // Send email
            $result = $this->mailer->send();
            
            // Log success
            $this->logger->logSecurityEvent('email_sent', [
                'to' => $to,
                'subject' => $subject
            ]);
            
            // Clear recipients for next email
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            return $result;
            
        } catch (Exception $e) {
            // Log error
            $this->logger->logSecurityEvent('email_error', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Email sending failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation($customerEmail, $orderData) {
        $subject = "Order Confirmation - " . SITE_NAME;
        $body = $this->generateOrderEmailTemplate($orderData);
        
        return $this->sendEmail($customerEmail, $subject, $body);
    }
    
    /**
     * Generate order email template
     */
    private function generateOrderEmailTemplate($orderData) {
        ob_start();
        ?>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .header { background: #333; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .order-details { background: #f5f5f5; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; color: #666; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1><?= SITE_NAME ?></h1>
                <p>Order Confirmation</p>
            </div>
            
            <div class="content">
                <h2>Thank you for your order!</h2>
                <p>Dear <?= htmlspecialchars($orderData['customer_name']) ?>,</p>
                <p>Your order has been confirmed and is being processed.</p>
                
                <div class="order-details">
                    <h3>Order Details</h3>
                    <p><strong>Order ID:</strong> <?= htmlspecialchars($orderData['order_id']) ?></p>
                    <p><strong>Total Amount:</strong> AED <?= number_format($orderData['total'], 2) ?></p>
                    <p><strong>Payment Method:</strong> <?= htmlspecialchars($orderData['payment_method']) ?></p>
                </div>
                
                <p>You will receive another email once your order ships.</p>
            </div>
            
            <div class="footer">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
?>
