# Environment Configuration Guide

**Project:** MyParty Admin Panel  
**Updated:** 2024  
**Status:** ✅ Environment configuration system implemented

---

## Overview

এই প্রজেক্টে **Developer Mode** এবং **Production Mode** উভয়ই সাপোর্ট করা হয়। প্রতিটি মোডের নিজস্ব কনফিগারেশন, লগিং লেভেল, এবং সিকিউরিটি সেটিন্গস আছে।

**Current Status:**
- ✅ APP_ENV variable configured
- ✅ APP_DEBUG flag available
- ✅ Environment detection working
- ✅ Configuration files created

---

## Quick Start

### Check Current Mode
```php
<?php
$env = $_ENV['APP_ENV'] ?? 'production';
$debug = $_ENV['APP_DEBUG'] ?? 'false';

echo "Environment: " . $env;
echo "Debug: " . $debug;
?>
```

### Switch Modes

**To Development:**
```bash
# Edit .env file
APP_ENV=development
APP_DEBUG=true
```

**To Production:**
```bash
# Edit .env file
APP_ENV=production
APP_DEBUG=false
```

---

## Environment Modes Explained

### 🟡 Development Mode

**Used for:** Local development, testing, debugging

**Key Features:**
- ✅ All errors are displayed
- ✅ Detailed debug output
- ✅ SQL queries logged
- ✅ No caching
- ✅ Extended logging
- ✅ Test APIs available

**Configuration (.env):**
```env
APP_ENV=development
APP_DEBUG=true
WEBSITE_PATH=http://localhost:8000
LOG_LEVEL=debug
CACHE_ENABLED=false
```

**When to use:**
- Local machine development
- Testing new features
- Debugging issues
- Running unit tests

### 🟢 Production Mode

**Used for:** Live server deployment

**Key Features:**
- ❌ Errors hidden from users
- ❌ Minimal logging
- ✅ Caching enabled
- ✅ Performance optimized
- ✅ Security hardened
- ✅ HTTPS enforced

**Configuration (.env):**
```env
APP_ENV=production
APP_DEBUG=false
WEBSITE_PATH=https://yourdomain.com
LOG_LEVEL=error
CACHE_ENABLED=true
```

**When to use:**
- Live server
- Production database
- Real users
- Performance critical

---

## File Structure

```
project/
├── .env                  ← Current configuration (DO NOT COMMIT)
├── .env.development      ← Development template
├── .env.production       ← Production template
├── .env.example          ← Example only (commit this)
├── .gitignore            ← Prevents .env from being committed
├── Configs.php           ← Loads environment variables
└── environment_configuration.php ← This GUI tool
```

---

## Configuration Comparison

| Feature | Development | Production |
|---------|------------|-----------|
| Debug Mode | ✅ ON | ❌ OFF |
| Error Display | Detailed | Hidden |
| Logging Level | DEBUG | ERROR |
| Caching | ❌ OFF | ✅ ON |
| HTTPS | Not Required | Required |
| Session Timeout | 24 hours | 1 hour |
| API Rate Limit | ❌ OFF | ✅ ON |
| Database | Dev Server | Prod Server |
| Performance | Standard | Optimized |
| Security | Relaxed | Strict |

---

## Environment Variables Reference

### Core Settings
```env
APP_ENV=development|production
APP_DEBUG=true|false
APP_NAME=MyParty Admin Panel
WEBSITE_PATH=http://localhost:8000|https://yourdomain.com
```

### Parse Server
```env
PARSE_APP_ID=your_app_id
PARSE_REST_API_KEY=your_rest_key
PARSE_MASTER_KEY=your_master_key
PARSE_SERVER_URL=https://parseapi.back4app.com/
```

### Logging
```env
LOG_LEVEL=debug|info|warning|error
LOG_SQL=true|false
LOG_API_REQUESTS=true|false
```

### Cache
```env
CACHE_ENABLED=true|false
CACHE_TTL=300  # seconds
CACHE_DRIVER=file|redis
```

### Security
```env
REQUIRE_HTTPS=true|false
SECURE_COOKIES=true|false
SAME_SITE_COOKIES=Strict|Lax|None
```

### Session
```env
SESSION_TIMEOUT=3600  # seconds
SESSION_HANDLER=files|database
```

### API Rate Limiting
```env
RATE_LIMIT_ENABLED=true|false
RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=60  # seconds
```

---

## How to Handle Different Environments in Code

### 1. Check Current Environment
```php
<?php
$env = $_ENV['APP_ENV'] ?? 'production';

if ($env === 'development') {
    // Development-specific code
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Production code
    ini_set('display_errors', 0);
}
?>
```

### 2. Conditional Error Handling
```php
<?php
$debug = $_ENV['APP_DEBUG'] ?? 'false';

try {
    // Your code
} catch (Exception $e) {
    if ($debug === 'true') {
        // Show detailed error
        die($e->getMessage() . "\n" . $e->getMyPartyAsString());
    } else {
        // Show generic error
        die("An error occurred. Please contact support.");
    }
}
?>
```

### 3. Different API Endpoints
```php
<?php
$env = $_ENV['APP_ENV'] ?? 'production';

if ($env === 'development') {
    $api_url = 'http://localhost:3000/api';
} else {
    $api_url = 'https://api.yourdomain.com';
}
?>
```

### 4. Environment-Based Database
```php
<?php
$env = $_ENV['APP_ENV'] ?? 'production';

if ($env === 'development') {
    // Local database
    $parse_app_id = $_ENV['DEV_PARSE_APP_ID'];
    $parse_key = $_ENV['DEV_PARSE_REST_API_KEY'];
} else {
    // Production database
    $parse_app_id = $_ENV['PROD_PARSE_APP_ID'];
    $parse_key = $_ENV['PROD_PARSE_REST_API_KEY'];
}
?>
```

### 5. Logging Levels
```php
<?php
$log_level = $_ENV['LOG_LEVEL'] ?? 'error';

function log_message($level, $message) {
    global $log_level;
    
    $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];
    
    if ($levels[$level] >= $levels[$log_level]) {
        error_log("[" . strtoupper($level) . "] " . $message);
    }
}

// Usage
log_message('debug', 'This is debug info');      // Shows only if LOG_LEVEL=debug
log_message('error', 'This is an error');        // Always shows (unless LOG_LEVEL set higher)
?>
```

---

## Deployment Checklist

### Before Production Deployment
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Update `WEBSITE_PATH` to production URL
- [ ] Configure `PARSE_MASTER_KEY` with production key
- [ ] Enable HTTPS (`REQUIRE_HTTPS=true`)
- [ ] Enable caching (`CACHE_ENABLED=true`)
- [ ] Set `LOG_LEVEL=error`
- [ ] Configure email settings
- [ ] Set strong session timeout
- [ ] Enable rate limiting
- [ ] Test all features
- [ ] Backup database
- [ ] Check third-party APIs
- [ ] Verify SSL certificate
- [ ] Test login system
- [ ] Check file permissions

### After Production Deployment
- [ ] Monitor error logs
- [ ] Verify application functions
- [ ] Check performance metrics
- [ ] Monitor server resources
- [ ] Test email notifications
- [ ] Verify API connectivity
- [ ] Check database backups
- [ ] Review security settings

---

## Troubleshooting

### Issues with Environment Detection
```php
<?php
// Debug: Check what variables are loaded
var_dump($_ENV);

// Verify Configs.php is loaded
require 'Configs.php';
var_dump($_ENV['APP_ENV']);
?>
```

### Reset to Default Configuration
```bash
# Copy example to .env
cp .env.example .env

# Edit .env with your settings
nano .env
```

### Environment Variables Not Loading
1. Check if `.env` file exists in project root
2. Verify `.env` file is readable
3. Check `Configs.php` is included before using `$_ENV`
4. Restart web server: `sudo service apache2 restart`

---

## Tools Available

1. **[environment_configuration.php](environment_configuration.php)** - GUI to view/change environment settings
2. **[api_documentation.php](api_documentation.php)** - API documentation and references
3. **[admin_import_verify.php](admin_import_verify.php)** - Admin user management
4. **[login_verification.php](login_verification.php)** - Login system testing

---

## Security Best Practices

### 1. Never Commit Credentials
```bash
# Add to .gitignore
echo ".env" >> .gitignore
echo ".env.*" >> .gitignore
```

### 2. Use .env.example for Reference
```bash
# Share template with team
git add .env.example
git add .env.production
git add .env.development
```

### 3. Rotate Credentials Regularly
- Change API keys every 90 days
- Update Master Key in production
- Monitor access logs

### 4. Limit Access in Production
```bash
# Restrict .env file permissions (Linux/Mac)
chmod 600 .env
chmod 600 .env.production
```

### 5. Use HTTPS in Production
```env
REQUIRE_HTTPS=true
SECURE_COOKIES=true
```

---

## FAQ

**Q: Can I have multiple environments?**  
A: Yes! Use separate .env files:
- `.env.local` - Local machine
- `.env.staging` - Staging server
- `.env.production` - Production server

**Q: How do I switch environments?**  
A: Edit the `.env` file and change `APP_ENV` value, or copy appropriate .env file:
```bash
cp .env.production .env  # Switch to production
cp .env.development .env # Switch to development
```

**Q: What if I accidentally commit `.env`?**  
A: 
```bash
git rm --cached .env
echo ".env" >> .gitignore
git add .gitignore
git commit -m "Remove .env from tracking"
```

**Q: How do I see current environment?**  
A: Visit [environment_configuration.php](environment_configuration.php) in your browser.

**Q: What's the difference between APP_ENV and APP_DEBUG?**  
- `APP_ENV`: Specifies environment type (development/production)
- `APP_DEBUG`: Enables detailed error output (true/false)

**Q: Should production have caching?**  
A: Yes! Set `CACHE_ENABLED=true` and `CACHE_TTL=3600` for better performance.

---

## Related Documentation

- [API Documentation](api_documentation.php)
- [Credentials Security Guide](CREDENTIALS_SECURITY.md)
- [Admin Setup Guide](ADMIN_SETUP_FIX.md)
- [Parse Server Documentation](https://docs.parseplatform.org/)
- [Back4app Dashboard](https://www.back4app.com/)

---

**Last Updated:** 2024  
**Maintained By:** Development Team
