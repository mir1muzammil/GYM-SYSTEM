<?php
session_start();
require_once 'user_db.php';
require_once 'user_auth.php';

$page_title = 'Profile';
$uid = $_SESSION['user_id'];

$success = '';
$error   = '';

$member = $conn->query("SELECT m.*, mt.TypeName FROM Member m LEFT JOIN Membership mt ON m.MembershipTypeID=mt.MembershipTypeID WHERE m.MemberID=$uid")->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name    = trim($_POST['name']);
    $age     = intval($_POST['age']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    if (empty($name)) {
        $error = 'Name cannot be empty.';
    } else {
        $stmt = $conn->prepare("UPDATE Member SET Name=?, Age=?, ContactNumber=?, Address=? WHERE MemberID=?");
        $stmt->bind_param("sissi", $name, $age, $contact, $address, $uid);
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
            $member['Name'] = $name;
            $member['Age'] = $age;
            $member['ContactNumber'] = $contact;
            $member['Address'] = $address;
        } else {
            $error = 'Failed to update profile.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new_pw  = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $member['Password'] ?? '')) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new_pw) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_pw !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Member SET Password=? WHERE MemberID=?");
        $stmt->bind_param("si", $hashed, $uid);
        if ($stmt->execute()) {
            $success = 'Password changed successfully!';
        } else {
            $error = 'Failed to change password.';
        }
    }
}

require_once 'user_header.php';
$initials = strtoupper(substr($member['Name'], 0, 2));
?>

<div class="page-header">
  <h1>PROFILE</h1>
  <p>Manage your account settings</p>
</div>

<?php if ($success): ?><div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="grid-2" style="gap:1.5rem;align-items:start;">

  <!-- Profile Card -->
  <div>
    <div class="card mb-2" style="text-align:center;padding:2.5rem;">
      <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,var(--lime),var(--lime-dim));display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:2.5rem;color:var(--black);margin:0 auto 1.25rem;">
        <?= $initials ?>
      </div>
      <div style="font-size:1.25rem;font-weight:700;margin-bottom:0.25rem;"><?= htmlspecialchars($member['Name']) ?></div>
      <div class="text-muted text-sm"><?= htmlspecialchars($member['Email']) ?></div>
      <?php if ($member['TypeName']): ?>
      <span class="badge badge-green mt-2"><?= htmlspecialchars($member['TypeName']) ?> Member</span>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-header"><div class="card-title">Member Info</div></div>
      <div style="display:flex;flex-direction:column;gap:0.75rem;font-size:0.875rem;">
        <div style="display:flex;justify-content:space-between;">
          <span class="text-muted">Member ID</span>
          <span style="font-family:var(--font-mono);">#<?= str_pad($uid,4,'0',STR_PAD_LEFT) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;">
          <span class="text-muted">Joined</span>
          <span><?= date('d M Y', strtotime($member['JoinDate'])) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;">
          <span class="text-muted">Gender</span>
          <span><?= htmlspecialchars($member['Gender'] ?? '—') ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;">
          <span class="text-muted">Age</span>
          <span><?= $member['Age'] ?: '—' ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;">
          <span class="text-muted">Contact</span>
          <span><?= htmlspecialchars($member['ContactNumber'] ?? '—') ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <span class="text-muted">Address</span>
          <span style="text-align:right;max-width:60%;"><?= htmlspecialchars($member['Address'] ?? '—') ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Forms -->
  <div>
    <div class="card mb-2">
      <div class="card-header"><div class="card-title">Edit Profile</div></div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($member['Name']) ?>" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Age</label>
            <input type="number" name="age" class="form-control" value="<?= $member['Age'] ?>" min="10" max="100">
          </div>
          <div class="form-group">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($member['ContactNumber'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($member['Address'] ?? '') ?>">
        </div>
        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
      </form>
    </div>

    <div class="card">
      <div class="card-header"><div class="card-title">Change Password</div></div>
      <form method="POST">
        <div class="form-group">
          <label class="form-label">Current Password</label>
          <input type="password" name="current_password" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters" required>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" name="change_password" class="btn btn-outline">Update Password</button>
      </form>
    </div>
  </div>

</div>

<?php require_once 'user_footer.php'; ?>