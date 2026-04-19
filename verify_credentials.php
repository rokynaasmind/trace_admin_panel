<?php
/**
 * Direct Credential Loader & Verification
 * Shows exactly what's being loaded and tests API directly
 */

// Force fresh load without caching
require_once __DIR__ . '/vendor/autoload.php';

// Manually reload .env (no caching)
$env_path = __DIR__ . '/.env';
$_ENV = array_merge($_ENV, $_SERVER); // Start fresh

if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $_ENV[$key] = $value;
        putenv("$key=$value"); // Also set in environment
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>🔧 Credential Verification & Direct API Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #333; }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .code { background: #f5f5f5; padding: 15px; border-radius: 3px; font-family: monospace; font-size: 12px; word-wrap: break-word; margin: 10px 0; }
        button { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin: 5px; }
        button:hover { background: #764ba2; }
        hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }
        .credential { padding: 10px; background: #f9f9f9; margin: 10px 0; border-left: 4px solid #667eea; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 Credential Verification & Direct API Test</h1>

    <!-- ==================== SECTION 1: SHOW LOADED CREDENTIALS ==================== -->
    <div class="section info">
        <h2>📋 Loaded Credentials (From .env)</h2>
        
        <?php
        $credentials = [
            'PARSE_APP_ID' => $_ENV['PARSE_APP_ID'] ?? '',
            'PARSE_REST_API_KEY' => $_ENV['PARSE_REST_API_KEY'] ?? '',
            'PARSE_MASTER_KEY' => $_ENV['PARSE_MASTER_KEY'] ?? '',
            'PARSE_SERVER_URL' => $_ENV['PARSE_SERVER_URL'] ?? '',
        ];

        foreach ($credentials as $key => $value) {
            if ($value) {
                echo '<div class="credential">';
                echo "<strong>$key:</strong><br>";
                if (strlen($value) > 50) {
                    echo '<code>' . substr($value, 0, 20) . '...' . substr($value, -10) . '</code>';
                } else {
                    echo '<code>' . htmlspecialchars($value) . '</code>';
                }
                echo '</div>';
            } else {
                echo '<div class="credential" style="background: #fff3cd;">';
                echo "<strong>$key:</strong> <span style=\"color: #d32f2f;\">NOT SET</span>";
                echo '</div>';
            }
        }

        $all_set = !empty($credentials['PARSE_APP_ID']) && 
                   !empty($credentials['PARSE_REST_API_KEY']) && 
                   !empty($credentials['PARSE_MASTER_KEY']);
        
        if ($all_set) {
            echo '<div class="success"><strong style="font-size: 16px;">✅ All credentials are loaded!</strong></div>';
        } else {
            echo '<div class="error"><strong>❌ Some credentials are missing!</strong></div>';
        }
        ?>
    </div>

    <hr>

    <!-- ==================== SECTION 2: DIRECT API TEST ==================== -->
    <div class="section">
        <h2>🧪 Direct API Test (Using cURL)</h2>
        <p>Tests Parse API directly with loaded credentials:</p>

        <?php
        $appId = $_ENV['PARSE_APP_ID'] ?? '';
        $masterKey = $_ENV['PARSE_MASTER_KEY'] ?? '';

        if (!$appId || !$masterKey) {
            echo '<div class="error">❌ Cannot test - credentials not loaded</div>';
        } else {
            // Test 1: Server Health
            echo '<h3>Test 1: Server Health</h3>';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://parseapi.back4app.com/parse/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200) {
                echo '<div class="success">✅ Parse Server is reachable</div>';
            } else {
                echo '<div class="error">❌ Parse Server error (Code: ' . $httpCode . ')</div>';
            }

            echo '<hr>';
            
            // Test 2: Query Users with Master Key
            echo '<h3>Test 2: Query Users (Master Key)</h3>';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://parseapi.back4app.com/parse/classes/_User');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Parse-Application-Id: ' . $appId,
                'X-Parse-Master-Key: ' . $masterKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            echo '<p>HTTP Status: <strong>' . $httpCode . '</strong></p>';

            if ($httpCode == 200) {
                $data = json_decode($response, true);
                echo '<div class="success">';
                echo '✅ API Call Successful!<br>';
                echo 'Users in database: ' . count($data['results'] ?? []) . '<br>';
                echo '</div>';

                if (!empty($data['results'])) {
                    echo '<div style="background: #f9f9f9; padding: 10px; margin-top: 10px; border-radius: 3px;">';
                    echo '<strong>Users:</strong><br>';
                    foreach ($data['results'] as $user) {
                        echo '- ' . htmlspecialchars($user['username'] ?? 'N/A') . ' (' . ($user['role'] ?? 'user') . ')<br>';
                    }
                    echo '</div>';
                }
            } elseif ($httpCode == 401) {
                echo '<div class="error">';
                echo '❌ Unauthorized (401)<br>';
                echo 'Message: ' . htmlspecialchars($response);
                echo '</div>';

                echo '<div class="info" style="margin-top: 10px;">';
                echo '<strong>🔧 Troubleshooting:</strong><br>';
                echo '1. Go to Back4app Dashboard<br>';
                echo '2. Select your application<br>';
                echo '3. Settings → Keys<br>';
                echo '4. Compare Master Key with this file:<br>';
                echo '<code style="display: block; margin-top: 10px;">' . htmlspecialchars(__FILE__) . '</code><br>';
                echo '5. If different, regenerate and update .env<br>';
                echo '</div>';
            } else {
                echo '<div class="error">';
                echo '❌ Error (' . $httpCode . ')<br>';
                if ($curlError) {
                    echo 'cURL Error: ' . htmlspecialchars($curlError);
                }
                echo '<br>Response: ' . htmlspecialchars(substr($response, 0, 200));
                echo '</div>';
            }

            echo '<hr>';

            // Test 3: Create Test User
            echo '<h3>Test 3: Create Test User</h3>';
            
            $testUsername = 'test_' . time();
            $testData = [
                'username' => $testUsername,
                'password' => 'TestPassword123',
                'email' => 'test_' . time() . '@test.com',
                'role' => 'test'
            ];

            echo '<p>Attempting to create user: <code>' . $testUsername . '</code></p>';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://parseapi.back4app.com/parse/classes/_User');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Parse-Application-Id: ' . $appId,
                'X-Parse-Master-Key: ' . $masterKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            echo '<p>HTTP Status: <strong>' . $httpCode . '</strong></p>';

            if ($httpCode == 201 || $httpCode == 200) {
                $data = json_decode($response, true);
                echo '<div class="success">';
                echo '✅ User created successfully!<br>';
                echo 'User ID: ' . ($data['objectId'] ?? 'N/A');
                echo '</div>';
            } elseif ($httpCode == 401) {
                echo '<div class="error">❌ Unauthorized - Same credential issue</div>';
            } else {
                echo '<div class="error">';
                echo '❌ Error (' . $httpCode . ')<br>';
                echo 'Response: ' . htmlspecialchars($response);
                echo '</div>';
            }
        }
        ?>
    </div>

    <hr>

    <!-- ==================== SECTION 3: SOLUTIONS ==================== -->
    <div class="section error">
        <h2>🔧 If Tests Still Fail</h2>
        <p><strong>Most Likely Cause:</strong> Back4app Master Key has been rotated or disabled</p>
        
        <h3>Solution Steps:</h3>
        <ol>
            <li>Go to <a href="https://dashboard.back4app.com" target="_blank">Back4app Dashboard</a></li>
            <li>Click your application</li>
            <li>Go to <strong>Settings → Keys</strong></li>
            <li>Look at the <strong>Master key</strong> value</li>
            <li>Compare with what you see above in "Loaded Credentials"</li>
            <li>If different:
                <ul>
                    <li>Click <strong>Change</strong> next to Master Key</li>
                    <li>Copy the NEW Master Key value</li>
                    <li>Update your <code>.env</code> file with: <code>PARSE_MASTER_KEY=&lt;new-key&gt;</code></li>
                    <li>Refresh this page</li>
                </ul>
            </li>
        </ol>

        <h3>Alternative: Generate Fresh Keys</h3>
        <ol>
            <li>Back4app → Your App → Settings → Keys</li>
            <li>Click <strong>Change</strong> for ALL keys:
                <ul>
                    <li>Application ID</li>
                    <li>REST API Key</li>
                    <li>Master Key</li>
                </ul>
            </li>
            <li>Copy all NEW values</li>
            <li>Update your <code>.env</code> file completely</li>
            <li>Refresh this page</li>
        </ol>
    </div>

    <hr>

    <!-- ==================== SECTION 4: FILE INFO ==================== -->
    <div class="section">
        <h2>📂 File Information</h2>
        <p><strong>.env File:</strong> <code><?php echo $env_path; ?></code></p>
        <p><strong>Last Modified:</strong> <?php echo date('Y-m-d H:i:s', filemtime($env_path)); ?></p>
        <p><strong>File Readable:</strong> <?php echo is_readable($env_path) ? '✅ Yes' : '❌ No'; ?></p>
    </div>

    <!-- ==================== NAVIGATION ==================== -->
    <div class="section">
        <a href="admin_import_verify.php"><button>← Back to Admin Import Tool</button></a>
        <a href="direct_api_test.php"><button>🧪 Advanced API Test</button></a>
    </div>

</div>

</body>
</html>
