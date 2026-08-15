<?php
$page_title = "Workout Plans Management";
require_once 'db_connect.php';
require_once 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    if ($_POST['action'] == 'add') {
        $plan_name = trim($_POST['plan_name']);
        $description = trim($_POST['description']);
        $duration = intval($_POST['duration']);
        $trainer_id = !empty($_POST['trainer_id']) ? intval($_POST['trainer_id']) : null;
        
        if (empty($plan_name)) {
            $response['message'] = 'Plan name is required';
            echo json_encode($response); exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO WorkoutPlan (PlanName, Description, DurationWeeks, TrainerID) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $plan_name, $description, $duration, $trainer_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Workout plan added successfully';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $stmt->close();
    }
    
    if ($_POST['action'] == 'edit') {
        $id = intval($_POST['plan_id']);
        $plan_name = trim($_POST['plan_name']);
        $description = trim($_POST['description']);
        $duration = intval($_POST['duration']);
        $trainer_id = !empty($_POST['trainer_id']) ? intval($_POST['trainer_id']) : null;
        
        $stmt = $conn->prepare("UPDATE WorkoutPlan SET PlanName=?, Description=?, DurationWeeks=?, TrainerID=? WHERE PlanID=?");
        $stmt->bind_param("ssiii", $plan_name, $description, $duration, $trainer_id, $id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Workout plan updated successfully';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $stmt->close();
    }
    
    echo json_encode($response); exit;
}

if (isset($_GET['get_plan'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['get_plan']);
    $stmt = $conn->prepare("SELECT * FROM WorkoutPlan WHERE PlanID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
    $stmt->close(); exit;
}

// START OF FIX: Implement try-catch for graceful Foreign Key error handling
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $msg_type = 'deleted';

    try {
        $stmt = $conn->prepare("DELETE FROM WorkoutPlan WHERE PlanID = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
             $msg_type = 'error';
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        // MySQL Error Code 1451 is the Foreign Key Constraint failure
        if ($e->getCode() == 1451) {
            $msg_type = 'fk_fail';
        } else {
            // Handle other SQL errors 
            $msg_type = 'error'; 
        }
    }
    
    header("Location: workout_plans_manage.php?msg=" . $msg_type);
    exit;
}
// END OF FIX

include 'header.php';
include 'sidebar.php';

$plans = $conn->query("SELECT wp.*, t.Name as TrainerName FROM WorkoutPlan wp LEFT JOIN Trainer t ON wp.TrainerID = t.TrainerID ORDER BY wp.PlanName ASC");
$trainers = $conn->query("SELECT * FROM Trainer ORDER BY Name");
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
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-size:14px}
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
        <h1>Workout Plans <small>Manage training programs</small></h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Workout Plans</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">All Workout Plans</h3>
                        <div class="box-tools">
                            <button onclick="openAddModal()" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i> Add Workout Plan
                            </button>
                        </div>
                    </div>

                    <div class="box-body">
                        <?php if (isset($_GET['msg'])): ?>
                            <?php if ($_GET['msg'] === 'deleted'): ?>
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="fa fa-check"></i> Workout plan deleted successfully!
                                </div>
                            <?php elseif ($_GET['msg'] === 'fk_fail'): // START OF FIX: Display Foreign Key Error ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="fa fa-warning"></i> **Deletion Failed**: This workout plan is currently **referenced in a workout schedule** and cannot be deleted. Please delete the associated schedule entries first.
                                </div>
                            <?php elseif ($_GET['msg'] === 'error'): // Generic Error Handle ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="fa fa-warning"></i> An unknown error occurred during the delete operation.
                                </div>
                            <?php endif; // END OF FIX ?>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Plan Name</th>
                                        <th>Description</th>
                                        <th>Duration</th>
                                        <th>Trainer</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $plans->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['PlanID'] ?></td>
                                        <td><strong><?= htmlspecialchars($row['PlanName']) ?></strong></td>
                                        <td><?= htmlspecialchars(substr($row['Description'] ?? '', 0, 50)) ?>...</td>
                                        <td><?= $row['DurationWeeks'] ?> weeks</td>
                                        <td><span class="label label-info"><?= htmlspecialchars($row['TrainerName'] ?? 'N/A') ?></span></td>
                                        <td class="text-nowrap">
                                            <button onclick="openEditModal(<?= $row['PlanID'] ?>)" class="btn btn-warning btn-xs">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <a href="workout_plans_manage.php?delete=<?= $row['PlanID'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-xs">
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

<div id="addModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h2>Add Workout Plan</h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form id="addForm" onsubmit="return false;">
            <div class="modal-body">
                <div id="addError" class="alert-modal alert-danger" style="display:none;"></div>
                <div class="form-group">
                    <label>Plan Name *</label>
                    <input type="text" name="plan_name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Duration (Weeks)</label>
                        <input type="number" name="duration" min="1" max="52">
                    </div>
                    <div class="form-group">
                        <label>Trainer</label>
                        <select name="trainer_id">
                            <option value="">Select Trainer</option>
                            <?php $trainers->data_seek(0); while ($t = $trainers->fetch_assoc()): ?>
                            <option value="<?= $t['TrainerID'] ?>"><?= htmlspecialchars($t['Name']) ?> - <?= htmlspecialchars($t['Specialization']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-success">Add Plan</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h2>Edit Workout Plan</h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" onsubmit="return false;">
            <input type="hidden" id="edit_id" name="plan_id">
            <div class="modal-body">
                <div id="editError" class="alert-modal alert-danger" style="display:none;"></div>
                <div class="form-group">
                    <label>Plan Name *</label>
                    <input type="text" id="edit_plan_name" name="plan_name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="edit_description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Duration (Weeks)</label>
                        <input type="number" id="edit_duration" name="duration" min="1" max="52">
                    </div>
                    <div class="form-group">
                        <label>Trainer</label>
                        <select id="edit_trainer" name="trainer_id">
                            <option value="">Select Trainer</option>
                            <?php $trainers->data_seek(0); while ($t = $trainers->fetch_assoc()): ?>
                            <option value="<?= $t['TrainerID'] ?>"><?= htmlspecialchars($t['Name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-primary">Update Plan</button>
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
    fetch('workout_plans_manage.php?get_plan=' + id)
        .then(r => r.json())
        .then(d => {
            document.getElementById('edit_id').value = d.PlanID;
            document.getElementById('edit_plan_name').value = d.PlanName;
            document.getElementById('edit_description').value = d.Description || '';
            document.getElementById('edit_duration').value = d.DurationWeeks || '';
            document.getElementById('edit_trainer').value = d.TrainerID || '';
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
    
    fetch('workout_plans_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { window.location.href = 'workout_plans_manage.php'; }
            else { document.getElementById('addError').innerHTML = d.message; document.getElementById('addError').style.display = 'block'; }
        });
});

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('ajax', '1');
    fd.append('action', 'edit');
    
    fetch('workout_plans_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { window.location.href = 'workout_plans_manage.php'; }
            else { document.getElementById('editError').innerHTML = d.message; document.getElementById('editError').style.display = 'block'; }
        });
});
</script>