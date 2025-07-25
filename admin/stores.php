<?php
$page_title = "Store Management";
require_once '../includes/header.php';

$database = new Database();
$conn = $database->getConnection();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $address = trim($_POST['address']);
                $phone = trim($_POST['phone']);
                $email = trim($_POST['email']);
                $manager_id = !empty($_POST['manager_id']) ? $_POST['manager_id'] : null;
                
                $query = "INSERT INTO stores (name, address, phone, email, manager_id) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$name, $address, $phone, $email, $manager_id])) {
                    $success_message = "Store added successfully.";
                } else {
                    $error_message = "Error adding store.";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $name = trim($_POST['name']);
                $address = trim($_POST['address']);
                $phone = trim($_POST['phone']);
                $email = trim($_POST['email']);
                $manager_id = !empty($_POST['manager_id']) ? $_POST['manager_id'] : null;
                $status = $_POST['status'];
                
                $query = "UPDATE stores SET name=?, address=?, phone=?, email=?, manager_id=?, status=? WHERE id=?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$name, $address, $phone, $email, $manager_id, $status, $id])) {
                    $success_message = "Store updated successfully.";
                } else {
                    $error_message = "Error updating store.";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $query = "DELETE FROM stores WHERE id = ?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$id])) {
                    $success_message = "Store deleted successfully.";
                } else {
                    $error_message = "Error deleting store.";
                }
                break;
        }
    }
}

// Get stores with related information
$query = "SELECT s.*, 
          u.first_name as manager_first_name, u.last_name as manager_last_name,
          (SELECT COUNT(*) FROM restaurant_tables WHERE store_id = s.id) as table_count,
          (SELECT COUNT(*) FROM orders WHERE store_id = s.id) as order_count
          FROM stores s 
          LEFT JOIN users u ON s.manager_id = u.id 
          ORDER BY s.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$stores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get managers for dropdown (employees and admins)
$query = "SELECT id, first_name, last_name, email FROM users WHERE role IN ('admin', 'employee') AND status = 'active' ORDER BY first_name, last_name";
$stmt = $conn->prepare($query);
$stmt->execute();
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Store Management</h1>
        <button class="btn btn-primary" data-modal-target="addStoreModal">
            <i class="fas fa-plus"></i> Add Store
        </button>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Store Overview -->
    <div class="dashboard-grid mb-3">
        <?php
        $total_stores = count($stores);
        $active_stores = count(array_filter($stores, function($s) { return $s['status'] === 'active'; }));
        $total_tables = array_sum(array_column($stores, 'table_count'));
        $total_orders = array_sum(array_column($stores, 'order_count'));
        ?>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_stores; ?></div>
            <div class="stat-label">Total Stores</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $active_stores; ?></div>
            <div class="stat-label">Active Stores</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_tables; ?></div>
            <div class="stat-label">Total Tables</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_orders; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>

    <!-- Stores Grid -->
    <div class="row mb-3">
        <?php foreach ($stores as $store): ?>
            <div class="col-md-6 mb-3">
                <div class="card <?php echo $store['status'] === 'inactive' ? 'bg-light' : ''; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($store['name']); ?></h5>
                            <span class="badge badge-<?php echo $store['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($store['status']); ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <p class="text-muted mb-1">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($store['address']); ?>
                            </p>
                            <?php if ($store['phone']): ?>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-phone"></i> 
                                    <?php echo htmlspecialchars($store['phone']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($store['email']): ?>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-envelope"></i> 
                                    <?php echo htmlspecialchars($store['email']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($store['manager_first_name']): ?>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-user-tie"></i> 
                                    Manager: <?php echo htmlspecialchars($store['manager_first_name'] . ' ' . $store['manager_last_name']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <strong><?php echo $store['table_count']; ?></strong>
                                <br><small class="text-muted">Tables</small>
                            </div>
                            <div class="col-4">
                                <strong><?php echo $store['order_count']; ?></strong>
                                <br><small class="text-muted">Orders</small>
                            </div>
                            <div class="col-4">
                                <strong><?php echo date('M Y', strtotime($store['created_at'])); ?></strong>
                                <br><small class="text-muted">Created</small>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button class="btn btn-sm btn-warning" onclick="editStore(<?php echo htmlspecialchars(json_encode($store)); ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="tables.php?store=<?php echo $store['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-chair"></i> Tables
                            </a>
                            <button class="btn btn-sm btn-danger" onclick="deleteStore(<?php echo $store['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Stores Table -->
    <div class="card">
        <div class="card-header">
            Stores List
            <div class="d-flex align-items-center" style="float: right;">
                <input type="text" placeholder="Search stores..." class="form-control" data-table-search="storesTable" style="width: 200px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="storesTable">
                    <thead>
                        <tr>
                            <th data-sort="name">Store Name</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>Manager</th>
                            <th data-sort="table_count">Tables</th>
                            <th data-sort="status">Status</th>
                            <th data-sort="created_at">Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stores as $store): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($store['name']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($store['address']); ?>
                                </td>
                                <td>
                                    <?php if ($store['phone']): ?>
                                        <div><?php echo htmlspecialchars($store['phone']); ?></div>
                                    <?php endif; ?>
                                    <?php if ($store['email']): ?>
                                        <div><small class="text-muted"><?php echo htmlspecialchars($store['email']); ?></small></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($store['manager_first_name']): ?>
                                        <?php echo htmlspecialchars($store['manager_first_name'] . ' ' . $store['manager_last_name']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No manager assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?php echo $store['table_count']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $store['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($store['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($store['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editStore(<?php echo htmlspecialchars(json_encode($store)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="tables.php?store=<?php echo $store['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-chair"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="deleteStore(<?php echo $store['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Store Modal -->
<div id="addStoreModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Store</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate>
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label class="form-label">Store Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address *</label>
                    <textarea name="address" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Manager</label>
                    <select name="manager_id" class="form-control">
                        <option value="">Select Manager</option>
                        <?php foreach ($managers as $manager): ?>
                            <option value="<?php echo $manager['id']; ?>">
                                <?php echo htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Store</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Store Modal -->
<div id="editStoreModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Store</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_store_id">
                
                <div class="form-group">
                    <label class="form-label">Store Name *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address *</label>
                    <textarea name="address" id="edit_address" class="form-control" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" id="edit_phone" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Manager</label>
                            <select name="manager_id" id="edit_manager_id" class="form-control">
                                <option value="">Select Manager</option>
                                <?php foreach ($managers as $manager): ?>
                                    <option value="<?php echo $manager['id']; ?>">
                                        <?php echo htmlspecialchars($manager['first_name'] . ' ' . $manager['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Status *</label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Store</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStore(store) {
    document.getElementById('edit_store_id').value = store.id;
    document.getElementById('edit_name').value = store.name;
    document.getElementById('edit_address').value = store.address;
    document.getElementById('edit_phone').value = store.phone || '';
    document.getElementById('edit_email').value = store.email || '';
    document.getElementById('edit_manager_id').value = store.manager_id || '';
    document.getElementById('edit_status').value = store.status;
    
    document.getElementById('editStoreModal').classList.add('show');
}

function deleteStore(id) {
    if (confirmDelete('Are you sure you want to delete this store? All related tables and orders will be affected.')) {
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