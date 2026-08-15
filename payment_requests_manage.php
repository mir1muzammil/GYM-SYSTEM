<?php
$page_title = "Payment Requests";
require_once 'db_connect.php';
require_once 'auth_check.php';

// Handle approve / reject
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id     = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'approve') {
        // Fetch request details
        $req = $conn->query("SELECT * FROM payment_requests WHERE RequestID=$id AND Status='Pending'")->fetch_assoc();
        if ($req) {
            // Insert into Payment table
            $date = date('Y-m-d');
            $stmt = $conn->prepare("INSERT INTO Payment (MemberID, Amount, Date, PaymentMethod) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $req['MemberID'], $req['Amount'], $date, $req['PaymentMethod']);
            $stmt->execute();

            // Update request status
            $admin_id = $_SESSION['admin_id'];
            $now = date('Y-m-d H:i:s');
            $conn->query("UPDATE payment_requests SET Status='Approved', ReviewedAt='$now', ReviewedBy=$admin_id WHERE RequestID=$id");
            header("Location: payment_requests_manage.php?msg=approved");
        }
    } elseif ($action === 'reject') {
        $admin_id = $_SESSION['admin_id'];
        $now = date('Y-m-d H:i:s');
        $conn->query("UPDATE payment_requests SET Status='Rejected', ReviewedAt='$now', ReviewedBy=$admin_id WHERE RequestID=$id");
        header("Location: payment_requests_manage.php?msg=rejected");
    }
    exit();
}

include 'header.php';
include 'sidebar.php';

$pending  = $conn->query("SELECT pr.*, m.Name as MemberName, m.Email FROM payment_requests pr JOIN member m ON pr.MemberID=m.MemberID WHERE pr.Status='Pending' ORDER BY pr.RequestedAt ASC");
$reviewed = $conn->query("SELECT pr.*, m.Name as MemberName FROM payment_requests pr JOIN member m ON pr.MemberID=m.MemberID WHERE pr.Status!='Pending' ORDER BY pr.ReviewedAt DESC LIMIT 20");
$pending_count = $pending->num_rows;
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Payment Requests <small>Review member payment submissions</small></h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Payment Requests</li>
        </ol>
    </section>

    <section class="content">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check"></i>
                <?= $_GET['msg'] === 'approved' ? 'Payment approved and recorded successfully!' : 'Payment request rejected.' ?>
            </div>
        <?php endif; ?>

        <!-- Pending -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-clock-o"></i> Pending Requests
                            <?php if ($pending_count > 0): ?>
                                <span class="label label-warning" style="margin-left:8px;"><?= $pending_count ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="box-body">
                        <?php if ($pending_count === 0): ?>
                            <p class="text-muted" style="padding:1rem;">No pending payment requests. All caught up! ✓</p>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th><th>Member</th><th>Amount</th><th>Method</th>
                                        <th>Reference</th><th>Notes</th><th>Submitted</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($r = $pending->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $r['RequestID'] ?></td>
                                        <td><strong><?= htmlspecialchars($r['MemberName']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($r['Email']) ?></small></td>
                                        <td><strong>Rs. <?= number_format($r['Amount'], 2) ?></strong></td>
                                        <td><span class="label label-info"><?= htmlspecialchars($r['PaymentMethod']) ?></span></td>
                                        <td style="font-family:monospace;"><?= htmlspecialchars($r['TransactionRef'] ?: '—') ?></td>
                                        <td style="max-width:200px;font-size:0.85rem;"><?= htmlspecialchars($r['Notes'] ?: '—') ?></td>
                                        <td><?= date('d M Y, h:i A', strtotime($r['RequestedAt'])) ?></td>
                                        <td class="text-nowrap">
                                            <a href="payment_requests_manage.php?action=approve&id=<?= $r['RequestID'] ?>"
                                               onclick="return confirm('Approve this payment of Rs.<?= number_format($r['Amount'],2) ?> from <?= addslashes($r['MemberName']) ?>?')"
                                               class="btn btn-success btn-xs"><i class="fa fa-check"></i> Approve</a>
                                            <a href="payment_requests_manage.php?action=reject&id=<?= $r['RequestID'] ?>"
                                               onclick="return confirm('Reject this request?')"
                                               class="btn btn-danger btn-xs"><i class="fa fa-times"></i> Reject</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recently reviewed -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-history"></i> Recently Reviewed</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover table-bordered">
                                <thead>
                                    <tr><th>ID</th><th>Member</th><th>Amount</th><th>Method</th><th>Submitted</th><th>Reviewed</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php while ($r = $reviewed->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $r['RequestID'] ?></td>
                                        <td><?= htmlspecialchars($r['MemberName']) ?></td>
                                        <td>Rs. <?= number_format($r['Amount'], 2) ?></td>
                                        <td><?= htmlspecialchars($r['PaymentMethod']) ?></td>
                                        <td><?= date('d M Y', strtotime($r['RequestedAt'])) ?></td>
                                        <td><?= $r['ReviewedAt'] ? date('d M Y', strtotime($r['ReviewedAt'])) : '—' ?></td>
                                        <td>
                                            <?php if ($r['Status'] === 'Approved'): ?>
                                                <span class="label label-success">Approved</span>
                                            <?php else: ?>
                                                <span class="label label-danger">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>
<script>$(document).ready(function(){ $('#dataTable').DataTable(); });</script>