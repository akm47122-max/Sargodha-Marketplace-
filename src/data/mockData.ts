import { Category, UserProfile, Listing, JobPost, ActivationPayment, ReportItem } from '../types';
import heroImg from '../assets/images/hero_sargodha_bazaar_1790802971175.jpg';
import cowImg from '../assets/images/listing_livestock_cow_1790802991968.jpg';
import wandaImg from '../assets/images/listing_kinnow_wanda_1790803004676.jpg';
import bikeImg from '../assets/images/listing_motorcycle_1790803020640.jpg';
import phoneImg from '../assets/images/listing_smartphone_1790803033699.jpg';

export const INITIAL_CATEGORIES: Category[] = [
  { id: 1, name: 'Mobiles', slug: 'mobiles', icon: '📱', subcategories: ['Smartphones', 'Feature Phones', 'Tablets', 'Accessories'] },
  { id: 2, name: 'Laptops & PCs', slug: 'laptops-pcs', icon: '💻', subcategories: ['Laptops', 'Desktops', 'Monitors'] },
  { id: 3, name: 'Electronics', slug: 'electronics', icon: '⚡', subcategories: ['LED TVs', 'Solar Panels & Inverters', 'Batteries'] },
  { id: 4, name: 'Cars', slug: 'cars', icon: '🚗', subcategories: ['Suzuki', 'Toyota', 'Honda', 'Commercial Pickups'] },
  { id: 5, name: 'Bikes', slug: 'bikes', icon: '🏍️', subcategories: ['Honda 125', 'Honda CD 70', 'Yamaha YBR', 'Electric Scooters'] },
  { id: 6, name: 'Property', slug: 'property', icon: '🏠', subcategories: ['Plots', 'Houses', 'Agricultural Land', 'Commercial Shops'] },
  { id: 7, name: 'Animals / Livestock', slug: 'animals-livestock', icon: '🐄', subcategories: ['Sahiwal Cows', 'Nili-Ravi Buffaloes', 'Goats & Sheep'] },
  { id: 8, name: 'Animal Feed / Wanda', slug: 'animal-feed-wanda', icon: '🌾', subcategories: ['Dairy Wanda 50kg', 'Silage Bales', 'Khal & Choker'] },
  { id: 9, name: 'Agriculture', slug: 'agriculture', icon: '🍊', subcategories: ['Kinnow Orchards & Fruit', 'Tractors & Implements', 'Fertilizers'] },
  { id: 10, name: 'Furniture', slug: 'furniture', icon: '🛋️', subcategories: ['Chinioti Bed Sets', 'Sofa Sets', 'Wardrobes'] },
  { id: 11, name: 'Services', slug: 'services', icon: '🔧', subcategories: ['Solar Installation', 'Drilling & Boring', 'Tractor Repair'] },
  { id: 12, name: 'Other', slug: 'other', icon: '📦', subcategories: ['General Merchandise', 'Tools'] }
];

export const INITIAL_USERS: UserProfile[] = [
  { id: 1, name: 'Muhammad Akram Tayyab', mobile: '03127453108', email: 'admin@sargodhamart.com', city: 'Sargodha', area: 'University Road / Satellite Town', role: 'super_admin', activationStatus: 'active', joinedDate: 'Jan 2026' },
  { id: 2, name: 'Malik Tariq Dairy Farm', mobile: '03001234567', email: 'tariq@sargodha.com', city: 'Shaheenabad', area: 'Canal Colony Dairy Belt', role: 'user', activationStatus: 'active', joinedDate: 'Feb 2026' },
  { id: 3, name: 'Chaudhry Naveed Agro', mobile: '03027654321', email: 'naveed@sillanwali.com', city: 'Sillanwali', area: 'Main Mandi Citrus Road', role: 'user', activationStatus: 'active', joinedDate: 'Feb 2026' },
  { id: 4, name: 'Rana Usman Mobile Zone', mobile: '03019876543', email: 'usman@sargodha.com', city: 'Sargodha', area: 'Trust Plaza, Kutchery Bazaar', role: 'user', activationStatus: 'active', joinedDate: 'Mar 2026' },
  { id: 5, name: 'Asad Ali (New User)', mobile: '03031122334', email: 'asad@sargodha.com', city: 'Sargodha', area: 'Fatima Jinnah Road', role: 'user', activationStatus: 'pending', joinedDate: 'Today' }
];

export const INITIAL_LISTINGS: Listing[] = [
  {
    id: 1,
    userId: 2,
    sellerName: 'Malik Tariq Dairy Farm',
    sellerPhone: '03001234567',
    sellerCity: 'Shaheenabad',
    sellerArea: 'Canal Colony Dairy Belt',
    sellerWhatsappGroup: 'https://chat.whatsapp.com/sampledairygroup',
    title: 'Pure Sahiwal Breed Milk Cow (18L Daily Yield) - Vaccinated',
    categoryId: 7,
    categoryName: 'Animals / Livestock',
    subcategory: 'Sahiwal Cows',
    price: 385000,
    condition: 'Used',
    description: 'Top class pure Sahiwal dairy cow. 2nd lactation, yielding 18 liters daily guaranteed with healthy feeding. Complete vaccination records from Livestock Dept Sargodha. Active, very calm temperament. Direct seller from Shaheenabad dairy farm. Serious buyers are welcome for live milking trial.',
    city: 'Shaheenabad',
    area: 'Canal Colony Dairy Belt',
    exactLocation: 'Near Shaheenabad Railway Crossing & Canal Bridge',
    status: 'published',
    isFeatured: true,
    images: [cowImg, heroImg],
    views: 412,
    createdAt: '2 hours ago'
  },
  {
    id: 2,
    userId: 3,
    sellerName: 'Chaudhry Naveed Agro',
    sellerPhone: '03027654321',
    sellerCity: 'Sillanwali',
    sellerArea: 'Main Mandi Citrus Road',
    sellerWhatsappGroup: 'https://chat.whatsapp.com/sampleagrogroup',
    title: 'Super Wanda High Protein 50kg Bags - Direct Mandi Factory Rates',
    categoryId: 8,
    categoryName: 'Animal Feed / Wanda',
    subcategory: 'Dairy Wanda 50kg',
    price: 4200,
    condition: 'New',
    description: '18% crude protein dairy cattle wanda enriched with calcium, phosphorus and vitamins. Guaranteed increase in milk fat and yield within 7 days. Fresh stock prepared in Sillanwali plant. Bulk delivery available across Sargodha district.',
    city: 'Sillanwali',
    area: 'Grain Market Bypass Road',
    exactLocation: 'Shop # 12, New Grain Market, Sillanwali',
    status: 'published',
    isFeatured: true,
    images: [wandaImg],
    views: 298,
    createdAt: '5 hours ago'
  },
  {
    id: 3,
    userId: 4,
    sellerName: 'Rana Usman Mobile Zone',
    sellerPhone: '03019876543',
    sellerCity: 'Sargodha',
    sellerArea: 'Trust Plaza, Kutchery Bazaar',
    title: 'Honda CG 125 Self Start 2024 Model - Punjab Registered',
    categoryId: 5,
    categoryName: 'Bikes',
    subcategory: 'Honda 125',
    price: 245000,
    condition: 'Used',
    description: 'First owner Honda 125 self-start red color. Driven only 8,200 km, totally original condition, scratchless tank and side covers. Original file, smart card, and both original keys available. Engine never opened.',
    city: 'Sargodha',
    area: 'Trust Plaza / Kutchery Bazaar',
    status: 'published',
    isFeatured: true,
    images: [bikeImg],
    views: 520,
    createdAt: 'Yesterday'
  },
  {
    id: 4,
    userId: 4,
    sellerName: 'Rana Usman Mobile Zone',
    sellerPhone: '03019876543',
    sellerCity: 'Sargodha',
    sellerArea: 'Trust Plaza, Kutchery Bazaar',
    title: 'iPhone 15 Pro Max 256GB Natural Titanium (PTA Approved)',
    categoryId: 1,
    categoryName: 'Mobiles',
    subcategory: 'Smartphones',
    price: 365000,
    condition: 'Used',
    description: 'Physical Dual SIM variant, official FBR PTA approved with tax slip. 94% original battery health, complete box with original braided USB-C cable. 10/10 scratchless body.',
    city: 'Sargodha',
    area: 'Trust Plaza, Shop #14',
    status: 'published',
    isFeatured: false,
    images: [phoneImg],
    views: 310,
    createdAt: '2 days ago'
  }
];

export const INITIAL_JOBS: JobPost[] = [
  {
    id: 1,
    userId: 2,
    posterName: 'Malik Tariq Dairy Farm',
    postType: 'need_worker',
    title: 'Dairy Farm Worker / Milker Needed (Shaheenabad)',
    category: 'Livestock & Agriculture',
    skills: 'Animal Care, Milking, Feed Mixing, Night Watch',
    experience: '1-2 Years Experience',
    workingHours: 'Full-time (Residence + Food Provided)',
    salaryOrPayment: 'Rs. 32,000 / month + Living',
    city: 'Shaheenabad',
    area: 'Canal Colony Dairy Belt',
    description: 'Looking for an experienced dairy farm helper to assist with 25 dairy cows. Tasks include daily milking, feeding wanda/silage, and general maintenance. Free private accommodation and meals provided on farm premises.',
    phone: '03001234567',
    whatsapp: '03001234567',
    status: 'published',
    views: 184,
    createdAt: '3 hours ago'
  },
  {
    id: 2,
    userId: 5,
    posterName: 'Asad Ali',
    postType: 'need_job',
    title: 'Experienced Heavy Tractor & Loader Driver Seeking Work',
    category: 'Driving & Machinery',
    skills: 'Fiat 480, Belarus, Kinnow Orchard Ploughing, Laser Levelling',
    experience: '5+ Years Farm Machinery Experience',
    workingHours: 'Daily or Monthly Contract',
    salaryOrPayment: 'Negotiable / Market Rate',
    city: 'Sargodha',
    area: 'Fatima Jinnah Road / 46-SB',
    description: 'Expert tractor operator with valid HTV driving license. Highly skilled in laser levelling, rotary tilling in citrus kinnow orchards, and farm haulage. Hardworking, punctual, non-smoker.',
    phone: '03031122334',
    whatsapp: '03031122334',
    status: 'published',
    views: 142,
    createdAt: '1 day ago'
  },
  {
    id: 3,
    userId: 4,
    posterName: 'Rana Usman Mobile Zone',
    postType: 'need_worker',
    title: 'Mobile Repairing Technician & Sales Counter Staff',
    category: 'Sales & Technical',
    skills: 'Hardware/Software Repairing, Customer Dealing, POS Management',
    experience: 'Min 1 Year Experience in Mobile Shop',
    workingHours: '11:00 AM to 10:00 PM',
    salaryOrPayment: 'Rs. 35,000 + Sales Commission',
    city: 'Sargodha',
    area: 'Trust Plaza, University Road',
    description: 'Urgent requirement for an honest sales person and technician for busy mobile retail outlet in Trust Plaza. Basic knowledge of smartphone unlocking and screen replacement preferred.',
    phone: '03019876543',
    whatsapp: '03019876543',
    status: 'published',
    views: 260,
    createdAt: '2 days ago'
  }
];

export const INITIAL_PAYMENTS: ActivationPayment[] = [
  {
    id: 1,
    userId: 2,
    userName: 'Malik Tariq Dairy Farm',
    userCity: 'Shaheenabad',
    userPhone: '03001234567',
    amount: 1000,
    method: 'EasyPaisa',
    senderNumber: '03001234567',
    transactionId: 'EP8472910382',
    paymentScreenshot: cowImg,
    whatsappScreenshot: heroImg,
    status: 'approved',
    adminNote: 'Verified in EasyPaisa account and verified WhatsApp channel member.',
    createdAt: 'Feb 15, 2026'
  },
  {
    id: 2,
    userId: 5,
    userName: 'Asad Ali (New User)',
    userCity: 'Sargodha',
    userPhone: '03031122334',
    amount: 1000,
    method: 'EasyPaisa',
    senderNumber: '03031122334',
    transactionId: 'EP9928172654',
    paymentScreenshot: phoneImg,
    whatsappScreenshot: heroImg,
    status: 'pending',
    adminNote: 'Awaiting admin dual-verification of Rs. 1,000 payment and WhatsApp channel screenshot.',
    createdAt: 'Today 10:15 AM'
  }
];

export const INITIAL_REPORTS: ReportItem[] = [
  {
    id: 1,
    targetType: 'listing',
    targetId: 4,
    targetTitle: 'iPhone 15 Pro Max 256GB',
    reporterName: 'Local Buyer',
    reason: 'Price Verification',
    details: 'Seller was asked about warranty and provided polite response.',
    status: 'resolved',
    createdAt: 'Yesterday'
  }
];
