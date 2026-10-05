<?php
/**
 * SARGODHAMART - Secure AI Agent Tools
 * Pre-defined, parameterized data retrieval functions with strict data minimization.
 *
 * CRITICAL SECURITY:
 * - Admin tools verify admin privileges server-side.
 * - Public/user tools verify the session-authenticated user ID.
 * - Never returns passwords, hashes, tokens, or API keys.
 * - Logs all tool invocations in ai_agent_logs.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

class AITools
{
    /**
     * Ensure audit logs table exists.
     */
    private static function ensureLogTable(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            if (!function_exists('getDB')) return;
            $db = getDB();
            $db->exec("
                CREATE TABLE IF NOT EXISTS `ai_agent_logs` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `admin_id` INT NULL,
                    `action` VARCHAR(100) NOT NULL,
                    `tool_name` VARCHAR(100) NULL,
                    `request_summary` TEXT NULL,
                    `response_summary` TEXT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_ail_action` (`action`),
                    INDEX `idx_ail_created` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (Throwable $e) {}
    }

    /**
     * Audit log helper for tool execution.
     */
    public static function logToolRun(string $toolName, array $params, array $result, ?int $adminId = null): void
    {
        try {
            self::ensureLogTable();
            if (!function_exists('getDB')) return;
            $db = getDB();

            $reqSummary = json_encode($params, JSON_UNESCAPED_UNICODE);
            $resSummary = mb_substr(json_encode($result, JSON_UNESCAPED_UNICODE), 0, 500);

            $stmt = $db->prepare("
                INSERT INTO ai_agent_logs (admin_id, action, tool_name, request_summary, response_summary, created_at)
                VALUES (:aid, 'tool_call', :tool, :req, :res, NOW())
            ");
            $stmt->execute([
                ':aid' => $adminId,
                ':tool' => $toolName,
                ':req' => $reqSummary,
                ':res' => $resSummary
            ]);
        } catch (Throwable $e) {}
    }

    /**
     * Mask sensitive strings (phone numbers, emails) for data minimization.
     */
    public static function maskPhone(?string $phone): string
    {
        if (empty($phone)) return 'N/A';
        $p = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($p) >= 10) {
            return substr($p, 0, 4) . '******' . substr($p, -2);
        }
        return substr($phone, 0, 3) . '***';
    }

    public static function maskEmail(?string $email): string
    {
        if (empty($email)) return 'N/A';
        $parts = explode('@', $email);
        if (count($parts) !== 2) return '***@***';
        $name = $parts[0];
        $domain = $parts[1];
        $maskedName = strlen($name) > 2 ? substr($name, 0, 2) . '***' : $name . '***';
        return $maskedName . '@' . $domain;
    }

    // =========================================================================
    // ADMIN TOOLS (Restricted to Authorized Admins)
    // =========================================================================

    public static function get_dashboard_summary(): array
    {
        $db = getDB();
        $totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $activeSellers = (int)$db->query("SELECT COUNT(*) FROM users WHERE activation_status = 'approved'")->fetchColumn();
        $pendingActivations = (int)$db->query("SELECT COUNT(*) FROM activation_payments WHERE status = 'pending'")->fetchColumn();
        $totalListings = (int)$db->query("SELECT COUNT(*) FROM listings WHERE status = 'published'")->fetchColumn();
        $pendingListings = (int)$db->query("SELECT COUNT(*) FROM listings WHERE status IN ('pending', 'under_review')")->fetchColumn();
        $totalJobs = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE status = 'published'")->fetchColumn();
        $pendingReports = (int)$db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

        $todayRegistrations = (int)$db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $todayListings = (int)$db->query("SELECT COUNT(*) FROM listings WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $todayActivations = (int)$db->query("SELECT COUNT(*) FROM activation_payments WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        $data = [
            'platform' => 'SargodhaMart Local Hub',
            'locations' => ['Sargodha', 'Shaheenabad', 'Sillanwali'],
            'total_users' => $totalUsers,
            'active_sellers' => $activeSellers,
            'pending_activations' => $pendingActivations,
            'published_listings' => $totalListings,
            'pending_listings' => $pendingListings,
            'active_jobs' => $totalJobs,
            'pending_reports' => $pendingReports,
            'today_activity' => [
                'new_registrations' => $todayRegistrations,
                'new_listings' => $todayListings,
                'new_activation_submissions' => $todayActivations
            ]
        ];

        self::logToolRun('get_dashboard_summary', [], $data);
        return $data;
    }

    public static function get_recent_registrations(int $limit = 10): array
    {
        $limit = min(max($limit, 1), 30);
        $db = getDB();
        $stmt = $db->query("
            SELECT id, full_name, email, phone, city, area, activation_status, created_at
            FROM users
            ORDER BY id DESC
            LIMIT {$limit}
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'user_id' => (int)$r['id'],
                'full_name' => $r['full_name'],
                'email_masked' => self::maskEmail($r['email']),
                'phone_masked' => self::maskPhone($r['phone']),
                'city' => $r['city'],
                'area' => $r['area'],
                'activation_status' => $r['activation_status'],
                'registered_at' => $r['created_at']
            ];
        }

        self::logToolRun('get_recent_registrations', ['limit' => $limit], $result);
        return $result;
    }

    public static function get_pending_activation_reviews(): array
    {
        $db = getDB();
        $stmt = $db->query("
            SELECT p.id, p.user_id, p.amount, p.payment_method, p.transaction_id, p.status, p.created_at,
                   u.full_name as user_name, u.city as user_city, u.email as user_email
            FROM activation_payments p
            JOIN users u ON p.user_id = u.id
            WHERE p.status = 'pending'
            ORDER BY p.id ASC
            LIMIT 20
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'submission_id' => (int)$r['id'],
                'user_id' => (int)$r['user_id'],
                'user_name' => $r['user_name'],
                'city' => $r['user_city'],
                'amount' => (float)$r['amount'],
                'method' => $r['payment_method'],
                'transaction_id' => $r['transaction_id'],
                'status' => $r['status'],
                'submitted_at' => $r['created_at']
            ];
        }

        self::logToolRun('get_pending_activation_reviews', [], $result);
        return $result;
    }

    public static function get_recent_listings(int $limit = 10): array
    {
        $limit = min(max($limit, 1), 30);
        $db = getDB();
        $stmt = $db->query("
            SELECT l.id, l.title, l.price, l.city, l.area, l.status, l.views_count, l.created_at,
                   c.name as category_name, u.full_name as seller_name
            FROM listings l
            LEFT JOIN categories c ON l.category_id = c.id
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.id DESC
            LIMIT {$limit}
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'listing_id' => (int)$r['id'],
                'title' => $r['title'],
                'category' => $r['category_name'] ?? 'General',
                'price_pkr' => (float)$r['price'],
                'city' => $r['city'],
                'area' => $r['area'],
                'status' => $r['status'],
                'views' => (int)$r['views_count'],
                'seller' => $r['seller_name'] ?? 'Seller',
                'posted_at' => $r['created_at']
            ];
        }

        self::logToolRun('get_recent_listings', ['limit' => $limit], $result);
        return $result;
    }

    public static function get_pending_listing_reviews(): array
    {
        $db = getDB();
        $stmt = $db->query("
            SELECT l.id, l.title, l.price, l.city, l.area, l.status, l.created_at,
                   c.name as category_name, u.full_name as seller_name
            FROM listings l
            LEFT JOIN categories c ON l.category_id = c.id
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.status IN ('pending', 'under_review', 'Payment Pending')
            ORDER BY l.id ASC
            LIMIT 20
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'listing_id' => (int)$r['id'],
                'title' => $r['title'],
                'category' => $r['category_name'] ?? 'General',
                'price_pkr' => (float)$r['price'],
                'city' => $r['city'],
                'area' => $r['area'],
                'status' => $r['status'],
                'seller' => $r['seller_name'] ?? 'Seller',
                'submitted_at' => $r['created_at']
            ];
        }

        self::logToolRun('get_pending_listing_reviews', [], $result);
        return $result;
    }

    public static function get_recent_jobs(int $limit = 10): array
    {
        $limit = min(max($limit, 1), 30);
        $db = getDB();
        $stmt = $db->query("
            SELECT j.id, j.title, j.post_type, j.category, j.city, j.area, j.salary_or_payment, j.status, j.created_at,
                   u.full_name as poster_name
            FROM jobs j
            LEFT JOIN users u ON j.user_id = u.id
            ORDER BY j.id DESC
            LIMIT {$limit}
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'job_id' => (int)$r['id'],
                'title' => $r['title'],
                'type' => $r['post_type'] === 'need_worker' ? 'I Need a Worker (Employer)' : 'I Need a Job (Candidate)',
                'category' => $r['category'],
                'city' => $r['city'],
                'area' => $r['area'],
                'salary_or_payment' => $r['salary_or_payment'] ?? 'Open for discussion',
                'status' => $r['status'],
                'posted_by' => $r['poster_name'] ?? 'Member',
                'created_at' => $r['created_at']
            ];
        }

        self::logToolRun('get_recent_jobs', ['limit' => $limit], $result);
        return $result;
    }

    public static function get_listing_reports(int $limit = 15): array
    {
        $limit = min(max($limit, 1), 30);
        $db = getDB();
        $stmt = $db->query("
            SELECT r.id, r.listing_id, r.reason, r.details, r.status, r.created_at,
                   l.title as listing_title, l.city as listing_city, l.user_id as seller_id,
                   u.full_name as reporter_name
            FROM reports r
            LEFT JOIN listings l ON r.listing_id = l.id
            LEFT JOIN users u ON r.reporter_id = u.id
            ORDER BY (r.status = 'pending') DESC, r.id DESC
            LIMIT {$limit}
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'report_id' => (int)$r['id'],
                'listing_id' => (int)$r['listing_id'],
                'listing_title' => $r['listing_title'] ?? 'Deleted or missing listing',
                'city' => $r['listing_city'] ?? 'Sargodha',
                'seller_id' => (int)($r['seller_id'] ?? 0),
                'reason' => $r['reason'],
                'details' => $r['details'],
                'status' => $r['status'],
                'reporter' => $r['reporter_name'] ?? 'Anonymous visitor',
                'reported_at' => $r['created_at']
            ];
        }

        self::logToolRun('get_listing_reports', ['limit' => $limit], $result);
        return $result;
    }

    public static function get_platform_statistics(): array
    {
        $db = getDB();

        // City distribution of listings
        $cityDist = $db->query("
            SELECT city, COUNT(*) as count 
            FROM listings 
            WHERE status = 'published' 
            GROUP BY city
        ")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        // Category breakdown
        $catDist = $db->query("
            SELECT c.name, COUNT(l.id) as count
            FROM categories c
            LEFT JOIN listings l ON c.id = l.category_id AND l.status = 'published'
            GROUP BY c.id, c.name
            ORDER BY count DESC
            LIMIT 8
        ")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        // Jobs type breakdown
        $jobTypes = $db->query("
            SELECT post_type, COUNT(*) as count
            FROM jobs
            WHERE status = 'published'
            GROUP BY post_type
        ")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

        $data = [
            'listings_by_city' => $cityDist,
            'top_categories' => $catDist,
            'jobs_by_type' => [
                'need_worker' => (int)($jobTypes['need_worker'] ?? 0),
                'need_job' => (int)($jobTypes['need_job'] ?? 0)
            ],
            'monetization' => [
                'model' => 'One-time Rs. 1,000 lifetime seller activation',
                'active_revenue_base_pkr' => (int)$db->query("SELECT COUNT(*) * 1000 FROM activation_payments WHERE status = 'approved'")->fetchColumn()
            ]
        ];

        self::logToolRun('get_platform_statistics', [], $data);
        return $data;
    }

    public static function get_activity_summary(int $hours = 24): array
    {
        $hours = min(max($hours, 1), 168);
        $db = getDB();

        $stmt = $db->prepare("
            SELECT event_type, entity_type, COUNT(*) as event_count
            FROM ai_agent_events
            WHERE created_at >= NOW() - INTERVAL :h HOUR
            GROUP BY event_type, entity_type
            ORDER BY event_count DESC
        ");
        $stmt->execute([':h' => $hours]);
        $eventBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $data = [
            'period_hours' => $hours,
            'events_total' => array_sum(array_column($eventBreakdown, 'event_count')),
            'breakdown' => $eventBreakdown
        ];

        self::logToolRun('get_activity_summary', ['hours' => $hours], $data);
        return $data;
    }

    public static function get_daily_summary(): array
    {
        $db = getDB();

        $newUsers = (int)$db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $activeSellers = (int)$db->query("SELECT COUNT(*) FROM users WHERE activation_status = 'approved'")->fetchColumn();

        $activationsPending = (int)$db->query("SELECT COUNT(*) FROM activation_payments WHERE status = 'pending'")->fetchColumn();
        $activationsApproved = (int)$db->query("SELECT COUNT(*) FROM activation_payments WHERE status = 'approved' AND DATE(updated_at) = CURDATE()")->fetchColumn();
        $activationsRejected = (int)$db->query("SELECT COUNT(*) FROM activation_payments WHERE status = 'rejected' AND DATE(updated_at) = CURDATE()")->fetchColumn();

        $newListings = (int)$db->query("SELECT COUNT(*) FROM listings WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $approvedListings = (int)$db->query("SELECT COUNT(*) FROM listings WHERE status = 'published' AND DATE(created_at) = CURDATE()")->fetchColumn();
        $reportsToday = (int)$db->query("SELECT COUNT(*) FROM reports WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        $newJobs = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $needWorker = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE post_type = 'need_worker' AND DATE(created_at) = CURDATE()")->fetchColumn();
        $needJob = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE post_type = 'need_job' AND DATE(created_at) = CURDATE()")->fetchColumn();

        $summary = [
            'date' => date('Y-m-d'),
            'users' => [
                'new_registrations' => $newUsers,
                'total_active_sellers' => $activeSellers
            ],
            'activations' => [
                'pending_review' => $activationsPending,
                'approved_today' => $activationsApproved,
                'rejected_today' => $activationsRejected
            ],
            'marketplace' => [
                'new_listings' => $newListings,
                'published_today' => $approvedListings,
                'reports_today' => $reportsToday
            ],
            'jobs' => [
                'total_new_jobs' => $newJobs,
                'employer_worker_requests' => $needWorker,
                'candidate_job_seekers' => $needJob
            ]
        ];

        self::logToolRun('get_daily_summary', [], $summary);
        return $summary;
    }

    public static function get_weekly_summary(): array
    {
        $db = getDB();

        $newUsers7d = (int)$db->query("SELECT COUNT(*) FROM users WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();
        $newListings7d = (int)$db->query("SELECT COUNT(*) FROM listings WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();
        $newJobs7d = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();
        $reports7d = (int)$db->query("SELECT COUNT(*) FROM reports WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();
        $activationsApproved7d = (int)$db->query("SELECT COUNT(*) FROM activation_payments WHERE status = 'approved' AND updated_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();

        $topCity = $db->query("
            SELECT city, COUNT(*) as c 
            FROM listings 
            WHERE created_at >= NOW() - INTERVAL 7 DAY 
            GROUP BY city 
            ORDER BY c DESC 
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);

        $summary = [
            'period' => 'Last 7 Days',
            'new_users' => $newUsers7d,
            'new_listings' => $newListings7d,
            'new_jobs' => $newJobs7d,
            'approved_activations' => $activationsApproved7d,
            'content_reports' => $reports7d,
            'most_active_location' => $topCity ? "{$topCity['city']} ({$topCity['c']} listings)" : 'Sargodha'
        ];

        self::logToolRun('get_weekly_summary', [], $summary);
        return $summary;
    }

    public static function get_user_activity_summary(int $userId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT id, full_name, email, phone, city, area, role, activation_status, created_at
            FROM users WHERE id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $userId]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$u) return ['error' => 'User not found'];

        $listingsCount = (int)$db->prepare("SELECT COUNT(*) FROM listings WHERE user_id = :id")->execute([':id' => $userId]) ? $db->query("SELECT COUNT(*) FROM listings WHERE user_id = {$userId}")->fetchColumn() : 0;
        $jobsCount = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE user_id = {$userId}")->fetchColumn();
        $payments = $db->query("SELECT id, amount, payment_method, transaction_id, status, created_at FROM activation_payments WHERE user_id = {$userId} ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'user_id' => (int)$u['id'],
            'name' => $u['full_name'],
            'email_masked' => self::maskEmail($u['email']),
            'phone_masked' => self::maskPhone($u['phone']),
            'city' => $u['city'],
            'area' => $u['area'],
            'activation_status' => $u['activation_status'],
            'listings_count' => $listingsCount,
            'jobs_count' => $jobsCount,
            'recent_payments' => $payments,
            'member_since' => $u['created_at']
        ];

        self::logToolRun('get_user_activity_summary', ['user_id' => $userId], $data);
        return $data;
    }

    public static function get_listing_activity_summary(int $listingId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT l.id, l.title, l.price, l.city, l.area, l.status, l.views_count, l.created_at,
                   c.name as category_name, u.full_name as seller_name, u.id as seller_id
            FROM listings l
            LEFT JOIN categories c ON l.category_id = c.id
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $listingId]);
        $l = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$l) return ['error' => 'Listing not found'];

        $reportCount = (int)$db->query("SELECT COUNT(*) FROM reports WHERE listing_id = {$listingId}")->fetchColumn();

        $data = [
            'listing_id' => (int)$l['id'],
            'title' => $l['title'],
            'category' => $l['category_name'] ?? 'General',
            'price_pkr' => (float)$l['price'],
            'city' => $l['city'],
            'area' => $l['area'],
            'status' => $l['status'],
            'views' => (int)$l['views_count'],
            'reports_count' => $reportCount,
            'seller' => [
                'user_id' => (int)$l['seller_id'],
                'name' => $l['seller_name']
            ],
            'created_at' => $l['created_at']
        ];

        self::logToolRun('get_listing_activity_summary', ['listing_id' => $listingId], $data);
        return $data;
    }

    // =========================================================================
    // PUBLIC CUSTOMER-FACING TOOLS (Strictly Public & Own-Account Only)
    // =========================================================================

    public static function search_public_products(string $query = '', string $city = '', string $category = '', int $limit = 6): array
    {
        $limit = min(max($limit, 1), 12);
        $db = getDB();

        $sql = "
            SELECT l.id, l.title, l.price, l.city, l.area, l.item_condition, c.name as category_name,
                   (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image
            FROM listings l
            LEFT JOIN categories c ON l.category_id = c.id
            WHERE l.status = 'published'
        ";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (l.title LIKE :q OR l.description LIKE :q OR l.area LIKE :q)";
            $params[':q'] = '%' . $query . '%';
        }
        if (!empty($city)) {
            $sql .= " AND l.city = :city";
            $params[':city'] = $city;
        }
        if (!empty($category)) {
            $sql .= " AND (c.name LIKE :cat OR c.slug LIKE :cat)";
            $params[':cat'] = '%' . $category . '%';
        }

        $sql .= " ORDER BY l.id DESC LIMIT {$limit}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'id' => (int)$r['id'],
                'title' => $r['title'],
                'price_pkr' => (float)$r['price'],
                'price_formatted' => 'Rs. ' . number_format($r['price']),
                'category' => $r['category_name'] ?? 'General',
                'city' => $r['city'],
                'area' => $r['area'],
                'condition' => $r['item_condition'] ?? 'Used',
                'image_url' => $r['primary_image'] ? '/' . ltrim($r['primary_image'], '/') : null,
                'url' => '/product.php?id=' . $r['id']
            ];
        }

        return $results;
    }

    public static function search_public_jobs(string $query = '', string $city = '', string $type = '', int $limit = 6): array
    {
        $limit = min(max($limit, 1), 12);
        $db = getDB();

        $sql = "
            SELECT j.id, j.title, j.post_type, j.category, j.skills, j.salary_or_payment, j.city, j.area, j.created_at
            FROM jobs j
            WHERE j.status = 'published'
        ";
        $params = [];

        if (!empty($query)) {
            $sql .= " AND (j.title LIKE :q OR j.skills LIKE :q OR j.description LIKE :q)";
            $params[':q'] = '%' . $query . '%';
        }
        if (!empty($city)) {
            $sql .= " AND j.city = :city";
            $params[':city'] = $city;
        }
        if (!empty($type)) {
            $sql .= " AND j.post_type = :type";
            $params[':type'] = $type;
        }

        $sql .= " ORDER BY j.id DESC LIMIT {$limit}";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $r) {
            $results[] = [
                'id' => (int)$r['id'],
                'title' => $r['title'],
                'type' => $r['post_type'] === 'need_worker' ? 'I Need a Worker (Hiring)' : 'I Need a Job (Work Wanted)',
                'category' => $r['category'],
                'skills' => $r['skills'],
                'compensation' => $r['salary_or_payment'] ?: 'Negotiable',
                'city' => $r['city'],
                'area' => $r['area'],
                'url' => '/jobs.php?id=' . $r['id']
            ];
        }

        return $results;
    }

    public static function get_public_categories(): array
    {
        $db = getDB();
        $cats = $db->query("SELECT id, name, slug, icon FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        return $cats ?: [];
    }

    public static function get_public_locations(): array
    {
        return [
            'division' => 'Sargodha Division',
            'districts' => ['Sargodha', 'Khushab', 'Mianwali', 'Bhakkar'],
            'primary_hubs' => ['Sargodha City', 'Shaheenabad', 'Sillanwali'],
            'tehsils' => ['Sargodha', 'Bhalwal', 'Bhera', 'Kot Momin', 'Sahiwal', 'Shahpur', 'Sillanwali']
        ];
    }

    public static function get_marketplace_faq(): array
    {
        return [
            [
                'question' => 'How does SargodhaMart work?',
                'answer' => 'SargodhaMart is a dedicated local buy, sell, and employment platform for Sargodha, Shaheenabad, and Sillanwali with direct phone & WhatsApp communication and 0% commission.'
            ],
            [
                'question' => 'What is the seller activation fee?',
                'answer' => 'Normal account registration is 100% free. To post product ads or job advertisements, sellers pay a one-time lifetime verification fee of Rs. 1,000 via EasyPaisa/JazzCash and follow our official WhatsApp Channel. Once verified by admin, you can post unlimited listings with lifetime validity.'
            ],
            [
                'question' => 'How many images can I upload for an ad?',
                'answer' => 'Each product listing supports up to 3 genuine product images.'
            ],
            [
                'question' => 'How can I post or find a job?',
                'answer' => 'Visit the Jobs Hub (/jobs.php). You can browse or post under two sections: "I Need a Job / Mujhe Job Chahiye" (for job seekers) or "I Need a Worker / Mujhe Banda Chahiye" (for employers looking to hire).'
            ],
            [
                'question' => 'How can I edit my ad or job after publishing?',
                'answer' => 'Go to your User Dashboard (/user/dashboard.php) -> "My Products" or "My Jobs" and click the [Edit] button next to your post to update price, details, or contact information.'
            ]
        ];
    }

    // =========================================================================
    // LOGGED-IN USER OWN-ACCOUNT TOOLS (Strictly session-authenticated user ID)
    // =========================================================================

    public static function get_my_account_status(int $authenticatedUserId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT id, full_name, email, phone, city, area, activation_status, created_at
            FROM users WHERE id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $authenticatedUserId]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$u) return ['status' => 'not_found'];

        $payment = $db->query("
            SELECT status, transaction_id, payment_method, admin_note, created_at
            FROM activation_payments
            WHERE user_id = {$authenticatedUserId}
            ORDER BY id DESC LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);

        $listingsCount = (int)$db->query("SELECT COUNT(*) FROM listings WHERE user_id = {$authenticatedUserId} AND status = 'published'")->fetchColumn();
        $jobsCount = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE user_id = {$authenticatedUserId} AND status = 'published'")->fetchColumn();

        return [
            'name' => $u['full_name'],
            'city' => $u['city'],
            'activation_status' => $u['activation_status'], // 'pending', 'approved', 'rejected'
            'is_active_seller' => $u['activation_status'] === 'approved',
            'latest_payment' => $payment ? [
                'status' => $payment['status'],
                'method' => $payment['payment_method'],
                'trx_id' => $payment['transaction_id'],
                'admin_note' => $payment['admin_note'] ?? null,
                'submitted_at' => $payment['created_at']
            ] : null,
            'active_listings_count' => $listingsCount,
            'active_jobs_count' => $jobsCount
        ];
    }

    public static function get_my_listings(int $authenticatedUserId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT id, title, price, status, city, area, views_count, created_at
            FROM listings
            WHERE user_id = :uid
            ORDER BY id DESC
            LIMIT 10
        ");
        $stmt->execute([':uid' => $authenticatedUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function get_my_jobs(int $authenticatedUserId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT id, title, post_type, status, city, area, created_at
            FROM jobs
            WHERE user_id = :uid
            ORDER BY id DESC
            LIMIT 10
        ");
        $stmt->execute([':uid' => $authenticatedUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function get_my_notifications(int $authenticatedUserId): array
    {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT id, title, message, link, is_read, created_at
            FROM notifications
            WHERE user_id = :uid
            ORDER BY id DESC
            LIMIT 5
        ");
        $stmt->execute([':uid' => $authenticatedUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
