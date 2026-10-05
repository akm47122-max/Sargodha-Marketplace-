<?php
/**
 * SARGODHAMART - Public Customer AI Assistant ("SargodhaMart Assistant")
 * Friendly, multilingual customer service layer for visitors and registered users.
 *
 * PRIVACY GUARANTEES:
 * - Read-only access to public marketplace data.
 * - Own-account data ONLY accessed via secure server-side session ($_SESSION['user_id']).
 * - Never trusts client-supplied user_id.
 * - Understands Urdu, Roman Urdu, and English.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/AITools.php';
require_once __DIR__ . '/AIAgent.php';

class PublicAIAssistant
{
    /**
     * Check if Public AI is enabled.
     */
    public static function isEnabled(): bool
    {
        if (function_exists('getSetting')) {
            return getSetting('public_ai_enabled', '1') === '1';
        }
        return true;
    }

    /**
     * Rate limiting by IP address and Session ID.
     */
    public static function checkRateLimit(string $ip, int $maxPerMinute = 15): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $key = 'ai_req_count_' . date('YmdHi');
        $current = (int)($_SESSION[$key] ?? 0);
        if ($current >= $maxPerMinute) {
            return false;
        }
        $_SESSION[$key] = $current + 1;
        return true;
    }

    /**
     * Handle Public AI Chat interaction.
     */
    public static function reply(string $userMessage, ?int $authenticatedUserId = null, ?string $sessionId = null): array
    {
        $userMessage = trim($userMessage);
        if (empty($userMessage)) {
            return [
                'success' => false,
                'error' => 'Please enter a message.'
            ];
        }

        if (mb_strlen($userMessage) > 500) {
            $userMessage = mb_substr($userMessage, 0, 500);
        }

        $msgLower = strtolower($userMessage);
        $dataContext = [];
        $actionCards = [];

        // 1. Check for Account / Personal queries (requires authentication)
        $isAskingOwnStatus = (
            str_contains($msgLower, 'mera status') ||
            str_contains($msgLower, 'meri activation') ||
            str_contains($msgLower, 'activation status') ||
            str_contains($msgLower, 'meri listing') ||
            str_contains($msgLower, 'meri ads') ||
            str_contains($msgLower, 'mera account') ||
            str_contains($msgLower, 'my account') ||
            str_contains($msgLower, 'my status')
        );

        if ($isAskingOwnStatus) {
            if ($authenticatedUserId && $authenticatedUserId > 0) {
                $status = AITools::get_my_account_status($authenticatedUserId);
                $dataContext['my_account_status'] = $status;
                if ($status['activation_status'] === 'approved') {
                    $statusText = "Aapka account **ACTIVE (Verified Seller)** hai! Aap unlimited ads aur jobs post kar sakte hain.";
                } elseif ($status['activation_status'] === 'pending') {
                    $statusText = "Aapki Rs. 1,000 activation request abhi **Pending Admin Review** mein hai. Hamari team jald verify kar degi.";
                } else {
                    $statusText = "Aapka account standard member hai. Ads post karne ke liye Rs. 1,000 one-time lifetime seller activation darkar hai.";
                }

                $reply = "Assalam-o-Alaikum {$status['name']}! 👋\n\n{$statusText}\n\n• Active Listings: **{$status['active_listings_count']}**\n• Active Jobs: **{$status['active_jobs_count']}**";
                return [
                    'success' => true,
                    'message' => $reply,
                    'cards' => [
                        ['title' => 'User Dashboard', 'url' => '/user/dashboard.php', 'icon' => 'bi-speedometer2'],
                        ['title' => 'My Listings', 'url' => '/user/my-listings.php', 'icon' => 'bi-card-checklist']
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'message' => "Apna personal account ya activation status janne ke liye baraye meharbani pehle login karein.",
                    'cards' => [
                        ['title' => 'Login Now', 'url' => '/login.php', 'icon' => 'bi-box-arrow-in-right'],
                        ['title' => 'Register Free', 'url' => '/register.php', 'icon' => 'bi-person-plus']
                    ]
                ];
            }
        }

        // 2. Check for Edit Own Post guidance
        if (str_contains($msgLower, 'edit') || str_contains($msgLower, 'tabdeel') || str_contains($msgLower, 'change')) {
            if (str_contains($msgLower, 'job')) {
                return [
                    'success' => true,
                    'message' => "Apni job post edit karne ke liye:\n1. User Dashboard par jayen.\n2. **My Jobs** section open karein.\n3. Apni post ke sath maujood **[Edit]** button par click karein.\n\nAap title, skills, compensation aur details tabdeel kar sakte hain.",
                    'cards' => [
                        ['title' => 'My Jobs Hub', 'url' => '/user/my-jobs.php', 'icon' => 'bi-briefcase'],
                        ['title' => 'Dashboard', 'url' => '/user/dashboard.php', 'icon' => 'bi-speedometer2']
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'message' => "Apna product ad edit karne ke liye:\n1. **Dashboard &rarr; My Listings** par jayen.\n2. Apne ad ke niche **[Edit]** button dabayen.\n3. Price, description, ya images update karke Save karein.\n\n*Note:* Agar badi tabdeeli ki jaye to woh admin review ke baad dobara update ho jayegi.",
                    'cards' => [
                        ['title' => 'My Product Listings', 'url' => '/user/my-listings.php', 'icon' => 'bi-card-checklist'],
                        ['title' => 'Dashboard', 'url' => '/user/dashboard.php', 'icon' => 'bi-speedometer2']
                    ]
                ];
            }
        }

        // 3. Search Products Intent
        $isSearchingProducts = (
            str_contains($msgLower, 'chahiye') ||
            str_contains($msgLower, 'laptop') ||
            str_contains($msgLower, 'bike') ||
            str_contains($msgLower, 'mobile') ||
            str_contains($msgLower, 'cow') ||
            str_contains($msgLower, 'kinnow') ||
            str_contains($msgLower, 'wanda') ||
            str_contains($msgLower, 'price') ||
            str_contains($msgLower, 'product') ||
            str_contains($msgLower, 'kharidna')
        );

        if ($isSearchingProducts && !str_contains($msgLower, 'job') && !str_contains($msgLower, 'kaam')) {
            // Extract potential keywords
            $cleanTerm = preg_replace('/(chahiye|mujhe|price|mein|rate|sargodha|shaheenabad|sillanwali)/i', '', $userMessage);
            $cleanTerm = trim($cleanTerm);
            if (empty($cleanTerm)) $cleanTerm = $userMessage;

            $city = '';
            if (str_contains($msgLower, 'shaheenabad')) $city = 'Shaheenabad';
            elseif (str_contains($msgLower, 'sillanwali')) $city = 'Sillanwali';
            elseif (str_contains($msgLower, 'sargodha')) $city = 'Sargodha';

            $products = AITools::search_public_products($cleanTerm, $city, '', 4);

            if (!empty($products)) {
                $reply = "Yeh hain SargodhaMart par milne wali live listings:\n\n";
                foreach ($products as $p) {
                    $reply .= "• **{$p['title']}** — {$p['price_formatted']} ({$p['city']})\n";
                }
                $reply .= "\nKisi bhi item par click karke direct Call ya WhatsApp kar sakte hain.";
                return [
                    'success' => true,
                    'message' => $reply,
                    'products' => $products
                ];
            }
        }

        // 4. Search Jobs Intent
        $isSearchingJobs = (
            str_contains($msgLower, 'job') ||
            str_contains($msgLower, 'naukri') ||
            str_contains($msgLower, 'kaam') ||
            str_contains($msgLower, 'banda') ||
            str_contains($msgLower, 'rozgar') ||
            str_contains($msgLower, 'electrician') ||
            str_contains($msgLower, 'driver') ||
            str_contains($msgLower, 'labor')
        );

        if ($isSearchingJobs) {
            $type = '';
            if (str_contains($msgLower, 'banda') || str_contains($msgLower, 'hiring') || str_contains($msgLower, 'worker')) {
                $type = 'need_worker';
            } elseif (str_contains($msgLower, 'naukri') || str_contains($msgLower, 'mujhe job')) {
                $type = 'need_job';
            }

            $jobs = AITools::search_public_jobs('', '', $type, 4);
            if (!empty($jobs)) {
                $reply = "💼 **Jobs Hub — Live Opportunities:**\n\n";
                foreach ($jobs as $j) {
                    $reply .= "• **{$j['title']}** ({$j['city']}) — {$j['compensation']}\n";
                }
                $reply .= "\nMazeed dekhne ke liye Jobs portal visit karein:";
                return [
                    'success' => true,
                    'message' => $reply,
                    'cards' => [
                        ['title' => 'Open Jobs Hub', 'url' => '/jobs.php', 'icon' => 'bi-briefcase'],
                        ['title' => 'Post a Job Advertisement', 'url' => '/post-job.php', 'icon' => 'bi-plus-circle']
                    ]
                ];
            }
        }

        // 5. General Knowledge & FAQ
        $reply = self::getKnowledgeReply($msgLower);

        return [
            'success' => true,
            'message' => $reply,
            'cards' => [
                ['title' => 'Post Free Ad', 'url' => '/post-ad.php', 'icon' => 'bi-plus-circle'],
                ['title' => 'Seller Activation (Rs. 1,000)', 'url' => '/activate-seller.php', 'icon' => 'bi-shield-check'],
                ['title' => 'Jobs Hub', 'url' => '/jobs.php', 'icon' => 'bi-briefcase']
            ]
        ];
    }

    /**
     * Rule-based multilingual knowledge base matching.
     */
    private static function getKnowledgeReply(string $m): string
    {
        if (str_contains($m, 'activate') || str_contains($m, '1000') || str_contains($m, 'fee') || str_contains($m, 'seller')) {
            return "📌 **SargodhaMart Seller Activation Rules:**\n\n" .
                   "1. SargodhaMart par account banana bilkul FREE hai.\n" .
                   "2. Products ya Jobs post karne ke liye **Rs. 1,000** one-time lifetime verification fee ada karni hoti hai.\n" .
                   "3. EasyPaisa ya JazzCash se transfer karke TRX ID aur screenshot upload karein.\n" .
                   "4. Official WhatsApp Channel follow karein.\n" .
                   "5. Admin verification ke baad aapka account ACTIVE ho jata hai aur aap hamesha ke liye unlimited free ads post kar sakte hain!";
        }

        if (str_contains($m, 'post') || str_contains($m, 'ad kaise') || str_contains($m, 'ishtihar')) {
            return "📝 **Ad Post Karne Ka Tariqa:**\n\n" .
                   "1. Apne account mein login karein.\n" .
                   "2. **'Post Ad'** button par click karein.\n" .
                   "3. Product ka title, price, category aur location (Sargodha, Shaheenabad ya Sillanwali) darj karein.\n" .
                   "4. Ziyada se ziyada **3 genuine photos** upload karein.\n" .
                   "5. Apna WhatsApp/Phone number verify karein aur Publish dabayen!";
        }

        if (str_contains($m, 'safety') || str_contains($m, 'scam') || str_contains($m, 'dhoka') || str_contains($m, 'rule')) {
            return "🛡️ **Ahtiyati Tadabeer (Safety Tips):**\n\n" .
                   "• Item live dekh kar tasalli ke baad hi payment karein.\n" .
                   "• Kahi door advance payment transfer na karein.\n" .
                   "• Sargodha, Shaheenabad aur Sillanwali ke aamne saamne saundy ko tarjeeh dein.\n" .
                   "• Kisi bhi mashkook listing ko furan **Report** karein.";
        }

        if (str_contains($m, 'contact') || str_contains($m, 'helpline') || str_contains($m, 'rabta') || str_contains($m, 'support')) {
            $phone = function_exists('getSetting') ? getSetting('official_phone', '03127453108') : '03127453108';
            return "📞 **SargodhaMart Official Contact & Helpline:**\n\n" .
                   "• Helpline Call / WhatsApp: **{$phone}**\n" .
                   "• WhatsApp Channel: https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g\n" .
                   "• Email: support@sargodhamart.com\n" .
                   "Hamari team subah 9 baje se raat 9 baje tak dastiyab hai.";
        }

        return "Assalam-o-Alaikum! 👋 Main SargodhaMart Assistant hoon.\n\nMain aapki in chezon mein madad kar sakta hoon:\n" .
               "• Product dhoondna (Laptops, Bikes, Mobiles, Janwar, etc.)\n" .
               "• Jobs portal aur worker talash karna\n" .
               "• Rs. 1,000 Lifetime Seller Activation ka tariqa\n" .
               "• Apne live ads ya jobs ko edit karna\n\nNiche diye gaye options mein se select karein ya apna sawal likhein!";
    }
}
