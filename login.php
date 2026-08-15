<?php
session_start();
require_once 'db_connect.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if (isset($_GET['reset'])) {
    $success = 'Password reset successfully! Please login with your new password.';
}

if (isset($_GET['timeout'])) {
    $error = 'Session expired. Please login again.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        $stmt = $conn->prepare("SELECT admin_id, username, password, full_name FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['admin_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['last_activity'] = time();
                
                header("Location: index.php");
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Username not found';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login | Fitness Club Management System</title>
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
        .btn-primary {
            height: 45px;
            border-radius: 5px;
            background: #11998e;
            border: none;
        }
        .btn-primary:hover {
            background: #0d7a70;
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
        <p class="login-box-msg">Sign in to start your session</p>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa fa-warning"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa fa-check"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="username" placeholder="Username" required>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-8">
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button>
                </div>
            </div>
        </form>
        
        <div class="text-center" style="margin-top: 20px;">
            <a href="signup.php">Don't have an account? Sign Up</a>
        </div>
    </div>
</div>
<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>