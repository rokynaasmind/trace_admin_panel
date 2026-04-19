<?php
require 'vendor/autoload.php';

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Back4app Credentials Checker</title>";
echo "<style>";
echo "body { font-family: Arial; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #333; }";
echo ".info-box { background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 15px 0; }";
echo ".code { background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; overflow-x: auto; }";
echo ".error { color: #d32f2f; }";
echo ".warning { color: #f57f17; }";
echo ".success { color: #388e3c; }";
echo "hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";

echo "<h1>🔍 Back4app Credentials Checker</h1>";

echo "<div class='info-box'>";
echo "<strong>What to do:</strong><br>";
echo "1. Go to <strong>back4app.com</strong> → Login<br>";
echo "2. Select your app<br>";
echo "3. Click <strong>Settings → Security & Keys</strong><br>";
echo "4. Copy each key exactly and paste below<br>";
echo "</div>";

// Show current values (masked)
echo "<div class='info-box'>";
echo "<strong>Current values in Configs.php:</strong><br>";

$configFile = file_get_contents('Configs.php');

// Extract APP ID
if (preg_match("/ParseClient::initialize\(\s*'([^']*)'", $configFile, $matches)) {
    $appId = $matches[1];
    $appIdMasked = substr($appId, 0, 5) . '...' . substr($appId, -5);
    echo "App ID: <span class='code'>$appIdMasked</span> (Length: " . strlen($appId) . " chars)<br>";
}

// Extract REST API Key
if (preg_match("/ParseClient::initialize\([^,]*,\s*'([^']*)'", $configFile, $matches)) {
    $restKey = $matches[1];
    $restKeyMasked = substr($restKey, 0, 5) . '...' . substr($restKey, -5);
    echo "REST API Key: <span class='code'>$restKeyMasked</span> (Length: " . strlen($restKey) . " chars)<br>";
}

// Extract Master Key
if (preg_match("/ParseClient::initialize\([^,]*,[^,]*,\s*'([^']*)'", $configFile, $matches)) {
    $masterKey = $matches[1];
    $masterKeyMasked = substr($masterKey, 0, 5) . '...' . substr($masterKey, -5);
    echo "Master Key: <span class='code'>$masterKeyMasked</span> (Length: " . strlen($masterKey) . " chars)<br>";
}

echo "</div>";

echo "<hr>";
echo "<h2>Solution: Update Configs.php</h2>";

echo "<p>Replace the credentials in <strong>Configs.php</strong> (lines 14-16) with:</p>";

echo "<div class='code' style='color: #d32f2f;'>";
echo "ParseClient::initialize(<br>";
echo "&nbsp;&nbsp;'YOUR_APP_ID_HERE',<br>";
echo "&nbsp;&nbsp;'YOUR_REST_API_KEY_HERE',<br>";
echo "&nbsp;&nbsp;'YOUR_MASTER_KEY_HERE'<br>";
echo ");<br>";
echo "</div>";

echo "<br>";
echo "<strong>⚠️ Important:</strong>";
echo "<ul>";
echo "<li>Copy from Back4app exactly (watch for spaces)</li>";
echo "<li>All credentials should be inside single quotes ('')</li>";
echo "<li>Each credential is a long alphanumeric string</li>";
echo "<li>Do NOT share credentials with anyone</li>";
echo "</ul>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
