<?php
/**
 * SARGODHAMART - AI Agent Core Engine ("SargodhaMart AI Assistant")
 * Admin Intelligence, Pattern Recognition, and Safe Analysis Service.
 *
 * SAFETY PRINCIPLES:
 * - Read-only analytics: Cannot automatically ban, delete, or financially approve.
 * - Uses cautious language: "This activity may require admin review because..."
 * - Evaluates patterns using predefined AITools only.
 * - Powered by Gemini API (gemini-3.8-flash) or intelligent local heuristics fallback.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/AITools.php';
require_once __DIR__ . '/AIEventLogger.php';

class AIAgent
{
    private const GEMINI_MODEL = 'gemini-3.8-flash';
    private const GEMINI_ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/';

    /**
     * Retrieve Gemini API Key securely from server-side environment.
     */
    public static function getApiKey(): ?string
    {
        $key = getenv('GEMINI_API_KEY');
        if (!empty($key)) return trim($key);

        if (!empty($_ENV['GEMINI_API_KEY'])) return trim($_ENV['GEMINI_API_KEY']);
        if (!empty($_SERVER['GEMINI_API_KEY'])) return trim($_SERVER['GEMINI_API_KEY']);

        // Check .env file
        $envPaths = [
            defined('ROOT_PATH') ? ROOT_PATH . '/.env' : null,
            dirname(__DIR__, 2) . '/.env'
        ];
        foreach ($envPaths as $p) {
            if ($p && file_exists($p) && is_readable($p)) {
                $lines = file($p, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if ($lines !== false) {
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (str_starts_with($line, 'GEMINI_API_KEY=')) {
                            $val = substr($line, 15);
                            return trim($val, " \t\n\r\0\x0B\"'");
                        }
                    }
                }
            }
        }

        // Fallback in site_settings
        if (function_exists('getSetting')) {
            $dbKey = getSetting('gemini_api_key');
            if (!empty($dbKey)) return trim($dbKey);
        }

        return null;
    }

    /**
     * Check if AI Agent is enabled in settings.
     */
    public static function isEnabled(): bool
    {
        if (function_exists('getSetting')) {
            return getSetting('ai_agent_enabled', '1') === '1';
        }
        return true;
    }

    /**
     * Process Admin Query through tools & contextual reasoning.
     */
    public static function askAdminAssistant(string $query, int $adminId): array
    {
        $queryLower = strtolower(trim($query));
        $toolsUsed = [];
        $contextData = [];

        // 1. Tool Selection & Execution based on intent
        if (str_contains($queryLower, 'seller') || str_contains($queryLower, 'register') || str_contains($queryLower, 'user')) {
            $toolsUsed[] = 'get_recent_registrations';
            $contextData['recent_registrations'] = AITools::get_recent_registrations(8);
        }

        if (str_contains($queryLower, 'activation') || str_contains($queryLower, 'payment') || str_contains($queryLower, 'pending') || str_contains($queryLower, 'fee')) {
            $toolsUsed[] = 'get_pending_activation_reviews';
            $contextData['pending_activations'] = AITools::get_pending_activation_reviews();
        }

        if (str_contains($queryLower, 'listing') || str_contains($queryLower, 'product') || str_contains($queryLower, 'ad')) {
            $toolsUsed[] = 'get_recent_listings';
            $contextData['recent_listings'] = AITools::get_recent_listings(8);
            $contextData['pending_listings'] = AITools::get_pending_listing_reviews();
        }

        if (str_contains($queryLower, 'job') || str_contains($queryLower, 'rozgar') || str_contains($queryLower, 'worker')) {
            $toolsUsed[] = 'get_recent_jobs';
            $contextData['recent_jobs'] = AITools::get_recent_jobs(8);
        }

        if (str_contains($queryLower, 'report') || str_contains($queryLower, 'scam') || str_contains($queryLower, 'fraud') || str_contains($queryLower, 'flag')) {
            $toolsUsed[] = 'get_listing_reports';
            $contextData['listing_reports'] = AITools::get_listing_reports(10);
        }

        // Always include high-level summary
        $toolsUsed[] = 'get_dashboard_summary';
        $contextData['dashboard_summary'] = AITools::get_dashboard_summary();

        if (str_contains($queryLower, 'week') || str_contains($queryLower, 'trend')) {
            $toolsUsed[] = 'get_weekly_summary';
            $contextData['weekly_summary'] = AITools::get_weekly_summary();
        }

        // 2. Query Gemini API with prompt, tools data, and safety constraints
        $apiKey = self::getApiKey();
        $aiResponseText = null;

        if (!empty($apiKey)) {
            $systemPrompt = "You are the SargodhaMart AI Assistant, an intelligent, privacy-conscious monitoring and analysis assistant for SargodhaMart (a local marketplace and jobs hub in Sargodha, Shaheenabad, and Sillanwali, Pakistan).
Your role is to help the website administrator understand platform activity, summarize registrations, activations, listings, jobs, reports, and identify patterns that may need human admin review.

STRICT SAFETY AND LANGUAGE RULES:
1. You have READ-ONLY advisory permissions. You CANNOT ban, approve, delete, or reject anything.
2. Use cautious, objective language. Never accuse anyone directly (e.g. NEVER say 'This user is a scammer'). Instead say: 'This activity may require admin review because [reason]'.
3. Always mention confidence levels (LOW / MEDIUM / HIGH) when highlighting patterns.
4. Format your response cleanly with bullet points, bold highlights, and clear sections.
5. SargodhaMart Business Model: Users register for free. Sellers pay a one-time lifetime verification fee of Rs. 1,000 and follow the official WhatsApp Channel to get activated by admin. Listings and jobs are free after activation.";

            $prompt = "Admin Question: \"{$query}\"\n\nAuthoritative Platform Data Retrieved by Authorized Tools:\n" . json_encode($contextData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\nPlease answer the administrator's question clearly and objectively based ONLY on the provided data.";

            $aiResponseText = self::callGemini($apiKey, $systemPrompt, $prompt);
        }

        // 3. Fallback heuristic response if Gemini API key not present or network unavailable
        if (empty($aiResponseText)) {
            $aiResponseText = self::generateLocalSummaryResponse($queryLower, $contextData);
        }

        // 4. Log interaction
        AITools::logToolRun('askAdminAssistant', ['query' => $query], ['response' => mb_substr($aiResponseText, 0, 200)], $adminId);

        return [
            'success' => true,
            'answer' => $aiResponseText,
            'tools_used' => array_unique($toolsUsed),
            'context_summary' => [
                'total_users' => $contextData['dashboard_summary']['total_users'] ?? 0,
                'active_sellers' => $contextData['dashboard_summary']['active_sellers'] ?? 0,
                'pending_activations' => $contextData['dashboard_summary']['pending_activations'] ?? 0,
                'pending_reports' => $contextData['dashboard_summary']['pending_reports'] ?? 0,
            ]
        ];
    }

    /**
     * Dispatch HTTP request to Gemini API (gemini-3.8-flash).
     */
    private static function callGemini(string $apiKey, string $systemInstruction, string $userPrompt): ?string
    {
        $url = self::GEMINI_ENDPOINT . self::GEMINI_MODEL . ':generateContent?key=' . $apiKey;

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userPrompt]
                    ]
                ]
            ],
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 1024
            ]
        ];

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 8
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError || $httpCode < 200 || $httpCode >= 300) {
                error_log("[AIAgent Gemini Error] HTTP {$httpCode}: " . ($curlError ?: $response));
                return null;
            }

            $decoded = json_decode($response, true);
            $candidateText = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;
            return $candidateText ? trim($candidateText) : null;
        } catch (Throwable $e) {
            error_log("[AIAgent Exception] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Intelligent rule-based fallback generator when offline or no API key.
     */
    private static function generateLocalSummaryResponse(string $q, array $data): string
    {
        $dash = $data['dashboard_summary'] ?? [];
        $out = "";

        if (str_contains($q, 'activation') || str_contains($q, 'payment')) {
            $pending = $dash['pending_activations'] ?? 0;
            $out .= "📊 **Seller Activation Status:**\n\n";
            $out .= "- Currently **{$pending} seller activation(s)** are awaiting administrative verification.\n";
            $out .= "- Total verified active sellers on platform: **" . ($dash['active_sellers'] ?? 0) . "**.\n";
            if (!empty($data['pending_activations'])) {
                $out .= "\n**Pending Review Queue:**\n";
                foreach (array_slice($data['pending_activations'], 0, 5) as $p) {
                    $out .= "• **{$p['user_name']}** ({$p['city']}) — {$p['method']} (TRX: `{$p['transaction_id']}`) — Rs. " . number_format($p['amount']) . "\n";
                }
            }
            $out .= "\n💡 *Reminder:* The AI Agent cannot verify payments automatically. Please inspect the payment screenshot and WhatsApp follow proof in Admin Payments.";
            return $out;
        }

        if (str_contains($q, 'listing') || str_contains($q, 'product')) {
            $total = $dash['published_listings'] ?? 0;
            $pending = $dash['pending_listings'] ?? 0;
            $out .= "🛍️ **Marketplace Listings Summary:**\n\n";
            $out .= "- **{$total} active listings** are published across Sargodha, Shaheenabad & Sillanwali.\n";
            $out .= "- **{$pending} listing(s)** currently require review or moderation.\n";
            if (!empty($data['recent_listings'])) {
                $out .= "\n**Recent Products:**\n";
                foreach (array_slice($data['recent_listings'], 0, 5) as $l) {
                    $out .= "• **{$l['title']}** in {$l['city']} — Rs. " . number_format($l['price_pkr']) . " ({$l['status']})\n";
                }
            }
            return $out;
        }

        if (str_contains($q, 'job') || str_contains($q, 'rozgar') || str_contains($q, 'worker')) {
            $total = $dash['active_jobs'] ?? 0;
            $out .= "💼 **Jobs & Rozgar Hub Activity:**\n\n";
            $out .= "- Total published job posts: **{$total}**.\n";
            if (!empty($data['recent_jobs'])) {
                $out .= "\n**Recent Postings:**\n";
                foreach (array_slice($data['recent_jobs'], 0, 4) as $j) {
                    $out .= "• **{$j['title']}** ({$j['type']}) in {$j['city']} — {$j['salary_or_payment']}\n";
                }
            }
            return $out;
        }

        if (str_contains($q, 'report') || str_contains($q, 'unusual') || str_contains($q, 'pattern')) {
            $reps = $dash['pending_reports'] ?? 0;
            $out .= "🛡️ **Safety & Moderation Review:**\n\n";
            $out .= "- **{$reps} unreviewed report(s)** currently require administrative investigation.\n";
            if (!empty($data['listing_reports'])) {
                $out .= "\n**Reports Needing Action:**\n";
                foreach (array_slice($data['listing_reports'], 0, 4) as $r) {
                    $out .= "• Listing: *\"{$r['listing_title']}\"* — Reason: `{$r['reason']}` ({$r['details']})\n";
                }
                $out .= "\n⚠️ *Confidence: Medium* — This activity may require admin review because multiple user flags were submitted.";
            } else {
                $out .= "✅ No urgent suspicious reports flagged in the queue.";
            }
            return $out;
        }

        // General overview
        $out .= "👋 **SargodhaMart Platform Overview:**\n\n";
        $out .= "- Total Registered Users: **" . ($dash['total_users'] ?? 0) . "**\n";
        $out .= "- Active Verified Sellers: **" . ($dash['active_sellers'] ?? 0) . "**\n";
        $out .= "- Published Product Listings: **" . ($dash['published_listings'] ?? 0) . "**\n";
        $out .= "- Pending Seller Activations: **" . ($dash['pending_activations'] ?? 0) . "**\n";
        $out .= "- Pending Content Reports: **" . ($dash['pending_reports'] ?? 0) . "**\n";
        $out .= "\nAsk me specific questions like: *\"How many activation payments are pending?\"* or *\"Show me today's new listings.\"*";
        return $out;
    }
}
