<?php
/**
 * Environment Configuration Manager
 * Manage Developer Mode vs Production Mode
 */

require 'vendor/autoload.php';
include 'Configs.php';

// Get current environment
$app_env = $_ENV['APP_ENV'] ?? 'development';
$app_debug = $_ENV['APP_DEBUG'] ?? 'false';

?>
<!DOCTYPE html>
<html>
<head>
    <title>⚙️ Environment Configuration</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; }
        h2 { color: #667eea; border-bottom: 3px solid #667eea; padding-bottom: 10px; margin-bottom: 15px; }
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0;
        }
        .status-dev { background: #FFE082; color: #333; }
        .status-prod { background: #4CAF50; color: white; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background: #667eea; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background: #f9f9f9; }
        .code-block {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 10px 0;
            border-left: 4px solid #667eea;
        }
        .feature { padding: 10px; margin: 10px 0; background: white; border-left: 4px solid #667eea; border-radius: 3px; }
        .dev-feature { border-left-color: #FFB74D; }
        .prod-feature { border-left-color: #4CAF50; }
        button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 5px; }
        button:hover { background: #764ba2; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #856404; margin: 15px 0; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; border-left: 4px solid #155724; margin: 15px 0; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 5px; border-left: 4px solid #0c5460; margin: 15px 0; }
        hr { border: none; border-top: 1px solid #ddd; margin: 30px 0; }
        .comparison { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .comparison-item { padding: 15px; border-radius: 5px; }
        .comp-dev { background: #fffde7; }
        .comp-prod { background: #e8f5e9; }
    </style>
</head>
<body>

<div class="container">
    <h1>⚙️ Environment Configuration Manager</h1>
    <p class="subtitle">Manage Developer Mode vs Production Mode settings</p>

    <!-- ==================== CURRENT STATUS ==================== -->
    <div class="section">
        <h2>📊 Current Environment Status</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <p><strong>Environment:</strong></p>
                <div class="status-badge <?php echo ($app_env === 'development' ? 'status-dev' : 'status-prod'); ?>">
                    <?php echo strtoupper($app_env); ?>
                </div>
            </div>
            <div>
                <p><strong>Debug Mode:</strong></p>
                <div class="status-badge <?php echo ($app_debug === 'true' || $app_debug === true ? 'status-dev' : 'status-prod'); ?>">
                    <?php echo ($app_debug === 'true' || $app_debug === true) ? 'ENABLED' : 'DISABLED'; ?>
                </div>
            </div>
        </div>

        <table>
            <tr>
                <th>Setting</th>
                <th>Current Value</th>
                <th>Description</th>
            </tr>
            <tr>
                <td><strong>APP_ENV</strong></td>
                <td><code><?php echo $app_env; ?></code></td>
                <td>Environment type (development/production)</td>
            </tr>
            <tr>
                <td><strong>APP_DEBUG</strong></td>
                <td><code><?php echo $app_debug; ?></code></td>
                <td>Error/debug display enabled</td>
            </tr>
            <tr>
                <td><strong>PHP Display Errors</strong></td>
                <td><?php echo ini_get('display_errors') ? 'ON' : 'OFF'; ?></td>
                <td>Show PHP errors in output</td>
            </tr>
            <tr>
                <td><strong>Parse Server</strong></td>
                <td><code><?php echo $_ENV['PARSE_SERVER_URL'] ?? 'N/A'; ?></code></td>
                <td>Backend API server URL</td>
            </tr>
        </table>
    </div>

    <hr>

    <!-- ==================== DEVELOPMENT MODE ==================== -->
    <div class="section">
        <h2>💻 Development Mode</h2>
        <p>Development mode is used during local development and testing.</p>

        <h3>✅ Features Enabled in Development:</h3>
        <div class="feature dev-feature">
            <strong>🔍 Debug Output:</strong> All errors, warnings, and debug messages are displayed
        </div>
        <div class="feature dev-feature">
            <strong>📋 Error Logging:</strong> Detailed error logs for debugging
        </div>
        <div class="feature dev-feature">
            <strong>🧪 Test Data:</strong> Dummy data for testing features
        </div>
        <div class="feature dev-feature">
            <strong>⚡ Development APIs:</strong> Extended APIs for testing
        </div>
        <div class="feature dev-feature">
            <strong>📊 Performance Monitoring:</strong> Detailed performance stats
        </div>
        <div class="feature dev-feature">
            <strong>🔓 CORS Relaxed:</strong> Broader CORS headers for testing
        </div>

        <h3>.env Settings for Development:</h3>
        <div class="code-block">
APP_ENV=development
APP_DEBUG=true
PARSE_SERVER_URL=https://parseapi.back4app.com/
WEBSITE_PATH=http://localhost:8000
        </div>

        <h3>Recommended .env.development file:</h3>
        <div class="code-block">
# .env.development

# Parse Server Configuration
PARSE_APP_ID=YOUR_APP_ID
PARSE_REST_API_KEY=YOUR_REST_KEY
PARSE_MASTER_KEY=YOUR_MASTER_KEY
PARSE_SERVER_URL=https://parseapi.back4app.com/

# Application Keys
APPLICATION_ID=YOUR_APPLICATION_ID
CLIENT_KEY=YOUR_CLIENT_KEY
JAVASCRIPT_KEY=YOUR_JAVASCRIPT_KEY

# Development Settings
APP_ENV=development
APP_DEBUG=true
WEBSITE_PATH=http://localhost:8000
APP_NAME=Trace

# Enhanced Logging
LOG_LEVEL=debug
LOG_SQL=true

# Cache (disabled for development)
CACHE_ENABLED=false

# Session
SESSION_TIMEOUT=1440
        </div>
    </div>

    <hr>

    <!-- ==================== PRODUCTION MODE ==================== -->
    <div class="section">
        <h2>🚀 Production Mode</h2>
        <p>Production mode is optimized for performance, security, and stability.</p>

        <h3>✅ Features in Production:</h3>
        <div class="feature prod-feature">
            <strong>🔒 Security Hardened:</strong> Error details hidden from users
        </div>
        <div class="feature prod-feature">
            <strong>⚡ Performance Optimized:</strong> Caching and optimization enabled
        </div>
        <div class="feature prod-feature">
            <strong>📝 Secure Logging:</strong> Minimal logging to prevent data leaks
        </div>
        <div class="feature prod-feature">
            <strong>🔐 SSL/HTTPS:</strong> All communications encrypted
        </div>
        <div class="feature prod-feature">
            <strong>🛡️ Rate Limiting:</strong> API rate limiting enabled
        </div>
        <div class="feature prod-feature">
            <strong>🔒 CORS Strict:</strong> Restricted CORS headers
        </div>

        <h3>.env Settings for Production:</h3>
        <div class="code-block">
APP_ENV=production
APP_DEBUG=false
PARSE_SERVER_URL=https://parseapi.back4app.com/
WEBSITE_PATH=https://yourdomain.com
        </div>

        <h3>Recommended .env.production file:</h3>
        <div class="code-block">
# .env.production

# Parse Server Configuration (Use secure credentials)
PARSE_APP_ID=YOUR_PROD_APP_ID
PARSE_REST_API_KEY=YOUR_PROD_REST_KEY
PARSE_MASTER_KEY=YOUR_PROD_MASTER_KEY
PARSE_SERVER_URL=https://parseapi.back4app.com/

# Application Keys
APPLICATION_ID=YOUR_PROD_APPLICATION_ID
CLIENT_KEY=YOUR_PROD_CLIENT_KEY
JAVASCRIPT_KEY=YOUR_PROD_JAVASCRIPT_KEY

# Production Settings
APP_ENV=production
APP_DEBUG=false
WEBSITE_PATH=https://yourdomain.com
APP_NAME=Trace

# Minimal Logging
LOG_LEVEL=error
LOG_SQL=false

# Cache (enabled for production)
CACHE_ENABLED=true
CACHE_TTL=3600

# Session
SESSION_TIMEOUT=3600

# Rate Limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=60

# Security
REQUIRE_HTTPS=true
SECURE_COOKIES=true
SAME_SITE_COOKIES=Strict
        </div>

        <div class="warning">
            <strong>⚠️ Important Production Checklist:</strong>
            <ul>
                <li>✓ Use HTTPS only</li>
                <li>✓ Set APP_DEBUG to false</li>
                <li>✓ Hide detailed error messages</li>
                <li>✓ Enable caching</li>
                <li>✓ Rotate credentials regularly</li>
                <li>✓ Monitor logs and performance</li>
                <li>✓ Set up backups</li>
                <li>✓ Enable security headers</li>
            </ul>
        </div>
    </div>

    <hr>

    <!-- ==================== COMPARISON ==================== -->
    <div class="section">
        <h2>📊 Development vs Production Comparison</h2>

        <table>
            <tr>
                <th>Feature</th>
                <th>Development</th>
                <th>Production</th>
            </tr>
            <tr>
                <td>Debug Mode</td>
                <td>✅ Enabled</td>
                <td>❌ Disabled</td>
            </tr>
            <tr>
                <td>Error Display</td>
                <td>✅ Detailed</td>
                <td>❌ Hidden</td>
            </tr>
            <tr>
                <td>Logging Level</td>
                <td>DEBUG</td>
                <td>ERROR</td>
            </tr>
            <tr>
                <td>Caching</td>
                <td>❌ Disabled</td>
                <td>✅ Enabled</td>
            </tr>
            <tr>
                <td>Performance</td>
                <td>Good</td>
                <td>Optimized</td>
            </tr>
            <tr>
                <td>Security</td>
                <td>Relaxed</td>
                <td>Strict</td>
            </tr>
            <tr>
                <td>Session Timeout</td>
                <td>24 hours</td>
                <td>1 hour</td>
            </tr>
            <tr>
                <td>HTTPS Required</td>
                <td>❌ No</td>
                <td>✅ Yes</td>
            </tr>
            <tr>
                <td>Database</td>
                <td>Development DB</td>
                <td>Production DB</td>
            </tr>
            <tr>
                <td>API Rate Limit</td>
                <td>Disabled</td>
                <td>Enabled</td>
            </tr>
        </table>
    </div>

    <hr>

    <!-- ==================== HOW TO CHANGE MODE ==================== -->
    <div class="section">
        <h2>🔄 How to Change Environment Mode</h2>

        <h3>Step 1: Edit .env File</h3>
        <p>Edit your <code>.env</code> file in the project root:</p>
        <div class="code-block">
# For Development
APP_ENV=development
APP_DEBUG=true

# For Production
APP_ENV=production
APP_DEBUG=false
        </div>

        <h3>Step 2: Save File</h3>
        <p>Save the .env file with your changes.</p>

        <h3>Step 3: Clear Cache (if applicable)</h3>
        <div class="code-block">
# Clear PHP cache
php -r "opcache_reset();"

# Or restart your web server
sudo service apache2 restart
        </div>

        <h3>Step 4: Verify Changes</h3>
        <p>Refresh this page or check your application to verify the environment has changed.</p>
    </div>

    <hr>

    <!-- ==================== FILE STRUCTURE ==================== -->
    <div class="section">
        <h2>📁 Recommended File Structure</h2>

        <div class="code-block">
project/
├── .env                  # Current environment config
├── .env.development      # Development-specific config
├── .env.production       # Production-specific config
├── .env.example          # Example template
├── .gitignore            # Ignore .env files
├── Configs.php           # Main configuration
├── index.php
├── auth/
│   └── login.php
├── dashboard/
│   └── panel.php
├── admin/
│   └── admin_settings.php
├── api/
│   ├── users.php
│   ├── posts.php
│   └── auth.php
└── vendor/
        </div>

        <p><strong>Note:</strong> Only commit <code>.env.example</code> to git. Add <code>.env</code>, <code>.env.development</code>, and <code>.env.production</code> to <code>.gitignore</code></p>
    </div>

    <hr>

    <!-- ==================== ENVIRONMENT VARIABLES ==================== -->
    <div class="section">
        <h2>📝 All Available Environment Variables</h2>

        <table>
            <tr>
                <th>Variable</th>
                <th>Description</th>
                <th>Development</th>
                <th>Production</th>
            </tr>
            <tr>
                <td>APP_ENV</td>
                <td>Application environment</td>
                <td>development</td>
                <td>production</td>
            </tr>
            <tr>
                <td>APP_DEBUG</td>
                <td>Debug mode enabled</td>
                <td>true</td>
                <td>false</td>
            </tr>
            <tr>
                <td>APP_NAME</td>
                <td>Application name</td>
                <td>Trace</td>
                <td>Trace</td>
            </tr>
            <tr>
                <td>WEBSITE_PATH</td>
                <td>Website base URL</td>
                <td>http://localhost:8000</td>
                <td>https://yourdomain.com</td>
            </tr>
            <tr>
                <td>PARSE_APP_ID</td>
                <td>Parse app identifier</td>
                <td>Dev App ID</td>
                <td>Prod App ID</td>
            </tr>
            <tr>
                <td>PARSE_REST_API_KEY</td>
                <td>Parse REST API key</td>
                <td>Dev Key</td>
                <td>Prod Key</td>
            </tr>
            <tr>
                <td>PARSE_MASTER_KEY</td>
                <td>Parse master key (SECURE)</td>
                <td>Dev Key</td>
                <td>Prod Key</td>
            </tr>
            <tr>
                <td>LOG_LEVEL</td>
                <td>Logging verbosity</td>
                <td>debug</td>
                <td>error</td>
            </tr>
            <tr>
                <td>CACHE_ENABLED</td>
                <td>Enable caching</td>
                <td>false</td>
                <td>true</td>
            </tr>
            <tr>
                <td>REQUIRE_HTTPS</td>
                <td>Require HTTPS</td>
                <td>false</td>
                <td>true</td>
            </tr>
        </table>
    </div>

    <hr>

    <!-- ==================== QUICK REFERENCE ==================== -->
    <div class="section">
        <h2>🎯 Quick Reference</h2>

        <h3>Development Mode Setup:</h3>
        <div class="code-block">
APP_ENV=development
APP_DEBUG=true
WEBSITE_PATH=http://localhost:8000
LOG_LEVEL=debug
CACHE_ENABLED=false
        </div>

        <h3>Production Mode Setup:</h3>
        <div class="code-block">
APP_ENV=production
APP_DEBUG=false
WEBSITE_PATH=https://yourdomain.com
LOG_LEVEL=error
CACHE_ENABLED=true
REQUIRE_HTTPS=true
        </div>

        <h3>Quick Switch Commands:</h3>
        <div class="code-block">
# To Development
sed -i 's/APP_ENV=production/APP_ENV=development/' .env
sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env

# To Production
sed -i 's/APP_ENV=development/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
        </div>
    </div>

    <hr>

    <!-- ==================== USING IN CODE ==================== -->
    <div class="section">
        <h2>💡 Using Environment Variables in Code</h2>

        <h3>Check Current Environment:</h3>
        <div class="code-block">
&lt;?php
$env = $_ENV['APP_ENV'] ?? 'production';

if ($env === 'development') {
    // Show detailed errors
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // Hide errors in production
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}
?&gt;
        </div>

        <h3>Conditional Logging:</h3>
        <div class="code-block">
&lt;?php
if ($_ENV['APP_DEBUG'] === 'true') {
    // Development logging
    error_log("Debug: " . json_encode($data));
} else {
    // Production logging (only errors)
    if ($error) {
        error_log("Error: " . $error);
    }
}
?&gt;
        </div>

        <h3>Different API Endpoints:</h3>
        <div class="code-block">
&lt;?php
$env = $_ENV['APP_ENV'] ?? 'production';

if ($env === 'development') {
    $api_url = 'http://localhost:3000/api';
} else {
    $api_url = 'https://api.yourdomain.com';
}
?&gt;
        </div>
    </div>

    <!-- ==================== LINKS ==================== -->
    <div class="section">
        <a href="admin_import_verify.php"><button>👥 Admin Management</button></a>
        <a href="api_documentation.php"><button>📡 API Documentation</button></a>
        <a href="login_verification.php"><button>🔐 Login Verification</button></a>
    </div>

</div>

</body>
</html>
