<?php
/**
 * Simple API Test - No cURL Required
 * Uses PHP streams for API calls
 */

require_once 'Configs.php';

$appId = $_ENV['PARSE_APP_ID'] ?? '';
$masterKey = $_ENV['PARSE_MASTER_KEY'] ?? '';
$restApiKey = $_ENV['PARSE_REST_API_KEY'] ?? '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>🚀 Simple API Test (No cURL)</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        .section { margin-bottom: 30px; }
        .success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .warning { 
            background: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeeba;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .code { 
            background: #f5f5f5; 
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .test-button { 
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .test-button:hover { 
            background: #764ba2;
        }
        .button-group { margin: 20px 0; }
        hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }
        .status { font-weight: bold; margin: 10px 0; }
        .cred-display { 
            background: #f0f0f0; 
            padding: 10px; 
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>🚀 Parse API Test (No cURL Required)</h1>
    <p class="subtitle">Simple API tests using PHP streams</p>

    <div class="section">
        <h2>✅ Loaded Credentials</h2>
        <p><strong>App ID:</strong> <span class="cred-display"><?php echo $appId; ?></span></p>
        <p><strong>Master Key:</strong> <span class="cred-display"><?php echo substr($masterKey, 0, 10) . '...' . substr($masterKey, -5); ?></span></p>
        <p><strong>REST API Key:</strong> <span class="cred-display"><?php echo substr($restApiKey, 0, 10) . '...' . substr($restApiKey, -5); ?></span></p>
    </div>

    <hr>

    <?php

    // Test function using PHP streams
    function test_parse_api($appId, $key, $keyType = 'master') {
        $url = 'https://parseapi.back4app.com/parse/classes/_User';
        
        $headerKey = ($keyType === 'master') ? 'X-Parse-Master-Key: ' : 'X-Parse-REST-API-Key: ';
        
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'X-Parse-Application-Id: ' . $appId,
                    $headerKey . $key,
                    'Content-Type: application/json'
                ],
                'timeout' => 10
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        try {
            $response = @file_get_contents($url, false, $context);
            
            // Extract HTTP status code
            $status = 'unknown';
            if (isset($http_response_header)) {
                $parts = explode(' ', $http_response_header[0]);
                $status = isset($parts[1]) ? (int)$parts[1] : 'unknown';
            }

            return [
                'status' => $status,
                'response' => $response,
                'headers' => $http_response_header ?? []
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'response' => 'Exception: ' . $e->getMessage(),
                'headers' => []
            ];
        }
    }

    // Perform tests
    if (!empty($_GET['test'])) {
        $testType = $_GET['test'];

        if ($testType === 'master') {
            echo "<div class='section'>";
            echo "<h2>Test 1: Query Users with Master Key</h2>";
            
            $result = test_parse_api($appId, $masterKey, 'master');
            echo "<div class='status'>Status Code: <strong>" . $result['status'] . "</strong></div>";
            
            if ($result['status'] === 200) {
                $data = json_decode($result['response'], true);
                echo "<div class='success'>";
                echo "✅ <strong>Success!</strong><br>";
                echo "Found " . count($data['results'] ?? []) . " users in database.";
                echo "</div>";
                
                if (!empty($data['results'])) {
                    echo "<div class='code'>";
                    echo "<strong>Users:</strong><br>";
                    foreach ($data['results'] as $user) {
                        echo "- ID: " . ($user['objectId'] ?? 'N/A') . "<br>";
                        echo "  Username: " . ($user['username'] ?? 'N/A') . "<br>";
                        echo "  Email: " . ($user['email'] ?? 'N/A') . "<br><br>";
                    }
                    echo "</div>";
                }
            } elseif ($result['status'] === 401) {
                echo "<div class='error'>";
                echo "❌ <strong>Unauthorized (401)</strong><br>";
                echo "The Master Key is not valid or credentials are wrong.";
                echo "</div>";
                echo "<div class='code'>Response: " . htmlspecialchars($result['response']) . "</div>";
            } else {
                echo "<div class='error'>";
                echo "❌ <strong>Error (" . $result['status'] . ")</strong>";
                echo "</div>";
                echo "<div class='code'>Response: " . htmlspecialchars(substr($result['response'], 0, 500)) . "</div>";
            }
            
            echo "</div>";
        }

        elseif ($testType === 'rest') {
            echo "<div class='section'>";
            echo "<h2>Test 2: Query Users with REST API Key</h2>";
            
            $result = test_parse_api($appId, $restApiKey, 'rest');
            echo "<div class='status'>Status Code: <strong>" . $result['status'] . "</strong></div>";
            
            if ($result['status'] === 200) {
                $data = json_decode($result['response'], true);
                echo "<div class='success'>";
                echo "✅ <strong>Success!</strong><br>";
                echo "Found " . count($data['results'] ?? []) . " users in database.";
                echo "</div>";
            } elseif ($result['status'] === 401) {
                echo "<div class='error'>";
                echo "❌ <strong>Unauthorized (401)</strong><br>";
                echo "The REST API Key is not valid.";
                echo "</div>";
                echo "<div class='code'>Response: " . htmlspecialchars($result['response']) . "</div>";
            } else {
                echo "<div class='error'>";
                echo "❌ <strong>Error (" . $result['status'] . ")</strong>";
                echo "</div>";
                echo "<div class='code'>Response: " . htmlspecialchars($result['response']) . "</div>";
            }
            
            echo "</div>";
        }

        echo "<hr>";
    }

    ?>

    <div class="button-group">
        <h2>🧪 Run Tests</h2>
        <a href="?test=master" class="test-button">Test Master Key</a>
        <a href="?test=rest" class="test-button">Test REST API Key</a>
    </div>

    <hr>

    <div class="section">
        <h2>💡 What These Tests Do</h2>
        <ul>
            <li><strong>Test Master Key:</strong> Queries the _User class using Master Key (has full permissions)</li>
            <li><strong>Test REST API Key:</strong> Queries the _User class using REST API Key (limited permissions)</li>
        </ul>
    </div>

    <div class="warning">
        <h3>⚠️ Important Notes</h3>
        <ul>
            <li>If you get <strong>401 Unauthorized</strong>, your credentials in <code>.env</code> are wrong</li>
            <li>Make sure the credentials match your Back4app application</li>
            <li>If tests pass here, your API connection is working!</li>
        </ul>
    </div>

    <hr>

    <div class="button-group">
        <a href="admin_setup_diagnostic.php" class="test-button">← Go to Admin Setup Diagnostic</a>
        <a href="setup_admin.php" class="test-button">← Go to Create Admin User</a>
    </div>

</div>

</body>
</html>
