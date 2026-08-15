<?php
$page_title = "Membership Types Management";
require_once 'db_connect.php';
require_once 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    if ($_POST['action'] == 'add') {
        $type_name = trim($_POST['type_name']);
        $duration = intval($_POST['duration']);
        $price = floatval($_POST['price']);
        
        if (empty($type_name)) {
            $response['message'] = 'Type name is required';
            echo json_encode($response); exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO Membership (TypeName, DurationMonths, Price) VALUES (?, ?, ?)");
        $stmt->bind_param("sid", $type_name, $duration, $price);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Membership type added successfully';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $stmt->close();
    }
    
    if ($_POST['action'] == 'edit') {
        $id = intval($_POST['membership_id']);
        $type_name = trim($_POST['type_name']);
        $duration = intval($_POST['duration']);
        $price = floatval($_POST['price']);
        
        $stmt = $conn->prepare("UPDATE Membership SET TypeName=?, DurationMonths=?, Price=? WHERE MembershipTypeID=?");
        $stmt->bind_param("sidi", $type_name, $duration, $price, $id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Membership type updated successfully';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $stmt->close();
    }
    
    echo json_encode($response); exit;
}

if (isset($_GET['get_membership'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['get_membership']);
    $stmt = $conn->prepare("SELECT * FROM Membership WHERE MembershipTypeID = ?");
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
        $stmt = $conn->prepare("DELETE FROM Membership WHERE MembershipTypeID = ?");
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
    
    header("Location: membership_manage.php?msg=" . $msg_type);
    exit;
}
// END OF FIX

include 'header.php';
include 'sidebar.php';

$memberships = $conn->query("SELECT * FROM Membership ORDER BY Price ASC");
?>

<style>
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);backdrop-filter:blur(5px);z-index:9999;overflow-y:auto}
.modal-overlay.active{display:flex;align-items:center;justify-content:center;padding:20px}
.modal-container{background:white;max-width:500px;width:100%;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,0.3);animation:slideDown 0.3s ease-out}
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
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Membership Types <small>Manage membership plans</small></h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Membership Types</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">All Membership Types</h3>
                        <div class="box-tools">
                            <button onclick="openAddModal()" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i> Add Membership Type
                            </button>
                        </div>
                    </div>

                    <div class="box-body">
                        <?php if (isset($_GET['msg'])): ?>
                            <?php if ($_GET['msg'] === 'deleted'): ?>
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="fa fa-check"></i> Membership type deleted successfully!
                                </div>
                            <?php elseif ($_GET['msg'] === 'fk_fail'): // START OF FIX: Display Foreign Key Error ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="fa fa-warning"></i> **Deletion Failed**: This membership type is currently being used by one or more **members** and cannot be deleted. Please update or delete the associated members first.
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
                                        <th>Type Name</th>
                                        <th>Duration (Months)</th>
                                        <th>Price (Rs.)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $memberships->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['MembershipTypeID'] ?></td>
                                        <td><span class="label label-primary"><?= htmlspecialchars($row['TypeName']) ?></span></td>
                                        <td><?= $row['DurationMonths'] ?> months</td>
                                        <td>Rs. <?= number_format($row['Price'], 2) ?></td>
                                        <td class="text-nowrap">
                                            <button onclick="openEditModal(<?= $row['MembershipTypeID'] ?>)" class="btn btn-warning btn-xs">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <a href="membership_manage.php?delete=<?= $row['MembershipTypeID'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-xs">
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
            <h2>Add Membership Type</h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form id="addForm" onsubmit="return false;">
            <div class="modal-body">
                <div id="addError" class="alert-modal alert-danger" style="display:none;"></div>
                <div class="form-group">
                    <label>Type Name *</label>
                    <input type="text" name="type_name" required>
                </div>
                <div class="form-group">
                    <label>Duration (Months) *</label>
                    <input type="number" name="duration" min="1" max="24" required>
                </div>
                <div class="form-group">
                    <label>Price (Rs.) *</label>
                    <input type="number" name="price" step="0.01" min="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-success">Add Membership</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h2>Edit Membership Type</h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" onsubmit="return false;">
            <input type="hidden" id="edit_id" name="membership_id">
            <div class="modal-body">
                <div id="editError" class="alert-modal alert-danger" style="display:none;"></div>
                <div class="form-group">
                    <label>Type Name *</label>
                    <input type="text" id="edit_type_name" name="type_name" required>
                </div>
                <div class="form-group">
                    <label>Duration (Months) *</label>
                    <input type="number" id="edit_duration" name="duration" min="1" max="24" required>
                </div>
                <div class="form-group">
                    <label>Price (Rs.) *</label>
                    <input type="number" id="edit_price" name="price" step="0.01" min="0" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-primary">Update Membership</button>
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
    fetch('membership_manage.php?get_membership=' + id)
        .then(r => r.json())
        .then(d => {
            document.getElementById('edit_id').value = d.MembershipTypeID;
            document.getElementById('edit_type_name').value = d.TypeName;
            document.getElementById('edit_duration').value = d.DurationMonths;
            document.getElementById('edit_price').value = d.Price;
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
    
    fetch('membership_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { window.location.href = 'membership_manage.php'; }
            else { document.getElementById('addError').innerHTML = d.message; document.getElementById('addError').style.display = 'block'; }
        });
});

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('ajax', '1');
    fd.append('action', 'edit');
    
    fetch('membership_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { window.location.href = 'membership_manage.php'; }
            else { document.getElementById('editError').innerHTML = d.message; document.getElementById('editError').style.display = 'block'; }
        });
});
</script>