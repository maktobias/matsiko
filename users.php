<?php
require_once 'includes/session.php';
require_once 'classes/User.php';

Session::requireLogin();

// Check if user is admin
if (!Session::isAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = 'User Management';
$user = new User();
$message = '';
$messageType = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $userData = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'],
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'phone' => trim($_POST['phone']),
                'user_group_id' => $_POST['user_group_id'],
                'status' => $_POST['status']
            ];
            
            if ($user->create($userData)) {
                $message = 'User created successfully';
                $messageType = 'success';
            } else {
                $message = 'Error creating user';
                $messageType = 'danger';
            }
            break;
            
        case 'update':
            $userId = $_POST['user_id'];
            $userData = [
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'phone' => trim($_POST['phone']),
                'user_group_id' => $_POST['user_group_id'],
                'status' => $_POST['status']
            ];
            
            if ($user->update($userId, $userData)) {
                $message = 'User updated successfully';
                $messageType = 'success';
            } else {
                $message = 'Error updating user';
                $messageType = 'danger';
            }
            break;
            
        case 'delete':
            $userId = $_POST['user_id'];
            if ($userId != Session::getUserId()) { // Prevent self-deletion
                if ($user->delete($userId)) {
                    $message = 'User deleted successfully';
                    $messageType = 'success';
                } else {
                    $message = 'Error deleting user';
                    $messageType = 'danger';
                }
            } else {
                $message = 'Cannot delete your own account';
                $messageType = 'warning';
            }
            break;
    }
}

$users = $user->getAll();
$userGroups = $user->getUserGroups();

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users"></i> User Management</h1>
        <button class="btn btn-success" data-modal="userModal" onclick="openUserModal()">
            <i class="fas fa-plus"></i> Add User
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>" data-auto-hide="5000">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Users (<?php echo count($users); ?>)</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" data-sortable>
                    <thead>
                        <tr>
                            <th data-sort="0">ID</th>
                            <th data-sort="1">Username</th>
                            <th data-sort="2">Name</th>
                            <th data-sort="3">Email</th>
                            <th data-sort="4">Phone</th>
                            <th data-sort="5">Group</th>
                            <th data-sort="6">Status</th>
                            <th data-sort="7">Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $userItem): ?>
                            <tr>
                                <td><?php echo $userItem['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($userItem['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($userItem['first_name'] . ' ' . $userItem['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($userItem['email']); ?></td>
                                <td><?php echo htmlspecialchars($userItem['phone']); ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo htmlspecialchars($userItem['group_name'] ?? 'No Group'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $userItem['status'] === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($userItem['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($userItem['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-warning btn-sm" onclick="editUser(<?php echo htmlspecialchars(json_encode($userItem)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($userItem['id'] != Session::getUserId()): ?>
                                            <button class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $userItem['id']; ?>, '<?php echo htmlspecialchars($userItem['username']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div id="userModal" class="modal">
    <div class="modal-dialog">
        <form id="userForm" method="POST" data-validate>
            <div class="modal-header">
                <h3 class="modal-title" id="userModalTitle">Add User</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" id="userAction" value="create">
                <input type="hidden" name="user_id" id="userId">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                
                <div class="form-group" id="passwordGroup">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" data-min-length="6">
                </div>
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control" data-phone>
                </div>
                
                <div class="form-group">
                    <label for="user_group_id">User Group</label>
                    <select name="user_group_id" id="user_group_id" class="form-control" required>
                        <option value="">Select Group</option>
                        <?php foreach ($userGroups as $group): ?>
                            <option value="<?php echo $group['id']; ?>">
                                <?php echo htmlspecialchars($group['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Confirm Delete</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
            <p class="text-danger"><small>This action cannot be undone.</small></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="deleteUserId">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete User
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openUserModal() {
    document.getElementById('userModalTitle').textContent = 'Add User';
    document.getElementById('userAction').value = 'create';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('passwordGroup').style.display = 'block';
    document.getElementById('password').required = true;
}

function editUser(userData) {
    document.getElementById('userModalTitle').textContent = 'Edit User';
    document.getElementById('userAction').value = 'update';
    document.getElementById('userId').value = userData.id;
    document.getElementById('username').value = userData.username;
    document.getElementById('email').value = userData.email;
    document.getElementById('first_name').value = userData.first_name;
    document.getElementById('last_name').value = userData.last_name;
    document.getElementById('phone').value = userData.phone || '';
    document.getElementById('user_group_id').value = userData.user_group_id || '';
    document.getElementById('status').value = userData.status;
    
    // Hide password field for editing
    document.getElementById('passwordGroup').style.display = 'none';
    document.getElementById('password').required = false;
    
    RestaurantApp.showModal(document.getElementById('userModal'));
}

function deleteUser(userId, username) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = username;
    RestaurantApp.showModal(document.getElementById('deleteModal'));
}
</script>

<?php include 'includes/footer.php'; ?>