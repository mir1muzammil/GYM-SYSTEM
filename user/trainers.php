<?php
session_start();
require_once 'user_db.php';
require_once 'user_auth.php';

$page_title = 'Our Trainers';
$uid = $_SESSION['user_id'];

$trainers = $conn->query("SELECT t.*, COUNT(wp.PlanID) as plan_count FROM Trainer t LEFT JOIN WorkoutPlan wp ON t.TrainerID = wp.TrainerID GROUP BY t.TrainerID ORDER BY t.Name");

require_once 'user_header.php';
?>

<div class="page-header">
  <h1>OUR TRAINERS</h1>
  <p>Meet our certified fitness coaches</p>
</div>

<div class="trainer-grid">
<?php while ($t = $trainers->fetch_assoc()):
  $initials = strtoupper(substr($t['Name'], 0, 1));
  // Get plans for this trainer
  $plans = $conn->query("SELECT PlanName FROM WorkoutPlan WHERE TrainerID={$t['TrainerID']}");
?>
<div class="trainer-card" style="text-align:left;padding:1.75rem;">
  <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.25rem;">
    <div class="trainer-avatar" style="margin:0;"><?= $initials ?></div>
    <div>
      <div class="trainer-name" style="text-align:left;"><?= htmlspecialchars($t['Name']) ?></div>
      <div class="trainer-spec" style="text-align:left;"><?= htmlspecialchars($t['Specialization']) ?></div>
    </div>
  </div>
  <hr>
  <div style="margin-top:1rem;display:flex;flex-direction:column;gap:0.5rem;">
    <div class="flex gap-1 items-center text-sm">
      <span style="color:var(--muted);">📞</span>
      <span><?= htmlspecialchars($t['ContactNumber']) ?></span>
    </div>
    <div class="flex gap-1 items-center text-sm">
      <span style="color:var(--muted);">✉️</span>
      <span class="text-muted"><?= htmlspecialchars($t['Email']) ?></span>
    </div>
  </div>
  <?php if ($t['plan_count'] > 0): ?>
  <div style="margin-top:1.25rem;">
    <div class="text-xs text-muted" style="text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.5rem;">Programs</div>
    <div style="display:flex;flex-wrap:wrap;gap:0.35rem;">
      <?php while ($p = $plans->fetch_assoc()): ?>
        <span class="badge badge-gray"><?= htmlspecialchars($p['PlanName']) ?></span>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endwhile; ?>
</div>

<?php require_once 'user_footer.php'; ?>