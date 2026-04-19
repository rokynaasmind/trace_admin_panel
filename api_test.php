<?php
echo "<!DOCTYPE html>";
echo "<html>";
echo "<head><title>Back4app Direct API Test</title>";
echo "<style>";
echo "body { font-family: Arial; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 700px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }";
echo "h1 { color: #333; }";
echo ".success { color: #388e3c; padding: 10px; background: #e8f5e9; border-radius: 4px; margin: 10px 0; }";
echo ".error { color: #d32f2f; padding: 10px; background: #ffebee; border-radius: 4px; margin: 10px 0; }";
echo ".code { background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; }";
echo "hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🔧 Back4app Direct API Test</h1>";

// From Configs.php
$appId = 'yiAEelcOnI3YnRYp9Xft6fAfI6CJLU0TLtKYf0nP';
$restApiKey = '2u2DEllH51wXLwDElQggSx7y7vJu3X1OgTn2ELIM';
$masterKey = 'AsDVQmszF2ybh9MeeYxW6tsWdfmJbCnxwUrlkkGt';

echo "<h2>Test 1: Query Users with Master Key</h2>";
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
curl_close($ch);

echo "Response Code: <strong>$httpCode</strong><br>";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "<div class='success'>✅ Success! Found " . count($data['results']) . " users</div>";
} else {
    echo "<div class='error'>❌ Error Code: $httpCode</div>";
    echo "Response: <div class='code'>" . htmlspecialchars(substr($response, 0, 200)) . "</div>";
}

echo "<hr>";
echo "<h2>Test 2: Query Users with REST API Key</h2>";

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
    echo "<div class='success'>✅ Success! Found " . count($data['results']) . " users</div>";
} else {
    echo "<div class='error'>❌ Error Code: $httpCode</div>";
    echo "Response: <div class='code'>" . htmlspecialchars(substr($response, 0, 200)) . "</div>";
}

echo "<hr>";
echo "<a href='test_connection.php' style='padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>← Back to Connection Test</a>";

echo "</div>";
echo "</body>";
echo "</html>";
?>
