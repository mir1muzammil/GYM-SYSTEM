<?php
session_start();
require_once 'user_db.php';

if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit(); }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $conn->prepare("SELECT MemberID, Name, Email, Password FROM Member WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($user['Password'] && password_verify($password, $user['Password'])) {
                $_SESSION['user_id'] = $user['MemberID'];
                $_SESSION['user_name'] = $user['Name'];
                $_SESSION['user_email'] = $user['Email'];
                $_SESSION['user_last_activity'] = time();
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Incorrect password. Please try again.';
            }
        } else {
            $error = 'No account found with that email address.';
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
<title>Sign In — Fitness Club</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <a href="index.php">FITNESS<span>CLUB</span></a>
      <p>Member portal</p>
    </div>
    <div class="auth-card">
      <div class="auth-title">Welcome Back</div>
      <div class="auth-subtitle">Sign in with your registered email to access your dashboard.</div>

      <?php if (isset($_GET['timeout'])): ?>
        <div class="alert alert-info">🕐 Your session expired. Please sign in again.</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Your password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In →</button>
      </form>

      <div class="auth-footer">
        New member? <a href="register.php">Create an account</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>