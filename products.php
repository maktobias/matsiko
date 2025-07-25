<?php
require_once 'includes/session.php';
require_once 'classes/Product.php';

Session::requireLogin();

if (!Session::hasPermission('products')) {
    header('Location: dashboard.php');
    exit();
}

$pageTitle = 'Product Management';
$product = new Product();
$message = '';
$messageType = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $productData = [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'category_id' => $_POST['category_id'],
                'price' => $_POST['price'],
                'cost_price' => $_POST['cost_price'] ?: null,
                'image' => $_POST['image'] ?: null,
                'ingredients' => trim($_POST['ingredients']),
                'allergens' => trim($_POST['allergens']),
                'preparation_time' => $_POST['preparation_time'] ?: 0,
                'status' => $_POST['status'],
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0
            ];
            
            if ($product->create($productData)) {
                $message = 'Product created successfully';
                $messageType = 'success';
            } else {
                $message = 'Error creating product';
                $messageType = 'danger';
            }
            break;
            
        case 'update':
            $productId = $_POST['product_id'];
            $productData = [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description']),
                'category_id' => $_POST['category_id'],
                'price' => $_POST['price'],
                'cost_price' => $_POST['cost_price'] ?: null,
                'image' => $_POST['image'] ?: null,
                'ingredients' => trim($_POST['ingredients']),
                'allergens' => trim($_POST['allergens']),
                'preparation_time' => $_POST['preparation_time'] ?: 0,
                'status' => $_POST['status'],
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0
            ];
            
            if ($product->update($productId, $productData)) {
                $message = 'Product updated successfully';
                $messageType = 'success';
            } else {
                $message = 'Error updating product';
                $messageType = 'danger';
            }
            break;
            
        case 'delete':
            $productId = $_POST['product_id'];
            if ($product->delete($productId)) {
                $message = 'Product deleted successfully';
                $messageType = 'success';
            } else {
                $message = 'Error deleting product';
                $messageType = 'danger';
            }
            break;
    }
}

// Get filters
$categoryFilter = $_GET['category'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$products = $product->getAll($categoryFilter, $statusFilter);
$categories = $product->getAllCategories();

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-utensils"></i> Product Management</h1>
        <div>
            <a href="categories.php" class="btn btn-info">
                <i class="fas fa-tags"></i> Manage Categories
            </a>
            <button class="btn btn-success" onclick="openProductModal()">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>" data-auto-hide="5000">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-filter"></i> Filters</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="d-flex gap-3 align-items-end">
                <div class="form-group">
                    <label for="category">Category</label>
                    <select name="category" id="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="available" <?php echo $statusFilter === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="unavailable" <?php echo $statusFilter === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                        <option value="out_of_stock" <?php echo $statusFilter === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="products.php" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Products (<?php echo count($products); ?>)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
                <p class="text-center text-muted">No products found.</p>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($products as $prod): ?>
                        <div class="card" style="border: 1px solid #dee2e6;">
                            <?php if ($prod['image']): ?>
                                <img src="<?php echo htmlspecialchars($prod['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                     style="width: 100%; height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100%; height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image text-muted" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">
                                        <?php echo htmlspecialchars($prod['name']); ?>
                                        <?php if ($prod['is_featured']): ?>
                                            <span class="badge badge-warning">Featured</span>
                                        <?php endif; ?>
                                    </h6>
                                    <span class="badge badge-<?php echo $prod['status'] === 'available' ? 'success' : ($prod['status'] === 'unavailable' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $prod['status'])); ?>
                                    </span>
                                </div>
                                
                                <p class="text-muted small mb-2">
                                    <?php echo htmlspecialchars($prod['category_name']); ?>
                                </p>
                                
                                <p class="mb-2" style="font-size: 0.9rem;">
                                    <?php echo htmlspecialchars(substr($prod['description'], 0, 100)); ?>
                                    <?php if (strlen($prod['description']) > 100): ?>...<?php endif; ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong style="color: #28a745; font-size: 1.1rem;">
                                            $<?php echo number_format($prod['price'], 2); ?>
                                        </strong>
                                        <?php if ($prod['preparation_time']): ?>
                                            <br><small class="text-muted">
                                                <i class="fas fa-clock"></i> <?php echo $prod['preparation_time']; ?> min
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="btn-group">
                                        <button class="btn btn-warning btn-sm" onclick="editProduct(<?php echo htmlspecialchars(json_encode($prod)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="deleteProduct(<?php echo $prod['id']; ?>, '<?php echo htmlspecialchars($prod['name']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-dialog" style="max-width: 600px;">
        <form id="productForm" method="POST" data-validate>
            <div class="modal-header">
                <h3 class="modal-title" id="productModalTitle">Add Product</h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" id="productAction" value="create">
                <input type="hidden" name="product_id" id="productId">
                
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="price">Selling Price</label>
                        <input type="number" name="price" id="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cost_price">Cost Price</label>
                        <input type="number" name="cost_price" id="cost_price" class="form-control" step="0.01" min="0">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image">Image URL</label>
                    <input type="url" name="image" id="image" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="ingredients">Ingredients</label>
                    <textarea name="ingredients" id="ingredients" class="form-control" rows="2" placeholder="e.g., Tomato, Cheese, Basil"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="allergens">Allergens</label>
                    <input type="text" name="allergens" id="allergens" class="form-control" placeholder="e.g., Dairy, Gluten">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="preparation_time">Prep Time (minutes)</label>
                        <input type="number" name="preparation_time" id="preparation_time" class="form-control" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="product_status">Status</label>
                        <select name="status" id="product_status" class="form-control" required>
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="out_of_stock">Out of Stock</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_featured" id="is_featured" value="1">
                        Featured Product
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Product
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
            <p>Are you sure you want to delete product <strong id="deleteProductName"></strong>?</p>
            <p class="text-danger"><small>This action cannot be undone.</small></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="product_id" id="deleteProductId">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Product
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function openProductModal() {
    document.getElementById('productModalTitle').textContent = 'Add Product';
    document.getElementById('productAction').value = 'create';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    RestaurantApp.showModal(document.getElementById('productModal'));
}

function editProduct(productData) {
    document.getElementById('productModalTitle').textContent = 'Edit Product';
    document.getElementById('productAction').value = 'update';
    document.getElementById('productId').value = productData.id;
    document.getElementById('name').value = productData.name;
    document.getElementById('description').value = productData.description || '';
    document.getElementById('category_id').value = productData.category_id;
    document.getElementById('price').value = productData.price;
    document.getElementById('cost_price').value = productData.cost_price || '';
    document.getElementById('image').value = productData.image || '';
    document.getElementById('ingredients').value = productData.ingredients || '';
    document.getElementById('allergens').value = productData.allergens || '';
    document.getElementById('preparation_time').value = productData.preparation_time || '';
    document.getElementById('product_status').value = productData.status;
    document.getElementById('is_featured').checked = productData.is_featured == 1;
    
    RestaurantApp.showModal(document.getElementById('productModal'));
}

function deleteProduct(productId, productName) {
    document.getElementById('deleteProductId').value = productId;
    document.getElementById('deleteProductName').textContent = productName;
    RestaurantApp.showModal(document.getElementById('deleteModal'));
}
</script>

<?php include 'includes/footer.php'; ?>