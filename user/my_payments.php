<?php
session_start();
require_once 'user_db.php';
require_once 'user_auth.php';

$page_title = 'My Payments';
$uid = $_SESSION['user_id'];
$success = '';
$error   = '';

// ── SUBMIT PAYMENT REQUEST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $amount  = floatval($_POST['amount']);
    $method  = trim($_POST['payment_method']);
    $ref     = trim($_POST['transaction_ref']);
    $notes   = trim($_POST['notes']);

    if ($amount <= 0) {
        $error = 'Please enter a valid amount.';
    } elseif (empty($method)) {
        $error = 'Please select a payment method.';
    } else {
        // Check if already has a pending request
        $pending = $conn->query("SELECT RequestID FROM payment_requests WHERE MemberID=$uid AND Status='Pending'")->fetch_assoc();
        if ($pending) {
            $error = 'You already have a pending payment request. Please wait for it to be reviewed.';
        } else {
            $stmt = $conn->prepare("INSERT INTO payment_requests (MemberID, Amount, PaymentMethod, TransactionRef, Notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("idsss", $uid, $amount, $method, $ref, $notes);
            if ($stmt->execute()) {
                $success = '✅ Payment request submitted! The admin will review and approve it shortly.';
            } else {
                $error = 'Failed to submit request. Please try again.';
            }
        }
    }
}

// ── DATA ──────────────────────────────────────────────────────────
$payments       = $conn->query("SELECT * FROM Payment WHERE MemberID=$uid ORDER BY Date DESC");
$total_paid     = $conn->query("SELECT SUM(Amount) as t FROM Payment WHERE MemberID=$uid")->fetch_assoc()['t'] ?? 0;
$payment_count  = $conn->query("SELECT COUNT(*) as c FROM Payment WHERE MemberID=$uid")->fetch_assoc()['c'];
$last_payment   = $conn->query("SELECT * FROM Payment WHERE MemberID=$uid ORDER BY Date DESC LIMIT 1")->fetch_assoc();

// My requests
$my_requests    = $conn->query("SELECT * FROM payment_requests WHERE MemberID=$uid ORDER BY RequestedAt DESC LIMIT 10");
$pending_req    = $conn->query("SELECT * FROM payment_requests WHERE MemberID=$uid AND Status='Pending' ORDER BY RequestedAt DESC LIMIT 1")->fetch_assoc();

// Member membership info for suggested amount
$member_plan = $conn->query("SELECT mt.Price, mt.TypeName FROM Member m JOIN Membership mt ON m.MembershipTypeID=mt.MembershipTypeID WHERE m.MemberID=$uid")->fetch_assoc();

require_once 'user_header.php';
?>

<style>
.request-form-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.request-form-header {
    background: linear-gradient(135deg, rgba(200,245,66,0.08) 0%, transparent 60%);
    border-bottom: 1px solid var(--border);
    padding: 1.5rem 1.75rem;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
}
.method-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 0.75rem;
    margin-top: 0.5rem;
}
.method-option {
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: all 0.15s;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 500;
}
.method-option:hover { border-color: var(--lime); color: var(--lime); }
.method-option input { display: none; }
.method-option.selected { border-color: var(--lime); background: rgba(200,245,66,0.08); color: var(--lime); }
.method-icon { font-size: 1.4rem; display: block; margin-bottom: 0.25rem; }
.status-timeline { display: flex; gap: 0; }
.status-step {
    flex: 1; padding: 1rem; text-align: center;
    border-right: 1px solid var(--border); position: relative;
}
.status-step:last-child { border-right: none; }
.status-step-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }
.status-step-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; }
.status-step-sub { font-size: 0.72rem; color: var(--muted); margin-top: 0.2rem; }
.status-step.active .status-step-label { color: var(--lime); }
.status-step.done .status-step-label { color: var(--muted); }
.pending-banner {
    background: rgba(255,140,66,0.08);
    border: 1px solid rgba(255,140,66,0.3);
    border-radius: var(--radius);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
}
</style>

<div class="page-header">
    <h1>PAYMENTS</h1>
    <p>Submit payment requests and view your billing history</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- ── STATS ── -->
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-value">Rs.<?= number_format($total_paid) ?></div>
        <div class="stat-label">Total Paid</div>
    </div>
    <div class="stat-card accent">
        <div class="stat-icon">🧾</div>
        <div class="stat-value"><?= $payment_count ?></div>
        <div class="stat-label">Transactions</div>
    </div>
    <div class="stat-card accent2">
        <div class="stat-icon">📅</div>
        <div class="stat-value"><?= $last_payment ? date('d M', strtotime($last_payment['Date'])) : '—' ?></div>
        <div class="stat-label">Last Payment</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-value"><?= $pending_req ? '1' : '0' ?></div>
        <div class="stat-label">Pending Request</div>
    </div>
</div>

<!-- ── PENDING REQUEST BANNER ── -->
<?php if ($pending_req): ?>
<div class="pending-banner">
    <div style="font-size:2rem;">⏳</div>
    <div style="flex:1;">
        <div style="font-weight:700;margin-bottom:0.25rem;">Payment Request Under Review</div>
        <div class="text-sm text-muted">
            Rs. <?= number_format($pending_req['Amount'], 2) ?> via <?= htmlspecialchars($pending_req['PaymentMethod']) ?>
            · Submitted <?= date('d M Y, h:i A', strtotime($pending_req['RequestedAt'])) ?>
        </div>
    </div>
    <span class="badge badge-orange">Pending Approval</span>
</div>
<?php endif; ?>

<!-- ── SUBMIT REQUEST FORM ── -->
<?php if (!$pending_req): ?>
<div class="request-form-card">
    <div class="request-form-header">
        <div>
            <div style="font-size:1rem;font-weight:700;margin-bottom:0.25rem;">Submit a Payment Request</div>
            <div class="text-sm text-muted">Pay via your preferred method, then submit the details here for admin approval.</div>
        </div>
        <?php if ($member_plan): ?>
        <div style="text-align:right;">
            <div class="text-xs text-muted">Your plan fee</div>
            <div style="font-family:var(--font-display);font-size:1.6rem;color:var(--lime);">Rs. <?= number_format($member_plan['Price']) ?></div>
            <div class="text-xs text-muted"><?= htmlspecialchars($member_plan['TypeName']) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <form method="POST" style="padding:1.75rem;">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Amount (Rs.) *</label>
                <input type="number" name="amount" class="form-control"
                    placeholder="Enter amount"
                    value="<?= $member_plan ? intval($member_plan['Price']) : '' ?>"
                    min="1" step="0.01" required>
            </div>
            <div class="form-group">
                <label class="form-label">Transaction Reference / Receipt No.</label>
                <input type="text" name="transaction_ref" class="form-control"
                    placeholder="e.g. TXN123456 or slip no.">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Payment Method *</label>
            <div class="method-grid" id="methodGrid">
                <?php
                $methods = [
                    ['Cash',          '💵'],
                    ['JazzCash',      '📱'],
                    ['EasyPaisa',     '📲'],
                    ['Bank Transfer', '🏦'],
                    ['Credit Card',   '💳'],
                    ['Debit Card',    '💳'],
                ];
                foreach ($methods as [$name, $icon]):
                ?>
                <label class="method-option" onclick="selectMethod(this, '<?= $name ?>')">
                    <input type="radio" name="payment_method" value="<?= $name ?>">
                    <span class="method-icon"><?= $icon ?></span>
                    <?= $name ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Notes (optional)</label>
            <textarea name="notes" class="form-control" rows="2"
                placeholder="e.g. Paid via JazzCash to 0300-XXXXXXX for Premium membership renewal..."></textarea>
        </div>

        <div class="alert alert-info" style="margin-bottom:1.25rem;">
            📋 After submitting, the admin will verify your payment and approve it. Your payment history will update once approved.
        </div>

        <button type="submit" name="submit_request" class="btn btn-primary btn-lg">Submit Payment Request →</button>
    </form>
</div>
<?php endif; ?>

<!-- ── MY REQUESTS HISTORY ── -->
<?php
$conn->query("SELECT COUNT(*) as c FROM payment_requests WHERE MemberID=$uid")->fetch_assoc();
$all_requests = $conn->query("SELECT * FROM payment_requests WHERE MemberID=$uid ORDER BY RequestedAt DESC");
if ($all_requests->num_rows > 0):
?>
<div class="card mb-3">
    <div class="card-header">
        <div class="card-title">Payment Requests</div>
        <span class="badge badge-gray"><?= $all_requests->num_rows ?> total</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Submitted</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php $all_requests->data_seek(0); while ($r = $all_requests->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted text-sm"><?= date('d M Y, h:i A', strtotime($r['RequestedAt'])) ?></td>
                    <td><strong>Rs. <?= number_format($r['Amount'], 2) ?></strong></td>
                    <td><?= htmlspecialchars($r['PaymentMethod']) ?></td>
                    <td style="font-family:var(--font-mono);font-size:0.8rem;"><?= htmlspecialchars($r['TransactionRef'] ?: '—') ?></td>
                    <td>
                        <?php if ($r['Status'] === 'Pending'): ?>
                            <span class="badge badge-orange">⏳ Pending</span>
                        <?php elseif ($r['Status'] === 'Approved'): ?>
                            <span class="badge badge-green">✓ Approved</span>
                        <?php else: ?>
                            <span class="badge badge-red">✗ Rejected</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ── CONFIRMED PAYMENTS ── -->
<div class="card">
    <div class="card-header">
        <div class="card-title">Confirmed Payment History</div>
    </div>
    <?php if ($payments->num_rows > 0): ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>ID</th><th>Date</th><th>Amount</th><th>Method</th></tr>
            </thead>
            <tbody>
                <?php while ($p = $payments->fetch_assoc()): ?>
                <tr>
                    <td class="text-muted" style="font-family:var(--font-mono);font-size:0.8rem;">#<?= $p['PaymentID'] ?></td>
                    <td><?= date('d M Y', strtotime($p['Date'])) ?></td>
                    <td><strong style="color:var(--lime);">Rs. <?= number_format($p['Amount'], 2) ?></strong></td>
                    <td>
                        <?php
                        $bc = 'badge-gray';
                        if (str_contains($p['PaymentMethod'], 'Card')) $bc = 'badge-blue';
                        elseif ($p['PaymentMethod'] === 'Cash') $bc = 'badge-green';
                        elseif (in_array($p['PaymentMethod'], ['JazzCash','EasyPaisa'])) $bc = 'badge-orange';
                        ?>
                        <span class="badge <?= $bc ?>"><?= htmlspecialchars($p['PaymentMethod']) ?></span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:3rem;color:var(--muted);">
        <div style="font-size:3rem;margin-bottom:1rem;">💳</div>
        <p>No confirmed payments yet.</p>
    </div>
    <?php endif; ?>
</div>

<script>
function selectMethod(el, name) {
    document.querySelectorAll('.method-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
}
</script>

<?php require_once 'user_footer.php'; ?>