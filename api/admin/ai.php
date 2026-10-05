<?php
/**
 * SARGODHAMART - Admin AI Assistant API Endpoint
 * Strictly protected: Requires authenticated admin session.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../services/AI/AIAgent.php';
require_once __DIR__ . '/../../services/AI/AITools.php';
require_once __DIR__ . '/../../services/AI/AIAlertService.php';
require_once __DIR__ . '/../../services/AI/AIReportService.php';
require_once __DIR__ . '/../../services/AI/AIUpgradeService.php';

// Strict Admin Authorization Check
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Forbidden: Administrative authorization required to access SargodhaMart AI Agent.'
    ]);
    exit;
}

$user = currentUser();
$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

try {
    // 1. Interactive Admin AI Chat
    if ($action === 'chat') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $query = trim($input['query'] ?? '');
        if (empty($query)) {
            echo json_encode(['success' => false, 'error' => 'Query cannot be empty.']);
            exit;
        }

        $res = AIAgent::askAdminAssistant($query, (int)$user['id']);
        echo json_encode($res);
        exit;
    }

    // 2. Daily & Weekly Report Generation
    if ($action === 'generate_report') {
        $type = $_GET['type'] ?? 'daily';
        $sendNotifs = isset($_GET['dispatch']) && $_GET['dispatch'] === '1';

        if ($type === 'weekly') {
            $rep = AIReportService::generateWeeklyReport($sendNotifs);
        } else {
            $rep = AIReportService::generateDailyReport($sendNotifs);
        }

        echo json_encode([
            'success' => true,
            'report' => $rep
        ]);
        exit;
    }

    // 3. Scan & Refresh Alerts
    if ($action === 'scan_alerts') {
        $newAlerts = AIAlertService::runPatternScan();
        $recentAlerts = AIAlertService::getRecentAlerts(25);
        echo json_encode([
            'success' => true,
            'new_alerts_count' => count($newAlerts),
            'alerts' => $recentAlerts
        ]);
        exit;
    }

    // 4. Update Alert Status (Review / Dismiss)
    if ($action === 'update_alert') {
        $alertId = (int)($_POST['alert_id'] ?? 0);
        $status = $_POST['status'] ?? 'reviewed';
        $success = AIAlertService::updateAlertStatus($alertId, $status, (int)$user['id']);
        echo json_encode([
            'success' => $success,
            'alert_id' => $alertId,
            'status' => $status
        ]);
        exit;
    }

    // 5. Plan Website Upgrade
    if ($action === 'plan_upgrade') {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $req = trim($input['request'] ?? '');
        if (empty($req)) {
            echo json_encode(['success' => false, 'error' => 'Upgrade request cannot be empty.']);
            exit;
        }

        $plan = AIUpgradeService::planUpgrade($req, (int)$user['id']);
        echo json_encode([
            'success' => true,
            'plan' => $plan
        ]);
        exit;
    }

    // 6. Security Audit Scanner
    if ($action === 'security_audit') {
        $audit = AIUpgradeService::runSecurityAudit();
        echo json_encode([
            'success' => true,
            'findings' => $audit,
            'scan_time' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // 7. Version Rollback / Snapshot
    if ($action === 'create_version') {
        $ver = trim($_POST['version'] ?? ('v' . date('Y.m.d')));
        $desc = trim($_POST['description'] ?? 'Manual Admin Snapshot');
        $id = AIUpgradeService::createVersionSnapshot($ver, $desc, ['All modified application files'], (int)$user['id']);
        echo json_encode([
            'success' => true,
            'version_id' => $id,
            'version' => $ver
        ]);
        exit;
    }

    // 8. General AI Agent Status & Tools List
    echo json_encode([
        'success' => true,
        'agent_name' => 'SargodhaMart AI Assistant',
        'model' => 'gemini-3.8-flash',
        'gemini_configured' => !empty(AIAgent::getApiKey()),
        'dashboard' => AITools::get_dashboard_summary(),
        'tools' => [
            'get_dashboard_summary',
            'get_recent_registrations',
            'get_pending_activation_reviews',
            'get_recent_listings',
            'get_pending_listing_reviews',
            'get_recent_jobs',
            'get_listing_reports',
            'get_platform_statistics',
            'get_activity_summary',
            'get_daily_summary',
            'get_weekly_summary'
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'AI Service Exception: ' . $e->getMessage()
    ]);
}
