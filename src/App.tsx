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
} from './types';
import {
  INITIAL_CATEGORIES,
  INITIAL_USERS,
  INITIAL_LISTINGS,
  INITIAL_JOBS,
  INITIAL_PAYMENTS,
  INITIAL_REPORTS,
} from './data/mockData';
import { DEFAULT_SITE_SETTINGS, INITIAL_ANNOUNCEMENTS } from './data/defaultSettings';
import { INITIAL_DISTRICTS, INITIAL_TEHSILS, INITIAL_AREAS } from './data/locationData';

import { Navbar } from './components/Navbar';
import { HeroSection } from './components/HeroSection';
import { ProductCard } from './components/ProductCard';
import { JobsSection } from './components/JobsSection';
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
  const [currentView, setCurrentView] = useState<'home' | 'jobs' | 'dashboard' | 'admin' | 'post-ad' | 'post-job'>('home');

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
  const [isPostAdOpen, setIsPostAdOpen] = useState(false);
  const [isPostJobOpen, setIsPostJobOpen] = useState(false);
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

  // Handle URL parameters for initial load & deep links (?product=123 or ?job=123)
  useEffect(() => {
    if (typeof window === 'undefined') return;
    const params = new URLSearchParams(window.location.search);
    const productId = params.get('product') || params.get('p');
    const jobId = params.get('job') || params.get('j');

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
    } else {
      if (currentView === 'jobs') {
        updatePageSEO({
          title: `Jobs & Employment in Sargodha, Shaheenabad & Sillanwali | ${settings.websiteName}`,
          description: 'Explore local job vacancies or find skilled workers across Sargodha district. Direct WhatsApp and phone contact without agent commissions.',
          url: `${origin}/?view=jobs`,
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
  }, [selectedListing, selectedJob, currentView, settings]);

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

          {/* VIEW 3: USER DASHBOARD */}
          {currentView === 'dashboard' && (
            currentUser ? (
              <UserDashboard
                currentUser={currentUser}
                listings={listings}
                jobs={jobs}
                favorites={favorites}
                onOpenPostAd={handlePostAdClick}
                onOpenPostJob={handlePostJobClick}
                onOpenActivation={() => setIsActivationOpen(true)}
                onDeleteListing={handleDeleteListing}
                onDeleteJob={handleDeleteJob}
                onSelectListing={setSelectedListing}
                onLogout={handleLogout}
              />
            ) : (
              <div className="py-20 px-4 text-center max-w-md mx-auto">
                <div className="w-16 h-16 rounded-3xl bg-cyan-50 border border-cyan-300 text-cyan-600 flex items-center justify-center mx-auto mb-4 shadow-[0_0_15px_rgba(6,182,212,0.2)]">
                  <LogIn className="w-8 h-8" />
                </div>
                <h2 className="text-2xl font-black text-slate-900 mb-2">Seller Dashboard</h2>
                <p className="text-slate-500 text-xs mb-6 font-medium">
                  Please login or create an account to view your listed products, active jobs, and lifetime status.
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

          {/* VIEW 4: ADMIN PORTAL WITH CENTRAL SETTINGS SYSTEM */}
          {currentView === 'admin' && (
            <AdminPanel
              currentUser={currentUser || INITIAL_USERS[0]}
              users={users}
              listings={listings}
              jobs={jobs}
              payments={payments}
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
    </div>
  );
}

export default App;
