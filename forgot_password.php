<?php
session_start();
require_once 'db_connect.php';
date_default_timezone_set('Asia/Karachi');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$phpmailer_exists = file_exists('PHPMailer-master/src/PHPMailer.php');

if ($phpmailer_exists) {
    require 'PHPMailer-master/src/Exception.php';
    require 'PHPMailer-master/src/PHPMailer.php';
    require 'PHPMailer-master/src/SMTP.php';
}

if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    unset($_SESSION['reset_username']);
    unset($_SESSION['reset_token_id']);
    unset($_SESSION['reset_admin_id']);
}

$error = '';
$success = '';
$step = 1;

$conn->query("CREATE TABLE IF NOT EXISTS password_reset_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    token VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE CASCADE
)");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == 1) {
        $input = trim($_POST['input']);

        if (empty($input)) {
            $error = 'Please enter your username or email';
        } else {
            $stmt = $conn->prepare("SELECT admin_id, email, full_name, username FROM admin WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $input, $input);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if (empty($user['email'])) {
                    $error = 'No email address found for this account. Please contact administrator.';
                } else {
                    $pin = sprintf("%06d", mt_rand(0, 999999));
                    $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                    $stmt = $conn->prepare("INSERT INTO password_reset_tokens (admin_id, token, expires_at) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $user['admin_id'], $pin, $expires_at);

                    if ($stmt->execute()) {
                        if ($phpmailer_exists) {
                            $mail = new PHPMailer(true);
                            try {
                                $mail->isSMTP();
                                $mail->Host       = 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = ''; // Change this
                                $mail->Password   = '';     // Change this
                                $mail->SMTPSecure = 'tls';
                                $mail->Port       = 587;

                                $mail->setFrom('', 'Fitness Club Management');
                                $mail->addAddress($user['email']);

                                $mail->isHTML(false);
                                $mail->Subject = "Password Reset PIN - Fitness Club";
                                $mail->Body    = "Hello {$user['full_name']},\n\nYour password reset PIN is: {$pin}\n\nThis PIN will expire in 15 minutes.\n\nIf you did not request this, please ignore this email.";

                                $mail->send();

                                $success = 'A 6-digit PIN has been sent to your email address.';
                                $step = 2;
                                $_SESSION['reset_username'] = $user['username'];
                                $_SESSION['reset_admin_id'] = $user['admin_id'];
                            } catch (Exception $e) {
                                $error = 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo;
                            }
                        } else {
                            $success = "PHPMailer not installed. Your PIN is: <strong>{$pin}</strong> (expires in 15 min)";
                            $step = 2;
                            $_SESSION['reset_username'] = $user['username'];
                            $_SESSION['reset_admin_id'] = $user['admin_id'];
                        }
                    } else {
                        $error = 'Error processing request. Please try again.';
                    }
                }
            } else {
                $error = 'Username or email not found';
            }
            $stmt->close();
        }

    } elseif (isset($_POST['step']) && $_POST['step'] == 2) {
        $pin = trim($_POST['pin']);
        $admin_id = $_SESSION['reset_admin_id'] ?? 0;

        if (empty($pin)) {
            $error = 'Please enter the PIN';
            $step = 2;
        } else {
            $stmt = $conn->prepare("SELECT token_id, admin_id FROM password_reset_tokens WHERE admin_id = ? AND token = ? AND used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param("is", $admin_id, $pin);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $token_data = $result->fetch_assoc();

                $update = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token_id = ?");
                $update->bind_param("i", $token_data['token_id']);
                $update->execute();
                $update->close();

                $_SESSION['reset_token_id'] = $token_data['token_id'];

                $success = 'PIN verified! Please enter your new password.';
                $step = 3;
            } else {
                $error = 'Invalid or expired PIN';
                $step = 2;
            }
            $stmt->close();
        }

    } elseif (isset($_POST['step']) && $_POST['step'] == 3) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $token_id = $_SESSION['reset_token_id'] ?? 0;
        $admin_id = $_SESSION['reset_admin_id'] ?? 0;

        if (empty($new_password) || empty($confirm_password)) {
            $error = 'Please enter both password fields';
            $step = 3;
        } elseif ($new_password !== $confirm_password) {
            $error = 'Passwords do not match';
            $step = 3;
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters long';
            $step = 3;
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE admin SET password = ? WHERE admin_id = ?");
            $stmt->bind_param("si", $hashed_password, $admin_id);

            if ($stmt->execute()) {
                unset($_SESSION['reset_username']);
                unset($_SESSION['reset_token_id']);
                unset($_SESSION['reset_admin_id']);

                header("Location: login.php?reset=1");
                exit();
            } else {
                $error = 'Error resetting password. Please try again.';
                $step = 3;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Forgot Password | Fitness Club Management System</title>
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
        .login-box { width: 400px; margin: 0 auto; }
        .login-logo { font-size: 30px; text-align: center; margin-bottom: 25px; font-weight: 300; }
        .login-logo b { font-weight: 600; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .login-box-body { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .login-box-msg { margin: 0; text-align: center; padding: 0 20px 20px 20px; font-size: 16px; color: #666; }
        .form-control { height: 45px; border-radius: 5px; }
        .btn-primary { height: 45px; border-radius: 5px; background: #11998e; border: none; }
        .btn-primary:hover { background: #0d7a70; }
        .alert { border-radius: 5px; }
        .back-link { color: #11998e; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo"><b>Fitness</b> Club</div>
    <div class="login-box-body">
        <p class="login-box-msg">Reset Your Password</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fa fa-warning"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fa fa-check"></i> <?= $success ?></div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
        <form method="post">
            <input type="hidden" name="step" value="1">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="input" placeholder="Enter email or username" required>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-8"><a href="login.php" class="back-link">Back to Login</a></div>
                <div class="col-xs-4"><button type="submit" class="btn btn-primary btn-block btn-flat">Send PIN</button></div>
            </div>
        </form>

        <?php elseif ($step == 2): ?>
        <form method="post">
            <input type="hidden" name="step" value="2">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="pin" placeholder="Enter 6-digit PIN" maxlength="6" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-8"><a href="forgot_password.php" class="back-link">Resend PIN</a></div>
                <div class="col-xs-4"><button type="submit" class="btn btn-primary btn-block btn-flat">Verify</button></div>
            </div>
        </form>

        <?php elseif ($step == 3): ?>
        <form method="post">
            <input type="hidden" name="step" value="3">
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="new_password" placeholder="New Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-flat">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>