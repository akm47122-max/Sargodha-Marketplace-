import { SiteSettings, AnnouncementItem } from '../types';

export const DEFAULT_SITE_SETTINGS: SiteSettings = {
  // 1. General Settings
  websiteName: 'SargodhaMart',
  logoText: 'SARGODHAMART',
  logoImageUrl: '',
  faviconUrl: '',
  tagline: 'Buy • Sell • Jobs • Grow',
  websiteDescription:
    'Premier local marketplace and employment platform for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp trading, verified Rs. 1,000 lifetime seller activation, zero middleman commission.',
  contactEmail: 'support@sargodhamart.com',
  officialPhone: '03127453108',
  officialWhatsapp: '03127453108',
  officialAddress: 'Trust Plaza / Club Road, Sargodha, Punjab, Pakistan',
  copyrightText: '© 2026 SARGODHAMART. All rights reserved. Production-Ready for Hostinger / cPanel.',

  // 2. WhatsApp & Contact Settings
  whatsappChannelUrl: 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g',
  whatsappGroupUrl: 'https://chat.whatsapp.com/sample-sargodha-community',
  officialWhatsappNumber: '03127453108',
  officialCallNumber: '03127453108',
  whatsappChannelName: 'SargodhaMart Official Channel',
  whatsappGroupName: 'Sargodha Local Trading Group',
  whatsappBtnText: 'WhatsApp',
  callBtnText: 'Call',
  followChannelBtnText: 'Follow Official Channel',
  joinGroupBtnText: 'Join WhatsApp Group',
  isWhatsappChannelEnabled: true,
  isWhatsappGroupEnabled: true,
  isWhatsappContactEnabled: true,
  isCallButtonEnabled: true,

  // 3. Social Media Settings
  socialLinks: [
    {
      id: 'soc-1',
      platform: 'WhatsApp Channel',
      title: 'Official Channel',
      url: 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g',
      isEnabled: true,
      displayOrder: 1,
    },
    {
      id: 'soc-2',
      platform: 'Facebook',
      title: 'Facebook Page',
      url: 'https://facebook.com/sargodhamart',
      isEnabled: true,
      displayOrder: 2,
    },
    {
      id: 'soc-3',
      platform: 'Instagram',
      title: 'Instagram Handle',
      url: 'https://instagram.com/sargodhamart',
      isEnabled: true,
      displayOrder: 3,
    },
    {
      id: 'soc-4',
      platform: 'YouTube',
      title: 'YouTube Channel',
      url: 'https://youtube.com/@sargodhamart',
      isEnabled: true,
      displayOrder: 4,
    },
    {
      id: 'soc-5',
      platform: 'TikTok',
      title: 'TikTok Account',
      url: 'https://tiktok.com/@sargodhamart',
      isEnabled: true,
      displayOrder: 5,
    },
  ],

  // 4. SEO Settings
  defaultSeoTitle: 'SargodhaMart - Buy • Sell • Jobs • Grow (Sargodha | Shaheenabad | Sillanwali)',
  defaultSeoDescription:
    'Dedicated local online marketplace and employment hub for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp trading, verified Rs. 1,000 lifetime activation.',
  defaultKeywords: 'sargodha classifieds, sargodha buy and sell, sillanwali kinnow, shaheenabad mandi, sargodha jobs, rozgar sargodha',
  ogTitle: 'SargodhaMart - Buy • Sell • Jobs • Grow',
  ogDescription: 'Premier local marketplace & employment platform for Sargodha, Shaheenabad, and Sillanwali.',
  ogImageUrl: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80',
  twitterTitle: 'SargodhaMart - Buy • Sell • Jobs • Grow',
  twitterDescription: 'Premier local marketplace & employment platform for Sargodha, Shaheenabad, and Sillanwali.',
  twitterImageUrl: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80',

  // 5. Homepage Settings
  heroHeading: 'Buy • Sell • Jobs • Grow',
  heroHeadingHighlight: 'Your Local Online Marketplace',
  heroDescription:
    'The premier trading & employment portal for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp dealing, verified Rs. 1,000 lifetime seller activation, zero middleman fees.',
  heroBannerImageUrl: '',
  heroButtonText: 'Find Products & Jobs',
  isFeaturedProductsEnabled: true,
  isLatestProductsEnabled: true,
  isJobsSectionEnabled: true,
  isCategoriesSectionEnabled: true,
  isWorkflowGuideEnabled: true,
  whatsappCtaText: 'Join 5,000+ local members on our WhatsApp Channel for instant verified deals!',

  // 6. Marketplace Settings
  maxImagesPerListing: 3,
  allowPriceNegotiable: true,
  defaultListingDurationDays: 90,
  listingRulesText:
    'Only genuine products from Sargodha, Shaheenabad, and Sillanwali. No weapons, counterfeit currency, or prohibited goods. Every ad must have authentic local contact.',
  reportReasons: [
    'Scam / Fraud / Fake Item',
    'Wrong Price or Misleading Info',
    'Prohibited / Offensive Content',
    'Already Sold / Job Position Filled',
    'Duplicate Listing',
    'Other Reason',
  ],

  // 7. Jobs Settings
  isNeedJobEnabled: true,
  isNeedWorkerEnabled: true,
  jobRulesText:
    'Only genuine local employment opportunities across Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp contact only. No illegal recruitment fees.',
  jobSalaryGuidance: 'Daily: Rs. 1,000 - 3,500 | Monthly: Rs. 25,000 - 85,000',
  jobSeoPattern: '{title} – {city} | SargodhaMart Jobs',

  // 8. Seller Activation Settings
  activationFee: 1000,
  paymentAccountTitle: 'Muhammad Akram Tayyab',
  easyPaisaNumber: '03127453108',
  jazzCashNumber: '03127453108',
  bankDetails: 'Bank of Punjab (BOP) - Sargodha Main Branch | A/C: 03127453108',
  paymentInstructions:
    'Transfer exactly Rs. 1,000 one-time fee via EasyPaisa or JazzCash to 03127453108 (Title: Muhammad Akram Tayyab). Save the receipt screenshot.',
  transactionIdInstructions:
    'Enter the exact 10 to 12 digit TRX ID / TID from your SMS confirmation.',
  isPaymentScreenshotRequired: true,
  isWhatsappFollowScreenshotRequired: false,
  activationUnderReviewNotice:
    'Your proofs are under review by the SargodhaMart verification team. Approvals are completed typically within 15-30 minutes.',

  // 8. Notifications & Messages
  registrationSuccessMsg: 'Account created! Complete one-time Rs. 1,000 verification to start posting.',
  activationSubmittedMsg: 'Payment transfer proof submitted! Awaiting admin verification.',
  activationApprovedMsg: 'Account ACTIVATED! You can now post unlimited free products & jobs directly.',
  activationRejectedMsg: 'Activation request rejected. Please verify your transfer details or contact WhatsApp support.',
  listingPublishedMsg: 'Your product is now LIVE directly in SargodhaMart!',
  jobPublishedMsg: 'Your job requirement / work post is now directly published!',

  // 9. Maintenance Settings
  isMaintenanceMode: false,
  maintenanceTitle: 'SargodhaMart Scheduled System Upgrade',
  maintenanceMessage:
    'We are performing routine maintenance to improve your marketplace experience. We will be back online shortly. Authorized administrators can still access the portal.',
  isNewRegistrationEnabled: true,
  isNewListingEnabled: true,
  isJobPostingEnabled: true,

  // 10. Telegram Automation Settings
  telegramEnabled: false,
  telegramChannelId: '-1003328935535',
  telegramAutoPublishProducts: true,
  telegramAutoPublishJobs: true,
  telegramAutoPublishAnnouncements: true,
  telegramTokenConfigured: false,
};

export const INITIAL_ANNOUNCEMENTS: AnnouncementItem[] = [
  {
    id: 1,
    title: '📢 Official WhatsApp Channel Now Live!',
    message: 'Join 5,000+ local citizens across Sargodha, Shaheenabad & Sillanwali for daily updates & alerts.',
    buttonText: 'Join Channel',
    buttonUrl: 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g',
    displayLocation: 'top_bar',
    isHighlighted: true,
    isActive: true,
    createdAt: '2026-10-01',
  },
  {
    id: 2,
    title: '🍊 Citrus & Kinnow Season Opening in Sillanwali Mandi',
    message: 'Orchard owners and commission agents can post seasonal fruit deals and labor requirements with zero commission.',
    buttonText: 'View Citrus Deals',
    buttonUrl: '#',
    displayLocation: 'homepage_banner',
    isHighlighted: false,
    isActive: true,
    createdAt: '2026-10-02',
  },
];
