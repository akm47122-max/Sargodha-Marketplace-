import React, { useState, useEffect } from 'react';
import {
  ShoppingBag,
  Briefcase,
  LayoutDashboard,
  Shield,
  PlusCircle,
  Flag,
  X,
  CheckCircle2,
  Sparkles,
  LogIn,
  UserCheck,
  Megaphone,
  Wrench,
  ExternalLink,
  ChevronRight,
} from 'lucide-react';
import {
  Listing,
  JobPost,
  UserProfile,
  ActivationPayment,
  ReportItem,
  City,
  SiteSettings,
  AnnouncementItem,
  TelegramLogItem,
  District,
  Tehsil,
  AreaLocation,
  DigitalSkillProfile,
  PromotionPayment,
  AdvertisingPackage,
  Advertisement,
  AdvertisingPayment,
} from './types';
import {
  INITIAL_CATEGORIES,
  INITIAL_USERS,
  INITIAL_LISTINGS,
  INITIAL_JOBS,
  INITIAL_PAYMENTS,
  INITIAL_REPORTS,
  INITIAL_DIGITAL_SKILLS,
  INITIAL_PROMOTIONS,
} from './data/mockData';
import {
  DEFAULT_ADVERTISING_PACKAGES,
  INITIAL_ADVERTISEMENTS,
  INITIAL_ADVERTISING_PAYMENTS,
} from './data/advertisingData';
import { DEFAULT_SITE_SETTINGS, INITIAL_ANNOUNCEMENTS } from './data/defaultSettings';
import { INITIAL_DISTRICTS, INITIAL_TEHSILS, INITIAL_AREAS } from './data/locationData';

import { Navbar } from './components/Navbar';
import { HeroSection } from './components/HeroSection';
import { ProductCard } from './components/ProductCard';
import { JobsSection } from './components/JobsSection';
import { DigitalSkillsSection } from './components/DigitalSkillsSection';
import { SkillProfileModal } from './components/SkillProfileModal';
import { PostSkillModal } from './components/PostSkillModal';
import { PromoteModal } from './components/PromoteModal';
import { CreateAdModal } from './components/CreateAdModal';
import { SponsoredBanner } from './components/SponsoredBanner';
import { AIAssistantWidget } from './components/AIAssistantWidget';
import { ProductDetailModal } from './components/ProductDetailModal';
import { JobDetailModal } from './components/JobDetailModal';
import { PostAdModal } from './components/PostAdModal';
import { PostJobModal } from './components/PostJobModal';
import { ActivationModal } from './components/ActivationModal';
import { AuthModal } from './components/AuthModal';
import { AdminPanel } from './components/AdminPanel';
import { UserDashboard } from './components/UserDashboard';
import { updatePageSEO, getProductSEO, getJobSEO, resetDefaultSEO } from './utils/seo';
import { getActiveAnnouncements } from './utils/settings';
import { broadcastToTelegram } from './utils/telegram';

export function App() {
  // Navigation State
  const [currentView, setCurrentView] = useState<'home' | 'jobs' | 'skills' | 'dashboard' | 'admin' | 'post-ad' | 'post-job'>('home');

  // Central Website Settings State with localStorage persistence
  const [settings, setSettings] = useState<SiteSettings>(() => {
    const saved = localStorage.getItem('sargodha_settings');
    if (saved) {
      try {
        return { ...DEFAULT_SITE_SETTINGS, ...JSON.parse(saved) };
      } catch (e) {}
    }
    return DEFAULT_SITE_SETTINGS;
  });

  // Announcements State with localStorage persistence
  const [announcements, setAnnouncements] = useState<AnnouncementItem[]>(() => {
    const saved = localStorage.getItem('sargodha_announcements');
    if (saved) {
      try {
        return JSON.parse(saved);
      } catch (e) {}
    }
    return INITIAL_ANNOUNCEMENTS;
  });

  const [dismissedPopupId, setDismissedPopupId] = useState<number | null>(null);

  // Core Data States with localStorage persistence
  const [users, setUsers] = useState<UserProfile[]>(() => {
    const saved = localStorage.getItem('sargodha_users');
    return saved ? JSON.parse(saved) : INITIAL_USERS;
  });

  // Website opens as Public Marketplace by default (Guest mode)
  const [currentUser, setCurrentUser] = useState<UserProfile | null>(() => {
    const saved = localStorage.getItem('sargodha_current_user');
    if (saved) {
      try {
        return JSON.parse(saved);
      } catch (e) {}
    }
    return null; // Public marketplace first
  });

  const [listings, setListings] = useState<Listing[]>(() => {
    const saved = localStorage.getItem('sargodha_listings');
    return saved ? JSON.parse(saved) : INITIAL_LISTINGS;
  });

  const [jobs, setJobs] = useState<JobPost[]>(() => {
    const saved = localStorage.getItem('sargodha_jobs');
    return saved ? JSON.parse(saved) : INITIAL_JOBS;
  });

  const [payments, setPayments] = useState<ActivationPayment[]>(() => {
    const saved = localStorage.getItem('sargodha_payments');
    return saved ? JSON.parse(saved) : INITIAL_PAYMENTS;
  });

  const [reports, setReports] = useState<ReportItem[]>(() => {
    const saved = localStorage.getItem('sargodha_reports');
    return saved ? JSON.parse(saved) : INITIAL_REPORTS;
  });

  // Digital Skills State with localStorage persistence
  const [skills, setSkills] = useState<DigitalSkillProfile[]>(() => {
    const saved = localStorage.getItem('sargodha_skills');
    return saved ? JSON.parse(saved) : INITIAL_DIGITAL_SKILLS;
  });

  // Separate Promotions State with localStorage persistence
  const [promotions, setPromotions] = useState<PromotionPayment[]>(() => {
    const saved = localStorage.getItem('sargodha_promotions');
    return saved ? JSON.parse(saved) : INITIAL_PROMOTIONS;
  });

  // Separate Advertising System States with localStorage persistence
  const [advertisingPackages, setAdvertisingPackages] = useState<AdvertisingPackage[]>(() => {
    const saved = localStorage.getItem('sargodha_ad_packages');
    return saved ? JSON.parse(saved) : DEFAULT_ADVERTISING_PACKAGES;
  });

  const [advertisements, setAdvertisements] = useState<Advertisement[]>(() => {
    const saved = localStorage.getItem('sargodha_advertisements');
    return saved ? JSON.parse(saved) : INITIAL_ADVERTISEMENTS;
  });

  const [advertisingPayments, setAdvertisingPayments] = useState<AdvertisingPayment[]>(() => {
    const saved = localStorage.getItem('sargodha_ad_payments');
    return saved ? JSON.parse(saved) : INITIAL_ADVERTISING_PAYMENTS;
  });

  const [favorites, setFavorites] = useState<number[]>(() => {
    const saved = localStorage.getItem('sargodha_favorites');
    return saved ? JSON.parse(saved) : [1];
  });

  // Telegram Publication Logs
  const [telegramLogs, setTelegramLogs] = useState<TelegramLogItem[]>(() => {
    const saved = localStorage.getItem('sargodha_telegram_logs');
    if (saved) {
      try {
        return JSON.parse(saved);
      } catch (e) {}
    }
    return [
      {
        id: 1,
        contentType: 'announcement',
        title: '📢 Official WhatsApp Channel Now Live!',
        status: 'success',
        messageId: '108',
        timestamp: 'Today, 10:15 AM',
      },
    ];
  });

  const handleAddTelegramLog = (log: TelegramLogItem) => {
    setTelegramLogs((prev) => {
      const updated = [log, ...prev].slice(0, 50);
      try {
        localStorage.setItem('sargodha_telegram_logs', JSON.stringify(updated));
      } catch (e) {}
      return updated;
    });
  };

  // Filter States
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<number | 'all'>('all');
  const [selectedCity, setSelectedCity] = useState<City | 'All'>('All');

  // Modals
  const [isAuthOpen, setIsAuthOpen] = useState(false);
  const [selectedListing, setSelectedListing] = useState<Listing | null>(null);
  const [selectedJob, setSelectedJob] = useState<JobPost | null>(null);
  const [selectedSkill, setSelectedSkill] = useState<DigitalSkillProfile | null>(null);
  const [isPostAdOpen, setIsPostAdOpen] = useState(false);
  const [isPostJobOpen, setIsPostJobOpen] = useState(false);
  const [isPostSkillOpen, setIsPostSkillOpen] = useState(false);
  const [editingSkill, setEditingSkill] = useState<DigitalSkillProfile | null>(null);
  const [isPromoteOpen, setIsPromoteOpen] = useState(false);
  const [promotePreselectedType, setPromotePreselectedType] = useState<'PRODUCT' | 'SKILL'>('PRODUCT');
  const [promotePreselectedId, setPromotePreselectedId] = useState<number | undefined>(undefined);
  const [isCreateAdOpen, setIsCreateAdOpen] = useState(false);
  const [createAdPreselectedPkgId, setCreateAdPreselectedPkgId] = useState<number | undefined>(undefined);
  const [isActivationOpen, setIsActivationOpen] = useState(false);
  const [reportTarget, setReportTarget] = useState<{ type: 'listing' | 'job'; item: Listing | JobPost } | null>(null);
  const [reportReason, setReportReason] = useState('Scam / Fake');
  const [reportDetails, setReportDetails] = useState('');

  // Notification Toast
  const [toastMessage, setToastMessage] = useState<string | null>(null);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(null), 3500);
  };

  // Sync state to localStorage
  useEffect(() => {
    localStorage.setItem('sargodha_settings', JSON.stringify(settings));
  }, [settings]);

  useEffect(() => {
    localStorage.setItem('sargodha_announcements', JSON.stringify(announcements));
  }, [announcements]);

  useEffect(() => {
    localStorage.setItem('sargodha_users', JSON.stringify(users));
  }, [users]);

  useEffect(() => {
    if (currentUser) {
      localStorage.setItem('sargodha_current_user', JSON.stringify(currentUser));
    } else {
      localStorage.removeItem('sargodha_current_user');
    }
  }, [currentUser]);

  useEffect(() => {
    localStorage.setItem('sargodha_listings', JSON.stringify(listings));
  }, [listings]);

  useEffect(() => {
    localStorage.setItem('sargodha_jobs', JSON.stringify(jobs));
  }, [jobs]);

  useEffect(() => {
    localStorage.setItem('sargodha_payments', JSON.stringify(payments));
  }, [payments]);

  useEffect(() => {
    localStorage.setItem('sargodha_favorites', JSON.stringify(favorites));
  }, [favorites]);

  useEffect(() => {
    localStorage.setItem('sargodha_reports', JSON.stringify(reports));
  }, [reports]);

  useEffect(() => {
    localStorage.setItem('sargodha_skills', JSON.stringify(skills));
  }, [skills]);

  useEffect(() => {
    localStorage.setItem('sargodha_promotions', JSON.stringify(promotions));
  }, [promotions]);

  useEffect(() => {
    localStorage.setItem('sargodha_ad_packages', JSON.stringify(advertisingPackages));
  }, [advertisingPackages]);

  useEffect(() => {
    localStorage.setItem('sargodha_advertisements', JSON.stringify(advertisements));
  }, [advertisements]);

  useEffect(() => {
    localStorage.setItem('sargodha_ad_payments', JSON.stringify(advertisingPayments));
  }, [advertisingPayments]);

  // Automatic Promotion & Advertisement Expiration Routine
  // Promoted items automatically expire after their configured duration.
  // After expiration: status = EXPIRED, product/skill remains on website (NOT deleted),
  // featured badge is removed, normal listing/profile visibility remains.
  // Advertisements automatically stop appearing on the website when duration ends.
  useEffect(() => {
    const now = new Date();
    let hasExpiredChanges = false;

    const updatedPromotions = promotions.map((p) => {
      if (p.status === 'APPROVED' && p.endAt) {
        const endDate = new Date(p.endAt);
        if (now > endDate) {
          hasExpiredChanges = true;
          return { ...p, status: 'EXPIRED' as const };
        }
      }
      return p;
    });

    if (hasExpiredChanges) {
      setPromotions(updatedPromotions);

      // Remove featured badge from expired listings
      setListings((prevListings) =>
        prevListings.map((l) => {
          const hasActivePromo = updatedPromotions.some(
            (p) => p.status === 'APPROVED' && (p.promotionType === 'PRODUCT' || p.entityType === 'PRODUCT') && p.entityId === l.id
          );
          if (!hasActivePromo && l.isFeatured) {
            return { ...l, isFeatured: false };
          }
          return l;
        })
      );

      // Remove featured badge from expired skills
      setSkills((prevSkills) =>
        prevSkills.map((s) => {
          const hasActivePromo = updatedPromotions.some(
            (p) => p.status === 'APPROVED' && (p.promotionType === 'SKILL' || p.entityType === 'SKILL_PROFILE') && p.entityId === s.id
          );
          if (!hasActivePromo && s.isFeatured) {
            return { ...s, isFeatured: false };
          }
          return s;
        })
      );
    }

    // Check Advertisement Expiry
    let hasExpiredAdChanges = false;
    const updatedAds = advertisements.map((ad) => {
      if (ad.status === 'ACTIVE' && ad.endAt) {
        const endDate = new Date(ad.endAt);
        if (now > endDate) {
          hasExpiredAdChanges = true;
          return { ...ad, status: 'EXPIRED' as const };
        }
      }
      return ad;
    });

    if (hasExpiredAdChanges) {
      setAdvertisements(updatedAds);
    }
  }, []);

  // Handle URL parameters for initial load & deep links (?product=123, ?job=123, ?skill=username or /skills/profile/username)
  useEffect(() => {
    if (typeof window === 'undefined') return;
    const params = new URLSearchParams(window.location.search);
    const productId = params.get('product') || params.get('p');
    const jobId = params.get('job') || params.get('j');
    const skillParam = params.get('skill') || params.get('s') || params.get('profile');

    const path = window.location.pathname;
    let pathUsername = '';
    if (path.includes('/skills/profile/')) {
      pathUsername = path.split('/skills/profile/')[1]?.replace(/\/$/, '');
    }
    const targetSkillUser = skillParam || pathUsername;

    if (productId) {
      const matchProduct = listings.find((l) => l.id.toString() === productId);
      if (matchProduct) {
        setSelectedListing(matchProduct);
        setCurrentView('home');
        return;
      }
    }

    if (jobId) {
      const matchJob = jobs.find((j) => j.id.toString() === jobId);
      if (matchJob) {
        setSelectedJob(matchJob);
        setCurrentView('jobs');
        return;
      }
    }

    if (targetSkillUser) {
      const matchSkill = skills.find(
        (s) => s.username.toLowerCase() === targetSkillUser.toLowerCase() || s.id.toString() === targetSkillUser
      );
      if (matchSkill) {
        setSelectedSkill(matchSkill);
        setCurrentView('skills');
        return;
      }
    }
  }, []);

  // Dynamically update document title, OpenGraph meta tags, Twitter cards, and Schema.org structured data
  useEffect(() => {
    if (typeof window === 'undefined') return;
    const origin = window.location.origin;

    if (selectedListing) {
      const seo = getProductSEO(selectedListing, origin, settings.websiteName || 'SargodhaMart');
      updatePageSEO(seo);
      window.history.replaceState({ product: selectedListing.id }, '', `${window.location.pathname}?product=${selectedListing.id}`);
    } else if (selectedJob) {
      const seo = getJobSEO(selectedJob, origin, settings.websiteName || 'SargodhaMart');
      updatePageSEO(seo);
      window.history.replaceState({ job: selectedJob.id }, '', `${window.location.pathname}?job=${selectedJob.id}`);
    } else if (selectedSkill) {
      updatePageSEO({
        title: `${selectedSkill.fullName} - ${selectedSkill.professionalTitle} | SargodhaMart Digital Skills`,
        description: selectedSkill.about.slice(0, 160),
        image: selectedSkill.profilePhoto,
        url: `${origin}/skills/profile/${selectedSkill.username}`,
        type: 'profile',
      });
      window.history.replaceState({ skill: selectedSkill.username }, '', `/skills/profile/${selectedSkill.username}`);
    } else {
      if (currentView === 'jobs') {
        updatePageSEO({
          title: `Jobs & Employment in Sargodha, Shaheenabad & Sillanwali | ${settings.websiteName}`,
          description: 'Explore local job vacancies or find skilled workers across Sargodha district. Direct WhatsApp and phone contact without agent commissions.',
          url: `${origin}/?view=jobs`,
          type: 'website',
        });
        window.history.replaceState({}, '', `${window.location.pathname}`);
      } else if (currentView === 'skills') {
        updatePageSEO({
          title: `Digital Skills & Freelancers Directory in Sargodha | ${settings.websiteName}`,
          description: 'Discover verified Website Designers, Video Editors, Developers, and Digital Marketers in Sargodha, Shaheenabad and Sillanwali. Direct contact.',
          url: `${origin}/?view=skills`,
          type: 'website',
        });
        window.history.replaceState({}, '', `${window.location.pathname}`);
      } else {
        updatePageSEO({
          title: settings.defaultSeoTitle,
          description: settings.defaultSeoDescription,
          image: settings.ogImageUrl,
          url: origin,
          type: 'website',
        });
        window.history.replaceState({}, '', `${window.location.pathname}`);
      }
    }
  }, [selectedListing, selectedJob, selectedSkill, currentView, settings]);

  // Account Switcher / Logout
  const handleSwitchUser = (user: UserProfile) => {
    setCurrentUser(user);
    showToast(`Switched account to: ${user.name} (${user.activationStatus === 'active' ? 'Active Seller' : 'Pending Activation'})`);
  };

  const handleLogout = () => {
    setCurrentUser(null);
    showToast('Logged out. Browsing as public guest.');
  };

  const handleForceLogoutUser = (userId: number) => {
    const targetUser = users.find((u) => u.id === userId);
    const targetName = targetUser ? targetUser.name : `User #${userId}`;

    if (currentUser?.id === userId) {
      setCurrentUser(null);
      showToast(`User session for "${targetName}" was terminated by Administrator.`);
    } else {
      showToast(`Administrator successfully terminated active session for "${targetName}".`);
    }
  };

  // Settings Management Handlers
  const handleSaveSettings = (updated: SiteSettings) => {
    setSettings(updated);
    showToast('Website Settings updated successfully!');
  };

  const handleAddAnnouncement = (item: Omit<AnnouncementItem, 'id' | 'createdAt'>) => {
    const newAnn: AnnouncementItem = {
      ...item,
      id: Date.now(),
      createdAt: new Date().toISOString().split('T')[0],
    };
    setAnnouncements([newAnn, ...announcements]);
    showToast('New announcement published!');

    if (newAnn.isActive && settings.telegramEnabled && settings.telegramAutoPublishAnnouncements) {
      broadcastToTelegram('announcement', newAnn, settings.telegramChannelId, window.location.origin)
        .then((res) => {
          if (res.success) {
            handleAddTelegramLog({
              id: Date.now(),
              contentType: 'announcement',
              contentId: newAnn.id,
              title: newAnn.title,
              status: 'success',
              messageId: res.messageId ? String(res.messageId) : undefined,
              timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            });
            showToast('📢 Announcement broadcasted to Telegram Channel!');
          }
        })
        .catch(() => {});
    }
  };

  const handleUpdateAnnouncement = (id: number, updates: Partial<AnnouncementItem>) => {
    setAnnouncements(announcements.map((a) => (a.id === id ? { ...a, ...updates } : a)));
    showToast('Announcement updated.');
  };

  const handleDeleteAnnouncement = (id: number) => {
    setAnnouncements(announcements.filter((a) => a.id !== id));
    showToast('Announcement removed.');
  };

  // The Guided User Flow
  const handlePostAdClick = () => {
    if (!settings.isNewListingEnabled) {
      showToast('New product listings are temporarily paused for maintenance.');
      return;
    }
    if (!currentUser) {
      setIsAuthOpen(true);
      showToast('Step 1: Please register or login to post an ad.');
      return;
    }
    if (currentUser.activationStatus !== 'active') {
      setIsActivationOpen(true);
      showToast(`Step 2: Rs. ${settings.activationFee.toLocaleString()} lifetime activation required.`);
      return;
    }
    setIsPostAdOpen(true);
  };

  const handlePostJobClick = () => {
    if (!settings.isJobPostingEnabled) {
      showToast('New job postings are temporarily paused for maintenance.');
      return;
    }
    if (!currentUser) {
      setIsAuthOpen(true);
      showToast('Step 1: Please register or login to post jobs.');
      return;
    }
    if (currentUser.activationStatus !== 'active') {
      setIsActivationOpen(true);
      showToast(`Step 2: Rs. ${settings.activationFee.toLocaleString()} lifetime activation required.`);
      return;
    }
    setIsPostJobOpen(true);
  };

  // Auth Callbacks
  const handleRegisterSuccess = (newUser: UserProfile) => {
    if (!settings.isNewRegistrationEnabled) {
      showToast('New account registrations are temporarily paused.');
      return;
    }
    setUsers([newUser, ...users]);
    setCurrentUser(newUser);
    showToast(settings.registrationSuccessMsg || `Account created for ${newUser.name}! Proceeding to lifetime activation.`);
    setIsActivationOpen(true);
  };

  const handleLoginSuccess = (user: UserProfile) => {
    setCurrentUser(user);
    showToast(`Welcome back, ${user.name}!`);
    if (user.activationStatus !== 'active') {
      setIsActivationOpen(true);
    } else {
      setCurrentView('dashboard');
    }
  };

  // Favorites handler
  const handleToggleFavorite = (id: number) => {
    if (favorites.includes(id)) {
      setFavorites(favorites.filter((f) => f !== id));
      showToast('Removed from favorites.');
    } else {
      setFavorites([...favorites, id]);
      showToast('Saved to your favorites.');
    }
  };

  // Add Product Handler (Direct Public Listing)
  const handleAddListing = (adData: Omit<Listing, 'id' | 'views' | 'createdAt'>) => {
    const newListing: Listing = {
      ...adData,
      id: Date.now(),
      views: 1,
      createdAt: 'Just now',
    };
    setListings([newListing, ...listings]);
    showToast(settings.listingPublishedMsg || 'Your product is now LIVE directly in SargodhaMart!');
  };

  // Add Job Handler (Direct Public Listing)
  const handleAddJob = (jobData: Omit<JobPost, 'id' | 'views' | 'createdAt'>) => {
    const newJob: JobPost = {
      ...jobData,
      id: Date.now(),
      views: 1,
      createdAt: 'Just now',
    };
    setJobs([newJob, ...jobs]);
    showToast(settings.jobPublishedMsg || 'Your job requirement / work post is now directly published!');
  };

  // Submit Activation Request (Configurable Fee + WhatsApp Follow)
  const handleSubmitActivation = (data: {
    method: 'EasyPaisa' | 'JazzCash';
    senderNumber: string;
    transactionId: string;
    paymentScreenshot: string;
    whatsappScreenshot: string;
  }) => {
    if (!currentUser) return;
    const newPayment: ActivationPayment = {
      id: Date.now(),
      userId: currentUser.id,
      userName: currentUser.name,
      userCity: currentUser.city,
      userPhone: currentUser.mobile,
      amount: settings.activationFee,
      method: data.method,
      senderNumber: data.senderNumber,
      transactionId: data.transactionId,
      paymentScreenshot: data.paymentScreenshot,
      whatsappScreenshot: data.whatsappScreenshot,
      status: 'pending',
      createdAt: 'Just now',
    };

    setPayments([newPayment, ...payments]);
    showToast(settings.activationSubmittedMsg || 'Payment & WhatsApp follow proofs submitted! Awaiting admin verification.');
  };

  // Admin approves payment & activates account
  const handleApprovePayment = (paymentId: number, userId: number) => {
    setPayments(
      payments.map((p) => (p.id === paymentId ? { ...p, status: 'approved' } : p))
    );
    setUsers(
      users.map((u) => (u.id === userId ? { ...u, activationStatus: 'active' } : u))
    );
    if (currentUser?.id === userId) {
      setCurrentUser({ ...currentUser, activationStatus: 'active' });
    }
    showToast(settings.activationApprovedMsg || 'Account ACTIVATED! User can now post unlimited free ads directly.');
  };

  const handleRejectPayment = (paymentId: number) => {
    setPayments(
      payments.map((p) => (p.id === paymentId ? { ...p, status: 'rejected' } : p))
    );
    showToast(settings.activationRejectedMsg || 'Payment marked as rejected.');
  };

  // Telegram Publication Handler (Manual or Retries)
  const handlePublishToTelegram = async (
    type: 'product' | 'job' | 'announcement',
    id: number,
    force = false
  ): Promise<boolean> => {
    let item: any = null;
    let title = '';

    if (type === 'product') {
      item = listings.find((l) => l.id === id);
      if (!item) return false;
      if (item.status !== 'published') {
        showToast('Only published products can be broadcasted to Telegram.');
        return false;
      }
      title = item.title;
    } else if (type === 'job') {
      item = jobs.find((j) => j.id === id);
      if (!item) return false;
      if (item.status !== 'published') {
        showToast('Only published jobs can be broadcasted to Telegram.');
        return false;
      }
      title = item.title;
    } else if (type === 'announcement') {
      item = announcements.find((a) => a.id === id);
      if (!item) return false;
      title = item.title;
    }

    if (item.telegramPublished && !force) {
      showToast(`Already broadcasted to Telegram (Message ID: ${item.telegramMessageId || 'recorded'}).`);
      return false;
    }

    showToast(`Broadcasting "${title}" to Telegram Channel...`);

    try {
      const res = await broadcastToTelegram(type, item, settings.telegramChannelId, window.location.origin);
      if (res.success) {
        const msgId = res.messageId ? String(res.messageId) : String(Date.now());
        if (type === 'product') {
          setListings((prev) => {
            const updated = prev.map((l) => (l.id === id ? { ...l, telegramPublished: true, telegramMessageId: msgId } : l));
            try { localStorage.setItem('sargodha_listings', JSON.stringify(updated)); } catch (e) {}
            return updated;
          });
        } else if (type === 'job') {
          setJobs((prev) => {
            const updated = prev.map((j) => (j.id === id ? { ...j, telegramPublished: true, telegramMessageId: msgId } : j));
            try { localStorage.setItem('sargodha_jobs', JSON.stringify(updated)); } catch (e) {}
            return updated;
          });
        }

        handleAddTelegramLog({
          id: Date.now(),
          contentType: type,
          contentId: id,
          title: title,
          status: 'success',
          messageId: msgId,
          timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        });

        showToast(`✅ Successfully published "${title}" to Telegram Channel (ID: ${msgId})!`);
        return true;
      } else {
        handleAddTelegramLog({
          id: Date.now(),
          contentType: type,
          contentId: id,
          title: title,
          status: 'failed',
          errorMessage: res.error || 'Failed to publish to Telegram',
          timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        });

        showToast(`⚠️ Telegram API: ${res.error || 'Broadcast failed. Check Bot Token & Channel.'}`);
        return false;
      }
    } catch (err: any) {
      showToast(`⚠️ Network error contacting Telegram API.`);
      return false;
    }
  };

  // Moderation Handlers
  const handleToggleListingStatus = (id: number) => {
    const listing = listings.find((l) => l.id === id);
    if (!listing) return;
    const isNowPublished = listing.status !== 'published';
    const nextStatus: 'published' | 'disabled' = isNowPublished ? 'published' : 'disabled';

    setListings((prev) => {
      const updated = prev.map((l) =>
        l.id === id ? { ...l, status: nextStatus } : l
      );
      try { localStorage.setItem('sargodha_listings', JSON.stringify(updated)); } catch (e) {}
      return updated;
    });

    showToast(isNowPublished ? 'Product published.' : 'Product disabled.');

    // Auto-publish to Telegram if enabled and not already published
    if (isNowPublished && settings.telegramEnabled && settings.telegramAutoPublishProducts && !listing.telegramPublished) {
      setTimeout(() => {
        handlePublishToTelegram('product', id);
      }, 500);
    }
  };

  const handleToggleListingFeatured = (id: number) => {
    setListings(
      listings.map((l) => (l.id === id ? { ...l, isFeatured: !l.isFeatured } : l))
    );
    showToast('Featured status updated.');
  };

  const handleDeleteListing = (id: number) => {
    setListings(listings.filter((l) => l.id !== id));
    showToast('Product listing removed.');
  };

  const handleToggleJobStatus = (id: number) => {
    const job = jobs.find((j) => j.id === id);
    if (!job) return;
    const isNowPublished = job.status !== 'published';
    const nextStatus: 'published' | 'disabled' = isNowPublished ? 'published' : 'disabled';

    setJobs((prev) => {
      const updated = prev.map((j) =>
        j.id === id ? { ...j, status: nextStatus } : j
      );
      try { localStorage.setItem('sargodha_jobs', JSON.stringify(updated)); } catch (e) {}
      return updated;
    });

    showToast(isNowPublished ? 'Job published.' : 'Job disabled.');

    // Auto-publish to Telegram if enabled and not already published
    if (isNowPublished && settings.telegramEnabled && settings.telegramAutoPublishJobs && !job.telegramPublished) {
      setTimeout(() => {
        handlePublishToTelegram('job', id);
      }, 500);
    }
  };

  const handleDeleteJob = (id: number) => {
    setJobs(jobs.filter((j) => j.id !== id));
    showToast('Job posting removed.');
  };

  // Digital Skills Handlers
  const handleOpenPostSkill = (skillToEdit?: DigitalSkillProfile | null) => {
    if (!settings.isNewListingEnabled) {
      showToast('Posting features are temporarily paused for maintenance.');
      return;
    }
    if (!currentUser) {
      setIsAuthOpen(true);
      showToast('Step 1: Please register or login to create your Digital Skill Profile.');
      return;
    }
    if (currentUser.activationStatus !== 'active') {
      setIsActivationOpen(true);
      showToast(`Step 2: Rs. ${settings.activationFee.toLocaleString()} lifetime seller activation required.`);
      return;
    }
    setEditingSkill(skillToEdit || null);
    setIsPostSkillOpen(true);
  };

  const handleSaveSkill = (skillData: Omit<DigitalSkillProfile, 'id' | 'createdAt'>, skillId?: number) => {
    if (skillId) {
      // Server-side ownership verification: verify current user owns this skill profile
      const existing = skills.find((s) => s.id === skillId);
      if (!existing || (existing.userId !== currentUser?.id && currentUser?.role !== 'super_admin' && currentUser?.role !== 'admin')) {
        showToast('Unauthorized: You can only edit your own skill profile.');
        return;
      }
      setSkills(skills.map((s) => (s.id === skillId ? { ...s, ...skillData } : s)));
      showToast('Digital Skill Profile updated successfully!');
    } else {
      const newSkill: DigitalSkillProfile = {
        ...skillData,
        id: Date.now(),
        userId: currentUser!.id,
        status: 'approved', // Auto approved for verified activated members
        createdAt: new Date().toISOString().split('T')[0],
      };
      setSkills([newSkill, ...skills]);
      showToast('Digital Skill Profile published successfully!');
    }
    setIsPostSkillOpen(false);
    setEditingSkill(null);
  };

  const handleDeleteSkill = (skillId: number) => {
    const existing = skills.find((s) => s.id === skillId);
    if (!existing || (existing.userId !== currentUser?.id && currentUser?.role !== 'super_admin' && currentUser?.role !== 'admin')) {
      showToast('Unauthorized: You can only delete your own skill profile.');
      return;
    }
    setSkills(skills.filter((s) => s.id !== skillId));
    showToast('Digital Skill Profile removed.');
  };

  const handleToggleSkillStatus = (skillId: number) => {
    setSkills(
      skills.map((s) => {
        if (s.id === skillId) {
          const next = s.status === 'approved' ? 'rejected' : 'approved';
          showToast(`Skill profile status changed to ${next.toUpperCase()}`);
          return { ...s, status: next };
        }
        return s;
      })
    );
  };

  // Promotion Handlers (Completely separate from lifetime activation)
  const handleOpenPromoteModal = (type: 'PRODUCT' | 'SKILL' = 'PRODUCT', id?: number) => {
    if (!currentUser) {
      setIsAuthOpen(true);
      showToast('Please login to promote your products or digital skills.');
      return;
    }
    setPromotePreselectedType(type);
    setPromotePreselectedId(id);
    setIsPromoteOpen(true);
  };

  const handleSubmitPromotion = (paymentData: Omit<PromotionPayment, 'id' | 'createdAt'>) => {
    const newPromo: PromotionPayment = {
      ...paymentData,
      id: Date.now(),
      status: 'PENDING',
      createdAt: new Date().toISOString(),
    };
    setPromotions([newPromo, ...promotions]);
    setIsPromoteOpen(false);
    showToast('Your promotion request has been submitted and is waiting for Admin verification.');
  };

  const handleApprovePromotion = (promoId: number) => {
    const promo = promotions.find((p) => p.id === promoId);
    if (!promo) return;

    const startDate = new Date();
    const endDate = new Date();
    endDate.setDate(startDate.getDate() + (promo.durationDays || 15));

    const startAtStr = startDate.toISOString().split('T')[0];
    const endAtStr = endDate.toISOString().split('T')[0];

    // 1. Update promotion record
    setPromotions(
      promotions.map((p) =>
        p.id === promoId
          ? {
              ...p,
              status: 'APPROVED',
              startAt: startAtStr,
              endAt: endAtStr,
              reviewedAt: new Date().toISOString(),
              reviewedBy: currentUser?.name || 'Administrator',
            }
          : p
      )
    );

    // 2. Mark entity as featured
    if (promo.promotionType === 'PRODUCT' || promo.entityType === 'PRODUCT') {
      setListings((prev) =>
        prev.map((l) => (l.id === promo.entityId ? { ...l, isFeatured: true } : l))
      );
      if (settings.telegramEnabled && settings.telegramAutoPublishPromotions) {
        const item = listings.find((l) => l.id === promo.entityId);
        if (item) {
          handlePublishToTelegram('product', item.id, true);
        }
      }
    } else {
      setSkills((prev) =>
        prev.map((s) =>
          s.id === promo.entityId
            ? { ...s, isFeatured: true, featuredStartAt: startAtStr, featuredEndAt: endAtStr }
            : s
        )
      );
    }

    showToast(`Promotion approved! Entity is now ⭐ FEATURED until ${endAtStr}.`);
  };

  const handleRejectPromotion = (promoId: number, reason: string) => {
    setPromotions(
      promotions.map((p) =>
        p.id === promoId
          ? {
              ...p,
              status: 'REJECTED',
              rejectionReason: reason,
              reviewedAt: new Date().toISOString(),
              reviewedBy: currentUser?.name || 'Administrator',
            }
          : p
      )
    );
    showToast(`Promotion rejected. Rejection reason logged.`);
  };

  // ==========================================
  // Advertising System Handlers (Price Security Enforced Server-Side)
  // ==========================================
  const handleSaveAdvertisingPackage = (pkg: AdvertisingPackage) => {
    setAdvertisingPackages((prev) => {
      const exists = prev.some((p) => p.id === pkg.id);
      if (exists) {
        return prev.map((p) => (p.id === pkg.id ? pkg : p));
      } else {
        return [...prev, pkg];
      }
    });
    showToast(`Package "${pkg.name}" updated successfully!`);
  };

  const handleDeleteAdvertisingPackage = (pkgId: number) => {
    setAdvertisingPackages((prev) => prev.filter((p) => p.id !== pkgId));
    showToast('Advertising package removed.');
  };

  const handleSubmitAdvertisement = (
    adData: Omit<Advertisement, 'id' | 'status' | 'amountPaid' | 'durationDays' | 'createdAt' | 'updatedAt'>,
    packageId: number
  ) => {
    // IMPORTANT PRICE SECURITY:
    // Determine the current price and duration from the server/admin package definition!
    const targetPkg = advertisingPackages.find((p) => p.id === packageId) || DEFAULT_ADVERTISING_PACKAGES[0];
    const authoritativePrice = targetPkg.price;
    const authoritativeDuration = targetPkg.durationDays;

    const newAdId = Date.now();
    const newAd: Advertisement = {
      ...adData,
      id: newAdId,
      packageId: targetPkg.id,
      packageName: targetPkg.name,
      adType: targetPkg.adType,
      placements: targetPkg.placements,
      status: 'PENDING',
      amountPaid: authoritativePrice,
      durationDays: authoritativeDuration,
      telegramEnabled: targetPkg.telegramEnabled,
      telegramStatus: targetPkg.telegramEnabled ? 'pending' : 'not_applicable',
      createdAt: new Date().toISOString().split('T')[0],
      updatedAt: new Date().toISOString(),
    };

    // Separate advertising payment record
    const newPayment: AdvertisingPayment = {
      id: Date.now(),
      advertisementId: newAdId,
      userId: currentUser!.id,
      userName: currentUser!.name,
      packageId: targetPkg.id,
      packageName: targetPkg.name,
      amount: authoritativePrice,
      durationDays: authoritativeDuration,
      transactionReference: adData.transactionReference,
      paymentScreenshot: adData.paymentScreenshot,
      status: 'PENDING',
      createdAt: new Date().toISOString(),
    };

    setAdvertisements([newAd, ...advertisements]);
    setAdvertisingPayments([newPayment, ...advertisingPayments]);
    setIsCreateAdOpen(false);
    showToast('Your advertisement request has been submitted with PENDING status for Admin verification.');
  };

  const handleApproveAdvertisement = (adId: number) => {
    const ad = advertisements.find((a) => a.id === adId);
    if (!ad) return;

    const startDate = new Date();
    const endDate = new Date();
    endDate.setDate(startDate.getDate() + (ad.durationDays || 15));

    const startAtStr = startDate.toISOString().split('T')[0];
    const endAtStr = endDate.toISOString().split('T')[0];

    // Update Ad status
    setAdvertisements((prev) =>
      prev.map((a) => {
        if (a.id === adId) {
          return {
            ...a,
            status: 'ACTIVE',
            startAt: startAtStr,
            endAt: endAtStr,
            telegramStatus: a.telegramEnabled ? 'pending' : 'not_applicable',
            updatedAt: new Date().toISOString(),
          };
        }
        return a;
      })
    );

    // Update Separate Advertising Payment
    setAdvertisingPayments((prev) =>
      prev.map((p) =>
        p.advertisementId === adId
          ? {
              ...p,
              status: 'APPROVED',
              reviewedBy: currentUser?.name || 'Administrator',
              reviewedAt: new Date().toISOString(),
            }
          : p
      )
    );

    // If package includes Telegram: automatically broadcast sponsored post
    if (ad.telegramEnabled && settings.telegramEnabled) {
      const origin = window.location.origin;
      broadcastToTelegram('announcement', {
        id: ad.id,
        title: `📢 [SPONSORED] ${ad.title}`,
        message: `${ad.description}\n\n📍 Location: ${ad.location}\n📞 Call: ${ad.phone}\n💬 WhatsApp: https://wa.me/${ad.whatsapp.replace(/[^0-9]/g, '')}`,
      } as any, settings.telegramChannelId, origin)
        .then((res) => {
          if (res.success) {
            const msgId = String(res.messageId || '901');
            setAdvertisements((prev) =>
              prev.map((a) =>
                a.id === adId
                  ? {
                      ...a,
                      telegramStatus: 'published',
                      telegramMessageId: msgId,
                      telegramPublishedAt: new Date().toLocaleString(),
                    }
                  : a
              )
            );
            handleAddTelegramLog({
              id: Date.now(),
              contentType: 'advertisement',
              contentId: ad.id,
              title: `Sponsored Ad: ${ad.title}`,
              status: 'success',
              messageId: msgId,
              timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            });
            showToast('✅ Advertisement approved & broadcasted to Telegram Channel!');
          } else {
            setAdvertisements((prev) =>
              prev.map((a) =>
                a.id === adId
                  ? {
                      ...a,
                      telegramStatus: 'failed',
                      telegramError: res.error || 'Failed to post',
                    }
                  : a
              )
            );
            showToast(`Advertisement active. Telegram broadcast: ${res.error || 'failed'}`);
          }
        })
        .catch(() => {});
    } else {
      showToast(`Advertisement approved! Active until ${endAtStr}.`);
    }
  };

  const handleRejectAdvertisement = (adId: number, reason: string) => {
    setAdvertisements((prev) =>
      prev.map((a) =>
        a.id === adId
          ? {
              ...a,
              status: 'REJECTED',
              rejectionReason: reason,
              updatedAt: new Date().toISOString(),
            }
          : a
      )
    );

    setAdvertisingPayments((prev) =>
      prev.map((p) =>
        p.advertisementId === adId
          ? {
              ...p,
              status: 'REJECTED',
              rejectionReason: reason,
              reviewedBy: currentUser?.name || 'Administrator',
              reviewedAt: new Date().toISOString(),
            }
          : p
      )
    );

    showToast('Advertisement rejected. Rejection reason logged for the client.');
  };

  const handleToggleAdSuspension = (adId: number) => {
    setAdvertisements((prev) =>
      prev.map((a) => {
        if (a.id === adId) {
          const next = a.status === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE';
          showToast(`Ad campaign is now ${next}.`);
          return { ...a, status: next };
        }
        return a;
      })
    );
  };

  const handleRetryAdTelegram = async (adId: number): Promise<boolean> => {
    const ad = advertisements.find((a) => a.id === adId);
    if (!ad) return false;
    const origin = window.location.origin;
    const res = await broadcastToTelegram('announcement', {
      id: ad.id,
      title: `📢 [SPONSORED] ${ad.title}`,
      message: `${ad.description}\n\n📍 Location: ${ad.location}\n📞 Call: ${ad.phone}\n💬 WhatsApp: https://wa.me/${ad.whatsapp.replace(/[^0-9]/g, '')}`,
    } as any, settings.telegramChannelId, origin);

    if (res.success) {
      const msgId = String(res.messageId || '902');
      setAdvertisements((prev) =>
        prev.map((a) =>
          a.id === adId
            ? {
                ...a,
                telegramStatus: 'published',
                telegramMessageId: msgId,
                telegramPublishedAt: new Date().toLocaleString(),
              }
            : a
        )
      );
      handleAddTelegramLog({
        id: Date.now(),
        contentType: 'advertisement',
        contentId: ad.id,
        title: `Sponsored Ad: ${ad.title}`,
        status: 'success',
        messageId: msgId,
        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
      });
      showToast('Telegram sponsored broadcast published successfully!');
      return true;
    } else {
      showToast(`Telegram retry failed: ${res.error || 'Network error'}`);
      return false;
    }
  };

  // Submit Report
  const handleSubmitReport = (e: React.FormEvent) => {
    e.preventDefault();
    if (!reportTarget) return;

    const newReport: ReportItem = {
      id: Date.now(),
      targetType: reportTarget.type,
      targetId: reportTarget.item.id,
      targetTitle: reportTarget.item.title,
      reporterName: currentUser ? currentUser.name : 'Public Buyer',
      reason: reportReason,
      details: reportDetails,
      status: 'pending',
      createdAt: 'Just now',
    };

    setReports([newReport, ...reports]);
    setReportTarget(null);
    setReportDetails('');
    showToast('Thank you! Report received for administrator investigation.');
  };

  // Filtering Listings
  const filteredListings = listings.filter((l) => {
    if (l.status !== 'published') return false;
    if (selectedCategory !== 'all' && l.categoryId !== selectedCategory) return false;
    if (selectedCity !== 'All' && l.city !== selectedCity) return false;
    if (searchQuery.trim() !== '') {
      const q = searchQuery.toLowerCase();
      const match =
        l.title.toLowerCase().includes(q) ||
        l.description.toLowerCase().includes(q) ||
        l.categoryName.toLowerCase().includes(q) ||
        (l.subcategory && l.subcategory.toLowerCase().includes(q)) ||
        l.area.toLowerCase().includes(q);
      if (!match) return false;
    }
    return true;
  });

  const featuredListings = filteredListings.filter((l) => l.isFeatured);
  const regularListings = filteredListings.filter((l) => !l.isFeatured);

  // Check if current user has an active pending payment
  const userPendingPayment = currentUser
    ? payments.find((p) => p.userId === currentUser.id && p.status === 'pending')
    : null;

  // Maintenance Mode Check (Admins retain access)
  const isUserAdmin = currentUser?.role === 'super_admin' || currentUser?.role === 'admin';
  const showMaintenance = settings.isMaintenanceMode && currentView !== 'admin' && !isUserAdmin;

  // Popup Announcement Check
  const popupAnnouncements = getActiveAnnouncements(announcements, 'popup');
  const activePopup = popupAnnouncements.find((a) => a.id !== dismissedPopupId);

  return (
    <div className="min-h-screen bg-[#f8fafc] neon-white-bg text-slate-800 flex flex-col font-sans selection:bg-cyan-500 selection:text-white">
      {/* Toast Notification */}
      {toastMessage && (
        <div className="fixed bottom-20 md:bottom-6 right-6 z-50 px-4 py-3 rounded-2xl bg-white border border-cyan-400 text-cyan-900 text-xs font-bold shadow-[0_0_20px_rgba(6,182,212,0.3)] flex items-center gap-2 animate-bounce">
          <CheckCircle2 className="w-4 h-4 text-cyan-600" />
          <span>{toastMessage}</span>
        </div>
      )}

      {/* Main Navbar */}
      <Navbar
        currentView={currentView}
        setCurrentView={setCurrentView}
        currentUser={currentUser}
        users={users}
        onSwitchUser={handleSwitchUser}
        onLogout={handleLogout}
        onOpenAuth={() => setIsAuthOpen(true)}
        pendingActivationsCount={payments.filter((p) => p.status === 'pending').length}
        onPostClick={handlePostAdClick}
        selectedCity={selectedCity}
        setSelectedCity={setSelectedCity}
        settings={settings}
        announcements={announcements}
      />

      {/* Maintenance Mode Screen (Active when enabled in Settings, bypassable for Admin) */}
      {showMaintenance ? (
        <div className="flex-1 flex items-center justify-center p-6 py-24 text-center">
          <div className="max-w-lg bg-white border border-amber-300 rounded-3xl p-8 shadow-2xl">
            <div className="w-16 h-16 rounded-3xl bg-amber-50 text-amber-600 border border-amber-300 flex items-center justify-center mx-auto mb-4">
              <Wrench className="w-8 h-8 animate-spin" style={{ animationDuration: '10s' }} />
            </div>
            <h2 className="text-2xl font-black text-slate-900 mb-2">{settings.maintenanceTitle}</h2>
            <p className="text-slate-600 text-xs leading-relaxed mb-6 font-medium">
              {settings.maintenanceMessage}
            </p>
            <div className="pt-4 border-t border-slate-100 flex items-center justify-center gap-3">
              <button
                onClick={() => setCurrentView('admin')}
                className="px-5 py-2.5 rounded-xl font-bold text-xs bg-slate-900 hover:bg-slate-800 text-white transition-all"
              >
                Administrator Login / Portal
              </button>
            </div>
          </div>
        </div>
      ) : (
        /* Standard Content Routing */
        <main className="flex-1 pb-16 md:pb-8">
          {/* VIEW 1: HOME MARKETPLACE */}
          {currentView === 'home' && (
            <div>
              <HeroSection
                categories={INITIAL_CATEGORIES}
                searchQuery={searchQuery}
                setSearchQuery={setSearchQuery}
                selectedCategory={selectedCategory}
                setSelectedCategory={setSelectedCategory}
                selectedCity={selectedCity}
                setSelectedCity={setSelectedCity}
                onPostClick={handlePostAdClick}
                onViewJobsClick={() => setCurrentView('jobs')}
                settings={settings}
                announcements={announcements}
              />

              <div className="max-w-7xl mx-auto px-4 py-10">
                {/* Featured Products Section */}
                {settings.isFeaturedProductsEnabled && featuredListings.length > 0 && (
                  <div className="mb-12">
                    <div className="flex items-center justify-between gap-4 mb-6">
                      <div className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-fuchsia-600 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-fuchsia-600/30">
                          <Sparkles className="w-4 h-4" />
                        </div>
                        <div>
                          <h2 className="text-xl font-bold text-slate-900">Featured Local Products</h2>
                          <p className="text-slate-500 text-xs font-medium">High-priority listings in Sargodha, Shaheenabad & Sillanwali</p>
                        </div>
                      </div>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                      {featuredListings.map((item) => (
                        <ProductCard
                          key={item.id}
                          listing={item}
                          isFavorite={favorites.includes(item.id)}
                          onToggleFavorite={handleToggleFavorite}
                          onSelect={setSelectedListing}
                          onShare={(l) => {
                            const shareUrl = `${window.location.origin}/?product=${l.id}`;
                            navigator.clipboard.writeText(shareUrl);
                            showToast(`Direct link copied for "${l.title}"!`);
                          }}
                        />
                      ))}
                    </div>
                  </div>
                )}

                {/* Latest Products Section */}
                {settings.isLatestProductsEnabled && (
                  <div>
                    <div className="flex items-center justify-between gap-4 mb-6">
                      <div>
                        <h2 className="text-xl font-bold text-slate-900">Latest Product Listings</h2>
                        <p className="text-slate-500 text-xs font-medium">Direct public ads from verified local sellers</p>
                      </div>
                      <div className="text-xs text-slate-500 font-bold">
                        Showing <strong className="text-cyan-700">{filteredListings.length}</strong> products
                      </div>
                    </div>

                    {filteredListings.length === 0 ? (
                      <div className="py-16 text-center bg-white border border-slate-200 rounded-3xl p-8 shadow-xs">
                        <ShoppingBag className="w-12 h-12 text-slate-400 mx-auto mb-3" />
                        <h3 className="text-base font-bold text-slate-900 mb-1">No products found</h3>
                        <p className="text-slate-500 text-xs mb-4">Try clearing your filters or post the first ad!</p>
                        <button
                          onClick={handlePostAdClick}
                          className="px-4 py-2 rounded-xl text-xs font-bold bg-cyan-600 text-white hover:bg-cyan-500 shadow-md shadow-cyan-600/20"
                        >
                          Post an Ad
                        </button>
                      </div>
                    ) : (
                      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        {regularListings.map((item) => (
                          <ProductCard
                            key={item.id}
                            listing={item}
                            isFavorite={favorites.includes(item.id)}
                            onToggleFavorite={handleToggleFavorite}
                            onSelect={setSelectedListing}
                            onShare={(l) => {
                              const shareUrl = `${window.location.origin}/?product=${l.id}`;
                              navigator.clipboard.writeText(shareUrl);
                              showToast(`Direct link copied for "${l.title}"!`);
                            }}
                          />
                        ))}
                      </div>
                    )}
                  </div>
                )}

                {/* Featured Digital Talents Spotlight */}
                {skills.filter((s) => s.status === 'approved' && s.isFeatured).length > 0 && (
                  <div className="mt-14 pt-10 border-t border-slate-200">
                    <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                      <div className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-amber-500 to-rose-500 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                          <Sparkles className="w-4 h-4" />
                        </div>
                        <div>
                          <h2 className="text-xl font-bold text-slate-900 flex items-center gap-2">
                            <span>Featured Digital Talents</span>
                            <span className="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-black uppercase">
                              Verified Pros
                            </span>
                          </h2>
                          <p className="text-slate-500 text-xs font-medium">Top promoted website designers, video editors, and digital marketers</p>
                        </div>
                      </div>
                      <button
                        onClick={() => setCurrentView('skills')}
                        className="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white transition-all flex items-center gap-1.5 shadow-sm"
                      >
                        <span>Explore All Freelancers</span>
                        <ChevronRight className="w-3.5 h-3.5" />
                      </button>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                      {skills
                        .filter((s) => s.status === 'approved' && s.isFeatured)
                        .slice(0, 3)
                        .map((skill) => (
                          <div
                            key={skill.id}
                            className="bg-white border-2 border-amber-200 hover:border-amber-400 rounded-3xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                          >
                            <div>
                              <div className="flex items-start gap-3 mb-3">
                                <img
                                  src={skill.profilePhoto}
                                  alt={skill.fullName}
                                  className="w-12 h-12 rounded-2xl object-cover border-2 border-amber-300"
                                />
                                <div>
                                  <div className="flex items-center gap-1.5">
                                    <h4 className="text-sm font-black text-slate-900">{skill.fullName}</h4>
                                    <span className="px-1.5 py-0.5 rounded bg-amber-400 text-slate-950 text-[9px] font-black uppercase">
                                      ⭐ FEATURED
                                    </span>
                                  </div>
                                  <div className="text-xs font-bold text-cyan-800">{skill.professionalTitle}</div>
                                  <div className="text-[11px] text-slate-500">{skill.tehsilName || 'Sargodha'} • {skill.experience} Exp</div>
                                </div>
                              </div>
                              <p className="text-xs text-slate-600 line-clamp-2 mb-3">
                                {skill.about}
                              </p>
                              <div className="flex flex-wrap gap-1 mb-3">
                                {skill.skills.slice(0, 3).map((sk, i) => (
                                  <span key={i} className="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-bold">
                                    {sk}
                                  </span>
                                ))}
                              </div>
                            </div>
                            <div className="pt-3 border-t border-slate-100 flex items-center justify-between">
                              <span className="text-[11px] font-bold text-slate-700">{skill.startingRate || 'Affordable Rates'}</span>
                              <button
                                onClick={() => setSelectedSkill(skill)}
                                className="px-3 py-1 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs"
                              >
                                View Digital CV
                              </button>
                            </div>
                          </div>
                        ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}

          {/* VIEW 2: JOBS & WORK HUB */}
          {currentView === 'jobs' && (
            <JobsSection
              jobs={jobs}
              onPostJobClick={handlePostJobClick}
              onReportJob={(j) => setReportTarget({ type: 'job', item: j })}
              onSelectJob={setSelectedJob}
              selectedCity={selectedCity}
              setSelectedCity={setSelectedCity}
            />
          )}

          {/* VIEW 3: DIGITAL SKILLS & TALENT DIRECTORY */}
          {currentView === 'skills' && (
            <DigitalSkillsSection
              skills={skills}
              currentUser={currentUser}
              onSelectSkill={setSelectedSkill}
              onOpenPostSkill={() => handleOpenPostSkill()}
              onOpenPromoteSkill={(s) => handleOpenPromoteModal('SKILL', s.id)}
            />
          )}

          {/* VIEW 4: USER DASHBOARD (WITH PROMOTE & DIGITAL SKILLS) */}
          {currentView === 'dashboard' && (
            currentUser ? (
              <UserDashboard
                currentUser={currentUser}
                listings={listings}
                jobs={jobs}
                skills={skills}
                promotions={promotions}
                favorites={favorites}
                onOpenPostAd={handlePostAdClick}
                onOpenPostJob={handlePostJobClick}
                onOpenPostSkill={handleOpenPostSkill}
                onOpenActivation={() => setIsActivationOpen(true)}
                onOpenPromoteModal={handleOpenPromoteModal}
                onDeleteListing={handleDeleteListing}
                onDeleteJob={handleDeleteJob}
                onDeleteSkill={handleDeleteSkill}
                onSelectListing={setSelectedListing}
                onSelectSkill={setSelectedSkill}
                onLogout={handleLogout}
              />
            ) : (
              <div className="py-20 px-4 text-center max-w-md mx-auto">
                <div className="w-16 h-16 rounded-3xl bg-cyan-50 border border-cyan-300 text-cyan-600 flex items-center justify-center mx-auto mb-4 shadow-[0_0_15px_rgba(6,182,212,0.2)]">
                  <LogIn className="w-8 h-8" />
                </div>
                <h2 className="text-2xl font-black text-slate-900 mb-2">Seller Dashboard</h2>
                <p className="text-slate-500 text-xs mb-6 font-medium">
                  Please login or create an account to view your listed products, active jobs, digital skills, and promote requests.
                </p>
                <button
                  onClick={() => setIsAuthOpen(true)}
                  className="w-full py-3 rounded-xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-xs shadow-lg shadow-cyan-500/25 hover:shadow-[0_0_15px_rgba(6,182,212,0.4)]"
                >
                  Login / Register
                </button>
              </div>
            )
          )}

          {/* VIEW 5: ADMIN PORTAL WITH PROMOTIONS & DIGITAL SKILLS */}
          {currentView === 'admin' && (
            <AdminPanel
              currentUser={currentUser || INITIAL_USERS[0]}
              users={users}
              listings={listings}
              jobs={jobs}
              payments={payments}
              promotions={promotions}
              onApprovePromotion={handleApprovePromotion}
              onRejectPromotion={handleRejectPromotion}
              skills={skills}
              onToggleSkillStatus={handleToggleSkillStatus}
              onDeleteSkill={handleDeleteSkill}
              reports={reports}
              settings={settings}
              onSaveSettings={handleSaveSettings}
              announcements={announcements}
              onAddAnnouncement={handleAddAnnouncement}
              onUpdateAnnouncement={handleUpdateAnnouncement}
              onDeleteAnnouncement={handleDeleteAnnouncement}
              onApprovePayment={handleApprovePayment}
              onRejectPayment={handleRejectPayment}
              onToggleListingStatus={handleToggleListingStatus}
              onToggleListingFeatured={handleToggleListingFeatured}
              onDeleteListing={handleDeleteListing}
              onToggleJobStatus={handleToggleJobStatus}
              onDeleteJob={handleDeleteJob}
              onResolveReport={(repId) => {
                setReports(
                  reports.map((r) => (r.id === repId ? { ...r, status: 'resolved' } : r))
                );
                showToast('Report marked as investigated & resolved.');
              }}
              onForceLogoutUser={handleForceLogoutUser}
              telegramLogs={telegramLogs}
              onPublishToTelegram={handlePublishToTelegram}
              onAddTelegramLog={handleAddTelegramLog}
            />
          )}
        </main>
      )}

      {/* Footer - White Neon Dynamic from Settings */}
      <footer className="bg-white border-t border-cyan-100 py-10 px-4 text-xs text-slate-600 shadow-sm">
        <div className="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
          <div>
            <div className="text-lg font-black text-slate-950 mb-2 flex items-center gap-1">
              SARGODHA<span className="text-cyan-700">MART</span>
            </div>
            <p className="text-slate-500 text-xs mb-3 font-medium">
              {settings.websiteDescription}
            </p>
            <div className="text-slate-700 text-[11px] font-semibold">
              One-Time Lifetime Seller Activation: <strong className="text-cyan-800 font-bold">Rs. {settings.activationFee.toLocaleString()}</strong>
            </div>
          </div>

          <div>
            <h4 className="font-bold text-slate-900 mb-3 uppercase tracking-wider text-[11px]">Local Hubs</h4>
            <ul className="space-y-1.5 text-slate-600 font-medium">
              <li><button onClick={() => { setSelectedCity('Sargodha'); setCurrentView('home'); }} className="hover:text-cyan-700">Sargodha City</button></li>
              <li><button onClick={() => { setSelectedCity('Shaheenabad'); setCurrentView('home'); }} className="hover:text-cyan-700">Shaheenabad Mandi</button></li>
              <li><button onClick={() => { setSelectedCity('Sillanwali'); setCurrentView('home'); }} className="hover:text-cyan-700">Sillanwali Citrus Belt</button></li>
            </ul>
          </div>

          <div>
            <h4 className="font-bold text-slate-900 mb-3 uppercase tracking-wider text-[11px]">Community & Trust</h4>
            <ul className="space-y-1.5 text-slate-600 font-medium">
              {settings.isWhatsappChannelEnabled && (
                <li>
                  <a href={settings.whatsappChannelUrl} target="_blank" rel="noreferrer" className="text-emerald-700 font-bold hover:underline">
                    {settings.whatsappChannelName}
                  </a>
                </li>
              )}
              {settings.isCallButtonEnabled && (
                <li>
                  <a href={`tel:${settings.officialPhone}`} className="hover:text-slate-900 font-semibold">
                    Support: {settings.officialPhone}
                  </a>
                </li>
              )}
              <li className="font-medium">Account Title: {settings.paymentAccountTitle}</li>
              <li className="text-[11px] text-slate-400">{settings.officialAddress}</li>
            </ul>
          </div>

          <div>
            <h4 className="font-bold text-slate-900 mb-3 uppercase tracking-wider text-[11px]">Social Media Channels</h4>
            <div className="flex flex-wrap gap-2 mb-3">
              {settings.socialLinks
                .filter((s) => s.isEnabled)
                .map((soc) => (
                  <a
                    key={soc.id}
                    href={soc.url}
                    target="_blank"
                    rel="noreferrer"
                    className="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] font-bold border border-slate-200 transition-all flex items-center gap-1"
                  >
                    <span>{soc.platform}</span>
                    <ExternalLink className="w-2.5 h-2.5 text-slate-400" />
                  </a>
                ))}
            </div>
            <p className="text-[10px] text-slate-400">
              Verified local updates & community trading notices.
            </p>
          </div>
        </div>

        <div className="max-w-7xl mx-auto pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-slate-500 text-[11px] font-medium">
          <div>{settings.copyrightText}</div>
          <div className="flex gap-4">
            <button onClick={() => setCurrentView('home')} className="hover:text-slate-900">Marketplace</button>
            <button onClick={() => setCurrentView('jobs')} className="hover:text-slate-900">Jobs</button>
            <button onClick={() => setCurrentView('dashboard')} className="hover:text-slate-900">Dashboard</button>
            <button onClick={() => setCurrentView('admin')} className="hover:text-slate-900">Admin</button>
          </div>
        </div>
      </footer>

      {/* Mobile-First Bottom Navigation Bar */}
      <div className="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-cyan-100 px-4 py-2 flex items-center justify-around text-[10px] font-bold shadow-lg">
        <button
          onClick={() => setCurrentView('home')}
          className={`flex flex-col items-center gap-1 ${currentView === 'home' ? 'text-cyan-700 font-black' : 'text-slate-500'}`}
        >
          <ShoppingBag className="w-5 h-5" />
          <span>Market</span>
        </button>

        <button
          onClick={() => setCurrentView('jobs')}
          className={`flex flex-col items-center gap-1 ${currentView === 'jobs' ? 'text-blue-700 font-black' : 'text-slate-500'}`}
        >
          <Briefcase className="w-5 h-5" />
          <span>Jobs</span>
        </button>

        <button
          onClick={() => setCurrentView('skills')}
          className={`flex flex-col items-center gap-1 ${currentView === 'skills' ? 'text-emerald-700 font-black' : 'text-slate-500'}`}
        >
          <Sparkles className="w-5 h-5" />
          <span>Skills</span>
        </button>

        <button
          onClick={handlePostAdClick}
          className="flex flex-col items-center gap-1 text-white bg-gradient-to-tr from-cyan-500 to-fuchsia-600 p-2.5 rounded-full -mt-6 shadow-xl shadow-cyan-500/30"
        >
          <PlusCircle className="w-6 h-6" />
        </button>

        <button
          onClick={() => {
            if (!currentUser) setIsAuthOpen(true);
            else setCurrentView('dashboard');
          }}
          className={`flex flex-col items-center gap-1 ${currentView === 'dashboard' ? 'text-fuchsia-700 font-black' : 'text-slate-500'}`}
        >
          <LayoutDashboard className="w-5 h-5" />
          <span>Dashboard</span>
        </button>

        <button
          onClick={() => setCurrentView('admin')}
          className={`flex flex-col items-center gap-1 ${currentView === 'admin' ? 'text-rose-700 font-black' : 'text-slate-500'}`}
        >
          <Shield className="w-5 h-5" />
          <span>Admin</span>
        </button>
      </div>

      {/* Popup Announcement Notice (Dismissable) */}
      {activePopup && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md">
          <div className="relative w-full max-w-md bg-white border border-amber-300 rounded-3xl p-6 shadow-2xl animate-in fade-in zoom-in duration-200">
            <button
              onClick={() => setDismissedPopupId(activePopup.id)}
              className="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-800 rounded-xl"
            >
              <X className="w-5 h-5" />
            </button>
            <div className="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-3">
              <Megaphone className="w-6 h-6" />
            </div>
            <h3 className="text-base font-black text-slate-900 text-center mb-1">{activePopup.title}</h3>
            <p className="text-xs text-slate-600 text-center font-medium leading-relaxed mb-6">
              {activePopup.message}
            </p>
            <div className="flex gap-2">
              <button
                onClick={() => setDismissedPopupId(activePopup.id)}
                className="flex-1 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 border border-slate-200"
              >
                Dismiss
              </button>
              {activePopup.buttonText && activePopup.buttonUrl && (
                <a
                  href={activePopup.buttonUrl}
                  target="_blank"
                  rel="noreferrer"
                  className="flex-1 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 flex items-center justify-center gap-1"
                >
                  <span>{activePopup.buttonText}</span>
                  <ExternalLink className="w-3 h-3" />
                </a>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Auth Modal (Register / Login) */}
      <AuthModal
        isOpen={isAuthOpen}
        onClose={() => setIsAuthOpen(false)}
        onRegisterSuccess={handleRegisterSuccess}
        onLoginSuccess={handleLoginSuccess}
        existingUsers={users}
      />

      {/* Product Detail Modal */}
      <ProductDetailModal
        listing={selectedListing}
        isOpen={!!selectedListing}
        onClose={() => setSelectedListing(null)}
        isFavorite={selectedListing ? favorites.includes(selectedListing.id) : false}
        onToggleFavorite={handleToggleFavorite}
        onReport={(l) => setReportTarget({ type: 'listing', item: l })}
      />

      {/* Job Detail Modal */}
      <JobDetailModal
        job={selectedJob}
        isOpen={!!selectedJob}
        onClose={() => setSelectedJob(null)}
        onReport={(j) => setReportTarget({ type: 'job', item: j })}
      />

      {/* Post Ad Modal */}
      {currentUser && (
        <PostAdModal
          currentUser={currentUser}
          categories={INITIAL_CATEGORIES}
          isOpen={isPostAdOpen}
          onClose={() => setIsPostAdOpen(false)}
          onSubmitAd={handleAddListing}
          onOpenActivation={() => setIsActivationOpen(true)}
          settings={settings}
        />
      )}

      {/* Post Job Modal */}
      {currentUser && (
        <PostJobModal
          currentUser={currentUser}
          isOpen={isPostJobOpen}
          onClose={() => setIsPostJobOpen(false)}
          onSubmitJob={handleAddJob}
          onOpenActivation={() => setIsActivationOpen(true)}
          settings={settings}
        />
      )}

      {/* Lifetime Activation Modal */}
      {currentUser && (
        <ActivationModal
          currentUser={currentUser}
          isOpen={isActivationOpen}
          onClose={() => setIsActivationOpen(false)}
          onSubmitActivation={handleSubmitActivation}
          existingPendingPayment={userPendingPayment}
          onOpenAdminPanel={() => setCurrentView('admin')}
          settings={settings}
        />
      )}

      {/* Report Modal */}
      {reportTarget && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md">
          <div className="relative w-full max-w-md bg-white border border-slate-200 rounded-3xl p-6 shadow-2xl">
            <button
              onClick={() => setReportTarget(null)}
              className="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-xl"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-base font-bold text-slate-900 mb-1 flex items-center gap-2">
              <Flag className="w-4 h-4 text-rose-600" /> Report {reportTarget.type === 'listing' ? 'Product' : 'Job Post'}
            </h3>
            <p className="text-xs text-slate-500 mb-4 truncate font-medium">{reportTarget.item.title}</p>

            <form onSubmit={handleSubmitReport} className="space-y-3 text-xs">
              <div>
                <label className="block text-slate-800 font-semibold mb-1">Reason for report</label>
                <select
                  value={reportReason}
                  onChange={(e) => setReportReason(e.target.value)}
                  className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-rose-400"
                >
                  {(settings.reportReasons || ['Scam / Fake', 'Wrong Price / Misleading', 'Other']).map((reason, idx) => (
                    <option key={idx} value={reason}>
                      {reason}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-slate-800 font-semibold mb-1">Additional details (Optional)</label>
                <textarea
                  rows={3}
                  value={reportDetails}
                  onChange={(e) => setReportDetails(e.target.value)}
                  placeholder="Describe the issue for the admin team..."
                  className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-rose-400"
                ></textarea>
              </div>

              <div className="flex justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setReportTarget(null)}
                  className="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 font-bold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-2 rounded-xl font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/25"
                >
                  Submit Report
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Digital Skill Profile Modal (Digital CV View) */}
      <SkillProfileModal
        skill={selectedSkill}
        onClose={() => setSelectedSkill(null)}
        onPromoteClick={(s) => handleOpenPromoteModal('SKILL', s.id)}
        isOwner={currentUser ? currentUser.id === selectedSkill?.userId : false}
      />

      {/* Post / Edit Digital Skill Profile Modal */}
      {currentUser && (
        <PostSkillModal
          isOpen={isPostSkillOpen}
          onClose={() => {
            setIsPostSkillOpen(false);
            setEditingSkill(null);
          }}
          currentUser={currentUser}
          initialSkill={editingSkill}
          onSaveSkill={handleSaveSkill}
        />
      )}

      {/* Promote Modal (Product & Skill Promotion - Fee Separation from Lifetime Activation) */}
      {currentUser && (
        <PromoteModal
          isOpen={isPromoteOpen}
          onClose={() => setIsPromoteOpen(false)}
          currentUser={currentUser}
          listings={listings}
          skills={skills}
          settings={settings}
          preselectedType={promotePreselectedType}
          preselectedId={promotePreselectedId}
          onSubmitPromotion={handleSubmitPromotion}
        />
      )}

      {/* Persistent Floating AI Assistant Button (Bottom Right Corner) */}
      <AIAssistantWidget
        currentUser={currentUser}
        settings={settings}
        onNavigate={(view) => setCurrentView(view)}
        onOpenActivation={() => setIsActivationOpen(true)}
        onOpenPromoteModal={() => handleOpenPromoteModal('PRODUCT')}
      />
    </div>
  );
}

export default App;
