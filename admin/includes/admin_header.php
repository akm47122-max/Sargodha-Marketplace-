<?php
/**
 * Admin Panel Header
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/admin_check.php';

$admin = currentUser();
$db = getDB();

// Global stats for badge counters
$pendingPaymentsCount = (int)$db->query("SELECT COUNT(*) FROM listing_payments WHERE status = 'pending'")->fetchColumn();
$pendingReportsCount = (int)$db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();
$flash = getFlash();

$adminTitle = $adminTitle ?? 'Admin Control Center - ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle) ?></title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .admin-sidebar {
            width: 250px;
            min-height: calc(100vh - 56px);
            background-color: #0f172a;
            color: #94a3b8;
        }
        .admin-nav-link {
            color: #cbd5e1;
            padding: 0.65rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.88rem;
            margin-bottom: 2px;
        }
        .admin-nav-link:hover, .admin-nav-link.active {
            background-color: #1e293b;
            color: #4ade80;
        }
    </style>
</head>
<body class="bg-light">

<!-- Admin Top Navbar -->
<nav class="navbar navbar-expand navbar-dark bg-dark px-3 sticky-top border-bottom border-secondary">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/admin/index.php">
        <span class="text-success fs-4"><i class="bi bi-shield-lock-fill"></i></span>
        <span>Sargodha Mandi <span class="badge bg-danger fs-6 fw-normal ms-1">Admin</span></span>
    </a>

    <div class="ms-auto d-flex align-items-center gap-3">
        <a href="/index.php" target="_blank" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> View Live Site
        </a>
        <div class="dropdown">
            <button class="btn btn-sm btn-dark dropdown-toggle text-white d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle text-success"></i>
                <span><?= e($admin['full_name']) ?> (<?= e($admin['role']) ?>)</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item" href="/user/dashboard.php">User Dashboard</a></li>
                <li><a class="dropdown-item" href="/admin/settings.php">Site Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="d-flex">
    <!-- Admin Sidebar -->
    <div class="admin-sidebar p-3 d-none d-md-block flex-shrink-0">
        <div class="small text-uppercase tracking-wider text-secondary fw-bold px-2 mb-2">Management</div>
        <nav class="nav flex-column">
            <a href="/admin/index.php" class="admin-nav-link">
                <span><i class="bi bi-speedometer2 me-2"></i> Dashboard</span>
            </a>
            <a href="/admin/payments.php" class="admin-nav-link">
                <span><i class="bi bi-cash-stack me-2"></i> Payments</span>
                <?php if ($pendingPaymentsCount > 0): ?>
                    <span class="badge bg-warning text-dark"><?= $pendingPaymentsCount ?></span>
                <?php endif; ?>
            </a>
            <a href="/admin/listings.php" class="admin-nav-link">
                <span><i class="bi bi-card-checklist me-2"></i> Listings</span>
            </a>
            <a href="/admin/users.php" class="admin-nav-link">
                <span><i class="bi bi-people me-2"></i> Users</span>
            </a>
            <a href="/admin/categories.php" class="admin-nav-link">
                <span><i class="bi bi-grid me-2"></i> Categories</span>
            </a>
            <a href="/admin/reports.php" class="admin-nav-link">
                <span><i class="bi bi-flag me-2"></i> Reports</span>
                <?php if ($pendingReportsCount > 0): ?>
                    <span class="badge bg-danger"><?= $pendingReportsCount ?></span>
                <?php endif; ?>
            </a>
            <a href="/admin/reviews.php" class="admin-nav-link">
                <span><i class="bi bi-star me-2"></i> Reviews</span>
            </a>
            <a href="/admin/ai-assistant.php" class="admin-nav-link text-primary fw-bold">
                <span><i class="bi bi-robot me-2"></i> AI Assistant</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;">LIVE</span>
            </a>

            <div class="small text-uppercase tracking-wider text-secondary fw-bold px-2 mt-4 mb-2">Configuration</div>
            <a href="/admin/settings.php" class="admin-nav-link">
                <span><i class="bi bi-sliders me-2"></i> Fees & Settings</span>
            </a>
            <a href="/logout.php" class="admin-nav-link text-danger mt-3">
                <span><i class="bi bi-box-arrow-right me-2"></i> Logout</span>
            </a>
        </nav>
    </div>

    <!-- Main Content Canvas -->
    <div class="flex-grow-1 p-3 p-md-4 overflow-x-hidden">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm mb-4" role="alert">
                <i class="bi bi-info-circle me-2"></i> <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
