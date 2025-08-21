<?php
/**
 * Example usage of Email and PDF systems
 * This file demonstrates how to use the new libraries
 */

// This is just an example - remove or move to documentation

define('APP_ACCESS', true);
require_once '../dubgift-config/config.php';
require_once 'includes/email.php';
require_once 'includes/pdf-generator.php';

// Example: Send order confirmation email
function sendOrderConfirmation($orderId) {
    try {
        // Get order data (example)
        $orderData = [
            'order_id' => $orderId,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'total' => 150.00,
            'payment_method' => 'Credit Card'
        ];
        
        // Send email
        $emailSystem = new EmailSystem();
        $result = $emailSystem->sendOrderConfirmation($orderData['customer_email'], $orderData);
        
        if ($result) {
            echo "Order confirmation email sent successfully!";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Example: Generate PDF invoice
function generateInvoice($orderId) {
    try {
        // Sample order data
        $orderData = [
            'order_id' => 'ORD-2025-001',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'shipping_address' => '123 Main St, Dubai, UAE',
            'payment_method' => 'Credit Card',
            'status' => 'Completed',
            'subtotal' => 140.00,
            'tax' => 7.00,
            'shipping' => 3.00,
            'total' => 150.00,
            'items' => [
                [
                    'name' => 'Sample Product 1',
                    'quantity' => 2,
                    'price' => 50.00
                ],
                [
                    'name' => 'Sample Product 2',
                    'quantity' => 1,
                    'price' => 40.00
                ]
            ]
        ];
        
        // Generate PDF
        $pdf = PDFGenerator::generateInvoice($orderData);
        
        // Save to file
        $filepath = $pdf->saveToFile('invoice_' . $orderId);
        echo "Invoice saved to: " . $filepath;
        
        // Or output to browser
        // $pdf->outputToBrowser('invoice_' . $orderId . '.pdf');
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Uncomment to test (remove in production)
// sendOrderConfirmation('ORD-2025-001');
// generateInvoice('ORD-2025-001');

?>
