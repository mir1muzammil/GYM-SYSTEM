<?php
$page_title = "Members Management";
require_once 'db_connect.php';
require_once 'auth_check.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    if ($_POST['action'] == 'add') {
        $name = trim($_POST['name']);
        $age = intval($_POST['age']);
        $gender = trim($_POST['gender']);
        $contact = trim($_POST['contact']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $join_date = $_POST['join_date'];
        $membership_id = intval($_POST['membership_id']);
        
        if (empty($name)) {
            $response['message'] = 'Member name is required';
            echo json_encode($response); exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO Member (Name, Age, Gender, ContactNumber, Email, Address, JoinDate, MembershipTypeID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssssi", $name, $age, $gender, $contact, $email, $address, $join_date, $membership_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Member added successfully';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $stmt->close();
    }
    
    if ($_POST['action'] == 'edit') {
        $id = intval($_POST['member_id']);
        $name = trim($_POST['name']);
        $age = intval($_POST['age']);
        $gender = trim($_POST['gender']);
        $contact = trim($_POST['contact']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $join_date = $_POST['join_date'];
        $membership_id = intval($_POST['membership_id']);
        
        $stmt = $conn->prepare("UPDATE Member SET Name=?, Age=?, Gender=?, ContactNumber=?, Email=?, Address=?, JoinDate=?, MembershipTypeID=? WHERE MemberID=?");
        $stmt->bind_param("sisssssii", $name, $age, $gender, $contact, $email, $address, $join_date, $membership_id, $id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Member updated successfully';
        } else {
            $response['message'] = 'Error: ' . $conn->error;
        }
        $stmt->close();
    }
    
    echo json_encode($response); exit;
}

if (isset($_GET['get_member'])) {
    header('Content-Type: application/json');
    $id = intval($_GET['get_member']);
    $stmt = $conn->prepare("SELECT * FROM Member WHERE MemberID = ?");
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
        $stmt = $conn->prepare("DELETE FROM Member WHERE MemberID = ?");
        $stmt->bind_param("i", $id);
        
        if (!$stmt->execute()) {
            // This is for non-throwing errors (unlikely with mysqli_sql_exception enabled, but safe)
             $msg_type = 'error';
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        // MySQL Error Code 1451 is the "Cannot delete or update a parent row: a foreign key constraint fails" error
        if ($e->getCode() == 1451) {
            $msg_type = 'fk_fail';
        } else {
            // Handle other SQL errors 
            $msg_type = 'error'; 
        }
    }
    
    header("Location: members_manage.php?msg=" . $msg_type);
    exit;
}
// END OF FIX

include 'header.php';
include 'sidebar.php';

$members = $conn->query("SELECT m.*, mt.TypeName FROM Member m LEFT JOIN Membership mt ON m.MembershipTypeID = mt.MembershipTypeID ORDER BY m.Name ASC");
$memberships = $conn->query("SELECT * FROM Membership ORDER BY TypeName");
?>

<style>
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);backdrop-filter:blur(5px);z-index:9999;overflow-y:auto}
.modal-overlay.active{display:flex;align-items:center;justify-content:center;padding:20px}
.modal-container{background:white;max-width:700px;width:100%;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,0.3);animation:slideDown 0.3s ease-out}
@keyframes slideDown{from{transform:translateY(-50px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-header{padding:20px 30px;border-bottom:1px solid #e5e5e5;display:flex;justify-content:space-between;align-items:center}
.modal-header h2{margin:0;color:#333;font-size:24px}
.modal-close{background:none;border:none;font-size:28px;color:#999;cursor:pointer;padding:0;width:30px;height:30px}
.modal-close:hover{color:#333}
.modal-body{padding:30px;max-height:70vh;overflow-y:auto}
.form-group{margin-bottom:20px}
.form-group label{display:block;margin-bottom:8px;color:#333;font-weight:500}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-size:14px}
.form-group input:focus,.form-group select:focus{outline:none;border-color:#3c8dbc}
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
        <h1>Members Management <small>Manage gym members</small></h1>
        <ol class="breadcrumb">
            <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Members</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">All Members</h3>
                        <div class="box-tools">
                            <button onclick="openAddModal()" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i> Add Member
                            </button>
                        </div>
                    </div>

                    <div class="box-body">
                        <?php if (isset($_GET['msg'])): ?>
                            <?php if ($_GET['msg'] === 'deleted'): ?>
                                <div class="alert alert-success alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="fa fa-check"></i> Member deleted successfully!
                                </div>
                            <?php elseif ($_GET['msg'] === 'fk_fail'): // START OF FIX: Display Foreign Key Error ?>
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <i class="fa fa-warning"></i> **Deletion Failed**: This member has associated **payment records** and cannot be deleted until those payments are removed or reassigned.
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
                                        <th>Name</th>
                                        <th>Age</th>
                                        <th>Gender</th>
                                        <th>Contact</th>
                                        <th>Membership</th>
                                        <th>Join Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $members->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['MemberID'] ?></td>
                                        <td><?= htmlspecialchars($row['Name']) ?></td>
                                        <td><?= $row['Age'] ?></td>
                                        <td><?= htmlspecialchars($row['Gender']) ?></td>
                                        <td><?= htmlspecialchars($row['ContactNumber']) ?></td>
                                        <td><span class="label label-info"><?= htmlspecialchars($row['TypeName'] ?? 'N/A') ?></span></td>
                                        <td><?= date('d M Y', strtotime($row['JoinDate'])) ?></td>
                                        <td class="text-nowrap">
                                            <button onclick="openEditModal(<?= $row['MemberID'] ?>)" class="btn btn-warning btn-xs">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                            <a href="members_manage.php?delete=<?= $row['MemberID'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-xs">
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
            <h2>Add Member</h2>
            <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form id="addForm" onsubmit="return false;">
            <div class="modal-body">
                <div id="addError" class="alert-modal alert-danger" style="display:none;"></div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" min="10" max="100">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact">
                    </div>
                    <div class="form-group">
                        <label>Membership Type</label>
                        <select name="membership_id">
                            <option value="">Select</option>
                            <?php $memberships->data_seek(0); while ($m = $memberships->fetch_assoc()): ?>
                            <option value="<?= $m['MembershipTypeID'] ?>"><?= htmlspecialchars($m['TypeName']) ?> - Rs.<?= number_format($m['Price']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Join Date</label>
                    <input type="date" name="join_date" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-success">Add Member</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h2>Edit Member</h2>
            <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" onsubmit="return false;">
            <input type="hidden" id="edit_id" name="member_id">
            <div class="modal-body">
                <div id="editError" class="alert-modal alert-danger" style="display:none;"></div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Name *</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="edit_email" name="email">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" id="edit_age" name="age" min="10" max="100">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select id="edit_gender" name="gender">
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" id="edit_contact" name="contact">
                    </div>
                    <div class="form-group">
                        <label>Membership Type</label>
                        <select id="edit_membership" name="membership_id">
                            <option value="">Select</option>
                            <?php $memberships->data_seek(0); while ($m = $memberships->fetch_assoc()): ?>
                            <option value="<?= $m['MembershipTypeID'] ?>"><?= htmlspecialchars($m['TypeName']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea id="edit_address" name="address" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label>Join Date</label>
                    <input type="date" id="edit_join_date" name="join_date">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn-modal btn-modal-primary">Update Member</button>
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
    fetch('members_manage.php?get_member=' + id)
        .then(r => r.json())
        .then(d => {
            document.getElementById('edit_id').value = d.MemberID;
            document.getElementById('edit_name').value = d.Name;
            document.getElementById('edit_email').value = d.Email || '';
            document.getElementById('edit_age').value = d.Age || '';
            document.getElementById('edit_gender').value = d.Gender || '';
            document.getElementById('edit_contact').value = d.ContactNumber || '';
            document.getElementById('edit_membership').value = d.MembershipTypeID || '';
            document.getElementById('edit_address').value = d.Address || '';
            document.getElementById('edit_join_date').value = d.JoinDate || '';
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
    
    fetch('members_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { window.location.href = 'members_manage.php'; }
            else { document.getElementById('addError').innerHTML = d.message; document.getElementById('addError').style.display = 'block'; }
        });
});

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('ajax', '1');
    fd.append('action', 'edit');
    
    fetch('members_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) { window.location.href = 'members_manage.php'; }
            else { document.getElementById('editError').innerHTML = d.message; document.getElementById('editError').style.display = 'block'; }
        });
});
</script>