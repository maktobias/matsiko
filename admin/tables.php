<?php
$page_title = "Table Management";
require_once '../includes/header.php';

$database = new Database();
$conn = $database->getConnection();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $store_id = $_POST['store_id'];
                $table_number = trim($_POST['table_number']);
                $capacity = (int)$_POST['capacity'];
                
                $query = "INSERT INTO restaurant_tables (store_id, table_number, capacity) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$store_id, $table_number, $capacity])) {
                    $success_message = "Table added successfully.";
                } else {
                    $error_message = "Error adding table. Table number might already exist.";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $store_id = $_POST['store_id'];
                $table_number = trim($_POST['table_number']);
                $capacity = (int)$_POST['capacity'];
                $status = $_POST['status'];
                
                $query = "UPDATE restaurant_tables SET store_id=?, table_number=?, capacity=?, status=? WHERE id=?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$store_id, $table_number, $capacity, $status, $id])) {
                    $success_message = "Table updated successfully.";
                } else {
                    $error_message = "Error updating table.";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $query = "DELETE FROM restaurant_tables WHERE id = ?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$id])) {
                    $success_message = "Table deleted successfully.";
                } else {
                    $error_message = "Error deleting table.";
                }
                break;
                
            case 'update_status':
                $id = $_POST['id'];
                $status = $_POST['status'];
                
                $query = "UPDATE restaurant_tables SET status = ? WHERE id = ?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$status, $id])) {
                    $success_message = "Table status updated successfully.";
                } else {
                    $error_message = "Error updating table status.";
                }
                break;
        }
    }
}

// Get tables with store information
$query = "SELECT rt.*, s.name as store_name, 
          (SELECT COUNT(*) FROM orders WHERE table_id = rt.id AND status IN ('pending', 'confirmed', 'preparing', 'ready')) as active_orders
          FROM restaurant_tables rt 
          LEFT JOIN stores s ON rt.store_id = s.id 
          ORDER BY s.name, rt.table_number";
$stmt = $conn->prepare($query);
$stmt->execute();
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get stores for dropdown
$query = "SELECT * FROM stores WHERE status = 'active' ORDER BY name";
$stmt = $conn->prepare($query);
$stmt->execute();
$stores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Table Management</h1>
        <button class="btn btn-primary" data-modal-target="addTableModal">
            <i class="fas fa-plus"></i> Add Table
        </button>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Table Status Overview -->
    <div class="dashboard-grid mb-3">
        <?php
        $status_counts = ['available' => 0, 'occupied' => 0, 'reserved' => 0, 'maintenance' => 0];
        foreach ($tables as $table) {
            $status_counts[$table['status']]++;
        }
        ?>
        <div class="stat-card">
            <div class="stat-number"><?php echo $status_counts['available']; ?></div>
            <div class="stat-label">Available</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $status_counts['occupied']; ?></div>
            <div class="stat-label">Occupied</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $status_counts['reserved']; ?></div>
            <div class="stat-label">Reserved</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $status_counts['maintenance']; ?></div>
            <div class="stat-label">Maintenance</div>
        </div>
    </div>

    <!-- Tables Grid View -->
    <div class="card mb-3">
        <div class="card-header">Table Layout</div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                <?php foreach ($tables as $table): ?>
                    <div class="card table-<?php echo $table['status']; ?>" style="padding: 15px; text-align: center; border: 2px solid;">
                        <h4><?php echo htmlspecialchars($table['table_number']); ?></h4>
                        <p><strong><?php echo htmlspecialchars($table['store_name']); ?></strong></p>
                        <p><i class="fas fa-users"></i> <?php echo $table['capacity']; ?> seats</p>
                        <p>
                            <span class="badge badge-<?php echo $table['status'] === 'available' ? 'success' : ($table['status'] === 'occupied' ? 'danger' : ($table['status'] === 'reserved' ? 'warning' : 'secondary')); ?>">
                                <?php echo ucfirst($table['status']); ?>
                            </span>
                        </p>
                        <?php if ($table['active_orders'] > 0): ?>
                            <p><span class="badge badge-info"><?php echo $table['active_orders']; ?> active orders</span></p>
                        <?php endif; ?>
                        <div style="margin-top: 10px;">
                            <select onchange="updateTableStatus(<?php echo $table['id']; ?>, this.value)" class="form-control" style="font-size: 0.8rem; margin-bottom: 5px;">
                                <option value="available" <?php echo $table['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="occupied" <?php echo $table['status'] === 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                <option value="reserved" <?php echo $table['status'] === 'reserved' ? 'selected' : ''; ?>>Reserved</option>
                                <option value="maintenance" <?php echo $table['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                            <button class="btn btn-sm btn-warning" onclick="editTable(<?php echo htmlspecialchars(json_encode($table)); ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTable(<?php echo $table['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Tables List -->
    <div class="card">
        <div class="card-header">
            Tables List
            <div class="d-flex align-items-center" style="float: right;">
                <input type="text" placeholder="Search tables..." class="form-control" data-table-search="tablesTable" style="width: 200px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="tablesTable">
                    <thead>
                        <tr>
                            <th data-sort="table_number">Table #</th>
                            <th data-sort="store_name">Store</th>
                            <th data-sort="capacity">Capacity</th>
                            <th data-sort="status">Status</th>
                            <th>Active Orders</th>
                            <th data-sort="created_at">Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $table): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($table['table_number']); ?></td>
                                <td><?php echo htmlspecialchars($table['store_name']); ?></td>
                                <td><i class="fas fa-users"></i> <?php echo $table['capacity']; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $table['status'] === 'available' ? 'success' : ($table['status'] === 'occupied' ? 'danger' : ($table['status'] === 'reserved' ? 'warning' : 'secondary')); ?>">
                                        <?php echo ucfirst($table['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($table['active_orders'] > 0): ?>
                                        <span class="badge badge-info"><?php echo $table['active_orders']; ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">0</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($table['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editTable(<?php echo htmlspecialchars(json_encode($table)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteTable(<?php echo $table['id']; ?>)">
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

<!-- Add Table Modal -->
<div id="addTableModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Table</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate>
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label class="form-label">Store *</label>
                    <select name="store_id" class="form-control" required>
                        <option value="">Select Store</option>
                        <?php foreach ($stores as $store): ?>
                            <option value="<?php echo $store['id']; ?>">
                                <?php echo htmlspecialchars($store['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Table Number *</label>
                            <input type="text" name="table_number" class="form-control" required placeholder="e.g., T01, Table 1">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Capacity *</label>
                            <input type="number" name="capacity" class="form-control" required min="1" max="20">
                        </div>
                    </div>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Table</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Table Modal -->
<div id="editTableModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Table</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_table_id">
                
                <div class="form-group">
                    <label class="form-label">Store *</label>
                    <select name="store_id" id="edit_store_id" class="form-control" required>
                        <option value="">Select Store</option>
                        <?php foreach ($stores as $store): ?>
                            <option value="<?php echo $store['id']; ?>">
                                <?php echo htmlspecialchars($store['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Table Number *</label>
                            <input type="text" name="table_number" id="edit_table_number" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Capacity *</label>
                            <input type="number" name="capacity" id="edit_capacity" class="form-control" required min="1" max="20">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" id="edit_status" class="form-control" required>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="reserved">Reserved</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Table</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTable(table) {
    document.getElementById('edit_table_id').value = table.id;
    document.getElementById('edit_store_id').value = table.store_id;
    document.getElementById('edit_table_number').value = table.table_number;
    document.getElementById('edit_capacity').value = table.capacity;
    document.getElementById('edit_status').value = table.status;
    
    document.getElementById('editTableModal').classList.add('show');
}

function deleteTable(id) {
    if (confirmDelete('Are you sure you want to delete this table?')) {
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

function updateTableStatus(tableId, status) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" value="${tableId}">
        <input type="hidden" name="status" value="${status}">
    `;
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php include '../includes/footer.php'; ?>