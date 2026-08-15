<?php
session_start();
require_once 'user_db.php';
require_once 'user_auth.php';

$page_title = 'My Schedule';
$uid = $_SESSION['user_id'];

$schedules = $conn->query("SELECT ws.*, wp.PlanName, wp.Description, wp.DurationWeeks, t.Name as TrainerName, t.Specialization, t.ContactNumber as TrainerContact
    FROM WorkoutSchedule ws
    JOIN WorkoutPlan wp ON ws.PlanID = wp.PlanID
    LEFT JOIN Trainer t ON wp.TrainerID = t.TrainerID
    WHERE ws.MemberID=$uid
    ORDER BY ws.StartDate DESC");

require_once 'user_header.php';
?>

<div class="page-header">
  <h1>MY SCHEDULE</h1>
  <p>Your assigned workout plans and programs</p>
</div>

<?php if ($schedules->num_rows === 0): ?>
<div class="card" style="text-align:center;padding:4rem;">
  <div style="font-size:4rem;margin-bottom:1rem;">🏋️</div>
  <h2 style="font-family:var(--font-display);font-size:2rem;margin-bottom:0.5rem;">No Schedules Yet</h2>
  <p class="text-muted">Your trainer will assign workout plans to you. Check back soon!</p>
  <a href="trainers.php" class="btn btn-primary mt-3">Meet Our Trainers</a>
</div>
<?php else: ?>

<?php $today = date('Y-m-d'); while ($s = $schedules->fetch_assoc()):
  $is_active = ($s['StartDate'] <= $today && $s['EndDate'] >= $today);
  $is_upcoming = $s['StartDate'] > $today;
  $is_done = $s['EndDate'] < $today;

  $start = strtotime($s['StartDate']);
  $end   = strtotime($s['EndDate']);
  $now   = time();
  $total_days = max(1, ($end - $start) / 86400);
  $done_days  = max(0, min($total_days, ($now - $start) / 86400));
  $pct = min(100, round(($done_days / $total_days) * 100));
?>
<div class="card mb-2" style="<?= $is_active ? 'border-color:rgba(200,245,66,0.4)' : '' ?>">
  <div style="display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap;">
    <div style="min-width:80px;text-align:center;">
      <div style="font-family:var(--font-display);font-size:2.5rem;line-height:1;color:<?= $is_active ? 'var(--lime)' : 'var(--muted)' ?>;"><?= date('d', strtotime($s['StartDate'])) ?></div>
      <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--muted);"><?= date('M Y', strtotime($s['StartDate'])) ?></div>
    </div>
    <div style="flex:1;min-width:200px;">
      <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.5rem;flex-wrap:wrap;">
        <span style="font-family:var(--font-display);font-size:1.6rem;letter-spacing:1px;"><?= htmlspecialchars($s['PlanName']) ?></span>
        <?php if ($is_active): ?>
          <span class="badge badge-green">● Active</span>
        <?php elseif ($is_upcoming): ?>
          <span class="badge badge-blue">Upcoming</span>
        <?php else: ?>
          <span class="badge badge-gray">Completed</span>
        <?php endif; ?>
      </div>
      <p class="text-muted text-sm mb-2"><?= htmlspecialchars($s['Description'] ?? '') ?></p>

      <div class="flex gap-2 text-sm mb-2" style="flex-wrap:wrap;">
        <div><span class="text-muted">Duration</span><br><strong><?= $s['DurationWeeks'] ?> weeks</strong></div>
        <div><span class="text-muted">Trainer</span><br><strong><?= htmlspecialchars($s['TrainerName'] ?? 'Unassigned') ?></strong></div>
        <div><span class="text-muted">Specialization</span><br><strong><?= htmlspecialchars($s['Specialization'] ?? '—') ?></strong></div>
        <div><span class="text-muted">Ends</span><br><strong><?= date('d M Y', strtotime($s['EndDate'])) ?></strong></div>
      </div>

      <?php if (!$is_upcoming): ?>
      <div class="text-xs text-muted mb-1" style="margin-top:0.75rem;">Progress — <?= $pct ?>% complete (<?= intval($done_days) ?> / <?= intval($total_days) ?> days)</div>
      <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= $pct ?>%;<?= $is_done ? 'background:var(--muted)' : '' ?>"></div></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endwhile; ?>

<?php endif; ?>

<?php require_once 'user_footer.php'; ?>