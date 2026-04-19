<?php
echo "<!DOCTYPE html>";
echo "<html>";
echo "<head><title>Back4app Direct API Test</title>";
echo "<style>";
echo "body { font-family: Arial; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }";
echo "h1 { color: #333; }";
echo ".success { color: #388e3c; padding: 10px; background: #e8f5e9; border-radius: 4px; margin: 10px 0; }";
echo ".error { color: #d32f2f; padding: 10px; background: #ffebee; border-radius: 4px; margin: 10px 0; }";
echo ".warning { color: #f57f17; padding: 10px; background: #fff3e0; border-radius: 4px; margin: 10px 0; }";
echo ".code { background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; word-wrap: break-word; }";
echo "hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }";
echo ".extension-check { margin: 20px 0; padding: 15px; border-left: 4px solid #2196F3; background: #e3f2fd; }";
echo ".section { margin-bottom: 30px; }";
echo "code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; font-size: 12px; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🔧 Back4app Direct API Test</h1>";

// Load environment variables
require_once 'Configs.php';

$appId = $_ENV['PARSE_APP_ID'] ?? '';
$restApiKey = $_ENV['PARSE_REST_API_KEY'] ?? '';
$masterKey = $_ENV['PARSE_MASTER_KEY'] ?? '';

echo "<div class='extension-check'>";
echo "<h3>📦 PHP Extension Check</h3>";

if (extension_loaded('curl')) {
    echo "<div class='success'>✅ cURL is ENABLED</div>";
    $curl_enabled = true;
} else {
    echo "<div class='error'>❌ cURL is DISABLED</div>";
    echo "<p>You need to enable cURL extension. See 'Enable cURL' section below.</p>";
    $curl_enabled = false;
}

echo "</div>";

echo "<div class='section'>";
echo "<h3>🔑 Loaded Credentials</h3>";
echo "<p><strong>App ID:</strong> " . (substr($appId, 0, 10) . '...') . "</p>";
echo "<p><strong>REST API Key:</strong> " . (substr($restApiKey, 0, 10) . '...') . "</p>";
echo "<p><strong>Master Key:</strong> " . (substr($masterKey, 0, 10) . '...') . "</p>";
echo "</div>";

// ========== Test 1: Using cURL (if available) ==========
if ($curl_enabled) {
    echo "<div class='section'>";
    echo "<h2>Test 1: Query Users with Master Key (cURL)</h2>";
    $url = 'https://parseapi.back4app.com/parse/classes/_User';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Parse-Application-Id: ' . $appId,
        'X-Parse-Master-Key: ' . $masterKey,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    echo "Response Code: <strong>$httpCode</strong><br>";

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo "<div class='success'>✅ Success! Found " . count($data['results'] ?? []) . " users</div>";
    } elseif ($httpCode === 401) {
        echo "<div class='error'>❌ Unauthorized (401)</div>";
        echo "<p>Possible causes:</p>";
        echo "<ul>";
        echo "<li>Wrong credentials in .env</li>";
        echo "<li>Back4app app is not active</li>";
        echo "<li>Master key is restricted</li>";
        echo "</ul>";
        echo "Response: <div class='code'>" . htmlspecialchars($response) . "</div>";
    } else {
        echo "<div class='error'>❌ Error Code: $httpCode</div>";
        if ($curl_error) {
            echo "cURL Error: <div class='code'>" . htmlspecialchars($curl_error) . "</div>";
        }
        echo "Response: <div class='code'>" . htmlspecialchars(substr($response, 0, 500)) . "</div>";
    }
    echo "</div>";

    echo "<hr>";
    echo "<div class='section'>";
    echo "<h2>Test 2: Query Users with REST API Key (cURL)</h2>";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Parse-Application-Id: ' . $appId,
        'X-Parse-REST-API-Key: ' . $restApiKey,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Response Code: <strong>$httpCode</strong><br>";

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo "<div class='success'>✅ Success! Found " . count($data['results'] ?? []) . " users</div>";
    } elseif ($httpCode === 401) {
        echo "<div class='error'>❌ Unauthorized (401)</div>";
        echo "Response: <div class='code'>" . htmlspecialchars($response) . "</div>";
    } else {
        echo "<div class='error'>❌ Error Code: $httpCode</div>";
        echo "Response: <div class='code'>" . htmlspecialchars(substr($response, 0, 500)) . "</div>";
    }
    echo "</div>";
} else {
    // ========== Alternative: Using PHP Streams (no cURL needed) ==========
    echo "<div class='warning'>";
    echo "<h2>⚠️ cURL Not Available - Using Alternative Method</h2>";
    echo "</div>";

    echo "<div class='section'>";
    echo "<h2>Test 1: Query Users with Master Key (Stream)</h2>";
    $url = 'https://parseapi.back4app.com/parse/classes/_User';

    try {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'X-Parse-Application-Id: ' . $appId,
                    'X-Parse-Master-Key: ' . $masterKey,
                    'Content-Type: application/json'
                ]
            ]
        ]);

        $response = file_get_contents($url, false, $context);
        $httpCode = isset($http_response_header) ? (int)explode(' ', $http_response_header[0])[1] : 0;

        echo "Response Code: <strong>$httpCode</strong><br>";

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            echo "<div class='success'>✅ Success! Found " . count($data['results'] ?? []) . " users</div>";
            echo "Users: <div class='code'>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</div>";
        } elseif ($httpCode === 401) {
            echo "<div class='error'>❌ Unauthorized (401)</div>";
            echo "Response: <div class='code'>" . htmlspecialchars($response) . "</div>";
        } else {
            echo "<div class='error'>❌ Error Code: $httpCode</div>";
            echo "Response: <div class='code'>" . htmlspecialchars($response) . "</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Exception: " . $e->getMessage() . "</div>";
    }
    echo "</div>";
}

echo "<hr>";
echo "<div class='section'>";
echo "<h2>🔧 How to Enable cURL on Your Server</h2>";
echo "<h3>On WAMP/Windows:</h3>";
echo "<ol>";
echo "<li>Open WAMP Control Panel</li>";
echo "<li>Go to <code>PHP → php.ini</code></li>";
echo "<li>Find line: <code>;extension=curl</code></li>";
echo "<li>Remove the semicolon: <code>extension=curl</code></li>";
echo "<li>Save and restart WAMP</li>";
echo "</ol>";

echo "<h3>Via Command Line (Windows PowerShell):</h3>";
echo "<div class='code'>";
echo "# Find your php.ini<br>";
echo "php -i | Select-String 'php.ini'<br><br>";
echo "# Edit it (uncomment curl extension)<br>";
echo "# Then restart your web server";
echo "</div>";

echo "<h3>On Linux/Docker:</h3>";
echo "<div class='code'>";
echo "apt-get install php-curl<br>";
echo "systemctl restart apache2  # or your web server<br>";
echo "# or in Docker rebuild with curl included";
echo "</div>";
echo "</div>";

echo "<hr>";
echo "<a href='admin_setup_diagnostic.php' style='padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; display: inline-block;'>→ Go to Admin Setup Diagnostic</a>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
