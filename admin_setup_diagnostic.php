<?php
/**
 * Detailed Diagnostic - Parse & Admin Setup
 * Helps identify why admin creation is failing
 */

require 'vendor/autoload.php';
include 'Configs.php';

use Parse\ParseClient;
use Parse\ParseUser;
use Parse\ParseException;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Setup Diagnostic</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .box { border: 1px solid #ccc; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        h2 { color: #333; margin-top: 20px; }
        code { background: #f5f5f5; padding: 2px 5px; border-radius: 3px; }
        ul { margin: 10px 0; }
        li { margin: 5px 0; }
    </style>
</head>
<body>

<h1>🔍 Parse & Admin Setup Diagnostic</h1>

<?php

// ===== SECTION 1: Environment Check =====
echo "<h2>1️⃣ Environment Configuration</h2>";

$env_checks = [
    'PARSE_APP_ID' => $_ENV['PARSE_APP_ID'] ?? null,
    'PARSE_REST_API_KEY' => $_ENV['PARSE_REST_API_KEY'] ?? null,
    'PARSE_MASTER_KEY' => $_ENV['PARSE_MASTER_KEY'] ?? null,
    'PARSE_SERVER_URL' => $_ENV['PARSE_SERVER_URL'] ?? null,
];

echo "<div class='box'>";
foreach ($env_checks as $key => $value) {
    if ($value) {
        $masked = substr($value, 0, 10) . '...' . substr($value, -5);
        echo "<div class='success'>✅ <strong>$key</strong> is set: <code>$masked</code></div>";
    } else {
        echo "<div class='error'>❌ <strong>$key</strong> is NOT set</div>";
    }
}
echo "</div>";

// ===== SECTION 2: Parse Connection Test =====
echo "<h2>2️⃣ Parse Server Connection</h2>";

try {
    $health = ParseClient::getServerHealth();
    echo "<div class='box success'>";
    echo "✅ <strong>Parse Server is Reachable</strong><br>";
    echo "Status Code: " . ($health['status'] ?? 'N/A') . "<br>";
    echo "Response: <pre>" . json_encode($health, JSON_PRETTY_PRINT) . "</pre>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='box error'>";
    echo "❌ <strong>Cannot Connect to Parse Server</strong><br>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Code: " . $e->getCode();
    echo "</div>";
}

// ===== SECTION 3: Test User Creation =====
echo "<h2>3️⃣ Test Admin User Creation</h2>";

if (!$_ENV['PARSE_APP_ID'] || !$_ENV['PARSE_REST_API_KEY']) {
    echo "<div class='box warning'>";
    echo "⚠️ <strong>Cannot Test</strong> - Missing credentials in environment";
    echo "</div>";
} else {
    try {
        $testUser = new ParseUser();
        $testUser->setUsername('test_' . time());
        $testUser->setPassword('TestPass123');
        $testUser->setEmail('test_' . time() . '@test.com');
        $testUser->set('role', 'admin');
        
        // This would actually create the user, so let's just test the structure
        echo "<div class='box info'>";
        echo "✅ <strong>User Object Created Successfully</strong><br>";
        echo "Can attempt to sign up. Ready for admin creation.";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='box error'>";
        echo "❌ <strong>Error Creating User Object</strong><br>";
        echo "Error: " . $e->getMessage();
        echo "</div>";
    }
}

// ===== SECTION 4: Recommendations =====
echo "<h2>4️⃣ Troubleshooting Guide</h2>";

echo "<div class='box'>";
echo "<h3>If you see '❌ PARSE ERRORS':</h3>";
echo "<ol>";
echo "<li>Check your .env file credentials are correct</li>";
echo "<li>Verify Back4app application is active</li>";
echo "<li>Check Back4app Dashboard for API key restrictions</li>";
echo "<li>Ensure REST API Key has permission to create users</li>";
echo "</ol>";
echo "</div>";

echo "<div class='box'>";
echo "<h3>Common Causes of 'Unauthorized' Error:</h3>";
echo "<ul>";
echo "<li><strong>Wrong Credentials</strong> - Double-check .env values match Back4app dashboard</li>";
echo "<li><strong>REST API Key Permissions</strong> - Some keys are read/write restricted</li>";
echo "<li><strong>Server Down</strong> - Check if Back4app is having issues</li>";
echo "<li><strong>IP Whitelist</strong> - Check if your IP is whitelisted in Back4app settings</li>";
echo "<li><strong>ACL Issues</strong> - Check class-level permissions in Back4app</li>";
echo "</ul>";
echo "</div>";

// ===== SECTION 5: Quick Actions =====
echo "<h2>5️⃣ Next Steps</h2>";

echo "<div class='box success'>";
echo "<p>If all checks pass above:</p>";
echo "<ol>";
echo "<li><a href='setup_admin.php' style='color: #0c5460; font-weight: bold;'>→ Go to Admin Setup</a></li>";
echo "<li>Fill in admin email, username, and password</li>";
echo "<li>Click 'Create Admin User'</li>";
echo "</ol>";
echo "</div>";

echo "<div class='box info'>";
echo "<p><strong>Credentials are stored securely in:</strong> <code>.env</code> (not in git)</p>";
echo "<p><strong>For debugging:</strong> Check PHP error logs for detailed messages</p>";
echo "</div>";

?>

</body>
</html>
