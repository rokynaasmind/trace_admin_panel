<?php
/**
 * Admin User Setup Script
 * This script creates an admin user in the Parse database
 */

require 'vendor/autoload.php';
include 'Configs.php';

use Parse\ParseUser;
use Parse\ParseException;

// Setup form process
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (!$email || !$username || !$password) {
        $error = 'Please fill all fields!';
    } else if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters!';
    } else {
        try {
            // Create new admin user
            $user = new ParseUser();
            $user->setUsername($username);
            $user->setPassword($password);
            $user->setEmail($email);
            $user->set('role', 'admin');
            
            $user->signUp();
            
            $message = '✅ Admin user created successfully!<br>';
            $message .= '<strong>Login Details:</strong><br>';
            $message .= 'Username: ' . htmlspecialchars($username) . '<br>';
            $message .= 'Email: ' . htmlspecialchars($email) . '<br>';
            $message .= 'Password: ' . htmlspecialchars($password) . '<br><br>';
            $message .= '<a href="auth/login.php" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Login Now →</a>';
            
        } catch (ParseException $e) {
            $error = 'Parse Error (' . $e->getCode() . '): ' . $e->getMessage();
            error_log('Admin setup error: ' . $error);
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
            error_log('Admin setup general error: ' . $error);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Trace Admin Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: inherit;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        button:active {
            transform: translateY(0);
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        a {
            color: #667eea;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-row .form-group {
            margin-bottom: 0;
        }
        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Admin Setup</h1>
        <p class="subtitle">Create your first admin user and login to the dashboard</p>
        
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$message): ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">📧 Email Address</label>
                <input type="email" id="email" name="email" placeholder="admin@example.com" required>
            </div>
            
            <div class="form-group">
                <label for="username">👤 Username</label>
                <input type="text" id="username" name="username" placeholder="admin" required>
            </div>
            
            <div class="form-group">
                <label for="password">🔑 Password</label>
                <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required>
            </div>
            
            <button type="submit">Create Admin User</button>
        </form>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: center; font-size: 13px; color: #666;">
            <p>✅ Back4app credentials set up correctly?</p>
            <p>If yes, fill the form above and submit.</p>
        </div>
    </div>
</body>
</html>
