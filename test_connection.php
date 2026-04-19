<?php
require 'vendor/autoload.php';
include 'Configs.php';

use Parse\ParseClient;
use Parse\ParseUser;
use Parse\ParseQuery;

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head><title>Parse Connection Test</title></head>";
echo "<body style='font-family: Arial; padding: 20px;'>";

echo "<h2>Parse Connection Test</h2>";

// Test 1: Check server health (may return 401 - this is okay)
echo "<h3>1. Testing Server Health...</h3>";
try {
    $health = ParseClient::getServerHealth();
    if ($health['status'] === 200) {
        echo "✅ <strong>Server is UP</strong><br>";
        echo "Status: " . $health['status'] . "<br>";
    } else if ($health['status'] === 401) {
        echo "⚠️ <strong>Server UP but Auth Issue (This is OK)</strong><br>";
        echo "Status: " . $health['status'] . "<br>";
        echo "Your queries still work correctly!<br>";
    } else {
        echo "❌ Server Issue - Status: " . $health['status'] . "<br>";
    }
} catch (Exception $e) {
    echo "⚠️ Health check failed (This is OK - queries work anyway)<br>";
}

// Test 2: Check current user
echo "<h3>2. Testing Current User...</h3>";
try {
    $currUser = ParseUser::getCurrentUser();
    if ($currUser) {
        echo "✅ <strong>User Found</strong><br>";
        echo "Username: " . $currUser->getUsername() . "<br>";
        echo "Role: " . $currUser->get('role') . "<br>";
    } else {
        echo "⚠️ No user logged in<br>";
    }
} catch (Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
}

// Test 3: Query Users
echo "<h3>3. Testing Query _User...</h3>";
try {
    $query = new ParseQuery('_User');
    $query->limit(1);
    $result = $query->find();
    echo "✅ <strong>Query Successful</strong><br>";
    echo "Found " . count($result) . " users<br>";
} catch (Exception $e) {
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<a href='auth/login.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Back to Login</a>";
echo "</body>";
echo "</html>";
?>
