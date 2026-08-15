<?php
$page_title = "Dashboard";
require_once 'db_connect.php';
require_once 'auth_check.php';

// Get statistics
$total_members = $conn->query("SELECT COUNT(*) as count FROM Member")->fetch_assoc()['count'];
$total_trainers = $conn->query("SELECT COUNT(*) as count FROM Trainer")->fetch_assoc()['count'];
$total_equipment = $conn->query("SELECT COUNT(*) as count FROM Equipment")->fetch_assoc()['count'];
$total_staff = $conn->query("SELECT COUNT(*) as count FROM Staff")->fetch_assoc()['count'];

// Monthly revenue
$monthly_revenue = $conn->query("SELECT SUM(Amount) as total FROM Payment WHERE MONTH(Date) = MONTH(CURRENT_DATE()) AND YEAR(Date) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'] ?? 0;

// Today's attendance
$today_attendance = $conn->query("SELECT COUNT(*) as count FROM Attendance WHERE Date = CURDATE() AND Status = 'Present'")->fetch_assoc()['count'];

// Recent members
$recent_members = $conn->query("SELECT m.*, mt.TypeName FROM Member m LEFT JOIN Membership mt ON m.MembershipTypeID = mt.MembershipTypeID ORDER BY m.JoinDate DESC LIMIT 5");

// Recent payments
$recent_payments = $conn->query("SELECT p.*, m.Name as MemberName FROM Payment p LEFT JOIN Member m ON p.MemberID = m.MemberID ORDER BY p.Date DESC LIMIT 5");

include 'header.php';
include 'sidebar.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Dashboard <small>Control panel</small></h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <section class="content">
        <!-- Info boxes -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Members</span>
                        <span class="info-box-number"><?= $total_members ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-user-plus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Trainers</span>
                        <span class="info-box-number"><?= $total_trainers ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-money"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Monthly Revenue</span>
                        <span class="info-box-number">Rs. <?= number_format($monthly_revenue) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-calendar-check-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Today's Attendance</span>
                        <span class="info-box-number"><?= $today_attendance ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-aqua">
                    <span class="info-box-icon"><i class="fa fa-cogs"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Equipment</span>
                        <span class="info-box-number"><?= $total_equipment ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-user-secret"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Staff</span>
                        <span class="info-box-number"><?= $total_staff ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Members -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-users"></i> Recent Members</h3>
                        <div class="box-tools pull-right">
                            <a href="members_manage.php" class="btn btn-box-tool"><i class="fa fa-arrow-right"></i> View All</a>
                        </div>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-striped">
                            <tr>
                                <th>Name</th>
                                <th>Membership</th>
                                <th>Join Date</th>
                            </tr>
                            <?php while ($member = $recent_members->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($member['Name']) ?></td>
                                <td><span class="label label-info"><?= htmlspecialchars($member['TypeName'] ?? 'N/A') ?></span></td>
                                <td><?= date('d M Y', strtotime($member['JoinDate'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-money"></i> Recent Payments</h3>
                        <div class="box-tools pull-right">
                            <a href="payments_manage.php" class="btn btn-box-tool"><i class="fa fa-arrow-right"></i> View All</a>
                        </div>
                    </div>
                    <div class="box-body no-padding">
                        <table class="table table-striped">
                            <tr>
                                <th>Member</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Method</th>
                            </tr>
                            <?php while ($payment = $recent_payments->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($payment['MemberName']) ?></td>
                                <td>Rs. <?= number_format($payment['Amount']) ?></td>
                                <td><?= date('d M', strtotime($payment['Date'])) ?></td>
                                <td><span class="label label-success"><?= htmlspecialchars($payment['PaymentMethod']) ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div class="box-body">
                        <a href="members_manage.php" class="btn btn-primary btn-lg"><i class="fa fa-user-plus"></i> Add Member</a>
                        <a href="attendance_manage.php" class="btn btn-success btn-lg"><i class="fa fa-calendar-check-o"></i> Mark Attendance</a>
                        <a href="payments_manage.php" class="btn btn-warning btn-lg"><i class="fa fa-money"></i> Record Payment</a>
                        <a href="schedules_manage.php" class="btn btn-info btn-lg"><i class="fa fa-calendar"></i> Schedule Workout</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>