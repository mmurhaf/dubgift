# Required Libraries for DubGift E-commerce

## PHP Libraries (Install via Composer or manually)

### 1. PHPMailer (Email functionality)
- **Purpose**: Send emails for order confirmations, customer notifications
- **Download**: https://github.com/PHPMailer/PHPMailer
- **Install via Composer**: `composer require phpmailer/phpmailer`

### 2. TCPDF or FPDF (PDF Generation)
- **Purpose**: Generate PDF invoices
- **TCPDF Download**: https://tcpdf.org/
- **FPDF Download**: http://www.fpdf.org/
- **Install TCPDF via Composer**: `composer require tecnickcom/tcpdf`

### 3. Intervention Image (Image manipulation)
- **Purpose**: Resize and process product images
- **Download**: http://image.intervention.io/
- **Install via Composer**: `composer require intervention/image`

### 4. WhatsApp API Library
- **Purpose**: Send WhatsApp notifications to admin
- **Option 1**: Twilio WhatsApp API
  - Download: https://github.com/twilio/twilio-php
  - Install: `composer require twilio/sdk`
- **Option 2**: WhatsApp Business API
- **Option 3**: Third-party WhatsApp gateway

### 5. Payment Gateway Libraries

#### Ziina Payment Integration
- **Purpose**: Process credit card payments
- **Documentation**: Contact Ziina for API documentation
- **Custom integration required based on Ziina API specs**

### 6. Bootstrap 5 (Frontend framework)
- **Purpose**: Responsive design and UI components
- **Download**: https://getbootstrap.com/
- **CDN**: Include via CDN or download locally

### 7. jQuery (JavaScript library)
- **Purpose**: DOM manipulation and AJAX requests
- **Download**: https://jquery.com/
- **CDN**: Include via CDN

### 8. Font Awesome (Icons)
- **Purpose**: Icons for the website
- **Download**: https://fontawesome.com/
- **CDN**: Include via CDN

## Manual Installation Steps

1. **Create composer.json** in your project root:
```json
{
    "require": {
        "phpmailer/phpmailer": "^6.8",
        "tecnickcom/tcpdf": "^6.6",
        "intervention/image": "^2.7",
        "twilio/sdk": "^7.0"
    }
}
```

2. **Run Composer** (if available):
```bash
composer install
```

3. **Alternative Manual Download**:
   - Download each library from their official websites
   - Extract to `/vendor/` directory
   - Include the autoload files in your PHP scripts

## Frontend Libraries (CDN Links)

Add these to your HTML head section:

```html
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
```

## Server Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- cURL extension enabled
- GD extension enabled (for image processing)
- OpenSSL extension enabled (for payment processing)

## File Permissions

Set the following permissions:
- `/assets/uploads/` - 755 or 777
- `/invoices/` - 755 or 777
- All PHP files - 644
- Directories - 755

## Environment Setup

1. Enable PHP extensions: gd, curl, openssl, pdo_mysql
2. Set `upload_max_filesize` to at least 10MB in php.ini
3. Set `post_max_size` to at least 20MB in php.ini
4. Configure Apache .htaccess for URL rewriting

## API Keys Required

You'll need to obtain API keys for:
1. **Ziina Payment Gateway** - Contact Ziina directly
2. **WhatsApp API** - Choose from Twilio, WhatsApp Business API, or third-party provider
3. **Email SMTP** - Configure with your email provider (Gmail, Yahoo, etc.)

## Testing

1. Test email functionality with a real SMTP server
2. Test payment processing in sandbox mode first
3. Test WhatsApp notifications with test numbers
4. Verify PDF generation works correctly

## Security Notes

- Change default admin password immediately
- Use strong database passwords
- Keep API keys secure and never expose them in client-side code
- Implement SSL certificate for production
- Regular security updates for all libraries
