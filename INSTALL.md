# Stresser Installation Guide for Ubuntu

This guide will help you set up the Stresser application on an Ubuntu server.

## Prerequisites

1. Ubuntu Server (20.04 LTS or newer)
2. Apache2
3. PHP 7.4 or newer
4. NowPayments API credentials

## Installation Steps

### 1. Update System
```bash
sudo apt update
sudo apt upgrade -y
```

### 2. Install Required Packages
```bash
sudo apt install apache2 php php-curl php-json php-common libapache2-mod-php -y
```

### 3. Configure Apache
```bash
# Enable required Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 4. Set Up Project Directory
```bash
# Navigate to web root
cd /var/www/html

# Create project directory
sudo mkdir stresser
sudo chown -R www-data:www-data stresser
sudo chmod -R 755 stresser

# Copy project files
sudo cp -r /path/to/project/* /var/www/html/stresser/
```

### 5. Create Database Directories
```bash
# Create database directory
sudo mkdir -p /var/www/html/stresser/database
sudo chown -R www-data:www-data /var/www/html/stresser/database
sudo chmod -R 755 /var/www/html/stresser/database
```

### 6. Configure NowPayments Integration

1. Sign up for a NowPayments account at https://nowpayments.io
2. Obtain your API key and IPN secret from the dashboard
3. Edit includes/config.php and update the following constants:
```php
define('NOWPAYMENTS_API_KEY', 'YOUR-API-KEY');
define('NOWPAYMENTS_IPN_SECRET', 'YOUR-IPN-SECRET');
```

4. Update the callback URLs in includes/nowpayments.php:
```php
'ipn_callback_url' => 'https://your-domain.com/ipn.php',
'success_url' => 'https://your-domain.com/payment_success.php',
'cancel_url' => 'https://your-domain.com/payment_cancel.php'
```

### 7. Set Up Apache Virtual Host (Optional)
```bash
sudo nano /etc/apache2/sites-available/stresser.conf
```

Add the following configuration:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/stresser
    
    <Directory /var/www/html/stresser>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/stresser-error.log
    CustomLog ${APACHE_LOG_DIR}/stresser-access.log combined
</VirtualHost>
```

Enable the site:
```bash
sudo a2ensite stresser.conf
sudo systemctl restart apache2
```

### 8. Security Recommendations

1. Set up SSL/TLS certificate using Let's Encrypt:
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

2. Configure PHP security settings in php.ini:
```bash
sudo nano /etc/php/7.4/apache2/php.ini
```

Update these settings:
```ini
display_errors = Off
log_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
allow_url_fopen = Off
max_execution_time = 30
memory_limit = 128M
post_max_size = 20M
upload_max_filesize = 2M
```

3. Set proper file permissions:
```bash
sudo find /var/www/html/stresser -type f -exec chmod 644 {} \;
sudo find /var/www/html/stresser -type d -exec chmod 755 {} \;
sudo chown -R www-data:www-data /var/www/html/stresser
```

### 9. Testing the Installation

1. Open your browser and navigate to your domain
2. Try to register a new account
3. Test the login functionality
4. Make a test deposit using NowPayments sandbox mode

### Troubleshooting

1. Check Apache error logs:
```bash
sudo tail -f /var/log/apache2/error.log
```

2. Check PHP error logs:
```bash
sudo tail -f /var/log/php/error.log
```

3. Common issues:
   - File permissions: Ensure proper ownership and permissions
   - Apache configuration: Check for syntax errors
   - PHP extensions: Verify all required extensions are installed
   - Database directory: Ensure it's writable by www-data

### Security Notes

1. Always keep your system and packages updated
2. Use strong passwords for all accounts
3. Regularly monitor log files for suspicious activity
4. Consider implementing rate limiting at the server level
5. Use a firewall (UFW) to restrict access
6. Keep your NowPayments API credentials secure
7. Regularly backup your database files

For support or issues, please contact the administrator.
