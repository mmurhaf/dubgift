<?php
/**
 * PDF Invoice Generator using FPDF
 * Secure PDF generation for order invoices
 */

// Prevent direct access
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}

// Load FPDF library
require_once __DIR__ . '/../vendor/fpdf/fpdf.php';

class InvoicePDF extends FPDF {
    private $orderData;
    private $logger;
    
    public function __construct($orderData) {
        parent::__construct();
        $this->orderData = $orderData;
        $this->logger = SecurityLogger::getInstance();
        
        // Security: Log PDF generation
        $this->logger->logSecurityEvent('pdf_generation', [
            'order_id' => $orderData['order_id'] ?? 'unknown'
        ]);
    }
    
    /**
     * PDF Header
     */
    function Header() {
        // Logo (if exists)
        $logoPath = __DIR__ . '/../assets/images/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 6, 30);
        }
        
        // Company name
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 15, SITE_NAME, 0, 1, 'C');
        
        // Invoice title
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'INVOICE', 0, 1, 'C');
        $this->Ln(10);
    }
    
    /**
     * PDF Footer
     */
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb} - Generated on ' . date('Y-m-d H:i:s'), 0, 0, 'C');
    }
    
    /**
     * Generate invoice content
     */
    public function generateInvoice() {
        $this->AliasNbPages();
        $this->AddPage();
        
        // Customer information
        $this->addCustomerInfo();
        
        // Order details
        $this->addOrderDetails();
        
        // Items table
        $this->addItemsTable();
        
        // Total
        $this->addTotal();
        
        // Terms and conditions
        $this->addTerms();
    }
    
    /**
     * Add customer information
     */
    private function addCustomerInfo() {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'Bill To:', 0, 1);
        
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, $this->sanitizeText($this->orderData['customer_name']), 0, 1);
        $this->Cell(0, 6, $this->sanitizeText($this->orderData['customer_email']), 0, 1);
        $this->Cell(0, 6, $this->sanitizeText($this->orderData['shipping_address']), 0, 1);
        $this->Ln(5);
        
        // Invoice info
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(50, 6, 'Invoice #:', 0, 0);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, $this->sanitizeText($this->orderData['order_id']), 0, 1);
        
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(50, 6, 'Date:', 0, 0);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, date('Y-m-d'), 0, 1);
        $this->Ln(10);
    }
    
    /**
     * Add order details
     */
    private function addOrderDetails() {
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(50, 6, 'Payment Method:', 0, 0);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, $this->sanitizeText($this->orderData['payment_method']), 0, 1);
        
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(50, 6, 'Order Status:', 0, 0);
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, $this->sanitizeText($this->orderData['status']), 0, 1);
        $this->Ln(10);
    }
    
    /**
     * Add items table
     */
    private function addItemsTable() {
        // Table header
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(80, 8, 'Product', 1, 0, 'L', true);
        $this->Cell(25, 8, 'Qty', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Price', 1, 0, 'R', true);
        $this->Cell(30, 8, 'Total', 1, 1, 'R', true);
        
        // Table content
        $this->SetFont('Arial', '', 10);
        $total = 0;
        
        foreach ($this->orderData['items'] as $item) {
            $itemTotal = $item['quantity'] * $item['price'];
            $total += $itemTotal;
            
            $this->Cell(80, 6, $this->sanitizeText($item['name']), 1, 0, 'L');
            $this->Cell(25, 6, $item['quantity'], 1, 0, 'C');
            $this->Cell(30, 6, 'AED ' . number_format($item['price'], 2), 1, 0, 'R');
            $this->Cell(30, 6, 'AED ' . number_format($itemTotal, 2), 1, 1, 'R');
        }
    }
    
    /**
     * Add total section
     */
    private function addTotal() {
        $this->Ln(5);
        
        // Subtotal
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(135, 8, 'Subtotal:', 0, 0, 'R');
        $this->Cell(30, 8, 'AED ' . number_format($this->orderData['subtotal'], 2), 1, 1, 'R');
        
        // Tax if applicable
        if (isset($this->orderData['tax']) && $this->orderData['tax'] > 0) {
            $this->Cell(135, 8, 'Tax:', 0, 0, 'R');
            $this->Cell(30, 8, 'AED ' . number_format($this->orderData['tax'], 2), 1, 1, 'R');
        }
        
        // Shipping if applicable
        if (isset($this->orderData['shipping']) && $this->orderData['shipping'] > 0) {
            $this->Cell(135, 8, 'Shipping:', 0, 0, 'R');
            $this->Cell(30, 8, 'AED ' . number_format($this->orderData['shipping'], 2), 1, 1, 'R');
        }
        
        // Total
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(135, 10, 'TOTAL:', 0, 0, 'R');
        $this->Cell(30, 10, 'AED ' . number_format($this->orderData['total'], 2), 1, 1, 'R', true);
    }
    
    /**
     * Add terms and conditions
     */
    private function addTerms() {
        $this->Ln(15);
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, 'Terms & Conditions:', 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $terms = [
            '• Payment is due within 30 days of invoice date.',
            '• All sales are final unless otherwise specified.',
            '• Please retain this invoice for your records.',
            '• For questions, contact us at ' . SITE_EMAIL
        ];
        
        foreach ($terms as $term) {
            $this->Cell(0, 5, $term, 0, 1);
        }
    }
    
    /**
     * Sanitize text for PDF output
     */
    private function sanitizeText($text) {
        // Remove potentially harmful characters and convert encoding
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        return iconv('UTF-8', 'windows-1252//IGNORE', $text);
    }
    
    /**
     * Save PDF to secure location
     */
    public function saveToFile($filename) {
        // Ensure secure filename
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        $filename = $filename ?: 'invoice_' . date('YmdHis');
        
        $secureDir = __DIR__ . '/../../invoices/';
        if (!is_dir($secureDir)) {
            mkdir($secureDir, 0755, true);
        }
        
        $filepath = $secureDir . $filename . '.pdf';
        $this->Output('F', $filepath);
        
        return $filepath;
    }
    
    /**
     * Output PDF to browser
     */
    public function outputToBrowser($filename = 'invoice.pdf') {
        $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '', $filename);
        $this->Output('D', $filename);
    }
}

/**
 * PDF Generator Factory
 */
class PDFGenerator {
    
    /**
     * Generate invoice PDF
     */
    public static function generateInvoice($orderData) {
        try {
            $pdf = new InvoicePDF($orderData);
            $pdf->generateInvoice();
            return $pdf;
            
        } catch (Exception $e) {
            $logger = SecurityLogger::getInstance();
            $logger->logSecurityEvent('pdf_generation_error', [
                'error' => $e->getMessage(),
                'order_id' => $orderData['order_id'] ?? 'unknown'
            ]);
            
            throw new \Exception('PDF generation failed: ' . $e->getMessage());
        }
    }
}
?>
