<?php
/**
 * Test Parse Connection
 * This script verifies that Parse credentials are loaded correctly
 */

require 'vendor/autoload.php';
include 'Configs.php';

use Parse\ParseUser;
use Parse\ParseException;

echo "<h2>🔍 Parse Connection Test</h2>";
echo "<hr>";

// Show loaded credentials (masked for security)
echo "<h3>📋 Loaded Credentials:</h3>";
echo "<ul>";
echo "<li><strong>APP ID:</strong> " . (substr($_ENV['PARSE_APP_ID'] ?? 'NOT SET', 0, 10) . '...') . "</li>";
echo "<li><strong>REST API Key:</strong> " . (substr($_ENV['PARSE_REST_API_KEY'] ?? 'NOT SET', 0, 10) . '...') . "</li>";
echo "<li><strong>Master Key:</strong> " . (substr($_ENV['PARSE_MASTER_KEY'] ?? 'NOT SET', 0, 10) . '...') . "</li>";
echo "<li><strong>Server URL:</strong> " . ($_ENV['PARSE_SERVER_URL'] ?? 'NOT SET') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>🧪 Testing Connection...</h3>";

try {
    // Try to get server health
    $health = ParseClient::getServerHealth();
    
    if (isset($health['status'])) {
        if ($health['status'] === 200) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; color: #155724;'>";
            echo "✅ <strong>Connection Successful!</strong><br>";
            echo "Health Status: " . $health['status'] . "<br>";
            echo "Response: " . json_encode($health);
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; color: #721c24;'>";
            echo "⚠️ <strong>Connection Issue</strong><br>";
            echo "Status: " . $health['status'] . "<br>";
            echo "Response: " . json_encode($health);
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; color: #721c24;'>";
        echo "⚠️ <strong>No Status in Response</strong><br>";
        echo "Response: " . json_encode($health);
        echo "</div>";
    }
} catch (ParseException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; color: #721c24;'>";
    echo "❌ <strong>Parse Exception</strong><br>";
    echo "Code: " . $e->getCode() . "<br>";
    echo "Message: " . $e->getMessage();
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; color: #721c24;'>";
    echo "❌ <strong>General Exception</strong><br>";
    echo "Code: " . $e->getCode() . "<br>";
    echo "Message: " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<h3>📝 Next Steps:</h3>";
echo "<ol>";
echo "<li>If connection is successful, try accessing <a href='setup_admin.php'>/setup_admin.php</a></li>";
echo "<li>If connection fails, check your credentials in the .env file</li>";
echo "<li>Make sure Back4app is running and accessible</li>";
echo "</ol>";
?>
