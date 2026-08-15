<?php
$page_title = "Exercises Management";
require_once 'db_connect.php';
require_once 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    if ($_POST['action'] == 'add') {
        $name = trim($_POST['exercise_name']);
        $muscle = trim($_POST['muscle_group']);
        $equipment = isset($_POST['equipment_needed']) ? 1 : 0;
        $reps = intval($_POST['repetitions']);
        $sets = intval($_POST['sets']);
        
        if (empty($name)) {
            $response['message'] = 'Exercise name is required';
            echo json_encode($response); exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO Exercise (ExerciseName, MuscleGroup, EquipmentNeeded, Repetitions, Sets) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiii", $name, $muscle, $equipment, $reps, $sets);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Exercise added successfully';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $stmt->close();
    }
    
    if ($_POST['action'] == 'edit') {
        $id = intval($_POST['exercise_id']);
        $name = trim($_POST['exercise_name']);
        $muscle = trim($_POST['muscle_group']);
        $equipment = isset($_POST['equipment_needed']) ? 1 : 0;
        $reps = intval($_POST['repetitions']);
        $sets = intval($_POST['sets']);
        
        $stmt = $conn->prepare("UPDATE Exercise SET ExerciseName=?, MuscleGroup=?, EquipmentNeeded=?, Repetitions=?, Sets=? WHERE ExerciseID=?");
        $stmt->bind_param("ssiiii", $name, $muscle, $equipment, $reps, $sets, $id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Exercise updated successfully';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $stmt->close();
    }
    
    echo json_encode($response); exit;
}

if (isset($_GET['get_exercise'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['get_exercise']);
    $stmt = $conn->prepare("SELECT * FROM Exercise WHERE ExerciseID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
    $stmt->close(); exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Exercise WHERE ExerciseID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: exercises_manage.php?msg=deleted");
    $stmt->close(); exit;
}

include 'header.php';
include 'sidebar.php';

$exercises = $conn->query("SELECT * FROM Exercise ORDER BY ExerciseName ASC");
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
.form-group.checkbox-group{display:flex;align-items:center;gap:10px}
.form-group.checkbox-group input{width:auto}
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
        <h1>Exercises <small>Manage exercise library</small></h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Exercises</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">All Exercises</h3>
                        <div class="box-tools">
                            <button onclick="openAddModal()" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i> Add Exercise
                            </button>
                        </div>
                    </div>

                    <div class="box-body">
                        <?php if (isset($_GET['msg'])): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="fa fa-check"></i> Exercise deleted successfully!
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table id="dataTable" class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Exercise Name</th>
                                        <th>Muscle Group</th>
                                        <th>Equipment</th>
                                        <th>Reps</th>
                                        <th>Sets</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $exercises->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['ExerciseID'] ?></td>
                                        <td><strong><?= htmlspecialchars($row['ExerciseName']) ?></strong></td>
                                        <td><span class="label label-warning"><?= htmlspecialchars($row['MuscleGroup']) ?></span></td>
                                        <td><?= $row['EquipmentNeeded'] ? '<span class="label label-info">Required</span>' : '<span class="label label-default">None</span>' ?></td>
                                        <td><?= $row['Repetitions'] ?></td>
                                        <td><?= $row['Sets'] ?></td>
                                        <td class="text-nowrap">
                                            <button onclick="openEditModal(<?= $row['ExerciseID'] ?>)" class="btn btn-warning btn-xs">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <a href="exercises_manage.php?delete=<?= $row['ExerciseID'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-xs">
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
            <h2>Add Exercise</h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form id="addForm" onsubmit="return false;">
            <div class="modal-body">
                <div id="addError" class="alert-modal alert-danger" style="display:none;"></div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Exercise Name *</label>
                        <input type="text" name="exercise_name" required>
                    </div>
                    <div class="form-group">
                        <label>Muscle Group</label>
                        <select name="muscle_group">
                            <option value="">Select</option>
                            <option value="Chest">Chest</option>
                            <option value="Back">Back</option>
                            <option value="Legs">Legs</option>
                            <option value="Arms">Arms</option>
                            <option value="Shoulders">Shoulders</option>
                            <option value="Core">Core</option>
                            <option value="Full Body">Full Body</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Repetitions</label>
                        <input type="number" name="repetitions" min="1" max="100">
                    </div>
                    <div class="form-group">
                        <label>Sets</label>
                        <input type="number" name="sets" min="1" max="10">
                    </div>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="equipment_needed" id="add_equipment">
                    <label for="add_equipment" style="margin-bottom:0">Equipment Needed</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-success">Add Exercise</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h2>Edit Exercise</h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" onsubmit="return false;">
            <input type="hidden" id="edit_id" name="exercise_id">
            <div class="modal-body">
                <div id="editError" class="alert-modal alert-danger" style="display:none;"></div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Exercise Name *</label>
                        <input type="text" id="edit_name" name="exercise_name" required>
                    </div>
                    <div class="form-group">
                        <label>Muscle Group</label>
                        <select id="edit_muscle" name="muscle_group">
                            <option value="">Select</option>
                            <option value="Chest">Chest</option>
                            <option value="Back">Back</option>
                            <option value="Legs">Legs</option>
                            <option value="Arms">Arms</option>
                            <option value="Shoulders">Shoulders</option>
                            <option value="Core">Core</option>
                            <option value="Full Body">Full Body</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Repetitions</label>
                        <input type="number" id="edit_reps" name="repetitions" min="1" max="100">
                    </div>
                    <div class="form-group">
                        <label>Sets</label>
                        <input type="number" id="edit_sets" name="sets" min="1" max="10">
                    </div>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="equipment_needed" id="edit_equipment">
                    <label for="edit_equipment" style="margin-bottom:0">Equipment Needed</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-primary">Update Exercise</button>
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
    fetch('exercises_manage.php?get_exercise=' + id)
        .then(r => r.json())
        .then(d => {
            document.getElementById('edit_id').value = d.ExerciseID;
            document.getElementById('edit_name').value = d.ExerciseName;
            document.getElementById('edit_muscle').value = d.MuscleGroup || '';
            document.getElementById('edit_reps').value = d.Repetitions || '';
            document.getElementById('edit_sets').value = d.Sets || '';
            document.getElementById('edit_equipment').checked = d.EquipmentNeeded == 1;
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
    
    fetch('exercises_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { window.location.href = 'exercises_manage.php'; }
            else { document.getElementById('addError').innerHTML = d.message; document.getElementById('addError').style.display = 'block'; }
        });
});

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('ajax', '1');
    fd.append('action', 'edit');
    
    fetch('exercises_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { window.location.href = 'exercises_manage.php'; }
            else { document.getElementById('editError').innerHTML = d.message; document.getElementById('editError').style.display = 'block'; }
        });
});
</script>