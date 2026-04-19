<?php
/**
 * Direct Parse API Test (No SDK)
 * Tests Parse API directly to identify the issue
 */

require_once 'Configs.php';

$appId = $_ENV['PARSE_APP_ID'] ?? '';
$masterKey = $_ENV['PARSE_MASTER_KEY'] ?? '';
$restKey = $_ENV['PARSE_REST_API_KEY'] ?? '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>🔧 Direct Parse API Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; overflow-x: auto; word-wrap: break-word; }
        h2 { border-bottom: 2px solid #667eea; padding-bottom: 10px; margin-top: 25px; }
        button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 5px; font-weight: bold; }
        button:hover { background: #764ba2; }
        hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }
        .status { font-weight: bold; margin: 10px 0; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 Direct Parse API Test (Without SDK)</h1>
    <p>Tests Parse API directly using HTTP requests to identify the exact issue.</p>

    <div class="info">
        <p><strong>App ID:</strong> <?php echo substr($appId, 0, 10) . '...'; ?></p>
        <p><strong>Master Key:</strong> <?php echo substr($masterKey, 0, 10) . '...'; ?></p>
    </div>

    <?php

    // Test function using file_get_contents or cURL
    function makeRequest($url, $appId, $masterKey, $method = 'GET', $data = null) {
        // Try cURL first
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Parse-Application-Id: ' . $appId,
                'X-Parse-Master-Key: ' . $masterKey,
                'Content-Type: application/json'
            ]);
            
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            return ['status' => $httpCode, 'response' => $response, 'error' => $error];
        } else {
            // Fallback to streams
            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => [
                        'X-Parse-Application-Id: ' . $appId,
                        'X-Parse-Master-Key: ' . $masterKey,
                        'Content-Type: application/json'
                    ],
                    'timeout' => 10
                ],
                'ssl' => [
                    'verify_peer' => false,
                ]
            ]);
            
            if ($data) {
                stream_context_set_option($context, ['http' => ['content' => json_encode($data)]]);
            }
            
            $response = @file_get_contents($url, false, $context);
            $status = 'unknown';
            if (isset($http_response_header)) {
                preg_match('/HTTP\/[\d.]+\s+(\d+)/', $http_response_header[0], $matches);
                $status = $matches[1] ?? 'unknown';
            }
            
            return ['status' => $status, 'response' => $response, 'error' => ''];
        }
    }

    if (!empty($_GET['test'])) {
        $test = $_GET['test'];

        // ========== Test 1: Get Users List ==========
        if ($test === '1') {
            echo "<h2>Test 1: Query Users (GET)</h2>";
            echo "<p>Testing if we can read from _User class with Master Key</p>";
            
            $result = makeRequest('https://parseapi.back4app.com/parse/classes/_User', $appId, $masterKey);
            echo "<div class='status'>HTTP Status: <strong>" . $result['status'] . "</strong></div>";
            
            if ($result['status'] == 200) {
                echo "<div class='success'>✅ Success! Can query users</div>";
                $data = json_decode($result['response'], true);
                echo "<p>Total users: " . count($data['results'] ?? []) . "</p>";
                echo "<div class='code'>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "</div>";
            } else {
                echo "<div class='error'>❌ Query failed</div>";
                echo "<div class='code'>" . htmlspecialchars($result['response']) . "</div>";
                if ($result['error']) {
                    echo "<p><strong>cURL Error:</strong> " . $result['error'] . "</p>";
                }
            }
        }

        // ========== Test 2: Create User ==========
        elseif ($test === '2') {
            echo "<h2>Test 2: Create New User (POST)</h2>";
            echo "<p>Testing if we can create a user with Master Key</p>";
            
            $testUsername = 'testuser_' . time();
            $testEmail = 'test_' . time() . '@example.com';
            $testPassword = 'TestPassword123';
            
            $userData = [
                'username' => $testUsername,
                'email' => $testEmail,
                'password' => $testPassword,
                'role' => 'admin'
            ];
            
            echo "<p><strong>Creating user:</strong></p>";
            echo "<ul>";
            echo "<li>Username: $testUsername</li>";
            echo "<li>Email: $testEmail</li>";
            echo "<li>Password: $testPassword</li>";
            echo "</ul>";
            
            $result = makeRequest(
                'https://parseapi.back4app.com/parse/classes/_User',
                $appId,
                $masterKey,
                'POST',
                $userData
            );
            
            echo "<div class='status'>HTTP Status: <strong>" . $result['status'] . "</strong></div>";
            
            if ($result['status'] == 201 || $result['status'] == 200) {
                echo "<div class='success'>✅ User created successfully!</div>";
                $data = json_decode($result['response'], true);
                echo "<p>New User ID: " . ($data['objectId'] ?? 'N/A') . "</p>";
                echo "<div class='code'>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</div>";
            } elseif ($result['status'] == 401) {
                echo "<div class='error'>❌ Unauthorized (401)</div>";
                echo "<p><strong>PROBLEM:</strong> Master Key is not valid or credentials are wrong!</p>";
                echo "<div class='code'>" . htmlspecialchars($result['response']) . "</div>";
            } else {
                echo "<div class='error'>❌ Error (" . $result['status'] . ")</div>";
                echo "<div class='code'>" . htmlspecialchars($result['response']) . "</div>";
            }
        }

        // ========== Test 3: Check Parse Server ==========
        elseif ($test === '3') {
            echo "<h2>Test 3: Parse Server Health Check</h2>";
            
            $result = makeRequest('https://parseapi.back4app.com/parse/health', $appId, $masterKey);
            echo "<div class='status'>HTTP Status: <strong>" . $result['status'] . "</strong></div>";
            
            if ($result['status'] == 200) {
                echo "<div class='success'>✅ Parse Server is online</div>";
            } else {
                echo "<div class='error'>❌ Parse Server issue</div>";
            }
            
            echo "<div class='code'>" . htmlspecialchars($result['response']) . "</div>";
        }

        echo "<hr>";
    }

    ?>

    <h2>Available Tests</h2>
    <p>Click a button to run a test:</p>
    
    <div>
        <a href="?test=3"><button>🏥 Test 1: Server Health</button></a>
        <a href="?test=1"><button>📋 Test 2: Query Users</button></a>
        <a href="?test=2"><button>👤 Test 3: Create User</button></a>
    </div>

    <hr>

    <h2>💡 What These Tests Do</h2>
    <ul>
        <li><strong>Test 1:</strong> Checks if Parse server is reachable</li>
        <li><strong>Test 2:</strong> Lists existing users (requires valid Master Key)</li>
        <li><strong>Test 3:</strong> Creates a new test user (requires valid Master Key + write permissions)</li>
    </ul>

    <h2>🔴 If Tests Fail</h2>
    <p><strong>Most Common Solutions:</strong></p>
    <ol>
        <li><strong>If you get 401 (Unauthorized):</strong> Your .env credentials are wrong or expired
            <ul>
                <li>Check Back4app Dashboard → Settings → Keys</li>
                <li>Compare with .env file</li>
                <li>Regenerate keys if needed</li>
            </ul>
        </li>
        <li><strong>If connection fails:</strong> Check internet/firewall
            <ul>
                <li>Try accessing parseapi.back4app.com in browser</li>
            </ul>
        </li>
        <li><strong>If status is 403:</strong> Check _User class permissions
            <ul>
                <li>Back4app → Browser → _User → Permissions</li>
            </ul>
        </li>
    </ol>

    <hr>

    <h2>📝 Next Steps</h2>
    <p>Once Test 3 works, then try:</p>
    <a href="debug_credentials.php"><button>🔍 Debug Credentials (SDK Test)</button></a>
    <a href="setup_admin.php"><button>👨‍💼 Create Admin User</button></a>

</div>

</body>
</html>
