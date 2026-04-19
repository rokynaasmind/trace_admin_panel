<?php
/**
 * Debug Credentials & Parse Initialization
 * Shows exactly what's happening with credential loading and Parse setup
 */

require 'vendor/autoload.php';

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>🔍 Debug: Parse Credentials & Initialization</title>";
echo "<style>";
echo "body { font-family: Arial; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }";
echo ".section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }";
echo ".success { background: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 3px; }";
echo ".error { background: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 3px; }";
echo ".info { background: #d1ecf1; color: #0c5460; padding: 10px; margin: 10px 0; border-radius: 3px; }";
echo ".code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px; word-wrap: break-word; }";
echo "h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }";
echo "hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }";
echo ".cred-item { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 3px solid #667eea; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🔍 Debug: Parse Initialization & Credentials</h1>";

// ========== STEP 1: .env File Loading ==========
echo "<div class='section'>";
echo "<h2>Step 1: Loading .env File</h2>";

$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    echo "<div class='success'>✅ .env file exists</div>";
    
    $env_file = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $credentials_loaded = [];
    
    foreach ($env_file as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            if (strpos($key, 'PARSE') === 0 || $key === 'APPLICATION_ID') {
                $credentials_loaded[$key] = $value;
            }
        }
    }
    
    echo "<p>Credentials found in .env:</p>";
    foreach ($credentials_loaded as $key => $value) {
        $masked = substr($value, 0, 8) . '...' . substr($value, -5);
        echo "<div class='cred-item'><strong>$key:</strong> $masked</div>";
    }
} else {
    echo "<div class='error'>❌ .env file NOT found at: $env_path</div>";
}
echo "</div>";

// ========== STEP 2: $_ENV Array ==========
echo "<div class='section'>";
echo "<h2>Step 2: Checking $_ENV Array (Before Configs.php)</h2>";

$parse_vars = ['PARSE_APP_ID', 'PARSE_REST_API_KEY', 'PARSE_MASTER_KEY'];
foreach ($parse_vars as $var) {
    if (isset($_ENV[$var])) {
        echo "<div class='success'>✅ $_ENV['$var'] is set</div>";
    } else {
        echo "<div class='info'>ℹ️ $_ENV['$var'] not yet set</div>";
    }
}
echo "</div>";

// ========== STEP 3: Load Configs.php ==========
echo "<div class='section'>";
echo "<h2>Step 3: Loading Configs.php & Parse Initialization</h2>";

include 'Configs.php';

echo "<p>After including Configs.php:</p>";
foreach ($parse_vars as $var) {
    if (isset($_ENV[$var])) {
        $masked = substr($_ENV[$var], 0, 8) . '...' . substr($_ENV[$var], -5);
        echo "<div class='success'>✅ $_ENV['$var'] = $masked</div>";
    } else {
        echo "<div class='error'>❌ $_ENV['$var'] is NOT set!</div>";
    }
}
echo "</div>";

// ========== STEP 4: Parse Client Status ==========
echo "<div class='section'>";
echo "<h2>Step 4: Parse Client Status</h2>";

use Parse\ParseClient;
use Parse\ParseUser;
use Parse\ParseException;

try {
    // Try to get server health
    $health = ParseClient::getServerHealth();
    
    if ($health['status'] === 200) {
        echo "<div class='success'>✅ Parse Server is REACHABLE (Status: 200)</div>";
    } else {
        echo "<div class='error'>❌ Parse Server returned status: " . $health['status'] . "</div>";
    }
    
    echo "<p>Health Response:</p>";
    echo "<div class='code'>" . json_encode($health, JSON_PRETTY_PRINT) . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Cannot connect to Parse Server</div>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<div class='code'>" . $e->getTraceAsString() . "</div>";
}
echo "</div>";

// ========== STEP 5: Test SignUp (Dry Run) ==========
echo "<div class='section'>";
echo "<h2>Step 5: Test User SignUp</h2>";

try {
    $testUser = new ParseUser();
    $testUser->setUsername('test_' . time());
    $testUser->setPassword('TestPassword123');
    $testUser->setEmail('test_' . time() . '@test.com');
    $testUser->set('role', 'admin');
    
    echo "<div class='info'>Attempting to create test user...</div>";
    $testUser->signUp();
    
    echo "<div class='success'>✅ User created successfully!</div>";
    echo "<p>New User ID: " . $testUser->getObjectId() . "</p>";
    
} catch (ParseException $e) {
    echo "<div class='error'>❌ ParseException: (" . $e->getCode() . ") " . $e->getMessage() . "</div>";
    
    if ($e->getCode() === 0) {
        echo "<div class='info'>";
        echo "<p><strong>Error Code 0 = Network/Authorization Error</strong></p>";
        echo "<p>Possible causes:</p>";
        echo "<ul>";
        echo "<li>Wrong credentials in .env</li>";
        echo "<li>Back4app API keys are invalid or expired</li>";
        echo "<li>Parse Server is rejecting the request</li>";
        echo "<li>_User class has ACL restrictions</li>";
        echo "</ul>";
        echo "</div>";
    }
    
    error_log($e);
    
} catch (Exception $e) {
    echo "<div class='error'>❌ General Exception: " . $e->getMessage() . "</div>";
    error_log($e);
}

echo "</div>";

// ========== STEP 6: Recommendations ==========
echo "<div class='section'>";
echo "<h2>Step 6: Troubleshooting Steps</h2>";

echo "<h3>Option 1: Verify Credentials in Back4app</h3>";
echo "<ol>";
echo "<li>Go to Back4app Dashboard</li>";
echo "<li>Select your application</li>";
echo "<li>Go to Settings → Keys</li>";
echo "<li>Verify these match your .env file:</li>";
echo "<ul>";
echo "<li>Application ID</li>";
echo "<li>REST API Key</li>";
echo "<li>Master Key</li>";
echo "</ul>";
echo "</ol>";

echo "<h3>Option 2: Check _User Class Permissions</h3>";
echo "<ol>";
echo "<li>Go to Back4app Dashboard</li>";
echo "<li>Go to Browser → _User</li>";
echo "<li>Click on the class name</li>";
echo "<li>Check 'Permissions' → Make sure Master Key can create users</li>";
echo "</ol>";

echo "<h3>Option 3: Regenerate Keys (Last Resort)</h3>";
echo "<ol>";
echo "<li>Back4app Dashboard → Settings → Keys</li>";
echo "<li>Click 'Change' next to each key</li>";
echo "<li>Copy new values to .env file</li>";
echo "<li>Try again</li>";
echo "</ol>";

echo "</div>";

// ========== FOOTER ==========
echo "<hr>";
echo "<p><a href='setup_admin.php' style='padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>← Back to Admin Setup</a></p>";
echo "<p><small>Last run: " . date('Y-m-d H:i:s') . "</small></p>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
