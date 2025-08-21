# URGENT SECURITY CHECKLIST - DubGift Setup
# Execute these steps IMMEDIATELY to secure your application

## 🚨 IMMEDIATE ACTIONS REQUIRED:

### 1. CHANGE ALL EXPOSED CREDENTIALS (Critical - Do This Now!)

#### Database Password:
- Login to your iPage control panel
- Change MySQL password for user 'mmurhaf'
- Update .env file with new password

#### Email Password:
- Login to your email hosting (iPage)
- Change password for sales@dubgift.com
- Update .env file with new password

#### Payment API Key:
- Login to Ziina dashboard
- Regenerate API keys
- Update .env file with new keys

### 2. SECURITY CONFIGURATION

#### File Permissions:
```bash
chmod 600 .env
chmod 600 ../dubgift-config/config.php
chmod 755 assets/uploads
```

#### Environment Variables:
```
# Generate secure keys using:
openssl rand -hex 32  # For ENCRYPTION_KEY
openssl rand -hex 32  # For PASSWORD_SALT
openssl rand -hex 16  # For CSRF_SECRET
```

### 3. PRODUCTION DEPLOYMENT

#### Update .env for production:
```
ENVIRONMENT=production
DEBUG_MODE=false
SITE_URL=https://www.dubgift.com
DB_HOST=mmurhaf50350.ipagemysql.com
```

### 4. IMMEDIATE TESTING

#### Test local setup:
1. Visit: http://localhost:83/dubgift/
2. Check database connection
3. Test email functionality
4. Verify payment integration

### 5. MONITORING

#### Setup security monitoring:
- Monitor failed login attempts
- Track API usage
- Review error logs daily
- Setup backup schedule

## 📋 CONFIGURATION SUMMARY:

### Local Development:
- Database: localhost (root/no password)
- URL: http://localhost:83/dubgift/
- Email: iPage SMTP (ssl, port 465)
- Payment: Ziina sandbox mode

### Production Ready:
- Database: mmurhaf50350.ipagemysql.com
- URL: https://www.dubgift.com
- Email: sales@dubgift.com via iPage
- Payment: Ziina live mode

## ⚠️ SECURITY NOTES:

1. Never share credentials in chat/email again
2. Use environment variables for all sensitive data
3. Enable 2FA on all accounts
4. Regular security audits
5. Keep backup of configurations

## 🔧 NEXT STEPS:

1. Change all passwords/keys ✅
2. Test local environment
3. Setup production environment
4. Configure SSL certificate
5. Setup monitoring and backups
