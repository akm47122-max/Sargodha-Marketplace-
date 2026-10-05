# Sargodha Mandi - Local Buy & Sell Marketplace
### Production-Ready PHP 8+ & MySQL Marketplace for Sargodha | Shaheenabad | Sillanwali

A complete, secure, responsive local marketplace designed specifically for the agricultural, commercial, and urban centers of Sargodha District.

---

## 1. Key Business Model & Payment Workflow

### A. Free Registration
- Creating a user account is **100% FREE**.
- Buyers and sellers can browse, search, and register without paying any initial registration fee.

### B. Mandatory Rs. 500 Product Listing Fee
To eliminate spam, fraudulent listings, and non-serious posts, every single product listing requires a manual **Rs. 500 Listing Fee**.

- **Authorized Receiver Name:** `Muhammad Akram Tayyab`
- **Authorized EasyPaisa / JazzCash Number:** `03127453108`
- **Fee Amount:** `Rs. 500 PKR` (configurable via Admin Settings)

### C. Listing & Verification Flow:
1. User logs into account and navigates to **Post an Ad** (`/post-ad.php`).
2. User provides title, selects category, price, condition (New/Used/Refurbished), area/location, and uploads product photos.
3. Upon form submission, the system saves the ad with status **Payment Pending** and immediately redirects the user to the Payment Step (`/user/submit-payment.php`).
4. The user sends Rs. 500 manually via EasyPaisa or JazzCash to `03127453108 (Muhammad Akram Tayyab)`.
5. The user enters their **Transaction ID (TRX ID)**, sender mobile number, and uploads a screenshot of the payment receipt.
6. The listing remains hidden from public view until an authorized administrator reviews the transaction.
7. Admin navigates to `/admin/payments.php`, inspects the screenshot and transaction ID, and clicks **Approve** or **Reject** with a note.
8. If approved, the listing status immediately becomes **Published** and appears on the live marketplace.
9. If rejected, the user receives an in-app notification with the administrator's reason and can resubmit corrected details.

---

## 2. Default Seed Logins for Testing

| Account Role | Email / Login | Mobile | Password | Location |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@sargodhamandi.com` | `03127453108` | `Password123!` | Sargodha (University Rd) |
| **Shaheenabad Seller** | `tariq@sargodha.com` | `03001234567` | `Password123!` | Shaheenabad (Dairy Farm) |
| **Sillanwali Seller** | `naveed@sillanwali.com` | `03027654321` | `Password123!` | Sillanwali (Citrus Mandi) |
| **Sargodha Mobile Shop** | `usman@sargodha.com` | `03019876543` | `Password123!` | Sargodha (Trust Plaza) |

*(A 1-click Quick Login bar is also available on the Login page for testing convenience).*

---

## 3. Hostinger / cPanel Production Deployment Guide

Follow these simple steps to deploy on Hostinger (Shared, Cloud, or VPS) or any cPanel web host:

### Step 1: Upload Files
1. Log into your Hostinger hPanel or cPanel.
2. Open **File Manager** and navigate to your domain's document root (typically `public_html`).
3. Upload all files and folders:
   - `/config/`
   - `/includes/`
   - `/admin/`
   - `/user/`
   - `/api/`
   - `/assets/`
   - `/uploads/`
   - All root `.php` files (`index.php`, `login.php`, `post-ad.php`, `product.php`, etc.)
   - `.htaccess`

### Step 2: Create MySQL Database & Import `database.sql`
1. Go to **MySQL Databases** in your hosting control panel.
2. Create a new database (e.g., `u123456_sargodha_mandi`).
3. Create a new MySQL user and assign a strong password.
4. Add the user to the database with **ALL PRIVILEGES**.
5. Open **phpMyAdmin**, select your newly created database, and click the **Import** tab.
6. Choose the file `database.sql` from the project root and click **Go / Import**.

### Step 3: Configure Database Credentials
Open `/config/config.php` and set your MySQL credentials:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'u123456_sargodha_mandi');
define('DB_USER', 'u123456_dbuser');
define('DB_PASS', 'YourStrongPasswordHere');
```

### Step 4: Verify Folder Permissions
Ensure the web server has write permissions to the uploads directories:
- `chmod -R 755 uploads/` (or `chmod -R 775 uploads/` depending on server configuration)
- Subdirectories: `uploads/products/`, `uploads/payments/`, `uploads/profiles/`

---

## 4. Built-in Security Protections

1. **PDO Prepared Statements:** Parameterized queries used everywhere to prevent SQL Injection.
2. **CSRF Protection:** Cryptographic random tokens validated on every POST action.
3. **MIME & Dimension Verification:** Image uploads are validated using `finfo` MIME detection and `getimagesize()` to prevent executable files from being uploaded as images.
4. **Password Hashing:** Passwords hashed with `PASSWORD_BCRYPT`.
5. **Session Isolation & RBAC:** Database-backed role hierarchy (`user`, `admin`, `super_admin`) checked server-side on every administrative route.
6. **XSS Protection:** All user-supplied data escaped with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
