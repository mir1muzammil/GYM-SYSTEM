<?php
session_start();
require_once 'user_db.php';
require_once 'user_auth.php';

$page_title = 'Membership';
$uid = $_SESSION['user_id'];

$member = $conn->query("SELECT m.*, mt.TypeName, mt.DurationMonths, mt.Price FROM Member m LEFT JOIN Membership mt ON m.MembershipTypeID = mt.MembershipTypeID WHERE m.MemberID=$uid")->fetch_assoc();
$all_plans = $conn->query("SELECT * FROM Membership ORDER BY Price ASC");

require_once 'user_header.php';
?>

<div class="page-header">
  <h1>MEMBERSHIP</h1>
  <p>Your current plan and all available options</p>
</div>

<!-- Current Plan -->
<div class="card mb-3" style="<?= $member['TypeName'] ? 'border-color:rgba(200,245,66,0.4)' : '' ?>">
  <div class="card-header">
    <div class="card-title">Your Current Plan</div>
    <span class="badge badge-green">Active</span>
  </div>
  <?php if ($member['TypeName']): ?>
  <div style="display:flex;align-items:center;gap:2rem;flex-wrap:wrap;">
    <div>
      <div style="font-family:var(--font-display);font-size:3rem;letter-spacing:2px;line-height:1;margin-bottom:0.25rem;"><?= htmlspecialchars($member['TypeName']) ?></div>
      <div class="text-muted"><?= $member['DurationMonths'] ?> month<?= $member['DurationMonths'] > 1 ? 's' : '' ?> membership</div>
    </div>
    <div style="font-size:2.5rem;font-weight:700;color:var(--lime);">Rs. <?= number_format($member['Price']) ?></div>
    <div class="text-sm text-muted">
      <div>Member since: <strong><?= date('d M Y', strtotime($member['JoinDate'])) ?></strong></div>
    </div>
  </div>
  <?php else: ?>
  <p class="text-muted">No membership plan assigned. Contact reception to get a plan assigned.</p>
  <?php endif; ?>
</div>

<!-- All Plans -->
<div class="card">
  <div class="card-header">
    <div>
      <div class="card-title">All Membership Plans</div>
      <div class="card-subtitle">To upgrade or change your plan, contact our reception team.</div>
    </div>
  </div>
  <div class="plan-grid">
    <?php while ($p = $all_plans->fetch_assoc()):
      $is_current = ($p['MembershipTypeID'] == $member['MembershipTypeID']);
    ?>
    <div class="plan-card <?= $is_current ? 'selected' : '' ?>">
      <?php if ($is_current): ?>
        <div class="badge badge-green mb-1">Your Plan</div>
      <?php endif; ?>
      <div class="plan-name"><?= htmlspecialchars($p['TypeName']) ?></div>
      <div class="plan-price">Rs. <?= number_format($p['Price']) ?><span>/plan</span></div>
      <div class="plan-duration"><?= $p['DurationMonths'] ?> month<?= $p['DurationMonths'] > 1 ? 's' : '' ?> access</div>
      <?php if (!$is_current): ?>
      <div class="text-xs text-muted mt-2">Contact reception to switch</div>
      <?php endif; ?>
    </div>
    <?php endwhile; ?>
  </div>
  <div class="alert alert-info mt-3">
    📞 To upgrade your membership, visit reception or call us at <strong>+92 300 000 0000</strong>.
  </div>
</div>

<?php require_once 'user_footer.php'; ?>