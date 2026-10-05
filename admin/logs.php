<?php
/**
 * SARGODHAMART - Admin Portal: Audit Logs
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$admin = currentUser();
$db = getDB();

// Filters
$actionFilter = trim($_GET['action'] ?? '');
$targetFilter = trim($_GET['target'] ?? '');

$sql = "
    SELECT al.*, u.full_name as admin_name, u.email as admin_email
    FROM admin_logs al
    LEFT JOIN users u ON al.admin_id = u.id
    WHERE 1=1
";
$params = [];

if (!empty($actionFilter)) {
    $sql .= " AND al.action = :action";
    $params[':action'] = $actionFilter;
}
if (!empty($targetFilter)) {
    $sql .= " AND al.target_type = :target";
    $params[':target'] = $targetFilter;
}

$sql .= " ORDER BY al.id DESC LIMIT 100";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

$pageTitle = 'Admin Audit Logs - ' . SITE_NAME . ' Admin';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">System Audit Logs</h3>
        <p class="text-muted small mb-0">Track all critical administrative approvals, bans, deletes, and edits</p>
    </div>
    <span class="badge bg-dark px-3 py-2 fs-6">
        <i class="bi bi-clock-history me-1"></i> Recent <?= count($logs) ?> Records
    </span>
</div>

<!-- Filter Bar -->
<div class="card border rounded-3 p-3 bg-white shadow-sm mb-4">
    <form method="GET" action="/admin/logs.php" class="row g-2 align-items-end">
        <div class="col-12 col-sm-4">
            <label class="form-label small fw-bold">Target Type</label>
            <select name="target" class="form-select form-select-sm">
                <option value="">All Targets</option>
                <option value="user" <?= $targetFilter === 'user' ? 'selected' : '' ?>>User Accounts</option>
                <option value="listing" <?= $targetFilter === 'listing' ? 'selected' : '' ?>>Product Listings</option>
                <option value="payment" <?= $targetFilter === 'payment' ? 'selected' : '' ?>>Activation Payments</option>
                <option value="report" <?= $targetFilter === 'report' ? 'selected' : '' ?>>User Reports</option>
                <option value="category" <?= $targetFilter === 'category' ? 'selected' : '' ?>>Categories</option>
            </select>
        </div>
        <div class="col-12 col-sm-4">
            <label class="form-label small fw-bold">Action</label>
            <input type="text" name="action" class="form-control form-control-sm" placeholder="e.g. approve_payment, suspend_user" value="<?= e($actionFilter) ?>">
        </div>
        <div class="col-12 col-sm-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary-green btn-sm fw-bold flex-grow-1">Filter</button>
            <a href="/admin/logs.php" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<!-- Logs Table -->
<div class="card border rounded-3 bg-white shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Target ID</th>
                    <th>Reason / Details</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No audit log entries recorded yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="text-muted">#<?= $log['id'] ?></td>
                            <td>
                                <strong><?= e($log['admin_name'] ?: 'System') ?></strong>
                                <div class="text-muted" style="font-size: 0.7rem;"><?= e($log['admin_email'] ?: '') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-<?= str_contains($log['action'], 'delete') || str_contains($log['action'], 'suspend') || str_contains($log['action'], 'reject') ? 'danger' : (str_contains($log['action'], 'approve') ? 'success' : 'primary') ?>">
                                    <?= e($log['action']) ?>
                                </span>
                            </td>
                            <td class="text-uppercase fw-bold text-secondary"><?= e($log['target_type']) ?></td>
                            <td><code>#<?= $log['target_id'] ?: '-' ?></code></td>
                            <td><?= e($log['reason'] ?: '-') ?></td>
                            <td><span class="font-monospace text-muted"><?= e($log['ip_address'] ?: '127.0.0.1') ?></span></td>
                            <td class="text-muted text-nowrap"><?= date('M j, Y h:i A', strtotime($log['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
