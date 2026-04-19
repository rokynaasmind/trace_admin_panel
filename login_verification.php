<?php
/**
 * Login System Verification & Troubleshooting
 * Tests login functionality and shows detailed diagnostics
 */

require 'vendor/autoload.php';
include 'Configs.php';

use Parse\ParseUser;
use Parse\ParseException;
use Parse\ParseQuery;

?>
<!DOCTYPE html>
<html>
<head>
    <title>🔐 Login System Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
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
        .subtitle { color: #666; margin-bottom: 30px; }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        h2 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; margin-bottom: 15px; }
        .code { background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; overflow-x: auto; word-wrap: break-word; margin: 10px 0; }
        button {
            padding: 12px 24px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin: 5px;
            font-size: 14px;
            transition: background 0.3s;
        }
        button:hover { background: #764ba2; }
        form { margin: 15px 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        .user-info { background: #f9f9f9; padding: 15px; border-left: 4px solid #667eea; border-radius: 3px; margin: 10px 0; }
        hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }
        .diagnostic-item {
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
            background: #f9f9f9;
        }
        .steps ol { margin-left: 20px; }
        .steps li { margin: 8px 0; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔐 Login System Verification Tool</h1>
    <p class="subtitle">Test and troubleshoot login functionality</p>

    <!-- ==================== DIAGNOSTICS ==================== -->
    <div class="section info">
        <h2>📊 System Diagnostics</h2>

        <?php
        // Check 1: Session
        echo '<div class="diagnostic-item">';
        if (session_status() === PHP_SESSION_ACTIVE) {
            echo '✅ <strong>SessionStorage:</strong> Active<br>';
        } else {
            echo '⚠️ <strong>Session:</strong> Not started or error';
        }
        echo '</div>';

        // Check 2: Parse Credentials
        echo '<div class="diagnostic-item">';
        $app_id = $_ENV['PARSE_APP_ID'] ?? '';
        if ($app_id) {
            echo '✅ <strong>Parse App ID:</strong> Loaded (' . substr($app_id, 0, 8) . '...)<br>';
        } else {
            echo '❌ <strong>Parse App ID:</strong> NOT loaded';
        }
        echo '</div>';

        // Check 3: Existing Users
        echo '<div class="diagnostic-item">';
        try {
            $userQuery = new ParseQuery("_User");
            $allUsers = $userQuery->find();
            echo '✅ <strong>Database Access:</strong> OK (Users in DB: ' . count($allUsers) . ')<br>';
        } catch (Exception $e) {
            echo '❌ <strong>Database Access:</strong> Error - ' . $e->getMessage();
        }
        echo '</div>';

        // Check 4: Admin Users
        echo '<div class="diagnostic-item">';
        try {
            $adminQuery = new ParseQuery("_User");
            $adminQuery->equalTo("role", "admin");
            $admins = $adminQuery->find();
            if (count($admins) > 0) {
                echo '✅ <strong>Admin Users Found:</strong> ' . count($admins) . '<br>';
            } else {
                echo '❌ <strong>Admin Users:</strong> None found - Create one!';
            }
        } catch (Exception $e) {
            echo '⚠️ <strong>Admin Query:</strong> ' . $e->getMessage();
        }
        echo '</div>';

        ?>
    </div>

    <hr>

    <!-- ==================== MANUAL LOGIN TEST ==================== -->
    <div class="section">
        <h2>🔐 Manual Login Test</h2>
        <p>Test login with your admin credentials:</p>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_login'])) {
            $username = trim($_POST['test_username'] ?? '');
            $password = trim($_POST['test_password'] ?? '');

            if (!$username || !$password) {
                echo '<div class="error">❌ Username and password required</div>';
            } else {
                try {
                    echo '<div class="info">';
                    echo '🔐 Testing login for: ' . htmlspecialchars($username) . '<br><br>';

                    // Attempt login
                    $user = ParseUser::logIn($username, $password);
                    $currUser = ParseUser::getCurrentUser();

                    if ($currUser) {
                        echo '<div class="success">';
                        echo '✅ <strong>LOGIN SUCCESSFUL!</strong><br><br>';
                        echo 'User Details:<br>';
                        echo '<div class="user-info">';
                        echo '<strong>Username:</strong> ' . $currUser->getUsername() . '<br>';
                        echo '<strong>Email:</strong> ' . ($currUser->getEmail() ?? 'Not set') . '<br>';
                        echo '<strong>User ID:</strong> ' . $currUser->getObjectId() . '<br>';
                        echo '<strong>Role:</strong> ' . ($currUser->get('role') ?? 'Not set') . '<br>';
                        echo '<strong>Created:</strong> ' . $currUser->getCreatedAt()->format('Y-m-d H:i:s') . '<br>';
                        echo '</div><br>';

                        if ($currUser->get('role') === 'admin') {
                            echo '<div style="background: #d1ecf1; padding: 10px; border-radius: 5px; margin-top: 10px;">';
                            echo '✅ <strong>Admin Role Verified!</strong><br>';
                            echo '<a href="dashboard/panel.php" style="color: #0c5460; text-decoration: underline; font-weight: bold;">→ Access Dashboard</a>';
                            echo '</div>';
                        } else {
                            echo '<div class="warning">';
                            echo '⚠️ User is not an admin (role: ' . ($currUser->get('role') ?? 'unset') . ')';
                            echo '</div>';
                        }

                        echo '</div>';
                    } else {
                        echo '<div class="error">❌ Login returned no user</div>';
                    }

                } catch (ParseException $e) {
                    $code = $e->getCode();
                    $msg = $e->getMessage();
                    echo '<div class="error" style="margin-bottom: 10px;">';
                    echo '❌ <strong>Login Failed</strong><br>';
                    echo 'Error Code: ' . $code . '<br>';
                    echo 'Message: ' . htmlspecialchars($msg) . '<br><br>';

                    if ($code == 101) {
                        echo 'This usually means:<br>';
                        echo '• Username doesn\'t exist<br>';
                        echo '• Password is incorrect<br>';
                    } elseif ($code == 0) {
                        echo 'Backend error - check credentials in .env<br>';
                    }
                    echo '</div>';

                } catch (Exception $e) {
                    echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label for="test_username">👤 Username:</label>
                <input type="text" id="test_username" name="test_username" placeholder="admin" required>
            </div>

            <div class="form-group">
                <label for="test_password">🔑 Password:</label>
                <input type="password" id="test_password" name="test_password" placeholder="Password" required>
            </div>

            <button type="submit" name="test_login" value="1">🔐 Test Login</button>
        </form>
    </div>

    <hr>

    <!-- ==================== CREATE TEST ADMIN ==================== -->
    <div class="section warning">
        <h2>🆕 Create Test Admin (If Needed)</h2>
        <p>If no admins exist, create one here:</p>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
            $create_email = trim($_POST['create_email'] ?? '');
            $create_username = trim($_POST['create_username'] ?? '');
            $create_password = trim($_POST['create_password'] ?? '');

            if (!$create_email || !$create_username || !$create_password) {
                echo '<div class="error">❌ All fields required</div>';
            } elseif (strlen($create_password) < 6) {
                echo '<div class="error">❌ Password must be at least 6 characters</div>';
            } else {
                try {
                    $newUser = new ParseUser();
                    $newUser->setUsername($create_username);
                    $newUser->setPassword($create_password);
                    $newUser->setEmail($create_email);
                    $newUser->set('role', 'admin');
                    $newUser->signUp();

                    echo '<div class="success">';
                    echo '✅ <strong>Admin Created!</strong><br><br>';
                    echo 'Credentials:<br>';
                    echo '<div class="user-info">';
                    echo '<strong>Username:</strong> ' . htmlspecialchars($create_username) . '<br>';
                    echo '<strong>Password:</strong> ' . htmlspecialchars($create_password) . '<br>';
                    echo '<strong>Email:</strong> ' . htmlspecialchars($create_email) . '<br>';
                    echo '</div><br>';
                    echo '<button onclick="document.getElementById(\'test_username\').value = \'' . htmlspecialchars($create_username) . '\'; document.getElementById(\'test_password\').value = \'' . htmlspecialchars($create_password) . '\'; document.querySelector(\'#test_username\').form.dispatchEvent(new Event(\'submit\'));">Use for Login Test →</button>';
                    echo '</div>';

                } catch (Exception $e) {
                    echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
                }
            }
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label for="create_email">📧 Email:</label>
                <input type="email" id="create_email" name="create_email" placeholder="admin@example.com" required>
            </div>

            <div class="form-group">
                <label for="create_username">👤 Username:</label>
                <input type="text" id="create_username" name="create_username" placeholder="admin" required>
            </div>

            <div class="form-group">
                <label for="create_password">🔑 Password:</label>
                <input type="password" id="create_password" name="create_password" placeholder="Min 6 chars" required>
            </div>

            <button type="submit" name="create_admin" value="1">➕ Create Admin</button>
        </form>
    </div>

    <hr>

    <!-- ==================== QUICK REFERENCE ==================== -->
    <div class="section">
        <h2>📋 Troubleshooting Guide</h2>
        <div class="steps">
            <h3>If Login Fails:</h3>
            <ol>
                <li><strong>Error 101:</strong> Username/password incorrect → Check credentials</li>
                <li><strong>Connection Error:</strong> Check .env credentials match Back4app</li>
                <li><strong>No admin exists:</strong> Create one using the form above</li>
                <li><strong>Session issues:</strong> Clear browser cookies and try again</li>
            </ol>
        </div>
    </div>

    <!-- ==================== LINKS ==================== -->
    <div class="section">
        <h2>🔗 Quick Navigation</h2>
        <a href="auth/login.php"><button>🔐 Go to Login Page</button></a>
        <a href="admin_import_verify.php"><button>👥 Admin Import/Verify</button></a>
        <a href="dashboard/panel.php"><button>📊 Dashboard</button></a>
        <a href="direct_api_test.php"><button>🧪 API Test</button></a>
    </div>

</div>

</body>
</html>
