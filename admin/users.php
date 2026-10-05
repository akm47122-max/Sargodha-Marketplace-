<?php
/**
 * Sargodha Mandi - Admin User Management
 */

$adminTitle = 'User Management - Admin Panel';
require_once __DIR__ . '/includes/admin_header.php';

// Handle Suspend / Activate / Role Change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($userId > 0 && $userId !== $admin['id']) {
        if ($action === 'suspend') {
            $db->prepare("UPDATE users SET status = 'suspended' WHERE id = :id")->execute([':id' => $userId]);
            logAdminAction($admin['id'], 'suspend_user', 'users', $userId);
            redirect('/admin/users.php', 'User account suspended.', 'warning');
        } elseif ($action === 'activate') {
            $db->prepare("UPDATE users SET status = 'active' WHERE id = :id")->execute([':id' => $userId]);
            logAdminAction($admin['id'], 'activate_user', 'users', $userId);
            redirect('/admin/users.php', 'User account activated.', 'success');
        } elseif ($action === 'make_admin' && isSuperAdmin()) {
            $db->prepare("UPDATE users SET role = 'admin' WHERE id = :id")->execute([':id' => $userId]);
            logAdminAction($admin['id'], 'promote_admin', 'users', $userId);
            redirect('/admin/users.php', 'User promoted to Admin role.', 'success');
        }
    }
}

$search = trim($_GET['search'] ?? '');
$cityFilter = trim($_GET['city'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(full_name LIKE :s OR email LIKE :s OR mobile_number LIKE :s OR area LIKE :s)";
    $params[':s'] = "%{$search}%";
}

if (!empty($cityFilter)) {
    $where[] = "city = :c";
    $params[':c'] = $cityFilter;
}

$whereClause = implode(" AND ", $where);
$stmt = $db->prepare("
    SELECT u.*, 
           (SELECT COUNT(*) FROM listings WHERE user_id = u.id) as total_ads,
           (SELECT COUNT(*) FROM listings WHERE user_id = u.id AND status = 'Published') as active_ads
    FROM users u 
    WHERE {$whereClause}
    ORDER BY u.id DESC
");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h3 class="fw-bold mb-1">User Accounts</h3>
        <p class="text-muted small mb-0">Registered buyers & sellers across Sargodha, Shaheenabad & Sillanwali</p>
    </div>
</div>

<div class="card border-0 shadow-sm p-3 bg-white mb-4">
    <form method="GET" action="/admin/users.php" class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by name, mobile, email or area..." value="<?= e($search) ?>">
        </div>
        <div class="col-6 col-md-4">
            <select name="city" class="form-select form-select-sm">
                <option value="">All Locations</option>
                <option value="Sargodha" <?= $cityFilter === 'Sargodha' ? 'selected' : '' ?>>Sargodha</option>
                <option value="Shaheenabad" <?= $cityFilter === 'Shaheenabad' ? 'selected' : '' ?>>Shaheenabad</option>
                <option value="Sillanwali" <?= $cityFilter === 'Sillanwali' ? 'selected' : '' ?>>Sillanwali</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <button type="submit" class="btn btn-dark btn-sm w-100">Search</button>
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>User Information</th>
                    <th>Mobile (WhatsApp)</th>
                    <th>Location Hub</th>
                    <th>Role</th>
                    <th>Ads (Live / Total)</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="text-muted">#<?= $u['id'] ?></td>
                        <td>
                            <strong><?= e($u['full_name']) ?></strong><br>
                            <small class="text-muted"><?= e($u['email']) ?></small>
                        </td>
                        <td>
                            <code class="text-dark bg-light px-1 rounded"><?= e($u['mobile_number']) ?></code>
                        </td>
                        <td>
                            <?= e($u['city']) ?><br>
                            <small class="text-muted"><?= e($u['area']) ?></small>
                        </td>
                        <td>
                            <?php if ($u['role'] === 'super_admin'): ?>
                                <span class="badge bg-danger">Super Admin</span>
                            <?php elseif ($u['role'] === 'admin'): ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark border">User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong class="text-success"><?= $u['active_ads'] ?></strong> / <?= $u['total_ads'] ?>
                        </td>
                        <td>
                            <?php if ($u['status'] === 'active'): ?>
                                <span class="badge bg-success-subtle text-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger">Suspended</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted text-nowrap"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/seller.php?id=<?= $u['id'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Public Profile">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>

                            <?php if ($u['id'] !== $admin['id']): ?>
                                <?php if ($u['status'] === 'active'): ?>
                                    <form method="POST" action="/admin/users.php" class="d-inline" onsubmit="return confirm('Suspend user <?= e($u['full_name']) ?>?');">
                                        <?= getCsrfField() ?>
                                        <input type="hidden" name="action" value="suspend">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">
                                            Suspend
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="/admin/users.php" class="d-inline">
                                        <?= getCsrfField() ?>
                                        <input type="hidden" name="action" value="activate">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm py-0 px-2">
                                            Activate
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
