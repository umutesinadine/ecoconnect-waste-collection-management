<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin('admin');

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name = sanitize($_POST['name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price_per_kg'] ?? 0);
    
    if ($_POST['action'] === 'add') {
        try {
            $stmt = $db->prepare("INSERT INTO waste_categories (category_name, category_description, price_per_kg) VALUES (?, ?, ?)");
            $stmt->execute([$name, $desc, $price]);
            logActivity('Category Added', "Added waste category: $name");
            setSuccess("Category added successfully.");
        } catch (PDOException $e) {
            setError("Category name already exists.");
        }
    } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE waste_categories SET category_name = ?, category_description = ?, price_per_kg = ? WHERE category_id = ?");
        $stmt->execute([$name, $desc, $price, $id]);
        logActivity('Category Updated', "Updated waste category: $name");
        setSuccess("Category updated successfully.");
    }
    header("Location: categories.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM waste_categories WHERE category_id = ?")->execute([$id]);
    setSuccess("Category deleted successfully.");
    header("Location: categories.php");
    exit();
}

$categories = $db->query("SELECT * FROM waste_categories ORDER BY category_name")->fetchAll();

$title = 'Waste Categories';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="fas fa-recycle me-2"></i>Waste Categories</h2>
            <p>Manage waste types and pricing</p>
        </div>
        <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus-circle me-1"></i> Add Category
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Price per Kg</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($categories) > 0): $c = 1; ?>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= $c++ ?></td>
                            <td><strong><?= htmlspecialchars($cat['category_name']) ?></strong></td>
                            <td><small><?= htmlspecialchars(substr($cat['category_description'] ?? '', 0, 50)) ?></small></td>
                            <td><?= formatCurrency($cat['price_per_kg']) ?></td>
                            <td><?= $cat['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                            <td class="action-btns">
                                <button class="btn btn-sm btn-info edit-btn" data-id="<?= $cat['category_id'] ?>" data-name="<?= htmlspecialchars($cat['category_name']) ?>" data-desc="<?= htmlspecialchars($cat['category_description'] ?? '') ?>" data-price="<?= $cat['price_per_kg'] ?>"><i class="fas fa-edit"></i></button>
                                <a href="?delete=<?= $cat['category_id'] ?>" class="btn btn-sm btn-danger" data-confirm="Delete this category?"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No categories yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Add Waste Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Price per Kg (RWF)</label>
                    <input type="number" class="form-control" name="price_per_kg" step="0.01" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Edit Waste Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" class="form-control" name="name" id="edit_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="edit_desc" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Price per Kg (RWF)</label>
                    <input type="number" class="form-control" name="price_per_kg" id="edit_price" step="0.01" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('edit_id').value = this.dataset.id;
        document.getElementById('edit_name').value = this.dataset.name;
        document.getElementById('edit_desc').value = this.dataset.desc;
        document.getElementById('edit_price').value = this.dataset.price;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>