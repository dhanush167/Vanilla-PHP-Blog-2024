<?php
/**
 * Super Admin Setup Script
 * 
 * This script creates a super admin user and assigns the super_admin role.
 * Run this script once after setting up the database.
 * 
 * Usage: Access via browser or run from command line
 * 
 * SECURITY: Delete this file after creating your super admin!
 */

require_once __DIR__ . '/../config/config.php';

// Check if super admin already exists
$checkStmt = $mysqli->prepare("SELECT COUNT(*) as count FROM user_roles ur INNER JOIN roles r ON ur.role_id = r.id WHERE r.slug = 'super_admin'");
$checkStmt->execute();
$result = $checkStmt->get_result();
$row = $result->fetch_assoc();
$superAdminExists = (int)($row['count'] ?? 0) > 0;
$checkStmt->close();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$superAdminExists) {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($username) || empty($password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        // Check if username or email already exists
        $stmt = $mysqli->prepare("SELECT id FROM login WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->close();
            
            $stmt = $mysqli->prepare("INSERT INTO login (name, email, username, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $username, $hashedPassword);
            
            if ($stmt->execute()) {
                $userId = $mysqli->insert_id;
                
                // Get super_admin role ID
                $roleStmt = $mysqli->prepare("SELECT id FROM roles WHERE slug = 'super_admin' LIMIT 1");
                $roleStmt->execute();
                $roleResult = $roleStmt->get_result();
                $role = $roleResult->fetch_assoc();
                $roleStmt->close();
                
                if ($role) {
                    // Assign super_admin role
                    $assignStmt = $mysqli->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                    $assignStmt->bind_param("ii", $userId, $role['id']);
                    
                    if ($assignStmt->execute()) {
                        $message = 'Super admin created successfully! You can now login with username: ' . htmlspecialchars($username);
                        $superAdminExists = true;
                    } else {
                        $error = 'Failed to assign super admin role.';
                    }
                    $assignStmt->close();
                } else {
                    $error = 'Super admin role not found. Please run the database migration first.';
                }
            } else {
                $error = 'Failed to create user.';
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
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
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .warning-box {
            background: #fff3cd;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Super Admin Setup</h1>
        
        <?php if ($superAdminExists): ?>
            <div class="alert alert-warning">
                <strong>Super admin already exists!</strong><br>
                A super admin user has already been created. If you need to create another one, 
                you can do so through the admin panel after logging in.
            </div>
            <p><a href="auth/login.php">Go to Login</a></p>
        <?php else: ?>
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?= $message ?>
                </div>
                <p><a href="auth/login.php">Go to Login</a></p>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <div class="warning-box">
                    <strong>⚠️ Security Warning:</strong><br>
                    Delete this file after creating your super admin account!
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required minlength="8">
                        <small style="color: #666;">Minimum 8 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    
                    <button type="submit">Create Super Admin</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
