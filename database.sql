-- SARGODHAMART - Local Marketplace Database Schema
-- Brand: SARGODHAMART (Buy • Sell • Connect)
-- Target Areas: Sargodha | Shaheenabad | Sillanwali
-- Production Ready for Hostinger / cPanel / MariaDB 10.4+ / MySQL 8.0+

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS conversations;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS featured_listings;
DROP TABLE IF EXISTS activation_payments;
DROP TABLE IF EXISTS listing_images;
DROP TABLE IF EXISTS listings;
DROP TABLE IF EXISTS subcategories;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS site_settings;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table
-- Important: activation_status controls seller privileges.
-- Registration is 100% Free -> starts with 'pending'.
-- One-time Rs. 500 activation -> becomes 'active'.
-- After approval: user can create UNLIMITED normal product listings for FREE!
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    mobile_number VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    city ENUM('Sargodha', 'Shaheenabad', 'Sillanwali') NOT NULL DEFAULT 'Sargodha',
    area VARCHAR(150) NOT NULL,
    role ENUM('user', 'admin', 'super_admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'suspended', 'blocked') NOT NULL DEFAULT 'active',
    activation_status ENUM('pending', 'active', 'rejected', 'suspended') NOT NULL DEFAULT 'pending',
    activated_at DATETIME DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expires_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_mobile (mobile_number),
    INDEX idx_user_email (email),
    INDEX idx_user_role (role),
    INDEX idx_user_city (city),
    INDEX idx_user_activation (activation_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    icon VARCHAR(60) NOT NULL DEFAULT 'bi-tag',
    description TEXT,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cat_slug (slug),
    INDEX idx_cat_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Subcategories Table
CREATE TABLE subcategories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_subcat_slug (slug),
    INDEX idx_subcat_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Listings Table
-- Products created by active sellers. Normal listings are FREE after one-time activation.
CREATE TABLE listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    subcategory_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(250) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    description TEXT NOT NULL,
    item_condition ENUM('New', 'Used', 'Refurbished') NOT NULL DEFAULT 'Used',
    city ENUM('Sargodha', 'Shaheenabad', 'Sillanwali') NOT NULL DEFAULT 'Sargodha',
    area VARCHAR(150) NOT NULL,
    exact_location VARCHAR(255) DEFAULT NULL,
    phone_number VARCHAR(20) NOT NULL,
    whatsapp_number VARCHAR(20) NOT NULL,
    contact_preference ENUM('Both', 'Call Only', 'WhatsApp Only', 'Chat Only') NOT NULL DEFAULT 'Both',
    seller_whatsapp_group VARCHAR(255) DEFAULT NULL,
    video_url VARCHAR(255) DEFAULT NULL,
    status ENUM('draft', 'pending', 'published', 'rejected', 'hidden', 'suspended', 'deleted') NOT NULL DEFAULT 'published',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    featured_until DATETIME DEFAULT NULL,
    views_count INT NOT NULL DEFAULT 0,
    rejection_reason TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (subcategory_id) REFERENCES subcategories(id) ON DELETE SET NULL,
    INDEX idx_listing_status (status),
    INDEX idx_listing_city (city),
    INDEX idx_listing_featured (is_featured),
    INDEX idx_listing_category (category_id),
    INDEX idx_listing_price (price),
    INDEX idx_listing_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Listing Images Table
CREATE TABLE listing_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    INDEX idx_img_listing (listing_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Activation Payments Table
-- VERY IMPORTANT:
-- This payment belongs to the USER ACCOUNT, NOT to an individual product.
-- User pays Rs. 1,000 ONCE for lifetime seller activation.
-- User also follows official WhatsApp Channel and uploads separate screenshot proof.
-- After admin approval: activation_status becomes 'active', unlocking UNLIMITED FREE listings & jobs!
CREATE TABLE activation_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 1000.00,
    payment_method ENUM('EasyPaisa', 'JazzCash', 'Bank Transfer', 'Other') NOT NULL DEFAULT 'EasyPaisa',
    payment_number VARCHAR(50) NOT NULL DEFAULT '03127453108',
    account_name VARCHAR(100) NOT NULL DEFAULT 'Muhammad Akram Tayyab',
    sender_number VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    payment_screenshot VARCHAR(255) DEFAULT NULL,
    whatsapp_screenshot VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    admin_note TEXT DEFAULT NULL,
    verified_at DATETIME DEFAULT NULL,
    verified_by INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_act_user (user_id),
    INDEX idx_act_status (status),
    INDEX idx_act_trx (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Featured Listings Payments Table (Optional Ad Promotion)
CREATE TABLE featured_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 1000.00,
    transaction_id VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    duration_days INT NOT NULL DEFAULT 15,
    starts_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Favorites Table
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    listing_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fav (user_id, listing_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Conversations Table
CREATE TABLE conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_conv (listing_id, buyer_id, seller_id),
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conv_buyer (buyer_id),
    INDEX idx_conv_seller (seller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Messages Table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_msg_conv (conversation_id),
    INDEX idx_msg_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Reviews Table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT DEFAULT NULL,
    seller_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    rating TINYINT(1) NOT NULL,
    review_text TEXT NOT NULL,
    status ENUM('published', 'hidden', 'flagged') NOT NULL DEFAULT 'published',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review (seller_id, reviewer_id),
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_rev_seller (seller_id),
    INDEX idx_rev_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Reports Table
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT DEFAULT NULL,
    job_id INT DEFAULT NULL,
    reporter_id INT DEFAULT NULL,
    reason ENUM('Scam/Fraud', 'Fake product', 'Wrong information', 'Duplicate', 'Prohibited item', 'Offensive content', 'Other') NOT NULL,
    details TEXT NOT NULL,
    status ENUM('pending', 'investigating', 'resolved', 'dismissed') NOT NULL DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_rep_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12B. Jobs & Employment Table
-- Integrated inside SargodhaMart: I Need a Job (worker) OR I Need a Worker (employer)
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_type ENUM('need_job', 'need_worker') NOT NULL DEFAULT 'need_worker',
    title VARCHAR(200) NOT NULL,
    category VARCHAR(100) NOT NULL,
    skills TEXT NOT NULL,
    experience VARCHAR(100) DEFAULT NULL,
    working_hours VARCHAR(100) DEFAULT NULL,
    salary_or_payment VARCHAR(100) DEFAULT NULL,
    city ENUM('Sargodha', 'Shaheenabad', 'Sillanwali') NOT NULL DEFAULT 'Sargodha',
    area VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    whatsapp_number VARCHAR(20) NOT NULL,
    status ENUM('published', 'disabled', 'deleted') NOT NULL DEFAULT 'published',
    views_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_jobs_type (post_type),
    INDEX idx_jobs_city (city),
    INDEX idx_jobs_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Notifications Table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    type VARCHAR(50) NOT NULL DEFAULT 'general',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notif_user (user_id),
    INDEX idx_notif_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Site Settings Table
CREATE TABLE site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Admin Audit Logs Table
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id INT NOT NULL,
    reason TEXT DEFAULT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_log_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- INITIAL SEED DATA
-- =======================================================

-- Site Settings
INSERT INTO site_settings (setting_key, setting_value, description) VALUES
('site_name', 'SARGODHAMART', 'Marketplace Brand Name'),
('site_tagline', 'Buy • Sell • Connect', 'Official Brand Tagline'),
('seller_activation_fee', '500', 'One-time seller account activation fee in PKR'),
('featured_fee', '1000', 'Featured promotion fee in PKR'),
('featured_days', '15', 'Duration of featured ad in days'),
('payment_number', '03127453108', 'Official payment collection mobile number'),
('payment_account_name', 'Muhammad Akram Tayyab', 'Official account title name'),
('payment_bank_name', 'EasyPaisa / JazzCash', 'Primary payment channel'),
('payment_instructions', 'Transfer Rs. 500 ONE TIME ONLY to 03127453108 (Account Name: Muhammad Akram Tayyab) via EasyPaisa or JazzCash. Once approved by admin, your seller account is activated and you can post UNLIMITED normal listings for free!', 'Instructions on activation screen'),
('max_images_per_listing', '8', 'Maximum allowed product images'),
('contact_email', 'admin@sargodhamart.com', 'Helpline & support email address'),
('contact_phone', '03127453108', 'Official helpline mobile number');

-- Seed Users (Password for all seed accounts is 'Password123!')
INSERT INTO users (id, full_name, mobile_number, email, password, city, area, role, status, activation_status, activated_at) VALUES
(1, 'Muhammad Akram Tayyab', '03127453108', 'admin@sargodhamart.com', '$2y$10$Y5n2l9lIflD6N6C1e6Rz4.Q0i1uBv4Gg5LqW6E7r8T9y0U1i2O3Pa', 'Sargodha', 'University Road / Satellite Town', 'super_admin', 'active', 'active', NOW()),
(2, 'Malik Tariq Dairy Farm', '03001234567', 'tariq@sargodha.com', '$2y$10$Y5n2l9lIflD6N6C1e6Rz4.Q0i1uBv4Gg5LqW6E7r8T9y0U1i2O3Pa', 'Shaheenabad', 'Canal Colony Dairy Belt', 'user', 'active', 'active', NOW()),
(3, 'Chaudhry Naveed Agro', '03027654321', 'naveed@sillanwali.com', '$2y$10$Y5n2l9lIflD6N6C1e6Rz4.Q0i1uBv4Gg5LqW6E7r8T9y0U1i2O3Pa', 'Sillanwali', 'Main Bazaar Citrus Belt', 'user', 'active', 'active', NOW()),
(4, 'Rana Usman Mobile Zone', '03019876543', 'usman@sargodha.com', '$2y$10$Y5n2l9lIflD6N6C1e6Rz4.Q0i1uBv4Gg5LqW6E7r8T9y0U1i2O3Pa', 'Sargodha', 'Trust Plaza, Kutchery Bazaar', 'user', 'active', 'active', NOW()),
(5, 'Asad Ali (New Seller)', '03031122334', 'asad@sargodha.com', '$2y$10$Y5n2l9lIflD6N6C1e6Rz4.Q0i1uBv4Gg5LqW6E7r8T9y0U1i2O3Pa', 'Sargodha', 'Fatima Jinnah Road', 'user', 'active', 'pending', NULL);

-- One-Time Seller Activation Payments (Belongs to User, NOT individual products!)
INSERT INTO activation_payments (user_id, amount, payment_method, payment_number, account_name, sender_number, transaction_id, payment_screenshot, status, admin_note, verified_at, verified_by) VALUES
(2, 500.00, 'EasyPaisa', '03127453108', 'Muhammad Akram Tayyab', '03001234567', 'EP9823412095', 'uploads/payments/sample_receipt.jpg', 'approved', 'One-time seller activation fee verified. User can post unlimited listings.', NOW(), 1),
(3, 500.00, 'JazzCash', '03127453108', 'Muhammad Akram Tayyab', '03027654321', 'JC8472910382', 'uploads/payments/sample_receipt.jpg', 'approved', 'Received via JazzCash. Seller account activated.', NOW(), 1),
(4, 500.00, 'EasyPaisa', '03127453108', 'Muhammad Akram Tayyab', '03019876543', 'EP7732910481', 'uploads/payments/sample_receipt.jpg', 'approved', 'One-time fee verified. Seller active for unlimited free listings.', NOW(), 1);

-- Categories
INSERT INTO categories (id, name, slug, icon, description, sort_order) VALUES
(1, 'Mobiles', 'mobiles', 'bi-phone', 'Smartphones, Feature Phones, Tablets & Accessories', 1),
(2, 'Laptops & Computers', 'laptops-computers', 'bi-laptop', 'Laptops, Desktop PCs, Monitors, Printers', 2),
(3, 'Electronics', 'electronics', 'bi-tv', 'LED TVs, Solar Panels, Inverters, Audio', 3),
(4, 'Cars', 'cars', 'bi-car-front', 'Toyota, Suzuki, Honda, Commercial Loaders', 4),
(5, 'Bikes', 'bikes', 'bi-bicycle', 'Honda 125, CD 70, Scooters & Heavy Bikes', 5),
(6, 'Property', 'property', 'bi-building', 'Plots, Agricultural Land, Houses, Commercial Shops', 6),
(7, 'Animals / Livestock', 'animals-livestock', 'bi-heart-pulse', 'Sahiwal Cows, Nili Ravi Buffaloes, Goats, Sheep', 7),
(8, 'Animal Feed / Wanda', 'animal-feed-wanda', 'bi-box-seam', 'Dairy Wanda, Calf Feed, Rhodes Grass, Silage', 8),
(9, 'Clothes & Shoes', 'clothes-shoes', 'bi-handbag', 'Men & Women Shalwar Kameez, Shoes, Khussa', 9),
(10, 'Furniture', 'furniture', 'bi-lamp', 'Chinioti Furniture, Bed Sets, Sofas, Office', 10),
(11, 'Home Appliances', 'home-appliances', 'bi-plug', 'Inverter Refrigerators, Deep Freezers, ACs, Washing Machines', 11),
(12, 'Jobs', 'jobs', 'bi-briefcase', 'Local Sales, Driver, Teaching & Skilled Vacancies', 12),
(13, 'Services', 'services', 'bi-tools', 'Solar Installation, Plumber, Electrician, Tractor Mechanics', 13),
(14, 'Agriculture', 'agriculture', 'bi-flower1', 'Sargodha Kinnow Citrus, Tractors, Seeds, Fertilizers', 14),
(15, 'Other', 'other', 'bi-grid', 'General Household, Books, Antiques & Tools', 15);

-- Subcategories
INSERT INTO subcategories (category_id, name, slug) VALUES
(1, 'Smartphones (Android & iPhone)', 'smartphones'),
(1, 'Feature Phones', 'feature-phones'),
(1, 'Tablets & iPads', 'tablets'),
(1, 'Mobile Accessories', 'mobile-accessories'),
(2, 'Laptops', 'laptops'),
(2, 'Desktop Computers', 'desktop-computers'),
(2, 'Printers & Scanners', 'printers-scanners'),
(3, 'LED & Smart TVs', 'led-tvs'),
(3, 'Solar Inverters & Batteries', 'solar-inverters'),
(3, 'Home Theater & Audio', 'audio'),
(4, 'Toyota', 'toyota'),
(4, 'Suzuki', 'suzuki'),
(4, 'Honda', 'honda'),
(4, 'Commercial Pickups & Vans', 'commercial-pickups'),
(5, 'Honda CG 125', 'honda-cg-125'),
(5, 'Honda CD 70', 'honda-cd-70'),
(5, 'Yamaha & Suzuki', 'yamaha-suzuki'),
(5, 'Electric Bikes & Scooters', 'electric-bikes'),
(6, 'Plots & Files', 'plots-files'),
(6, 'Agricultural Farmland', 'agricultural-land'),
(6, 'Houses for Sale', 'houses-sale'),
(6, 'Shops & Commercial Spaces', 'shops-commercial'),
(7, 'Sahiwal Dairy Cows', 'sahiwal-cows'),
(7, 'Nili Ravi Buffaloes', 'buffaloes'),
(7, 'Bakray / Goats & Sheep', 'goats-sheep'),
(7, 'Fancy Birds & Poultry', 'poultry'),
(8, 'Dairy Milk Wanda (20% Protein)', 'dairy-wanda'),
(8, 'Calf Starter / Growth Feed', 'calf-starter'),
(8, 'Silage Bales & Rhodes Grass', 'silage-rhodes'),
(8, 'Cottonseed Cake (Khal) & Choker', 'khal-choker'),
(9, 'Men Clothing', 'men-clothing'),
(9, 'Women Formal & Casual', 'women-clothing'),
(9, 'Footwear & Traditional Khussa', 'shoes-khussa'),
(10, 'Double Bed Sets', 'bed-sets'),
(10, 'Sofa Sets & Diwans', 'sofa-sets'),
(10, 'Dining Tables & Chairs', 'dining-tables'),
(11, 'Refrigerators & Freezers', 'refrigerators'),
(11, 'Air Conditioners & Inverters', 'air-conditioners'),
(11, 'Washing Machines & Spinners', 'washing-machines'),
(12, 'Sales & Shop Assistants', 'sales-jobs'),
(12, 'Drivers & Delivery', 'driver-jobs'),
(12, 'Skilled Craftsmen & Technicians', 'technician-jobs'),
(13, 'Solar System Installation & Maintenance', 'solar-services'),
(13, 'Tractor & Harvester Repair', 'tractor-repair'),
(13, 'Plumber, Electrician & Construction', 'construction-services'),
(14, 'Sargodha Export Kinnow Citrus', 'kinnow-citrus'),
(14, 'Tractors (Millat / Al-Ghazi / Fiat)', 'tractors'),
(14, 'Wheat & Cotton Seeds / Fertilizers', 'seeds-fertilizers'),
(14, 'Tube-wells & Solar Turbine Pumps', 'tube-well-pumps'),
(15, 'General Items', 'general-items');

-- Seed Listings (UNLIMITED FREE for active sellers!)
INSERT INTO listings (id, user_id, category_id, subcategory_id, title, slug, price, description, item_condition, city, area, exact_location, phone_number, whatsapp_number, contact_preference, status, is_featured, views_count) VALUES
(1, 2, 7, 17, 'Pure Sahiwal Breed Milk Cow (18L Daily Yield) - Vaccinated', 'pure-sahiwal-breed-milk-cow-18l-daily-yield-vaccinated', 385000.00, 'Top class pure Sahiwal dairy cow. 2nd lactation, yielding 18 liters daily guaranteed with healthy feeding. Complete vaccination records from Livestock Dept Sargodha. Active, very calm temperament. Direct seller from Shaheenabad dairy farm. Serious buyers are welcome for live milking trial.', 'Used', 'Shaheenabad', 'Canal Colony Dairy Belt', 'Near Shaheenabad Railway Crossing & Canal Bridge', '03001234567', '03001234567', 'Both', 'published', 1, 342),
(2, 3, 14, 38, 'Export Quality Sargodha Fresh Kinnow + High Protein Dairy Wanda', 'export-quality-sargodha-fresh-kinnow-high-protein-dairy-wanda', 1450.00, 'Fresh sweet Sargodha Kinnow directly harvested from Sillanwali orchards. Also available: Premium grade 20% protein dairy cattle wanda (40kg bags) at factory wholesale rates. Bulk orders for wholesale mandi traders and dairy farms catered across Sargodha district.', 'New', 'Sillanwali', 'Main Mandi Bazaar & Orchard Road', 'Sillanwali Kinnow Mandi Gate 2', '03027654321', '03027654321', 'Both', 'published', 1, 512),
(3, 4, 5, 13, 'Honda CG 125 Special Edition 2024 - Punjab Sargodha Registered', 'honda-cg-125-special-edition-2024-punjab-sargodha-registered', 275000.00, 'First owner Honda CG 125 Special Edition Self-Start model. 100% genuine condition, scratchless black & gold color scheme. Total driven only 4,800 KM in Sargodha city. Smart card original file available on spot. Bio-metric will be provided immediately.', 'Used', 'Sargodha', 'Fatima Jinnah Road', 'Near District Council Hall, Sargodha', '03019876543', '03019876543', 'Both', 'published', 0, 189),
(4, 4, 1, 1, 'iPhone 15 Pro Max 256GB Natural Titanium (PTA Approved Official)', 'iphone-15-pro-max-256gb-natural-titanium-pta-approved-official', 420000.00, 'Apple iPhone 15 Pro Max 256GB Natural Titanium finish. Official PTA Approved with receipt. 96% battery health, completely scratchless with original box, braided USB-C cable and premium tempered glass. Physical SIM + eSIM. Location: Trust Plaza Mobile Market Sargodha.', 'Used', 'Sargodha', 'Trust Plaza Market', 'Shop #14, Lower Ground Trust Plaza, Sargodha', '03019876543', '03019876543', 'Both', 'published', 0, 290);

-- Listing Images
INSERT INTO listing_images (listing_id, image_path, is_primary, sort_order) VALUES
(1, 'uploads/products/listing_livestock_cow_1790802991968.jpg', 1, 0),
(2, 'uploads/products/listing_kinnow_wanda_1790803004676.jpg', 1, 0),
(3, 'uploads/products/listing_motorcycle_1790803020640.jpg', 1, 0),
(4, 'uploads/products/listing_smartphone_1790803033699.jpg', 1, 0);

-- Reviews
INSERT INTO reviews (listing_id, seller_id, reviewer_id, rating, review_text, status) VALUES
(1, 2, 3, 5, 'Purchased dairy animals from Malik Tariq sb before in Shaheenabad. Very honest gentleman, exact milk yield as promised. Highly recommended local seller!', 'published'),
(2, 3, 4, 5, 'Best kinnow citrus boxes and quality wanda bags in Sillanwali. Very fast dealing and honest weights. Zabardast service!', 'published');

-- Conversations & Messages
INSERT INTO conversations (id, listing_id, buyer_id, seller_id) VALUES
(1, 1, 4, 2);

INSERT INTO messages (conversation_id, sender_id, message, is_read) VALUES
(1, 4, 'Assalam o Alaikum Malik sb! Is the Sahiwal cow still available for visit in Shaheenabad?', 1),
(1, 2, 'Walaikum Assalam! Yes brother, you can come tomorrow morning to Canal Colony farm for live milking trial.', 1),
(1, 4, 'Shukriya, I will contact on your WhatsApp before leaving.', 0);

-- Notifications
INSERT INTO notifications (user_id, title, message, link, is_read, type) VALUES
(2, 'Seller Account Activated!', 'Your one-time Rs. 500 fee has been approved. You can now post unlimited listings for free!', '/post-ad.php', 1, 'account_activation'),
(3, 'Seller Account Activated!', 'Your one-time Rs. 500 fee has been approved. You can now post unlimited listings for free!', '/post-ad.php', 1, 'account_activation'),
(4, 'Seller Account Activated!', 'Your one-time Rs. 500 fee has been approved. You can now post unlimited listings for free!', '/post-ad.php', 1, 'account_activation');

-- Resend Transactional Email Logs
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `recipient_email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `email_type` VARCHAR(50) NOT NULL DEFAULT 'general',
  `resend_id` VARCHAR(100) NULL,
  `status` ENUM('sent', 'failed') NOT NULL DEFAULT 'sent',
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email_recipient` (`recipient_email`),
  INDEX `idx_email_status` (`status`),
  INDEX `idx_email_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SARGODHAMART AI AGENT & INTELLIGENCE TABLES
CREATE TABLE IF NOT EXISTS `ai_agent_events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_type` VARCHAR(50) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT NULL,
  `user_id` INT NULL,
  `metadata_json` TEXT NULL,
  `processed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_evt_type` (`event_type`),
  INDEX `idx_evt_entity` (`entity_type`, `entity_id`),
  INDEX `idx_evt_user` (`user_id`),
  INDEX `idx_evt_processed` (`processed`),
  INDEX `idx_evt_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_agent_alerts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `severity` ENUM('INFO', 'LOW', 'MEDIUM', 'HIGH', 'CRITICAL') NOT NULL DEFAULT 'INFO',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `reason` TEXT NOT NULL,
  `confidence` ENUM('LOW', 'MEDIUM', 'HIGH') NOT NULL DEFAULT 'MEDIUM',
  `entity_type` VARCHAR(50) NULL,
  `entity_id` INT NULL,
  `status` ENUM('pending', 'reviewed', 'dismissed') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` TIMESTAMP NULL,
  `reviewed_by` INT NULL,
  INDEX `idx_alert_severity` (`severity`),
  INDEX `idx_alert_status` (`status`),
  INDEX `idx_alert_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE IF NOT EXISTS `ai_system_versions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `version_tag` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `created_by` INT NULL,
  `changes_json` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ver_tag` (`version_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


