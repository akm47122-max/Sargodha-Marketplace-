# SARGODHAMART CHANGELOG & MIGRATION RECORDS

## [Version 2.4.0] - 2026-10-05

### 1. Telegram User Number & Admin Approval System
- **User Telegram Number:** Added `telegram_number` field to User Profile and Settings.
- **Admin Approval Workflow:** Implemented 3-state approval flow (`PENDING`, `APPROVED`, `REJECTED`).
  - When user submits or edits their Telegram number, it enters `PENDING` state.
  - Previous `APPROVED` status is immediately invalidated upon number alteration.
  - Users cannot self-approve; only `admin` / `super_admin` can approve, reject (with mandatory reason), or revoke.
  - Every action creates a persistent audit entry in `user_telegram_verifications`.
- **Public Contact Privacy Controls:** Added `telegram_visibility` (`HIDDEN` [default] vs `VISIBLE`) and `contact_privacy` (`SHOW_ALL`, `WHATSAPP_ONLY`, `CALL_WHATSAPP`, `HIDE_PHONE`) to ensure unapproved or hidden phone numbers are never leaked into public HTML, APIs, analytics, or search results.

### 2. Fix for Telegram API Error & Token Leak Vulnerability
- **Root Cause Identified:** 
  1. Unsanitized `fetch` / `cURL` network failure exceptions contained the raw endpoint URL `https://api.telegram.org/bot<TOKEN>/...`, which leaked the secret bot token in error messages sent to frontend clients.
  2. Missing Channel ID normalization (e.g. users inputting IDs without `-100` prefix resulted in `Bad Request: chat not found`).
  3. Lack of automatic retry on transient HTTP 429/5xx errors.
  4. Missing in-memory and database duplicate publication suppression within rapid succession windows.
- **Fix Applied:**
  - Token redaction filter (`sanitizeTelegramString` / `sanitizeTelegramText`) strips tokens from all errors, logs, and responses.
  - Automatic `-100` prefix normalization for target channel IDs.
  - Translated raw Telegram error descriptions (e.g., bot kicked, chat not found, not enough rights) into clear admin-actionable messages.
  - Implemented exponential backoff retry mechanism (up to 2 retries for transient failures).
  - Added duplicate publication cache with 5-minute suppression window.

### 3. Seller Account Activation System & "FIRST 20 SELLERS FREE" Launch Deal
- **Launch Promotion:**
  - First 20 approved sellers receive **Rs. 0 Lifetime Seller Activation**.
  - Dynamic slot counter displayed in Activation Modal and Admin Dashboard: `X / 20 Free Slots Remaining`.
  - Free slot is strictly consumed **only when admin reviews and approves** the seller activation.
  - After 20 approved free sellers, the system automatically transitions to `CLOSED` / `Launch Offer Ended`, and the standard Rs. 1,000 fee applies.
- **WhatsApp Channel Requirement:**
  - Free applicants upload WhatsApp channel follow proof; payment receipt requirement is waived for Rs. 0 applicants.
  - Paid applicants upload both transfer screenshot and WhatsApp follow proof.
- **Admin Activations Queue:**
  - Added filter tabs: `Pending`, `Under Review`, `Approved`, `Rejected`, `Free Activations`, `Paid Activations`.
  - Prominent "FREE SELLER ACTIVATION" KPI card displaying `Used / 20` and remaining free slots.

### 4. Database Migrations
- **File:** `migrations/003_telegram_verification_and_seller_launch.sql`
- **Tables Updated/Created:**
  - `users`: Added `telegram_number`, `telegram_verification_status`, `telegram_submitted_at`, `telegram_reviewed_by`, `telegram_reviewed_at`, `telegram_rejection_reason`, `telegram_visibility`, `contact_privacy`.
  - `user_telegram_verifications`: New audit table tracking all submission and admin decisions.
  - `activation_payments`: Added `activation_type`, `is_free_slot`, `approved_at`, `rejection_reason`.
  - `telegram_publication_logs`: New table for tracking broadcasts and preventing duplicates.
  - `site_settings`: Added `free_seller_activation_limit = 20` and `is_free_seller_offer_active = 1`.
