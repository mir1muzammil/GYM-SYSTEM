<?php
session_start();
require_once 'db_connect.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Username already exists';
        } else {
            // Check if email exists
            $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Email already registered';
            } else {
                // Create account
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO admin (username, email, full_name, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $full_name, $hashed_password);
                
                if ($stmt->execute()) {
                    header("Location: login.php?registered=1");
                    exit();
                } else {
                    $error = 'Error creating account. Please try again.';
                }
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sign Up | Fitness Club Management System</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    
    <style>
        .login-page {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            width: 400px;
            margin: 0 auto;
        }
        .login-logo {
            font-size: 30px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 300;
        }
        .login-logo b {
            font-weight: 600;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .login-box-body {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .login-box-msg {
            margin: 0;
            text-align: center;
            padding: 0 20px 20px 20px;
            font-size: 16px;
            color: #666;
        }
        .form-control {
            height: 45px;
            border-radius: 5px;
        }
        .btn-success {
            height: 45px;
            border-radius: 5px;
        }
        .alert {
            border-radius: 5px;
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <b>Fitness</b> Club
    </div>
    <div class="login-box-body">
        <p class="login-box-msg">Create a new account</p>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa fa-warning"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="full_name" placeholder="Full Name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="username" placeholder="Username *" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="email" class="form-control" name="email" placeholder="Email *" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="password" placeholder="Password *" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password *" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <button type="submit" class="btn btn-success btn-block btn-flat">Sign Up</button>
        </form>
        
        <div class="text-center" style="margin-top: 20px;">
            <a href="login.php">Already have an account? Sign In</a>
        </div>
    </div>
</div>
<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>