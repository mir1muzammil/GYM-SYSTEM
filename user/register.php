<?php
session_start();
require_once 'user_db.php';

if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit(); }

$error = '';
$success = '';
$memberships = $conn->query("SELECT * FROM Membership ORDER BY Price ASC");
$selected_plan = intval($_GET['plan'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $age      = intval($_POST['age']);
    $gender   = trim($_POST['gender']);
    $contact  = trim($_POST['contact']);
    $address  = trim($_POST['address']);
    $mem_id   = intval($_POST['membership_id']);

    if (empty($name) || empty($email) || empty($password) || empty($gender)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif ($age < 10 || $age > 100) {
        $error = 'Please enter a valid age.';
    } else {
        // Check email exists
        $check = $conn->prepare("SELECT MemberID FROM Member WHERE Email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'This email is already registered. <a href="user_login.php" style="color:var(--lime)">Sign in instead?</a>';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $join_date = date('Y-m-d');
            $stmt = $conn->prepare("INSERT INTO Member (Name, Age, Gender, ContactNumber, Email, Address, JoinDate, MembershipTypeID, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssssis", $name, $age, $gender, $contact, $email, $address, $join_date, $mem_id, $hashed);
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                $_SESSION['user_id'] = $new_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_last_activity'] = time();
                header("Location: dashboard.php?welcome=1");
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Join Fitness Club</title>
<link rel="stylesheet" href="style.css">
<style>
.auth-page { align-items: flex-start; padding-top: 2rem; }
.auth-box { max-width: 560px; }
.step-indicator { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; }
.step { height: 3px; flex: 1; background: var(--border); border-radius: 99px; }
.step.done { background: var(--lime); }
</style>
</head>
<body>
<div class="auth-page">
  <div class="auth-box">
    <div class="auth-logo">
      <a href="index.php">FITNESS<span>CLUB</span></a>
      <p>Create your member account</p>
    </div>
    <div class="auth-card">
      <div class="auth-title">Join the Club</div>
      <div class="auth-subtitle">Fill in your details to get started. It takes less than 2 minutes.</div>

      <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?= $error ?></div>
      <?php endif; ?>

      <form method="POST" id="regForm">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control" placeholder="Ahmed Khan" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" placeholder="ahmed@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Age *</label>
            <input type="number" name="age" class="form-control" placeholder="25" min="10" max="100" value="<?= htmlspecialchars($_POST['age'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Gender *</label>
            <select name="gender" class="form-control" required>
              <option value="">Select gender</option>
              <option value="Male" <?= ($_POST['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= ($_POST['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact" class="form-control" placeholder="0300-1234567" value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" placeholder="Your area, Karachi" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Membership Plan *</label>
          <div class="plan-grid" style="grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:0.75rem;">
            <?php $memberships->data_seek(0); while ($m = $memberships->fetch_assoc()): ?>
            <label class="plan-card" style="cursor:pointer;padding:1rem;" id="plan_<?= $m['MembershipTypeID'] ?>">
              <input type="radio" name="membership_id" value="<?= $m['MembershipTypeID'] ?>"
                style="display:none"
                <?= (($selected_plan == $m['MembershipTypeID']) || (($_POST['membership_id'] ?? 0) == $m['MembershipTypeID'])) ? 'checked' : '' ?>
                onchange="selectPlan(<?= $m['MembershipTypeID'] ?>)" required>
              <div class="plan-name" style="font-size:1.2rem;"><?= htmlspecialchars($m['TypeName']) ?></div>
              <div class="plan-price" style="font-size:1.2rem;">Rs.<?= number_format($m['Price']) ?></div>
              <div class="plan-duration"><?= $m['DurationMonths'] ?> month<?= $m['DurationMonths'] > 1 ? 's' : '' ?></div>
            </label>
            <?php endwhile; ?>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Create Account →</button>
      </form>

      <div class="auth-footer">Already a member? <a href="user_login.php">Sign In</a></div>
    </div>
  </div>
</div>

<script>
function selectPlan(id) {
  document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
  document.getElementById('plan_' + id)?.classList.add('selected');
}
// Highlight pre-selected
document.addEventListener('DOMContentLoaded', () => {
  const checked = document.querySelector('input[name="membership_id"]:checked');
  if (checked) selectPlan(checked.value);
  <?php if ($selected_plan): ?>
  selectPlan(<?= $selected_plan ?>);
  document.querySelector('input[name="membership_id"][value="<?= $selected_plan ?>"]').checked = true;
  <?php endif; ?>
});
</script>
</body>
</html>