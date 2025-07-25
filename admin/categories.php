<?php
$page_title = "Categories Management";
require_once '../includes/header.php';

$database = new Database();
$conn = $database->getConnection();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                
                $query = "INSERT INTO categories (name, description) VALUES (?, ?)";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$name, $description])) {
                    $success_message = "Category added successfully.";
                } else {
                    $error_message = "Error adding category.";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $status = $_POST['status'];
                
                $query = "UPDATE categories SET name=?, description=?, status=? WHERE id=?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$name, $description, $status, $id])) {
                    $success_message = "Category updated successfully.";
                } else {
                    $error_message = "Error updating category.";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                
                // Check if category has products
                $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->execute([$id]);
                $product_count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($product_count > 0) {
                    $error_message = "Cannot delete category. It has {$product_count} products assigned to it.";
                } else {
                    $query = "DELETE FROM categories WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    if ($stmt->execute([$id])) {
                        $success_message = "Category deleted successfully.";
                    } else {
                        $error_message = "Error deleting category.";
                    }
                }
                break;
        }
    }
}

// Get categories with product count
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count,
          (SELECT COUNT(*) FROM products WHERE category_id = c.id AND status = 'available') as available_products
          FROM categories c 
          ORDER BY c.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Categories Management</h1>
        <button class="btn btn-primary" data-modal-target="addCategoryModal">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Categories Overview -->
    <div class="dashboard-grid mb-3">
        <?php
        $total_categories = count($categories);
        $active_categories = count(array_filter($categories, function($c) { return $c['status'] === 'active'; }));
        $total_products = array_sum(array_column($categories, 'product_count'));
        $avg_products_per_category = $total_categories > 0 ? $total_products / $total_categories : 0;
        ?>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_categories; ?></div>
            <div class="stat-label">Total Categories</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $active_categories; ?></div>
            <div class="stat-label">Active Categories</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_products; ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($avg_products_per_category, 1); ?></div>
            <div class="stat-label">Avg Products/Category</div>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="row mb-3">
        <?php foreach ($categories as $category): ?>
            <div class="col-md-4 mb-3">
                <div class="card <?php echo $category['status'] === 'inactive' ? 'bg-light' : ''; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <span class="badge badge-<?php echo $category['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($category['status']); ?>
                            </span>
                        </div>
                        
                        <?php if ($category['description']): ?>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($category['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="row text-center">
                            <div class="col-6">
                                <strong><?php echo $category['product_count']; ?></strong>
                                <br><small class="text-muted">Total Products</small>
                            </div>
                            <div class="col-6">
                                <strong><?php echo $category['available_products']; ?></strong>
                                <br><small class="text-muted">Available</small>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button class="btn btn-sm btn-warning" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-utensils"></i> Products
                            </a>
                            <?php if ($category['product_count'] == 0): ?>
                                <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Categories Table -->
    <div class="card">
        <div class="card-header">
            Categories List
            <div class="d-flex align-items-center" style="float: right;">
                <input type="text" placeholder="Search categories..." class="form-control" data-table-search="categoriesTable" style="width: 200px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="categoriesTable">
                    <thead>
                        <tr>
                            <th data-sort="name">Name</th>
                            <th>Description</th>
                            <th data-sort="product_count">Products</th>
                            <th data-sort="available_products">Available</th>
                            <th data-sort="status">Status</th>
                            <th data-sort="created_at">Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                <td>
                                    <?php if ($category['description']): ?>
                                        <?php echo htmlspecialchars(substr($category['description'], 0, 50)) . (strlen($category['description']) > 50 ? '...' : ''); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No description</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?php echo $category['product_count']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-success"><?php echo $category['available_products']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $category['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($category['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-utensils"></i>
                                    </a>
                                    <?php if ($category['product_count'] == 0): ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
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

<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Category</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate>
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Optional description for the category..."></textarea>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Edit Category</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_category_id">
                
                <div class="form-group">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" id="edit_status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(category) {
    document.getElementById('edit_category_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_description').value = category.description || '';
    document.getElementById('edit_status').value = category.status;
    
    document.getElementById('editCategoryModal').classList.add('show');
}

function deleteCategory(id) {
    if (confirmDelete('Are you sure you want to delete this category? This action cannot be undone.')) {
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