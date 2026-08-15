<?php
session_start();
require_once 'user_db.php';
require_once 'user_auth.php';

$page_title = 'Dashboard';
$uid = $_SESSION['user_id'];

// Fetch member full details
$member = $conn->query("SELECT m.*, mt.TypeName, mt.DurationMonths, mt.Price
    FROM Member m LEFT JOIN Membership mt ON m.MembershipTypeID = mt.MembershipTypeID
    WHERE m.MemberID = $uid")->fetch_assoc();

// Attendance this month
$att = $conn->query("SELECT COUNT(*) as cnt FROM Attendance WHERE MemberID=$uid AND Status='Present' AND MONTH(Date)=MONTH(CURDATE()) AND YEAR(Date)=YEAR(CURDATE())")->fetch_assoc()['cnt'];

// Total attendance
$att_total = $conn->query("SELECT COUNT(*) as cnt FROM Attendance WHERE MemberID=$uid AND Status='Present'")->fetch_assoc()['cnt'];

// Active schedule
$schedules = $conn->query("SELECT ws.*, wp.PlanName, wp.DurationWeeks, wp.Description, t.Name as TrainerName
    FROM WorkoutSchedule ws
    JOIN WorkoutPlan wp ON ws.PlanID = wp.PlanID
    LEFT JOIN Trainer t ON wp.TrainerID = t.TrainerID
    WHERE ws.MemberID=$uid AND ws.StartDate <= CURDATE() AND ws.EndDate >= CURDATE()");
$active_schedule = $schedules->fetch_assoc();

// Total payments
$paid_total = $conn->query("SELECT SUM(Amount) as total FROM Payment WHERE MemberID=$uid")->fetch_assoc()['total'] ?? 0;

// Recent attendance (last 7)
$recent_att = $conn->query("SELECT * FROM Attendance WHERE MemberID=$uid ORDER BY Date DESC LIMIT 7");

// Days since joining
$join_days = (strtotime(date('Y-m-d')) - strtotime($member['JoinDate'])) / 86400;

require_once 'user_header.php';
?>

<?php if (isset($_GET['welcome'])): ?>
<div class="alert alert-success">🎉 Welcome to Fitness Club, <?= htmlspecialchars($member['Name']) ?>! Your membership is now active.</div>
<?php endif; ?>

<div class="page-header">
  <h1>DASHBOARD</h1>
  <p>Welcome back, <?= htmlspecialchars(explode(' ', $member['Name'])[0]) ?>. Here's your fitness overview.</p>
</div>

<!-- STATS -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon">🏃</div>
    <div class="stat-value"><?= $att ?></div>
    <div class="stat-label">Visits This Month</div>
  </div>
  <div class="stat-card accent">
    <div class="stat-icon">📅</div>
    <div class="stat-value"><?= $att_total ?></div>
    <div class="stat-label">Total Check-ins</div>
  </div>
  <div class="stat-card accent2">
    <div class="stat-icon">💪</div>
    <div class="stat-value"><?= intval($join_days) ?></div>
    <div class="stat-label">Days as Member</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon">💳</div>
    <div class="stat-value">Rs.<?= number_format($paid_total) ?></div>
    <div class="stat-label">Total Paid</div>
  </div>
</div>

<div class="grid-2" style="gap:1.5rem;">
  <!-- Membership Info -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Membership</div>
        <div class="card-subtitle">Your current plan</div>
      </div>
      <a href="membership.php" class="btn btn-outline btn-sm">View All Plans</a>
    </div>
    <?php if ($member['TypeName']): ?>
    <div style="display:flex;gap:1rem;align-items:center;margin-bottom:1rem;">
      <div style="flex:1;">
        <div style="font-family:var(--font-display);font-size:1.8rem;letter-spacing:1px;"><?= htmlspecialchars($member['TypeName']) ?></div>
        <div style="color:var(--muted);font-size:0.85rem;"><?= $member['DurationMonths'] ?> month<?= $member['DurationMonths'] > 1 ? 's' : '' ?> · Rs. <?= number_format($member['Price']) ?></div>
      </div>
      <span class="badge badge-green">Active</span>
    </div>
    <hr>
    <div class="flex gap-2 text-sm" style="margin-top:1rem;">
      <div><span class="text-muted">Member since</span><br><strong><?= date('d M Y', strtotime($member['JoinDate'])) ?></strong></div>
      <div><span class="text-muted">Member ID</span><br><strong>#<?= str_pad($uid, 4, '0', STR_PAD_LEFT) ?></strong></div>
    </div>
    <?php else: ?>
    <p class="text-muted">No membership plan assigned. <a href="membership.php" style="color:var(--lime)">Choose a plan →</a></p>
    <?php endif; ?>
  </div>

  <!-- Active Workout Plan -->
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Active Workout Plan</div>
        <div class="card-subtitle">Your current program</div>
      </div>
      <a href="my_schedule.php" class="btn btn-outline btn-sm">All Schedules</a>
    </div>
    <?php if ($active_schedule): ?>
    <div style="margin-bottom:1rem;">
      <div style="font-family:var(--font-display);font-size:1.8rem;letter-spacing:1px;margin-bottom:0.25rem;"><?= htmlspecialchars($active_schedule['PlanName']) ?></div>
      <div class="text-muted text-sm"><?= htmlspecialchars($active_schedule['Description'] ?? '') ?></div>
    </div>
    <?php
    $start = strtotime($active_schedule['StartDate']);
    $end   = strtotime($active_schedule['EndDate']);
    $now   = time();
    $total_days = max(1, ($end - $start) / 86400);
    $done_days  = min($total_days, ($now - $start) / 86400);
    $pct = min(100, round(($done_days / $total_days) * 100));
    ?>
    <div class="text-xs text-muted mb-1">Progress — <?= $pct ?>% complete</div>
    <div class="progress-bar-wrap mb-2"><div class="progress-bar-fill" style="width:<?= $pct ?>%"></div></div>
    <div class="flex gap-2 text-sm">
      <div><span class="text-muted">Trainer</span><br><strong><?= htmlspecialchars($active_schedule['TrainerName'] ?? 'N/A') ?></strong></div>
      <div><span class="text-muted">Ends</span><br><strong><?= date('d M Y', strtotime($active_schedule['EndDate'])) ?></strong></div>
    </div>
    <?php else: ?>
    <p class="text-muted">No active workout plan. Ask your trainer to assign one!</p>
    <?php endif; ?>
  </div>
</div>

<!-- Recent Attendance -->
<div class="card mt-3">
  <div class="card-header">
    <div class="card-title">Recent Attendance</div>
    <a href="my_attendance.php" class="btn btn-outline btn-sm">Full History</a>
  </div>
  <?php if ($recent_att->num_rows > 0): ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Duration</th><th>Status</th></tr></thead>
      <tbody>
        <?php while ($a = $recent_att->fetch_assoc()):
          $dur = '';
          if ($a['CheckInTime'] && $a['CheckOutTime']) {
            $diff = strtotime($a['CheckOutTime']) - strtotime($a['CheckInTime']);
            $dur = floor($diff/3600).'h '.round(($diff%3600)/60).'m';
          }
        ?>
        <tr>
          <td><?= date('D, d M', strtotime($a['Date'])) ?></td>
          <td><?= $a['CheckInTime'] ? date('h:i A', strtotime($a['CheckInTime'])) : '—' ?></td>
          <td><?= $a['CheckOutTime'] ? date('h:i A', strtotime($a['CheckOutTime'])) : '—' ?></td>
          <td><?= $dur ?: '—' ?></td>
          <td>
            <?php if ($a['Status'] === 'Present'): ?>
              <span class="badge badge-green">● Present</span>
            <?php else: ?>
              <span class="badge badge-red">● Absent</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <p class="text-muted">No attendance records found.</p>
  <?php endif; ?>
</div>

<?php require_once 'user_footer.php'; ?>