<?php
/**
 * Admin Importer & Verifier
 * Creates/imports admin users and verifies they exist in Back4app
 */

require 'vendor/autoload.php';
include 'Configs.php';

use Parse\ParseUser;
use Parse\ParseException;

?>
<!DOCTYPE html>
<html>
<head>
    <title>🔧 Admin Import & Verification</title>
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
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        .info { background: #d1ecf1; color: #0c5460; }
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
        .code { background: #f5f5f5; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; overflow-x: auto; }
        form { margin: 20px 0; }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .user-list { margin-top: 20px; }
        .user-item {
            background: #f9f9f9;
            padding: 12px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
            border-radius: 3px;
        }
        hr { border: none; border-top: 1px solid #ddd; margin: 20px 0; }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="container">
    <h1>🔧 Admin Import & Verification Tool</h1>
    <p class="subtitle">Create admin users and verify they're properly stored in Back4app</p>

    <?php

    // ==================== SECTION 1: Create Admin ====================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

        if ($_POST['action'] === 'create_admin') {
            $email = trim($_POST['email'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!$email || !$username || !$password) {
                echo '<div class="section error">❌ All fields are required!</div>';
            } else if (strlen($password) < 6) {
                echo '<div class="section error">❌ Password must be at least 6 characters!</div>';
            } else {
                try {
                    $user = new ParseUser();
                    $user->setUsername($username);
                    $user->setPassword($password);
                    $user->setEmail($email);
                    $user->set('role', 'admin');
                    $user->signUp();

                    echo '<div class="section success">';
                    echo '✅ <strong>Admin User Created Successfully!</strong><br>';
                    echo 'User ID: ' . $user->getObjectId() . '<br>';
                    echo 'Username: ' . htmlspecialchars($username) . '<br>';
                    echo 'Email: ' . htmlspecialchars($email) . '<br>';
                    echo '<strong>Credentials for Login:</strong><br>';
                    echo '  Username: ' . htmlspecialchars($username) . '<br>';
                    echo '  Password: ' . htmlspecialchars($password) . '<br>';
                    echo '</div>';

                } catch (ParseException $e) {
                    echo '<div class="section error">';
                    echo '❌ Parse Error (' . $e->getCode() . '): ' . $e->getMessage() . '<br>';
                    if (strpos($e->getMessage(), 'already exists') !== false) {
                        echo 'Username already exists - use a different username';
                    }
                    echo '</div>';
                } catch (Exception $e) {
                    echo '<div class="section error">❌ Error: ' . $e->getMessage() . '</div>';
                }
            }
        }

        // ==================== VERIFY ADMIN IN BACK4APP ====================
        elseif ($_POST['action'] === 'verify_admins') {
            echo '<div class="section info">';
            echo '🔍 Checking Back4app for admin users...<br>';

            try {
                // Query for all users with role='admin'
                $query = new Parse\ParseQuery("_User");
                $query->equalTo("role", "admin");
                $admins = $query->find();

                if (count($admins) > 0) {
                    echo '<div class="user-list">';
                    echo '<strong>Found ' . count($admins) . ' admin(s) in Back4app:</strong><br><br>';

                    foreach ($admins as $admin) {
                        echo '<div class="user-item">';
                        echo '<strong>Username:</strong> ' . htmlspecialchars($admin->getUsername()) . '<br>';
                        echo '<strong>Email:</strong> ' . htmlspecialchars($admin->getEmail() ?? 'N/A') . '<br>';
                        echo '<strong>User ID:</strong> ' . $admin->getObjectId() . '<br>';
                        echo '<strong>Role:</strong> <span class="status-badge badge-success">' . $admin->get("role") . '</span><br>';
                        echo '<strong>Created:</strong> ' . $admin->getCreatedAt()->format('Y-m-d H:i:s') . '<br>';
                        echo '</div>';
                    }

                    echo '</div>';
                } else {
                    echo '<strong style="color: #d32f2f;">❌ No admin users found in Back4app!</strong><br>';
                    echo 'You need to create at least one admin user.';
                }

            } catch (Exception $e) {
                echo '❌ Error querying admins: ' . $e->getMessage();
            }

            echo '</div>';
        }

        // ==================== TEST LOGIN ====================
        elseif ($_POST['action'] === 'test_login') {
            $login_username = trim($_POST['login_username'] ?? '');
            $login_password = trim($_POST['login_password'] ?? '');

            if (!$login_username || !$login_password) {
                echo '<div class="section error">❌ Username and password are required!</div>';
            } else {
                try {
                    echo '<div class="section info">';
                    echo '🔐 Attempting login as: ' . htmlspecialchars($login_username) . '...<br><br>';

                    $user = ParseUser::logIn($login_username, $login_password);
                    $currUser = ParseUser::getCurrentUser();

                    if ($currUser) {
                        echo '<div style="background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 10px;">';
                        echo '✅ <strong>Login Successful!</strong><br>';
                        echo 'User: ' . $currUser->getUsername() . '<br>';
                        echo 'Email: ' . ($currUser->getEmail() ?? 'N/A') . '<br>';
                        echo 'Role: ' . ($currUser->get('role') ?? 'N/A') . '<br>';
                        echo '</div>';

                        if ($currUser->get("role") === 'admin') {
                            echo '<div style="background: #d1ecf1; padding: 10px; border-radius: 5px;">';
                            echo '✅ Admin role confirmed!<br>';
                            echo 'Ready to access: <a href="dashboard/panel.php" target="_blank">Dashboard →</a>';
                            echo '</div>';
                        } else {
                            echo '<div style="background: #fff3cd; padding: 10px; border-radius: 5px;">';
                            echo '⚠️ User is NOT an admin (role: ' . ($currUser->get('role') ?? 'none') . ')';
                            echo '</div>';
                        }

                    } else {
                        echo '<div style="background: #f8d7da; padding: 10px; border-radius: 5px;">';
                        echo '❌ Login failed - no user returned';
                        echo '</div>';
                    }

                    echo '</div>';

                } catch (ParseException $e) {
                    echo '<div class="section error">';
                    echo '❌ Login Failed (' . $e->getCode() . ')<br>';
                    echo 'Message: ' . $e->getMessage() . '<br>';
                    if ($e->getCode() == 101) {
                        echo 'Invalid username or password';
                    }
                    echo '</div>';

                } catch (Exception $e) {
                    echo '<div class="section error">❌ Error: ' . $e->getMessage() . '</div>';
                }
            }
        }

        echo '<hr>';
    }

    ?>

    <!-- ==================== CREATE ADMIN FORM ==================== -->
    <div class="section">
        <h2>1️⃣ Create New Admin User</h2>
        <form method="POST">
            <input type="hidden" name="action" value="create_admin">

            <div class="form-group">
                <label for="email">📧 Email Address:</label>
                <input type="email" id="email" name="email" placeholder="admin@example.com" required>
            </div>

            <div class="form-group">
                <label for="username">👤 Username:</label>
                <input type="text" id="username" name="username" placeholder="admin" required>
            </div>

            <div class="form-group">
                <label for="password">🔑 Password (min 6 chars):</label>
                <input type="password" id="password" name="password" placeholder="SecurePassword123" required>
            </div>

            <button type="submit">✅ Create Admin User</button>
        </form>
    </div>

    <!-- ==================== VERIFY ADMINS SECTION ==================== -->
    <div class="section">
        <h2>2️⃣ Verify Admins in Back4app</h2>
        <p>Check all admin users stored in your Back4app database:</p>
        <form method="POST">
            <input type="hidden" name="action" value="verify_admins">
            <button type="submit">🔍 List All Admins</button>
        </form>
    </div>

    <!-- ==================== LOGIN TEST FORM ==================== -->
    <div class="section">
        <h2>3️⃣ Test Login Functionality</h2>
        <p>Test if login works with your admin credentials:</p>
        <form method="POST">
            <input type="hidden" name="action" value="test_login">

            <div class="form-group">
                <label for="login_username">👤 Username:</label>
                <input type="text" id="login_username" name="login_username" placeholder="admin" required>
            </div>

            <div class="form-group">
                <label for="login_password">🔑 Password:</label>
                <input type="password" id="login_password" name="login_password" placeholder="SecurePassword123" required>
            </div>

            <button type="submit">🔐 Test Login</button>
        </form>
    </div>

    <!-- ==================== INSTRUCTIONS ==================== -->
    <div class="section info">
        <h2>📝 How to Use</h2>
        <ol>
            <li><strong>Create Admin:</strong> Fill the form above and click "Create Admin User"</li>
            <li><strong>Verify:</strong> Click "List All Admins" to see all admin users in Back4app</li>
            <li><strong>Test Login:</strong> Try logging in with the admin credentials</li>
            <li><strong>If successful:</strong> You can access the dashboard at <code>/dashboard/panel.php</code></li>
        </ol>
    </div>

    <!-- ==================== QUICK LINKS ==================== -->
    <div class="section">
        <h2>🔗 Quick Links</h2>
        <a href="auth/login.php"><button>🔐 Go to Login Page</button></a>
        <a href="dashboard/panel.php"><button>📊 Go to Dashboard</button></a>
        <a href="direct_api_test.php"><button>🧪 API Test</button></a>
    </div>

</div>

</body>
</html>
