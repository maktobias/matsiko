<?php
$page_title = "Products Management";
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
                $category_id = $_POST['category_id'];
                $price = (float)$_POST['price'];
                $cost_price = !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : null;
                $ingredients = trim($_POST['ingredients']);
                $preparation_time = !empty($_POST['preparation_time']) ? (int)$_POST['preparation_time'] : null;
                
                $query = "INSERT INTO products (name, description, category_id, price, cost_price, ingredients, preparation_time) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$name, $description, $category_id, $price, $cost_price, $ingredients, $preparation_time])) {
                    $success_message = "Product added successfully.";
                } else {
                    $error_message = "Error adding product.";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $category_id = $_POST['category_id'];
                $price = (float)$_POST['price'];
                $cost_price = !empty($_POST['cost_price']) ? (float)$_POST['cost_price'] : null;
                $ingredients = trim($_POST['ingredients']);
                $preparation_time = !empty($_POST['preparation_time']) ? (int)$_POST['preparation_time'] : null;
                $status = $_POST['status'];
                
                $query = "UPDATE products SET name=?, description=?, category_id=?, price=?, cost_price=?, ingredients=?, preparation_time=?, status=? WHERE id=?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$name, $description, $category_id, $price, $cost_price, $ingredients, $preparation_time, $status, $id])) {
                    $success_message = "Product updated successfully.";
                } else {
                    $error_message = "Error updating product.";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $query = "DELETE FROM products WHERE id = ?";
                $stmt = $conn->prepare($query);
                if ($stmt->execute([$id])) {
                    $success_message = "Product deleted successfully.";
                } else {
                    $error_message = "Error deleting product.";
                }
                break;
        }
    }
}

// Get products with category information
$query = "SELECT p.*, c.name as category_name,
          (SELECT COUNT(*) FROM order_items WHERE product_id = p.id) as order_count
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY c.name, p.name";
$stmt = $conn->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for dropdown
$query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$stmt = $conn->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/sidebar.php'; ?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Products Management</h1>
        <div>
            <a href="categories.php" class="btn btn-secondary">
                <i class="fas fa-tags"></i> Manage Categories
            </a>
            <button class="btn btn-primary" data-modal-target="addProductModal">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Products Stats -->
    <div class="dashboard-grid mb-3">
        <?php
        $total_products = count($products);
        $available_products = count(array_filter($products, function($p) { return $p['status'] === 'available'; }));
        $categories_count = count($categories);
        $avg_price = $total_products > 0 ? array_sum(array_column($products, 'price')) / $total_products : 0;
        ?>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_products; ?></div>
            <div class="stat-label">Total Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $available_products; ?></div>
            <div class="stat-label">Available</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $categories_count; ?></div>
            <div class="stat-label">Categories</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">$<?php echo number_format($avg_price, 2); ?></div>
            <div class="stat-label">Average Price</div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header">
            Products
            <div class="d-flex align-items-center" style="float: right;">
                <select id="categoryFilter" class="form-control" style="width: 150px; margin-right: 10px;" onchange="filterByCategory()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['name']); ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" placeholder="Search products..." class="form-control" data-table-search="productsTable" style="width: 200px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="productsTable">
                    <thead>
                        <tr>
                            <th data-sort="name">Name</th>
                            <th data-sort="category_name">Category</th>
                            <th data-sort="price">Price</th>
                            <th data-sort="cost_price">Cost</th>
                            <th>Margin</th>
                            <th data-sort="preparation_time">Prep Time</th>
                            <th data-sort="status">Status</th>
                            <th>Orders</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr data-category="<?php echo htmlspecialchars($product['category_name']); ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <?php if ($product['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : ''); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo htmlspecialchars($product['category_name']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <?php if ($product['cost_price']): ?>
                                        $<?php echo number_format($product['cost_price'], 2); ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['cost_price']): ?>
                                        <?php $margin = (($product['price'] - $product['cost_price']) / $product['price']) * 100; ?>
                                        <span class="badge badge-<?php echo $margin > 50 ? 'success' : ($margin > 30 ? 'warning' : 'danger'); ?>">
                                            <?php echo number_format($margin, 1); ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['preparation_time']): ?>
                                        <?php echo $product['preparation_time']; ?> min
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $product['status'] === 'available' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?php echo $product['order_count']; ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
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

<!-- Add Product Modal -->
<div id="addProductModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Add New Product</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate>
                <input type="hidden" name="action" value="add">
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Price *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Cost Price</label>
                            <input type="number" name="cost_price" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Preparation Time (minutes)</label>
                            <input type="number" name="preparation_time" class="form-control" min="1" max="120">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ingredients</label>
                    <textarea name="ingredients" class="form-control" rows="2" placeholder="List main ingredients..."></textarea>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Product</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" data-validate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_product_id">
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category_id" id="edit_category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Price *</label>
                            <input type="number" name="price" id="edit_price" class="form-control" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Cost Price</label>
                            <input type="number" name="cost_price" id="edit_cost_price" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Preparation Time (minutes)</label>
                            <input type="number" name="preparation_time" id="edit_preparation_time" class="form-control" min="1" max="120">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label">Status *</label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ingredients</label>
                    <textarea name="ingredients" id="edit_ingredients" class="form-control" rows="2"></textarea>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('edit_product_id').value = product.id;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_category_id').value = product.category_id;
    document.getElementById('edit_description').value = product.description || '';
    document.getElementById('edit_price').value = product.price;
    document.getElementById('edit_cost_price').value = product.cost_price || '';
    document.getElementById('edit_preparation_time').value = product.preparation_time || '';
    document.getElementById('edit_status').value = product.status;
    document.getElementById('edit_ingredients').value = product.ingredients || '';
    
    document.getElementById('editProductModal').classList.add('show');
}

function deleteProduct(id) {
    if (confirmDelete('Are you sure you want to delete this product?')) {
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

function filterByCategory() {
    const categoryFilter = document.getElementById('categoryFilter').value;
    const tableRows = document.querySelectorAll('#productsTable tbody tr');
    
    tableRows.forEach(row => {
        const category = row.getAttribute('data-category');
        if (categoryFilter === '' || category === categoryFilter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>