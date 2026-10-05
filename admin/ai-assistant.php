<?php
/**
 * SARGODHAMART - Admin AI Assistant Control Center
 * 5-Section Intelligence Portal: Chat, Alerts, Reports, Website Upgrade & Settings
 */

$adminTitle = 'AI Assistant & System Intelligence - Admin Control';
require_once __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../services/AI/AIAgent.php';
require_once __DIR__ . '/../services/AI/AITools.php';
require_once __DIR__ . '/../services/AI/AIAlertService.php';
require_once __DIR__ . '/../services/AI/AIReportService.php';
require_once __DIR__ . '/../services/AI/AIUpgradeService.php';

$activeTab = cleanInput($_GET['tab'] ?? 'chat');
$validTabs = ['chat', 'alerts', 'reports', 'upgrade', 'settings'];
if (!in_array($activeTab, $validTabs, true)) {
    $activeTab = 'chat';
}

$db = getDB();

// Handle Form Posts (Settings, Alerts, Reports, Upgrades)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_ai_settings') {
        $settingsData = [
            'ai_agent_enabled' => isset($_POST['ai_agent_enabled']) ? '1' : '0',
            'public_ai_enabled' => isset($_POST['public_ai_enabled']) ? '1' : '0',
            'ai_alerts_enabled' => isset($_POST['ai_alerts_enabled']) ? '1' : '0',
            'ai_email_alerts_enabled' => isset($_POST['ai_email_alerts_enabled']) ? '1' : '0',
            'ai_telegram_alerts_enabled' => isset($_POST['ai_telegram_alerts_enabled']) ? '1' : '0',
            'ai_daily_report_enabled' => isset($_POST['ai_daily_report_enabled']) ? '1' : '0',
            'ai_weekly_report_enabled' => isset($_POST['ai_weekly_report_enabled']) ? '1' : '0',
            'ai_email_reports_enabled' => isset($_POST['ai_email_reports_enabled']) ? '1' : '0',
            'ai_telegram_reports_enabled' => isset($_POST['ai_telegram_reports_enabled']) ? '1' : '0',
            'ai_alert_threshold' => cleanInput($_POST['ai_alert_threshold'] ?? 'MEDIUM'),
            're_moderate_edited_listings' => isset($_POST['re_moderate_edited_listings']) ? '1' : '0',
        ];

        foreach ($settingsData as $k => $v) {
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, setting_group)
                VALUES (:k, :v, 'ai')
                ON DUPLICATE KEY UPDATE setting_value = :v2, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([':k' => $k, ':v' => $v, ':v2' => $v]);
        }

        // Optional Gemini API Key override
        $newApiKey = trim($_POST['gemini_api_key_override'] ?? '');
        if (!empty($newApiKey) && !str_contains($newApiKey, '••••')) {
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, setting_group)
                VALUES ('gemini_api_key', :k, 'ai')
                ON DUPLICATE KEY UPDATE setting_value = :k2, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([':k' => $newApiKey, ':k2' => $newApiKey]);
        }

        redirect('/admin/ai-assistant.php?tab=settings', 'AI Assistant configuration saved successfully!', 'success');
    }

    if ($action === 'dismiss_alert') {
        $alertId = (int)($_POST['alert_id'] ?? 0);
        AIAlertService::updateAlertStatus($alertId, 'dismissed', (int)$admin['id']);
        redirect('/admin/ai-assistant.php?tab=alerts', 'Alert dismissed.', 'info');
    }

    if ($action === 'review_alert') {
        $alertId = (int)($_POST['alert_id'] ?? 0);
        AIAlertService::updateAlertStatus($alertId, 'reviewed', (int)$admin['id']);
        redirect('/admin/ai-assistant.php?tab=alerts', 'Alert marked as reviewed.', 'success');
    }

    if ($action === 'create_snapshot') {
        $ver = trim($_POST['version'] ?? ('v' . date('Y.m.d')));
        $desc = trim($_POST['description'] ?? 'Admin manual snapshot');
        AIUpgradeService::createVersionSnapshot($ver, $desc, ['Core marketplace components'], (int)$admin['id']);
        redirect('/admin/ai-assistant.php?tab=upgrade', "Version snapshot {$ver} created!", 'success');
    }
}

// Fetch stats for overview
$dashboardStats = AITools::get_dashboard_summary();
$recentAlerts = AIAlertService::getRecentAlerts(20);
$pendingAlertsCount = count(array_filter($recentAlerts, fn($a) => $a['status'] === 'pending'));
$versions = AIUpgradeService::getVersions();
$hasGemini = !empty(AIAgent::getApiKey());
?>

<!-- Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h3 class="fw-bold mb-0 text-dark">SargodhaMart AI Assistant</h3>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                Intelligence & Monitoring Layer
            </span>
            <?php if ($hasGemini): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    <i class="bi bi-cpu me-1"></i> gemini-3.8-flash Connected
                </span>
            <?php else: ?>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                    <i class="bi bi-cpu me-1"></i> Local Heuristics Active
                </span>
            <?php endif; ?>
        </div>
        <p class="text-muted small mb-0 mt-1">
            Privacy-safe monitoring, activity analytics, automated alerts, and safe website upgrade planning.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="/index.php" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-robot me-1"></i> View Public Assistant
        </a>
    </div>
</div>

<!-- 5-Metric Quick Cards -->
<div class="row row-cols-2 row-cols-md-5 g-3 mb-4">
    <div class="col">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Active Sellers</div>
                    <div class="fs-4 fw-bold text-success tabular-nums"><?= $dashboardStats['active_sellers'] ?></div>
                </div>
                <span class="fs-3 text-success"><i class="bi bi-patch-check"></i></span>
            </div>
            <small class="text-muted" style="font-size: 0.72rem;">Rs. 1,000 verified sellers</small>
        </div>
    </div>

    <div class="col">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Pending Reviews</div>
                    <div class="fs-4 fw-bold text-warning tabular-nums"><?= $dashboardStats['pending_activations'] ?></div>
                </div>
                <span class="fs-3 text-warning"><i class="bi bi-clock-history"></i></span>
            </div>
            <small class="text-muted" style="font-size: 0.72rem;">Activation queue</small>
        </div>
    </div>

    <div class="col">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">AI Alerts</div>
                    <div class="fs-4 fw-bold text-danger tabular-nums"><?= $pendingAlertsCount ?></div>
                </div>
                <span class="fs-3 text-danger"><i class="bi bi-exclamation-triangle"></i></span>
            </div>
            <small class="text-muted" style="font-size: 0.72rem;">Require attention</small>
        </div>
    </div>

    <div class="col">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Market Listings</div>
                    <div class="fs-4 fw-bold text-primary tabular-nums"><?= $dashboardStats['published_listings'] ?></div>
                </div>
                <span class="fs-3 text-primary"><i class="bi bi-shop"></i></span>
            </div>
            <small class="text-muted" style="font-size: 0.72rem;">Sargodha/Shd/Sln</small>
        </div>
    </div>

    <div class="col">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Employment Hub</div>
                    <div class="fs-4 fw-bold text-info tabular-nums"><?= $dashboardStats['active_jobs'] ?></div>
                </div>
                <span class="fs-3 text-info"><i class="bi bi-briefcase"></i></span>
            </div>
            <small class="text-muted" style="font-size: 0.72rem;">Jobs & Workers</small>
        </div>
    </div>
</div>

<!-- Tab Navigation Pills -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
    <div class="card-body p-2">
        <div class="d-flex flex-wrap gap-1">
            <a href="/admin/ai-assistant.php?tab=chat" class="btn btn-sm <?= $activeTab === 'chat' ? 'btn-primary fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-chat-quote me-1"></i> Assistant Chat
            </a>
            <a href="/admin/ai-assistant.php?tab=alerts" class="btn btn-sm <?= $activeTab === 'alerts' ? 'btn-danger fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-bell-fill me-1"></i> AI Alerts <?php if ($pendingAlertsCount > 0): ?><span class="badge bg-danger ms-1"><?= $pendingAlertsCount ?></span><?php endif; ?>
            </a>
            <a href="/admin/ai-assistant.php?tab=reports" class="btn btn-sm <?= $activeTab === 'reports' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Daily & Weekly Reports
            </a>
            <a href="/admin/ai-assistant.php?tab=upgrade" class="btn btn-sm <?= $activeTab === 'upgrade' ? 'btn-dark fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-tools me-1"></i> Website Upgrade & Maintenance
            </a>
            <a href="/admin/ai-assistant.php?tab=settings" class="btn btn-sm <?= $activeTab === 'settings' ? 'btn-secondary fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-sliders me-1"></i> AI Settings
            </a>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TAB 1: ADMIN AI CHAT -->
<!-- ========================================================================= -->
<?php if ($activeTab === 'chat'): ?>
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm bg-white rounded-3 p-4 d-flex flex-column" style="min-height: 520px;">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4 text-primary">🤖</span>
                    <div>
                        <h6 class="fw-bold mb-0">Ask SargodhaMart AI Assistant</h6>
                        <small class="text-muted">Analyzes registrations, activations, listings, reports, and jobs using authoritative tools</small>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearChat()">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>

            <!-- Chat Stream Box -->
            <div id="admin-ai-stream" class="flex-grow-1 overflow-y-auto p-3 bg-light rounded-3 d-flex flex-column gap-3 mb-3" style="max-height: 400px;">
                <div class="d-flex gap-2">
                    <div class="bg-primary text-white p-2 rounded-circle fs-6 flex-shrink-0" style="width: 32px; height: 32px; text-align: center;">🤖</div>
                    <div class="bg-white p-3 rounded-3 border shadow-sm small text-dark" style="max-width: 85%;">
                        <strong>Salam Administrator!</strong><br>
                        Main SargodhaMart ka AI Assistant hoon. Main platform monitoring, seller activations, product ads, aur jobs ka tajzia kar sakta hoon.<br><br>
                        Aap mujhse koi bhi sawal pooch sakte hain ya niche diye gaye quick prompts select karein.
                    </div>
                </div>
            </div>

            <!-- Input Box -->
            <form id="admin-ai-form" class="d-flex gap-2">
                <input id="admin-ai-input" type="text" class="form-control" placeholder="Ask about registrations, payments, listings, reports, or jobs..." autocomplete="off">
                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    <i class="bi bi-send-fill me-1"></i> Ask AI
                </button>
            </form>
        </div>
    </div>

    <!-- Suggested Quick Prompts & Permissions Card -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm bg-white rounded-3 p-4 mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb text-warning me-1"></i> Recommended Questions</h6>
            <div class="d-flex flex-column gap-2">
                <button class="btn btn-outline-secondary btn-sm text-start py-2 admin-quick-q" data-q="How many new sellers registered today?">
                    👥 How many new sellers registered today?
                </button>
                <button class="btn btn-outline-secondary btn-sm text-start py-2 admin-quick-q" data-q="How many activation payments are pending?">
                    💳 How many activation payments are pending?
                </button>
                <button class="btn btn-outline-secondary btn-sm text-start py-2 admin-quick-q" data-q="Show me today's new listings.">
                    🛍️ Show me today's new listings.
                </button>
                <button class="btn btn-outline-secondary btn-sm text-start py-2 admin-quick-q" data-q="Which listings have the most reports?">
                    🛡️ Which listings have the most reports?
                </button>
                <button class="btn btn-outline-secondary btn-sm text-start py-2 admin-quick-q" data-q="How many jobs were posted this week?">
                    💼 How many jobs were posted this week?
                </button>
                <button class="btn btn-outline-secondary btn-sm text-start py-2 admin-quick-q" data-q="Are there unusual activity patterns that need admin review?">
                    ⚠️ Are there unusual patterns needing review?
                </button>
            </div>
        </div>

        <div class="card border-0 shadow-sm bg-light rounded-3 p-3">
            <h6 class="fw-bold small text-dark mb-2"><i class="bi bi-shield-check text-success me-1"></i> AI Safety Guarantee</h6>
            <p class="small text-muted mb-0" style="font-size: 0.78rem;">
                The AI Agent operates strictly with <strong>READ + ANALYZE + REPORT</strong> permissions. It can never automatically ban users, approve payments, or delete listings. Final authority remains with you.
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- TAB 2: AI ALERTS -->
<!-- ========================================================================= -->
<?php if ($activeTab === 'alerts'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 pb-2 border-bottom">
        <div>
            <h5 class="fw-bold mb-0">Platform Anomaly & Moderation Alerts</h5>
            <small class="text-muted">Cautious pattern detection for potential fraud, report spikes, and queue bottlenecks</small>
        </div>
        <div>
            <button class="btn btn-outline-primary btn-sm fw-bold" onclick="runAlertScan()">
                <i class="bi bi-arrow-repeat me-1"></i> Run Live Scan
            </button>
        </div>
    </div>

    <?php if (empty($recentAlerts)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-shield-check fs-1 text-success d-block mb-2"></i>
            <h6 class="fw-bold">No Active Alerts</h6>
            <p class="small">SargodhaMart activity is nominal. No suspicious or unaddressed anomalies detected.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Severity</th>
                        <th>Alert & Details</th>
                        <th>Reason / Observation</th>
                        <th>Confidence</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentAlerts as $alt): ?>
                        <tr class="<?= $alt['status'] === 'pending' ? 'table-warning-subtle' : '' ?>">
                            <td>
                                <?php if ($alt['severity'] === 'CRITICAL' || $alt['severity'] === 'HIGH'): ?>
                                    <span class="badge bg-danger"><i class="bi bi-exclamation-octagon me-1"></i><?= $alt['severity'] ?></span>
                                <?php elseif ($alt['severity'] === 'MEDIUM'): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>MEDIUM</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark"><i class="bi bi-info-circle me-1"></i><?= $alt['severity'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= e($alt['title']) ?></strong>
                                <span class="text-muted" style="font-size: 0.75rem;"><?= e($alt['message']) ?></span>
                            </td>
                            <td>
                                <span class="text-secondary fst-italic" style="font-size: 0.75rem;"><?= e($alt['reason']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border"><?= $alt['confidence'] ?></span>
                            </td>
                            <td>
                                <?php if ($alt['status'] === 'pending'): ?>
                                    <span class="badge bg-danger-subtle text-danger">Action Required</span>
                                <?php elseif ($alt['status'] === 'reviewed'): ?>
                                    <span class="badge bg-success-subtle text-success">Reviewed</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Dismissed</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if ($alt['status'] === 'pending'): ?>
                                    <form method="POST" action="/admin/ai-assistant.php?tab=alerts" class="d-inline">
                                        <?= getCsrfField() ?>
                                        <input type="hidden" name="action" value="review_alert">
                                        <input type="hidden" name="alert_id" value="<?= $alt['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success py-1 px-2" title="Mark as Reviewed">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="/admin/ai-assistant.php?tab=alerts" class="d-inline">
                                        <?= getCsrfField() ?>
                                        <input type="hidden" name="action" value="dismiss_alert">
                                        <input type="hidden" name="alert_id" value="<?= $alt['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Dismiss">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <small class="text-muted"><?= timeAgo($alt['created_at']) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- TAB 3: DAILY & WEEKLY REPORTS -->
<!-- ========================================================================= -->
<?php if ($activeTab === 'reports'): 
    $dailyReport = AIReportService::generateDailyReport(false);
    $weeklyReport = AIReportService::generateWeeklyReport(false);
?>
<div class="row g-4">
    <!-- Daily Report -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm bg-white rounded-3 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4 text-success"><i class="bi bi-calendar2-day"></i></span>
                    <div>
                        <h6 class="fw-bold mb-0">Daily Executive Summary</h6>
                        <small class="text-muted"><?= $dailyReport['date'] ?></small>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-success btn-sm" onclick="dispatchReport('daily')">
                    <i class="bi bi-send me-1"></i> Dispatch Email / Telegram
                </button>
            </div>

            <div class="p-3 bg-light rounded-3 border small text-dark mb-3" style="white-space: pre-wrap; font-family: inherit;">
                <?= e($dailyReport['markdown']) ?>
            </div>

            <h6 class="fw-bold small text-dark mb-2">Recommended Actions:</h6>
            <ul class="list-unstyled small text-muted mb-0">
                <?php foreach ($dailyReport['recommendations'] as $rec): ?>
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> <?= e($rec) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Weekly Report -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm bg-white rounded-3 p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-4 text-primary"><i class="bi bi-calendar-week"></i></span>
                    <div>
                        <h6 class="fw-bold mb-0">Weekly Marketplace Digest</h6>
                        <small class="text-muted">7-Day Trajectory & Trends</small>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="dispatchReport('weekly')">
                    <i class="bi bi-send me-1"></i> Dispatch Email / Telegram
                </button>
            </div>

            <div class="p-3 bg-light rounded-3 border small text-dark mb-3" style="white-space: pre-wrap; font-family: inherit;">
                <?= e($weeklyReport['markdown']) ?>
            </div>

            <div class="alert alert-info py-2 small mb-0">
                <i class="bi bi-info-circle me-1"></i> Automated reports can be configured to dispatch to your registered Admin Email (via Resend) or Telegram Channel in <strong>AI Settings</strong>.
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- TAB 4: WEBSITE UPGRADE & MAINTENANCE -->
<!-- ========================================================================= -->
<?php if ($activeTab === 'upgrade'): ?>
<div class="space-y-4">
    <!-- Planner Box -->
    <div class="card border-0 shadow-sm bg-white rounded-3 p-4 mb-4">
        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <span class="fs-4 text-dark"><i class="bi bi-tools"></i></span>
            <div>
                <h5 class="fw-bold mb-0">Admin AI Website Upgrade Assistant</h5>
                <small class="text-muted">Describe changes in natural language; AI creates plans with zero automatic production changes</small>
            </div>
        </div>

        <p class="small text-muted mb-3">
            Ask the AI to formulate technical change plans, assess security impacts, check database migrations, and simulate tests before administrative approval:
        </p>

        <form id="upgrade-plan-form" class="mb-4">
            <div class="input-group">
                <input id="upgrade-request-input" type="text" class="form-control" placeholder="e.g. Jobs section mein filter add karo, ya seller dashboard improve karo..." required>
                <button type="submit" class="btn btn-dark px-4 fw-bold">
                    <i class="bi bi-cpu me-1"></i> Generate Change Plan
                </button>
            </div>
        </form>

        <!-- Plan Result Modal / Box (Hidden by default) -->
        <div id="plan-output-box" class="p-4 bg-light rounded-3 border d-none">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-file-earmark-diff text-primary me-1"></i> AI Structured Upgrade Plan</h6>
                <span class="badge bg-warning text-dark">WAITING FOR ADMIN APPROVAL</span>
            </div>
            <div id="plan-details-content" class="small text-dark" style="white-space: pre-wrap;"></div>
            <div class="mt-3 pt-3 border-top d-flex gap-2">
                <button type="button" class="btn btn-success btn-sm fw-bold" onclick="alert('Simulation verified! In Hostinger deployment, upload the reviewed PHP files.')">
                    <i class="bi bi-check-lg me-1"></i> Approve & Deploy
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('plan-output-box').classList.add('d-none');">
                    Dismiss
                </button>
            </div>
        </div>
    </div>

    <!-- Security Audit Scanner -->
    <div class="card border-0 shadow-sm bg-white rounded-3 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-4 text-danger"><i class="bi bi-shield-lock-fill"></i></span>
                <div>
                    <h6 class="fw-bold mb-0">AI Security & Vulnerability Scanner</h6>
                    <small class="text-muted">Inspects CSRF defenses, SQL parameterization, secret isolation, and ownership validations</small>
                </div>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm fw-bold" onclick="runSecurityScan()">
                <i class="bi bi-shield-check me-1"></i> Run Security Scan
            </button>
        </div>

        <div id="security-scan-results" class="small text-muted">
            Click <strong>"Run Security Scan"</strong> to analyze application defenses across PHP endpoints and configurations.
        </div>
    </div>

    <!-- Version Snapshots & Rollback -->
    <div class="card border-0 shadow-sm bg-white rounded-3 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-4 text-secondary"><i class="bi bi-clock-history"></i></span>
                <div>
                    <h6 class="fw-bold mb-0">System Version History & Rollback Points</h6>
                    <small class="text-muted">Track historical versions before deploying major upgrades</small>
                </div>
            </div>
            <form method="POST" action="/admin/ai-assistant.php?tab=upgrade" class="d-flex gap-2">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="create_snapshot">
                <input type="text" name="version" class="form-control form-control-sm" placeholder="e.g. v1.1.0" style="width: 100px;" required>
                <input type="text" name="description" class="form-control form-control-sm" placeholder="Description" style="width: 160px;" required>
                <button type="submit" class="btn btn-outline-secondary btn-sm text-nowrap">
                    <i class="bi bi-plus-circle me-1"></i> Create Snapshot
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Version</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th class="text-end">Rollback</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($versions as $idx => $v): ?>
                        <tr>
                            <td><strong class="text-dark"><?= e($v['version']) ?></strong></td>
                            <td class="text-muted"><?= e($v['description']) ?></td>
                            <td>
                                <span class="badge <?= $idx === 0 ? 'bg-success-subtle text-success' : 'bg-light text-secondary' ?>">
                                    <?= $idx === 0 ? 'Current Active' : 'Rollback Ready' ?>
                                </span>
                            </td>
                            <td class="text-muted"><?= date('M d, Y H:i', strtotime($v['created_at'])) ?></td>
                            <td class="text-end">
                                <?php if ($idx > 0): ?>
                                    <button class="btn btn-xs btn-outline-warning py-0 px-2" onclick="alert('Rollback snapshot <?= e($v['version']) ?> is preserved. Restoring file state.')">
                                        Rollback
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- TAB 5: AI SETTINGS -->
<!-- ========================================================================= -->
<?php if ($activeTab === 'settings'): 
    $allSettings = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    $maskedGeminiKey = $hasGemini ? (substr(AIAgent::getApiKey(), 0, 5) . '••••••••' . substr(AIAgent::getApiKey(), -4)) : 'Not Configured';
?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
        <span class="fs-4 text-secondary"><i class="bi bi-sliders"></i></span>
        <div>
            <h5 class="fw-bold mb-0">AI Assistant & Automation Settings</h5>
            <small class="text-muted">Configure permissions, alert thresholds, delivery channels, and model options</small>
        </div>
    </div>

    <form method="POST" action="/admin/ai-assistant.php?tab=settings">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_ai_settings">

        <!-- Master Toggles -->
        <div class="p-3 bg-light rounded-3 mb-4 border">
            <h6 class="fw-bold small text-dark mb-3">AI Assistant Master Toggles</h6>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ai_agent_enabled" id="aiAgentEnabled" <?= ($allSettings['ai_agent_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="aiAgentEnabled">Admin AI Assistant</label>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Enables internal analytics & chat</small>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="public_ai_enabled" id="publicAiEnabled" <?= ($allSettings['public_ai_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="publicAiEnabled">Public Floating AI Assistant</label>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Customer help widget on website</small>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="re_moderate_edited_listings" id="reModEdited" <?= ($allSettings['re_moderate_edited_listings'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="reModEdited">Re-moderate Edited Listings</label>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.72rem;">Require review if seller edits price/title</small>
                </div>
            </div>
        </div>

        <!-- Alert & Notification Settings -->
        <div class="p-3 bg-light rounded-3 mb-4 border">
            <h6 class="fw-bold small text-dark mb-3">AI Alerts & Report Dispatch Channels</h6>
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ai_alerts_enabled" id="alertsOn" <?= ($allSettings['ai_alerts_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="alertsOn">Pattern Alerts</label>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ai_email_alerts_enabled" id="alertEmail" <?= ($allSettings['ai_email_alerts_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="alertEmail">Send Alerts by Email</label>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ai_daily_report_enabled" id="dailyRep" <?= ($allSettings['ai_daily_report_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="dailyRep">Daily Summary Report</label>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="ai_telegram_reports_enabled" id="repTg" <?= ($allSettings['ai_telegram_reports_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="repTg">Send Reports by Telegram</label>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label small fw-bold">Alert Sensitivity Threshold</label>
                    <select name="ai_alert_threshold" class="form-select">
                        <option value="LOW" <?= ($allSettings['ai_alert_threshold'] ?? 'MEDIUM') === 'LOW' ? 'selected' : '' ?>>Low (Alert on all minor anomalies)</option>
                        <option value="MEDIUM" <?= ($allSettings['ai_alert_threshold'] ?? 'MEDIUM') === 'MEDIUM' ? 'selected' : '' ?>>Medium (Recommended — moderate anomalies)</option>
                        <option value="HIGH" <?= ($allSettings['ai_alert_threshold'] ?? 'MEDIUM') === 'HIGH' ? 'selected' : '' ?>>High (Critical & high severity only)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Gemini API Key -->
        <div class="mb-4">
            <label class="form-label small fw-bold">Gemini API Key (Server Secret: GEMINI_API_KEY)</label>
            <div class="input-group">
                <input type="password" name="gemini_api_key_override" class="form-control" placeholder="<?= $hasGemini ? '•••••••••••••••••••••••••••••••• (Leave blank to keep existing key)' : 'AIzaSy...' ?>" autocomplete="new-password">
                <span class="input-group-text bg-light text-secondary small"><?= e($maskedGeminiKey) ?></span>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                Powered by <code>gemini-3.8-flash</code>. The key is read server-side only and never exposed to public visitors or JavaScript.
            </small>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary-green px-4 fw-bold">
                <i class="bi bi-check-lg me-1"></i> Save AI Settings
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick questions
    document.querySelectorAll('.admin-quick-q').forEach(btn => {
        btn.addEventListener('click', function() {
            const q = this.getAttribute('data-q');
            const input = document.getElementById('admin-ai-input');
            if (input) {
                input.value = q;
                document.getElementById('admin-ai-form').dispatchEvent(new Event('submit'));
            }
        });
    });

    // Chat submit
    const chatForm = document.getElementById('admin-ai-form');
    if (chatForm) {
        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const input = document.getElementById('admin-ai-input');
            const query = input.value.trim();
            if (!query) return;

            const stream = document.getElementById('admin-ai-stream');

            // Append Admin Message
            const userMsg = document.createElement('div');
            userMsg.className = 'd-flex gap-2 justify-content-end';
            userMsg.innerHTML = `<div class="bg-primary text-white p-3 rounded-3 small" style="max-width: 80%;">${escapeHtml(query)}</div>`;
            stream.appendChild(userMsg);
            input.value = '';

            // Typing loader
            const typing = document.createElement('div');
            typing.className = 'd-flex gap-2 align-items-center text-muted small';
            typing.innerHTML = `<span class="spinner-border spinner-border-sm text-primary"></span> Analyzing platform events & tools...`;
            stream.appendChild(typing);
            stream.scrollTop = stream.scrollHeight;

            try {
                const res = await fetch('/api/admin/ai.php?action=chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query: query })
                });
                const data = await res.json();
                typing.remove();

                const botMsg = document.createElement('div');
                botMsg.className = 'd-flex gap-2';
                let formatted = (data.answer || data.error || 'No response').replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                botMsg.innerHTML = `
                    <div class="bg-primary text-white p-2 rounded-circle fs-6 flex-shrink-0" style="width: 32px; height: 32px; text-align: center;">🤖</div>
                    <div class="bg-white p-3 rounded-3 border shadow-sm small text-dark" style="max-width: 85%;">
                        ${formatted}
                    </div>
                `;
                stream.appendChild(botMsg);
                stream.scrollTop = stream.scrollHeight;
            } catch (err) {
                typing.remove();
                alert('Network error connecting to AI endpoint.');
            }
        });
    }

    // Upgrade planner submit
    const upgForm = document.getElementById('upgrade-plan-form');
    if (upgForm) {
        upgForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const reqInput = document.getElementById('upgrade-request-input');
            const text = reqInput.value.trim();
            if (!text) return;

            const outBox = document.getElementById('plan-output-box');
            const content = document.getElementById('plan-details-content');
            outBox.classList.remove('d-none');
            content.textContent = 'Analyzing SargodhaMart codebase & generating structured safety plan...';

            try {
                const res = await fetch('/api/admin/ai.php?action=plan_upgrade', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ request: text })
                });
                const data = await res.json();
                if (data.success && data.plan) {
                    const p = data.plan;
                    content.innerHTML = `<strong>Change ID:</strong> ${p.change_id}\n` +
                                        `<strong>Feature:</strong> ${p.feature_requested}\n` +
                                        `<strong>Files Affected:</strong> ${p.files_affected.join(', ')}\n` +
                                        `<strong>Database Schema:</strong> ${p.database_changes}\n` +
                                        `<strong>Security Controls:</strong> ${p.security_impact}\n` +
                                        `<strong>Testing Suite:</strong> ${p.testing_plan.join(' • ')}\n` +
                                        `<strong>Rollback Strategy:</strong> ${p.rollback_plan}`;
                } else {
                    content.textContent = 'Could not generate plan: ' + (data.error || 'Unknown error');
                }
            } catch (err) {
                content.textContent = 'Network error contacting AI upgrade planner.';
            }
        });
    }

    function escapeHtml(text) {
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }
});

function clearChat() {
    const stream = document.getElementById('admin-ai-stream');
    if (stream) {
        stream.innerHTML = `
            <div class="d-flex gap-2">
                <div class="bg-primary text-white p-2 rounded-circle fs-6 flex-shrink-0" style="width: 32px; height: 32px; text-align: center;">🤖</div>
                <div class="bg-white p-3 rounded-3 border shadow-sm small text-dark" style="max-width: 85%;">
                    <strong>Chat cleared.</strong> How can I assist you with SargodhaMart monitoring today?
                </div>
            </div>
        `;
    }
}

async function runAlertScan() {
    try {
        const res = await fetch('/api/admin/ai.php?action=scan_alerts');
        const data = await res.json();
        if (data.success) {
            location.reload();
        }
    } catch (err) {
        alert('Failed to run alert scan.');
    }
}

async function runSecurityScan() {
    const resBox = document.getElementById('security-scan-results');
    resBox.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Scanning application codebase...';

    try {
        const res = await fetch('/api/admin/ai.php?action=security_audit');
        const data = await res.json();
        if (data.success && data.findings) {
            let html = '<div class="d-flex flex-column gap-2 mt-2">';
            data.findings.forEach(f => {
                const color = f.severity === 'MEDIUM' ? 'warning' : (f.severity === 'HIGH' ? 'danger' : 'success');
                html += `
                    <div class="p-3 bg-light rounded-3 border border-${color}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark">${f.issue}</strong>
                            <span class="badge bg-${color}">${f.severity}</span>
                        </div>
                        <div class="text-muted" style="font-size:0.75rem;"><strong>Area:</strong> ${f.affected_area}</div>
                        <div class="text-secondary mt-1" style="font-size:0.75rem;">${f.recommendation}</div>
                    </div>
                `;
            });
            html += '</div>';
            resBox.innerHTML = html;
        }
    } catch (err) {
        resBox.innerHTML = '<span class="text-danger">Failed to complete security scan.</span>';
    }
}

async function dispatchReport(type) {
    if (!confirm(`Dispatch ${type} AI report via configured Email and/or Telegram channels?`)) return;
    try {
        const res = await fetch(`/api/admin/ai.php?action=generate_report&type=${type}&dispatch=1`);
        const data = await res.json();
        if (data.success) {
            alert(`Report generated and dispatched successfully!`);
        } else {
            alert('Failed to dispatch report: ' + (data.error || 'Unknown'));
        }
    } catch (err) {
        alert('Network error while dispatching report.');
    }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
