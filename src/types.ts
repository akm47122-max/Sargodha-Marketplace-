export type City = 'Sargodha' | 'Shaheenabad' | 'Sillanwali';
export type Condition = 'New' | 'Used' | 'Refurbished';
export type ActivationStatus = 'pending' | 'active' | 'rejected' | 'suspended';
export type JobPostType = 'need_job' | 'need_worker';

// ==========================================
// User Telegram Number & Verification Status
// ==========================================
export type TelegramVerificationStatus = 'NONE' | 'PENDING' | 'APPROVED' | 'REJECTED';
export type TelegramVisibility = 'VISIBLE' | 'HIDDEN';
export type ContactPrivacy = 'SHOW_ALL' | 'WHATSAPP_ONLY' | 'CALL_WHATSAPP' | 'HIDE_PHONE';

export interface UserTelegramAuditLog {
  id: number;
  userId: number;
  userName: string;
  telegramNumber: string;
  status: TelegramVerificationStatus;
  actionType: 'SUBMIT' | 'APPROVE' | 'REJECT' | 'REVOKE' | 'CHANGE_INVALIDATED';
  reviewedBy?: string;
  reviewedAt?: string;
  rejectionReason?: string;
  timestamp: string;
}

// ==========================================
// Location Hierarchy: Division → District → Tehsil → Area
// ==========================================
export interface Division {
  id: number;
  name: string;
  isActive: boolean;
}

export interface District {
  id: number;
  divisionId: number;
  name: string;
  isActive: boolean;
}

export interface Tehsil {
  id: number;
  districtId: number;
  name: string;
  isActive: boolean;
}

export interface AreaLocation {
  id: number;
  tehsilId: number;
  name: string;
  isActive: boolean;
}

export interface UserProfile {
  id: number;
  name: string;
  mobile: string;
  email: string;
  city: City;
  area: string;
  role: 'user' | 'admin' | 'super_admin';
  activationStatus: ActivationStatus;
  joinedDate: string;
  // User Telegram Number & Verification
  telegramNumber?: string;
  telegramStatus?: TelegramVerificationStatus;
  telegramSubmittedAt?: string;
  telegramReviewedBy?: string;
  telegramReviewedAt?: string;
  telegramRejectionReason?: string;
  telegramVisibility?: TelegramVisibility;
  contactPrivacy?: ContactPrivacy;
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  icon: string;
  subcategories: string[];
}

export interface Listing {
  id: number;
  userId: number;
  sellerName: string;
  sellerPhone: string;
  sellerCity: City;
  sellerArea: string;
  sellerWhatsappGroup?: string;
  title: string;
  categoryId: number;
  categoryName: string;
  subcategory?: string;
  price: number;
  condition: Condition;
  description: string;
  city: City;
  area: string;
  exactLocation?: string;
  divisionId?: number;
  districtId?: number;
  tehsilId?: number;
  areaId?: number;
  districtName?: string;
  tehsilName?: string;
  areaName?: string;
  telegramPublished?: boolean;
  telegramMessageId?: string;
  status: 'published' | 'pending' | 'rejected' | 'disabled';
  isFeatured: boolean;
  images: string[];
  views: number;
  createdAt: string;
}

export interface JobPost {
  id: number;
  userId: number;
  posterName: string;
  postType: JobPostType;
  title: string;
  category: string;
  skills: string;
  experience?: string;
  workingHours?: string;
  salaryOrPayment?: string;
  city: City;
  area: string;
  divisionId?: number;
  districtId?: number;
  tehsilId?: number;
  areaId?: number;
  districtName?: string;
  tehsilName?: string;
  areaName?: string;
  telegramPublished?: boolean;
  telegramMessageId?: string;
  description: string;
  phone: string;
  whatsapp: string;
  status: 'published' | 'disabled';
  views: number;
  createdAt: string;
}

export interface ActivationPayment {
  id: number;
  userId: number;
  userName: string;
  userCity: City;
  userPhone: string;
  amount: number;
  method: 'EasyPaisa' | 'JazzCash' | 'Bank Transfer';
  senderNumber: string;
  transactionId: string;
  paymentScreenshot: string;
  whatsappScreenshot: string;
  status: 'pending' | 'approved' | 'rejected';
  activationType?: 'FREE' | 'PAID';
  isFreeSlot?: boolean;
  adminNote?: string;
  reviewedBy?: string;
  reviewedAt?: string;
  rejectionReason?: string;
  approvedAt?: string;
  createdAt: string;
}

export interface ReportItem {
  id: number;
  targetType: 'listing' | 'job' | 'seller';
  targetId: number;
  targetTitle: string;
  reporterName: string;
  reason: string;
  details: string;
  status: 'pending' | 'resolved' | 'dismissed';
  createdAt: string;
}

export interface AnnouncementItem {
  id: number;
  title: string;
  message: string;
  imageUrl?: string;
  buttonText?: string;
  buttonUrl?: string;
  displayLocation: 'top_bar' | 'homepage_banner' | 'popup';
  isHighlighted: boolean;
  isActive: boolean;
  startDate?: string;
  expiryDate?: string;
  telegramPublished?: boolean;
  telegramMessageId?: string;
  createdAt: string;
}

export interface SocialLink {
  id: string;
  platform: 'Facebook' | 'Instagram' | 'YouTube' | 'TikTok' | 'WhatsApp Channel' | 'Other';
  title: string;
  url: string;
  isEnabled: boolean;
  displayOrder: number;
}

export interface SiteSettings {
  // 1. General Settings
  websiteName: string;
  logoText: string;
  logoImageUrl?: string;
  faviconUrl?: string;
  tagline: string;
  websiteDescription: string;
  contactEmail: string;
  officialPhone: string;
  officialWhatsapp: string;
  officialAddress: string;
  copyrightText: string;

  // 2. WhatsApp & Contact Settings
  whatsappChannelUrl: string;
  whatsappGroupUrl: string;
  officialWhatsappNumber: string;
  officialCallNumber: string;
  whatsappChannelName: string;
  whatsappGroupName: string;
  whatsappBtnText: string;
  callBtnText: string;
  followChannelBtnText: string;
  joinGroupBtnText: string;
  isWhatsappChannelEnabled: boolean;
  isWhatsappGroupEnabled: boolean;
  isWhatsappContactEnabled: boolean;
  isCallButtonEnabled: boolean;

  // 3. Social Media Settings
  socialLinks: SocialLink[];

  // 4. SEO Settings
  defaultSeoTitle: string;
  defaultSeoDescription: string;
  defaultKeywords: string;
  ogTitle: string;
  ogDescription: string;
  ogImageUrl: string;
  twitterTitle: string;
  twitterDescription: string;
  twitterImageUrl: string;

  // 5. Homepage Settings
  heroHeading: string;
  heroHeadingHighlight: string;
  heroDescription: string;
  heroBannerImageUrl?: string;
  heroButtonText: string;
  isFeaturedProductsEnabled: boolean;
  isLatestProductsEnabled: boolean;
  isJobsSectionEnabled: boolean;
  isCategoriesSectionEnabled: boolean;
  isWorkflowGuideEnabled: boolean;
  whatsappCtaText: string;

  // 6. Marketplace Settings
  maxImagesPerListing: number;
  allowPriceNegotiable: boolean;
  defaultListingDurationDays: number;
  listingRulesText: string;
  reportReasons: string[];

  // 7. Jobs Settings
  isNeedJobEnabled: boolean;
  isNeedWorkerEnabled: boolean;
  jobRulesText: string;
  jobSalaryGuidance: string;
  jobSeoPattern: string;

  // 8. Seller Activation Settings
  activationFee: number;
  freeSellerActivationLimit: number;
  isFreeSellerOfferActive: boolean;
  paymentAccountTitle: string;
  easyPaisaNumber: string;
  jazzCashNumber: string;
  bankDetails: string;
  paymentInstructions: string;
  transactionIdInstructions: string;
  isPaymentScreenshotRequired: boolean;
  isWhatsappFollowScreenshotRequired: boolean;
  activationUnderReviewNotice: string;

  // 8. Notifications & Messages
  registrationSuccessMsg: string;
  activationSubmittedMsg: string;
  activationApprovedMsg: string;
  activationRejectedMsg: string;
  listingPublishedMsg: string;
  jobPublishedMsg: string;

  // 9. Maintenance Settings
  isMaintenanceMode: boolean;
  maintenanceTitle: string;
  maintenanceMessage: string;
  isNewRegistrationEnabled: boolean;
  isNewListingEnabled: boolean;
  isJobPostingEnabled: boolean;

  // 10. Telegram Automation Settings
  telegramEnabled: boolean;
  telegramChannelId: string;
  telegramAutoPublishProducts: boolean;
  telegramAutoPublishJobs: boolean;
  telegramAutoPublishAnnouncements: boolean;
  telegramTokenConfigured: boolean;

  // 11. Promotion & Feature Settings
  promoProductEnabled: boolean;
  promoProductPrice: number;
  promoProductDuration: number;
  promoProductTitle: string;
  promoProductDesc: string;
  promoSkillEnabled: boolean;
  promoSkillPrice: number;
  promoSkillDuration: number;
  promoSkillTitle: string;
  promoSkillDesc: string;
  telegramAutoPublishPromotions: boolean;
  jobEmailNotificationsEnabled: boolean;
}

export type PromotionType = 'PRODUCT' | 'SKILL';
export type PromotionStatus = 'PENDING' | 'APPROVED' | 'REJECTED' | 'EXPIRED' | 'CANCELLED';

export interface PromotionPayment {
  id: number;
  userId: number;
  userName: string;
  userPhone?: string;
  promotionType: PromotionType;
  entityType: 'PRODUCT' | 'SKILL_PROFILE';
  entityId: number;
  entityTitle: string;
  amount: number;
  durationDays: number;
  transactionReference: string;
  paymentScreenshot: string;
  status: PromotionStatus;
  rejectionReason?: string;
  submittedAt: string;
  reviewedAt?: string;
  reviewedBy?: string;
  startAt?: string;
  endAt?: string;
  createdAt: string;
}

export interface DigitalSkillProfile {
  id: number;
  userId: number;
  fullName: string;
  username: string;
  profilePhoto: string;
  professionalTitle: string;
  mainSkill: string;
  category: string;
  skills: string[];
  experience: string;
  about: string;
  divisionId?: number;
  districtId?: number;
  tehsilId?: number;
  areaId?: number;
  divisionName?: string;
  districtName?: string;
  tehsilName?: string;
  areaName?: string;
  phone: string;
  whatsapp: string;
  email?: string;
  showEmail?: boolean;
  portfolioLinks?: { label: string; url: string }[];
  servicesOffered: string[];
  startingRate?: number;
  availability: 'Full-time' | 'Part-time' | 'Freelance' | 'Hourly';
  cvFilePath?: string;
  status: 'pending' | 'approved' | 'rejected';
  isFeatured: boolean;
  featuredStartAt?: string;
  featuredEndAt?: string;
  createdAt: string;
}

export interface TelegramLogItem {
  id: number;
  contentType: 'product' | 'job' | 'announcement' | 'test' | 'advertisement';
  contentId?: number;
  title: string;
  status: 'success' | 'failed';
  messageId?: string;
  errorMessage?: string;
  timestamp: string;
}

// ==========================================
// Advertising Pricing & Management System
// ==========================================
export type AdPlacement =
  | 'Homepage'
  | 'Category Page'
  | 'Product Page'
  | 'Jobs Page'
  | 'Digital Skills Page'
  | 'Search Results'
  | 'Location Page'
  | 'Telegram Channel'
  | 'Multiple Website Locations';

export interface AdvertisingPackage {
  id: number;
  name: string;
  adType: string;
  description: string;
  price: number;
  durationDays: number;
  placements: AdPlacement[];
  telegramEnabled: boolean;
  recommended: boolean;
  isActive: boolean;
  displayOrder: number;
  features: string[];
  createdAt?: string;
  updatedAt?: string;
}

export type AdStatus = 'PENDING' | 'APPROVED' | 'ACTIVE' | 'EXPIRED' | 'REJECTED' | 'SUSPENDED';

export interface Advertisement {
  id: number;
  userId: number;
  userName: string;
  userPhone?: string;
  userEmail?: string;
  packageId: number;
  packageName: string;
  adType: string;
  title: string;
  description: string;
  imageUrl: string;
  targetUrl?: string;
  phone: string;
  whatsapp: string;
  location: string;
  placements: AdPlacement[];
  status: AdStatus;
  amountPaid: number;
  durationDays: number;
  transactionReference: string;
  paymentScreenshot: string;
  rejectionReason?: string;
  startAt?: string;
  endAt?: string;
  telegramEnabled: boolean;
  telegramStatus?: 'pending' | 'published' | 'failed' | 'not_applicable';
  telegramMessageId?: string;
  telegramPublishedAt?: string;
  telegramError?: string;
  createdAt: string;
  updatedAt?: string;
}

export interface AdvertisingPayment {
  id: number;
  advertisementId: number;
  userId: number;
  userName: string;
  packageId: number;
  packageName: string;
  amount: number;
  durationDays: number;
  transactionReference: string;
  paymentScreenshot: string;
  status: 'PENDING' | 'APPROVED' | 'REJECTED' | 'REFUNDED';
  rejectionReason?: string;
  reviewedBy?: string;
  reviewedAt?: string;
  createdAt: string;
}

