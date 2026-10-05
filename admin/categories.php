<?php
/**
 * Sargodha Mandi - Category & Subcategory Administration
 */

$adminTitle = 'Categories - Admin Panel';
require_once __DIR__ . '/includes/admin_header.php';

// Handle Add / Edit / Toggle Category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');
        $desc = trim($_POST['description'] ?? '');

        if (!empty($name)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $ins = $db->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (:n, :s, :i, :d)");
            $ins->execute([':n' => $name, ':s' => $slug, ':i' => $icon, ':d' => $desc]);
            redirect('/admin/categories.php', 'New category added!', 'success');
        }
    } elseif ($action === 'add_subcategory') {
        $catId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['sub_name'] ?? '');

        if ($catId > 0 && !empty($name)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $ins = $db->prepare("INSERT INTO subcategories (category_id, name, slug) VALUES (:cid, :n, :s)");
            $ins->execute([':cid' => $catId, ':n' => $name, ':s' => $slug]);
            redirect('/admin/categories.php', 'Subcategory added!', 'success');
        }
    } elseif ($action === 'toggle_active') {
        $catId = (int)($_POST['category_id'] ?? 0);
        $curr = $db->query("SELECT is_active FROM categories WHERE id = {$catId}")->fetchColumn();
        $newVal = $curr ? 0 : 1;
        $db->prepare("UPDATE categories SET is_active = :v WHERE id = :id")->execute([':v' => $newVal, ':id' => $catId]);
        redirect('/admin/categories.php', 'Category status updated.', 'info');
    }
}

// Fetch categories with ad counts
$categories = $db->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM listings WHERE category_id = c.id) as ad_count,
           (SELECT COUNT(*) FROM subcategories WHERE category_id = c.id) as sub_count
    FROM categories c 
    ORDER BY c.sort_order ASC, c.id ASC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Marketplace Categories</h3>
        <p class="text-muted small mb-0">Organize categories and subcategories for local listings</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary-green btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-circle me-1"></i> Add Category
        </button>
        <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#addSubcatModal">
            <i class="bi bi-plus-lg me-1"></i> Add Subcategory
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Icon</th>
                    <th>Category Name</th>
                    <th>Slug</th>
                    <th>Subcategories</th>
                    <th>Total Ads</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td class="text-muted">#<?= $c['id'] ?></td>
                        <td>
                            <i class="bi <?= e($c['icon']) ?> fs-5 text-success"></i>
                        </td>
                        <td>
                            <strong><?= e($c['name']) ?></strong><br>
                            <small class="text-muted"><?= e($c['description']) ?></small>
                        </td>
                        <td><code><?= e($c['slug']) ?></code></td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= $c['sub_count'] ?> subcategories</span>
                        </td>
                        <td class="tabular-nums fw-bold"><?= $c['ad_count'] ?> ads</td>
                        <td>
                            <?php if ($c['is_active']): ?>
                                <span class="badge bg-success-subtle text-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <form method="POST" action="/admin/categories.php" class="d-inline">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn btn-outline-secondary btn-sm py-0 px-2">
                                    <?= $c['is_active'] ? 'Disable' : 'Enable' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Add New Marketplace Category</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/admin/categories.php">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="add_category">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Solar Equipment" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Bootstrap Icon Class</label>
                        <input type="text" name="icon" class="form-control" value="bi-tag" placeholder="bi-sun / bi-phone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Brief summary of items in this category"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-green btn-sm">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Subcategory Modal -->
<div class="modal fade" id="addSubcatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Add Subcategory</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/admin/categories.php">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="add_subcategory">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Parent Category *</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Subcategory Name *</label>
                        <input type="text" name="sub_name" class="form-control" placeholder="e.g. Hybrid Solar Inverters" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm">Add Subcategory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
