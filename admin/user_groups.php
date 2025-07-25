<?php
$page_title = "User Groups Management";
require_once '../includes/header.php';

$database = new Database();
$conn = $database->getConnection();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $permissions = [];
                
                // Build permissions array
                if (isset($_POST['permissions'])) {
                    foreach ($_POST['permissions'] as $permission) {
                        $permissions[$permission] = true;
                    }
                }
                
                $query = "INSERT INTO user_groups (name, permissions) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$name, json_encode($permissions)])) {
                    $success_message = "User group added successfully.";
                } else {
                    $error_message = "Error adding user group.";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $name = trim($_POST['name']);
                $permissions = [];
                
                // Build permissions array
                if (isset($_POST['permissions'])) {
                    foreach ($_POST['permissions'] as $permission) {
                        $permissions[$permission] = true;
                    }
                }
                
                $query = "UPDATE user_groups SET name=?, permissions=? WHERE id=?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$name, json_encode($permissions), $id])) {
                    $success_message = "User group updated successfully.";
                } else {
                    $error_message = "Error updating user group.";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $query = "DELETE FROM user_groups WHERE id = ? AND id != 1"; // Prevent deleting super admin
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$id])) {
                    $success_message = "User group deleted successfully.";
                } else {
                    $error_message = "Error deleting user group.";
                }
                break;
        }
    }
}

// Get user groups
$query = "SELECT *, (SELECT COUNT(*) FROM users WHERE group_id = user_groups.id) as user_count FROM user_groups ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$user_groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Available permissions
$available_permissions = [
    'orders' => 'Order Management',
    'tables' => 'Table Management',
    'products' => 'Product Management',
    'categories' => 'Category Management',
    'reports' => 'Reports & Analytics',
    'stores' => 'Store Management',
    'users' => 'User Management',
    'company' => 'Company Settings'
];
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>User Groups Management</h1>
        <button class="btn btn-primary" data-modal-target="addGroupModal">
            <i class="fas fa-plus"></i> Add Group
        </button>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Groups Table -->
    <div class="card">
        <div class="card-header">User Groups</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Group Name</th>
                            <th>Users</th>
                            <th>Permissions</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_groups as $group): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($group['name']); ?></td>
                                <td>
                                    <span class="badge badge-info"><?php echo $group['user_count']; ?> users</span>
                                </td>
                                <td>
                                    <?php 
                                    $permissions = json_decode($group['permissions'], true);
                                    if (isset($permissions['all']) && $permissions['all']) {
                                        echo '<span class="badge badge-danger">All Permissions</span>';
                                    } else {
                                        $count = count($permissions);
                                        echo '<span class="badge badge-info">' . $count . ' permissions</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($group['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editGroup(<?php echo htmlspecialchars(json_encode($group)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($group['id'] != 1): // Don't allow deleting super admin ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteGroup(<?php echo $group['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Group Modal -->
<div id="addGroupModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New User Group</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate>
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label class="form-label">Group Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Permissions</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                        <?php foreach ($available_permissions as $key => $label): ?>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="permissions[]" value="<?php echo $key; ?>" style="margin-right: 8px;">
                                <?php echo $label; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Group Modal -->
<div id="editGroupModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit User Group</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_group_id">
                
                <div class="form-group">
                    <label class="form-label">Group Name *</label>
                    <input type="text" name="name" id="edit_group_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Permissions</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                        <?php foreach ($available_permissions as $key => $label): ?>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="permissions[]" value="<?php echo $key; ?>" id="edit_perm_<?php echo $key; ?>" style="margin-right: 8px;">
                                <?php echo $label; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editGroup(group) {
    document.getElementById('edit_group_id').value = group.id;
    document.getElementById('edit_group_name').value = group.name;
    
    // Clear all checkboxes first
    document.querySelectorAll('#editGroupModal input[type="checkbox"]').forEach(cb => cb.checked = false);
    
    // Set permissions
    const permissions = JSON.parse(group.permissions);
    for (const permission in permissions) {
        if (permissions[permission]) {
            const checkbox = document.getElementById('edit_perm_' + permission);
            if (checkbox) {
                checkbox.checked = true;
            }
        }
    }
    
    document.getElementById('editGroupModal').classList.add('show');
}

function deleteGroup(id) {
    if (confirmDelete('Are you sure you want to delete this group? Users assigned to this group will lose their group permissions.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>