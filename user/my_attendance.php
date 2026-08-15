<?php
session_start();
require_once 'user_db.php';
require_once 'user_auth.php';

$page_title = 'My Attendance';
$uid = $_SESSION['user_id'];
$success = '';
$error   = '';

// ── SELF CHECK-IN LOGIC ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'checkin') {
        // Check if already checked in today
        $today = date('Y-m-d');
        $exists = $conn->query("SELECT AttendanceID, CheckInTime, CheckOutTime FROM Attendance WHERE MemberID=$uid AND Date='$today'")->fetch_assoc();

        if ($exists && $exists['CheckInTime'] && !$exists['CheckOutTime']) {
            $error = 'You are already checked in. Click Check Out when you leave.';
        } elseif ($exists && $exists['CheckOutTime']) {
            $error = 'You have already completed your session for today.';
        } elseif ($exists) {
            $error = 'Attendance already recorded for today.';
        } else {
            $time = date('H:i:s');
            $stmt = $conn->prepare("INSERT INTO Attendance (MemberID, Date, CheckInTime, Status) VALUES (?, ?, ?, 'Present')");
            $stmt->bind_param("iss", $uid, $today, $time);
            if ($stmt->execute()) {
                $success = '✅ Checked in at ' . date('h:i A') . '. Have a great workout!';
            } else {
                $error = 'Check-in failed. Please try again.';
            }
        }
    }

    if ($_POST['action'] === 'checkout') {
        $today = date('Y-m-d');
        $record = $conn->query("SELECT AttendanceID, CheckInTime FROM Attendance WHERE MemberID=$uid AND Date='$today' AND CheckOutTime IS NULL")->fetch_assoc();

        if (!$record) {
            $error = 'No active check-in found for today.';
        } else {
            $time = date('H:i:s');
            $stmt = $conn->prepare("UPDATE Attendance SET CheckOutTime=? WHERE AttendanceID=?");
            $stmt->bind_param("si", $time, $record['AttendanceID']);
            if ($stmt->execute()) {
                // Calculate duration
                $diff = strtotime($time) - strtotime($record['CheckInTime']);
                $dur  = floor($diff/3600).'h '.round(($diff%3600)/60).'m';
                $success = '👋 Checked out at ' . date('h:i A') . ". Great session! Duration: $dur";
            } else {
                $error = 'Check-out failed. Please try again.';
            }
        }
    }
}

// ── TODAY STATUS ─────────────────────────────────────────────────
$today_record = $conn->query("SELECT * FROM Attendance WHERE MemberID=$uid AND Date='" . date('Y-m-d') . "'")->fetch_assoc();

// ── FILTERS ──────────────────────────────────────────────────────
$month = intval($_GET['month'] ?? date('m'));
$year  = intval($_GET['year']  ?? date('Y'));

$attendance     = $conn->query("SELECT * FROM Attendance WHERE MemberID=$uid AND MONTH(Date)=$month AND YEAR(Date)=$year ORDER BY Date DESC");
$present_count  = $conn->query("SELECT COUNT(*) as c FROM Attendance WHERE MemberID=$uid AND Status='Present' AND MONTH(Date)=$month AND YEAR(Date)=$year")->fetch_assoc()['c'];
$absent_count   = $conn->query("SELECT COUNT(*) as c FROM Attendance WHERE MemberID=$uid AND Status='Absent'  AND MONTH(Date)=$month AND YEAR(Date)=$year")->fetch_assoc()['c'];
$total_visits   = $conn->query("SELECT COUNT(*) as c FROM Attendance WHERE MemberID=$uid AND Status='Present'")->fetch_assoc()['c'];

// Total hours this month
$hours_row = $conn->query("SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(CheckOutTime, CheckInTime)))) as total_time FROM Attendance WHERE MemberID=$uid AND CheckInTime IS NOT NULL AND CheckOutTime IS NOT NULL AND MONTH(Date)=$month AND YEAR(Date)=$year")->fetch_assoc();
$total_time = $hours_row['total_time'] ?? null;

require_once 'user_header.php';
?>

<style>
.checkin-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 2rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}
.checkin-card.active {
    border-color: rgba(200,245,66,0.5);
    background: linear-gradient(135deg, rgba(200,245,66,0.06) 0%, var(--card) 60%);
}
.checkin-card.done {
    border-color: rgba(74,158,255,0.3);
}
.checkin-status-dot {
    width: 12px; height: 12px; border-radius: 50%;
    display: inline-block; margin-right: 8px;
    animation: pulse 2s infinite;
}
.checkin-status-dot.active { background: var(--lime); }
.checkin-status-dot.idle   { background: var(--muted); animation: none; }
.checkin-status-dot.done   { background: var(--blue); animation: none; }
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.5; transform: scale(1.3); }
}
.checkin-time {
    font-family: var(--font-mono);
    font-size: 3rem;
    line-height: 1;
    color: var(--white);
    margin: 0.5rem 0;
}
.checkin-actions { display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap; }
.btn-checkin {
    padding: 1rem 2.5rem;
    font-size: 1rem;
    font-family: var(--font-display);
    letter-spacing: 2px;
    border-radius: var(--radius-sm);
    border: none; cursor: pointer;
    transition: all 0.2s;
}
.btn-checkin-in  { background: var(--lime); color: var(--black); }
.btn-checkin-in:hover  { background: #d4ff55; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(200,245,66,0.25); }
.btn-checkin-out { background: var(--orange); color: var(--black); }
.btn-checkin-out:hover { background: #ffaa66; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,140,66,0.25); }
.duration-live {
    font-family: var(--font-mono);
    font-size: 1.4rem;
    color: var(--lime);
    margin-top: 0.5rem;
}
</style>

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1>ATTENDANCE</h1>
            <p>Self check-in and your visit history</p>
        </div>
        <form method="GET" style="display:flex;gap:0.5rem;align-items:center;">
            <select name="month" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <?php for ($m=1; $m<=12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-control" style="width:auto;" onchange="this.form.submit()">
                <?php for ($y=date('Y'); $y>=date('Y')-3; $y--): ?>
                    <option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- ── CHECK-IN CARD ── -->
<?php
$is_checked_in  = $today_record && $today_record['CheckInTime'] && !$today_record['CheckOutTime'];
$is_checked_out = $today_record && $today_record['CheckOutTime'];
?>
<div class="checkin-card <?= $is_checked_in ? 'active' : ($is_checked_out ? 'done' : '') ?>">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--muted);margin-bottom:0.5rem;">
                <?php if ($is_checked_in): ?>
                    <span class="checkin-status-dot active"></span>SESSION IN PROGRESS
                <?php elseif ($is_checked_out): ?>
                    <span class="checkin-status-dot done"></span>SESSION COMPLETE
                <?php else: ?>
                    <span class="checkin-status-dot idle"></span>NOT CHECKED IN TODAY
                <?php endif; ?>
            </div>

            <div class="checkin-time" id="liveClock"><?= date('h:i A') ?></div>
            <div style="color:var(--muted);font-size:0.875rem;"><?= date('l, d F Y') ?></div>

            <?php if ($is_checked_in): ?>
                <div class="duration-live" id="sessionTimer">Session: calculating...</div>
            <?php elseif ($is_checked_out):
                $diff = strtotime($today_record['CheckOutTime']) - strtotime($today_record['CheckInTime']);
                $dur  = floor($diff/3600).'h '.round(($diff%3600)/60).'m';
            ?>
                <div style="color:var(--blue);font-family:var(--font-mono);font-size:1.1rem;margin-top:0.5rem;">
                    Duration: <?= $dur ?> &nbsp;·&nbsp;
                    <?= date('h:i A', strtotime($today_record['CheckInTime'])) ?> → <?= date('h:i A', strtotime($today_record['CheckOutTime'])) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action buttons -->
        <div class="checkin-actions">
            <?php if (!$today_record): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="checkin">
                    <button type="submit" class="btn-checkin btn-checkin-in">CHECK IN →</button>
                </form>
            <?php elseif ($is_checked_in): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="checkout">
                    <button type="submit" class="btn-checkin btn-checkin-out">CHECK OUT →</button>
                </form>
            <?php else: ?>
                <div style="padding:1rem 1.5rem;background:rgba(74,158,255,0.1);border:1px solid rgba(74,158,255,0.3);border-radius:var(--radius-sm);color:var(--blue);font-size:0.875rem;font-weight:600;">
                    ✓ Session complete for today
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── STATS ── -->
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-value"><?= $present_count ?></div>
        <div class="stat-label">Present This Month</div>
    </div>
    <div class="stat-card accent">
        <div class="stat-value"><?= $absent_count ?></div>
        <div class="stat-label">Absent This Month</div>
    </div>
    <div class="stat-card accent2">
        <div class="stat-value"><?= $total_visits ?></div>
        <div class="stat-label">All-Time Visits</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size:1.4rem;"><?= $total_time ? substr($total_time, 0, 5).'h' : '—' ?></div>
        <div class="stat-label">Hours This Month</div>
    </div>
</div>

<!-- ── HISTORY TABLE ── -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><?= date('F Y', mktime(0,0,0,$month,1,$year)) ?> Records</div>
        <span class="badge badge-gray"><?= $present_count + $absent_count ?> entries</span>
    </div>

    <?php if ($attendance->num_rows > 0): ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Day</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Duration</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php while ($a = $attendance->fetch_assoc()):
                    $dur = '';
                    if ($a['CheckInTime'] && $a['CheckOutTime']) {
                        $diff = strtotime($a['CheckOutTime']) - strtotime($a['CheckInTime']);
                        $dur = floor($diff/3600).'h '.round(($diff%3600)/60).'m';
                    }
                    $is_today = ($a['Date'] === date('Y-m-d'));
                ?>
                <tr <?= $is_today ? 'style="background:rgba(200,245,66,0.04);"' : '' ?>>
                    <td class="text-muted"><?= date('l', strtotime($a['Date'])) ?></td>
                    <td>
                        <?= date('d M Y', strtotime($a['Date'])) ?>
                        <?php if ($is_today): ?><span class="badge badge-green" style="margin-left:0.5rem;">Today</span><?php endif; ?>
                    </td>
                    <td><?= $a['CheckInTime']  ? date('h:i A', strtotime($a['CheckInTime']))  : '—' ?></td>
                    <td><?= $a['CheckOutTime'] ? date('h:i A', strtotime($a['CheckOutTime'])) : ($a['CheckInTime'] ? '<span class="badge badge-orange">In gym</span>' : '—') ?></td>
                    <td><span style="font-family:var(--font-mono);font-size:0.85rem;"><?= $dur ?: '—' ?></span></td>
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
    <div style="text-align:center;padding:3rem;color:var(--muted);">
        <div style="font-size:3rem;margin-bottom:1rem;">📋</div>
        <p>No attendance records for <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?>.</p>
    </div>
    <?php endif; ?>
</div>

<script>
// Live clock
function updateClock() {
    const now = new Date();
    const h = now.getHours();
    const m = now.getMinutes().toString().padStart(2,'0');
    const s = now.getSeconds().toString().padStart(2,'0');
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = ((h % 12) || 12).toString().padStart(2,'0');
    const el = document.getElementById('liveClock');
    if (el) el.textContent = `${h12}:${m} ${ampm}`;
}
setInterval(updateClock, 1000);
updateClock();

<?php if ($is_checked_in): ?>
// Live session timer
const checkInTime = new Date();
checkInTime.setHours(<?= (int)date('H', strtotime($today_record['CheckInTime'])) ?>,
                     <?= (int)date('i', strtotime($today_record['CheckInTime'])) ?>,
                     <?= (int)date('s', strtotime($today_record['CheckInTime'])) ?>, 0);

function updateTimer() {
    const diff = Math.floor((Date.now() - checkInTime.getTime()) / 1000);
    const h = Math.floor(diff / 3600);
    const m = Math.floor((diff % 3600) / 60);
    const s = diff % 60;
    const el = document.getElementById('sessionTimer');
    if (el) el.textContent = `Session: ${h}h ${m.toString().padStart(2,'0')}m ${s.toString().padStart(2,'0')}s`;
}
setInterval(updateTimer, 1000);
updateTimer();
<?php endif; ?>
</script>

<?php require_once 'user_footer.php'; ?>