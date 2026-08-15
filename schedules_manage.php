<?php
$page_title = "Workout Schedules Management";
require_once 'db_connect.php';
require_once 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    if ($_POST['action'] == 'add') {
        $member_id = intval($_POST['member_id']);
        $plan_id = intval($_POST['plan_id']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        
        if (empty($member_id) || empty($plan_id)) {
            $response['message'] = 'Member and Plan are required';
            echo json_encode($response); exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO WorkoutSchedule (MemberID, PlanID, StartDate, EndDate) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $member_id, $plan_id, $start_date, $end_date);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Schedule added successfully';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $stmt->close();
    }
    
    if ($_POST['action'] == 'edit') {
        $id = intval($_POST['schedule_id']);
        $member_id = intval($_POST['member_id']);
        $plan_id = intval($_POST['plan_id']);
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        
        $stmt = $conn->prepare("UPDATE WorkoutSchedule SET MemberID=?, PlanID=?, StartDate=?, EndDate=? WHERE ScheduleID=?");
        $stmt->bind_param("iissi", $member_id, $plan_id, $start_date, $end_date, $id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Schedule updated successfully';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $stmt->close();
    }
    
    echo json_encode($response); exit;
}

if (isset($_GET['get_schedule'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['get_schedule']);
    $stmt = $conn->prepare("SELECT * FROM WorkoutSchedule WHERE ScheduleID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
    $stmt->close(); exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM WorkoutSchedule WHERE ScheduleID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: schedules_manage.php?msg=deleted");
    $stmt->close(); exit;
}

include 'header.php';
include 'sidebar.php';

$schedules = $conn->query("SELECT ws.*, m.Name as MemberName, wp.PlanName FROM WorkoutSchedule ws 
    LEFT JOIN Member m ON ws.MemberID = m.MemberID 
    LEFT JOIN WorkoutPlan wp ON ws.PlanID = wp.PlanID 
    ORDER BY ws.StartDate DESC");
$members = $conn->query("SELECT * FROM Member ORDER BY Name");
$plans = $conn->query("SELECT * FROM WorkoutPlan ORDER BY PlanName");
?>

<style>
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);backdrop-filter:blur(5px);z-index:9999;overflow-y:auto}
.modal-overlay.active{display:flex;align-items:center;justify-content:center;padding:20px}
.modal-container{background:white;max-width:600px;width:100%;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,0.3);animation:slideDown 0.3s ease-out}
@keyframes slideDown{from{transform:translateY(-50px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-header{padding:20px 30px;border-bottom:1px solid #e5e5e5;display:flex;justify-content:space-between;align-items:center}
.modal-header h2{margin:0;color:#333;font-size:24px}
.modal-close{background:none;border:none;font-size:28px;color:#999;cursor:pointer}
.modal-body{padding:30px;max-height:70vh;overflow-y:auto}
.form-group{margin-bottom:20px}
.form-group label{display:block;margin-bottom:8px;color:#333;font-weight:500}
.form-group input,.form-group select{width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-size:14px}
.modal-footer{padding:20px 30px;border-top:1px solid #e5e5e5;display:flex;gap:10px;justify-content:flex-end}
.btn-modal{padding:10px 20px;border:none;border-radius:4px;cursor:pointer;font-size:14px}
.btn-modal-primary{background-color:#3c8dbc;color:white}
.btn-modal-success{background-color:#00a65a;color:white}
.btn-modal-secondary{background-color:#A9A9A9;color:white}
.alert-modal{padding:12px;border-radius:4px;margin-bottom:20px}
.alert-modal.alert-danger{background-color:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
.form-row{display:flex;gap:20px}
.form-row .form-group{flex:1}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Workout Schedules <small>Assign workout plans to members</small></h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Workout Schedules</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">All Schedules</h3>
                        <div class="box-tools">
                            <button onclick="openAddModal()" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i> Add Schedule
                            </button>
                        </div>
                    </div>

                    <div class="box-body">
                        <?php if (isset($_GET['msg'])): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="fa fa-check"></i> Schedule deleted successfully!
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Member</th>
                                        <th>Workout Plan</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $schedules->fetch_assoc()): 
                                        $today = date('Y-m-d');
                                        $status = 'Upcoming';
                                        $statusClass = 'label-info';
                                        if ($row['StartDate'] <= $today && $row['EndDate'] >= $today) {
                                            $status = 'Active';
                                            $statusClass = 'label-success';
                                        } elseif ($row['EndDate'] < $today) {
                                            $status = 'Completed';
                                            $statusClass = 'label-default';
                                        }
                                    ?>
                                    <tr>
                                        <td><?= $row['ScheduleID'] ?></td>
                                        <td><?= htmlspecialchars($row['MemberName']) ?></td>
                                        <td><span class="label label-primary"><?= htmlspecialchars($row['PlanName']) ?></span></td>
                                        <td><?= date('d M Y', strtotime($row['StartDate'])) ?></td>
                                        <td><?= date('d M Y', strtotime($row['EndDate'])) ?></td>
                                        <td><span class="label <?= $statusClass ?>"><?= $status ?></span></td>
                                        <td class="text-nowrap">
                                            <button onclick="openEditModal(<?= $row['ScheduleID'] ?>)" class="btn btn-warning btn-xs">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <a href="schedules_manage.php?delete=<?= $row['ScheduleID'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-xs">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
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

<!-- Add Modal -->
<div id="addModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h2>Add Workout Schedule</h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form id="addForm" onsubmit="return false;">
            <div class="modal-body">
                <div id="addError" class="alert-modal alert-danger" style="display:none;"></div>
                <div class="form-group">
                    <label>Member *</label>
                    <select name="member_id" required>
                        <option value="">Select Member</option>
                        <?php $members->data_seek(0); while ($m = $members->fetch_assoc()): ?>
                        <option value="<?= $m['MemberID'] ?>"><?= htmlspecialchars($m['Name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Workout Plan *</label>
                    <select name="plan_id" required>
                        <option value="">Select Plan</option>
                        <?php $plans->data_seek(0); while ($p = $plans->fetch_assoc()): ?>
                        <option value="<?= $p['PlanID'] ?>"><?= htmlspecialchars($p['PlanName']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-success">Add Schedule</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h2>Edit Workout Schedule</h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" onsubmit="return false;">
            <input type="hidden" id="edit_id" name="schedule_id">
            <div class="modal-body">
                <div id="editError" class="alert-modal alert-danger" style="display:none;"></div>
                <div class="form-group">
                    <label>Member *</label>
                    <select id="edit_member" name="member_id" required>
                        <option value="">Select Member</option>
                        <?php $members->data_seek(0); while ($m = $members->fetch_assoc()): ?>
                        <option value="<?= $m['MemberID'] ?>"><?= htmlspecialchars($m['Name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Workout Plan *</label>
                    <select id="edit_plan" name="plan_id" required>
                        <option value="">Select Plan</option>
                        <?php $plans->data_seek(0); while ($p = $plans->fetch_assoc()): ?>
                        <option value="<?= $p['PlanID'] ?>"><?= htmlspecialchars($p['PlanName']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" id="edit_start" name="start_date">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" id="edit_end" name="end_date">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-primary">Update Schedule</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
$(document).ready(function() { $('#dataTable').DataTable(); });

function openAddModal() {
    document.getElementById('addModal').classList.add('active');
    document.getElementById('addForm').reset();
    document.getElementById('addError').style.display = 'none';
}

function openEditModal(id) {
    fetch('schedules_manage.php?get_schedule=' + id)
        .then(r => r.json())
        .then(d => {
            document.getElementById('edit_id').value = d.ScheduleID;
            document.getElementById('edit_member').value = d.MemberID;
            document.getElementById('edit_plan').value = d.PlanID;
            document.getElementById('edit_start').value = d.StartDate;
            document.getElementById('edit_end').value = d.EndDate;
            document.getElementById('editError').style.display = 'none';
            document.getElementById('editModal').classList.add('active');
        });
}

function closeModal(id) { document.getElementById(id).classList.remove('active'); }

document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
}));

document.getElementById('addForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('ajax', '1');
    fd.append('action', 'add');
    
    fetch('schedules_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { window.location.href = 'schedules_manage.php'; }
            else { document.getElementById('addError').innerHTML = d.message; document.getElementById('addError').style.display = 'block'; }
        });
});

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('ajax', '1');
    fd.append('action', 'edit');
    
    fetch('schedules_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { window.location.href = 'schedules_manage.php'; }
            else { document.getElementById('editError').innerHTML = d.message; document.getElementById('editError').style.display = 'block'; }
        });
});
</script>