import React, { useState, useEffect } from 'react';
import {
  Globe,
  MessageCircle,
  Megaphone,
  Share2,
  Search,
  Home,
  ShoppingBag,
  Briefcase,
  ShieldCheck,
  Bell,
  Wrench,
  Save,
  Check,
  Plus,
  Trash2,
  Edit,
  ExternalLink,
  Power,
  AlertCircle,
  Eye,
  Send,
  Bot,
  RefreshCw,
  KeyRound,
  Radio,
  CheckCircle2,
  XCircle,
} from 'lucide-react';
import { SiteSettings, AnnouncementItem, SocialLink, TelegramLogItem } from '../types';
import {
  checkTelegramStatus,
  saveTelegramToken,
  testTelegramConnection,
  sendTelegramTestMessage,
} from '../utils/telegram';

interface AdminSettingsManagerProps {
  settings: SiteSettings;
  onSaveSettings: (updated: SiteSettings) => void;
  announcements: AnnouncementItem[];
  onAddAnnouncement: (item: Omit<AnnouncementItem, 'id' | 'createdAt'>) => void;
  onUpdateAnnouncement: (id: number, item: Partial<AnnouncementItem>) => void;
  onDeleteAnnouncement: (id: number) => void;
  telegramLogs?: TelegramLogItem[];
  onAddTelegramLog?: (log: TelegramLogItem) => void;
  onPublishToTelegram?: (type: 'product' | 'job' | 'announcement', id: number, force?: boolean) => Promise<boolean>;
}

export const AdminSettingsManager: React.FC<AdminSettingsManagerProps> = ({
  settings,
  onSaveSettings,
  announcements,
  onAddAnnouncement,
  onUpdateAnnouncement,
  onDeleteAnnouncement,
  telegramLogs = [],
  onAddTelegramLog,
  onPublishToTelegram,
}) => {
  const [formData, setFormData] = useState<SiteSettings>({ ...settings });
  const [activeSubTab, setActiveSubTab] = useState<
    | 'general'
    | 'whatsapp'
    | 'announcements'
    | 'social'
    | 'seo'
    | 'homepage'
    | 'marketplace'
    | 'jobs'
    | 'activation'
    | 'notifications'
    | 'maintenance'
    | 'telegram'
  >('general');

  const [savedSuccess, setSavedSuccess] = useState(false);

  // Telegram state
  const [telegramStatus, setTelegramStatus] = useState<{
    hasTokenConfigured: boolean;
    channelId: string;
    botUsername?: string;
  } | null>(null);
  const [checkingTelegram, setCheckingTelegram] = useState(false);
  const [newTokenInput, setNewTokenInput] = useState('');
  const [isUpdatingToken, setIsUpdatingToken] = useState(false);
  const [tokenNotice, setTokenNotice] = useState<{ type: 'success' | 'error'; message: string } | null>(null);
  const [testingConnection, setTestingConnection] = useState(false);
  const [connectionNotice, setConnectionNotice] = useState<{ type: 'success' | 'error'; message: string } | null>(null);
  const [sendingTestMsg, setSendingTestMsg] = useState(false);
  const [testMsgNotice, setTestMsgNotice] = useState<{ type: 'success' | 'error'; message: string; messageId?: string | number } | null>(null);

  // Load telegram status when telegram tab is opened
  useEffect(() => {
    if (activeSubTab === 'telegram') {
      loadTelegramStatus();
    }
  }, [activeSubTab]);

  const loadTelegramStatus = async () => {
    setCheckingTelegram(true);
    try {
      const res = await checkTelegramStatus();
      setTelegramStatus(res);
    } catch (e) {
      // ignore
    } finally {
      setCheckingTelegram(false);
    }
  };

  const handleUpdateToken = async () => {
    if (!newTokenInput.trim()) {
      setTokenNotice({ type: 'error', message: 'Please enter a valid Bot Token.' });
      return;
    }
    setIsUpdatingToken(true);
    setTokenNotice(null);
    try {
      const res = await saveTelegramToken(newTokenInput.trim());
      if (res.success) {
        setTokenNotice({ type: 'success', message: res.message || 'Token securely saved to server!' });
        setNewTokenInput('');
        loadTelegramStatus();
      } else {
        setTokenNotice({ type: 'error', message: res.error || 'Failed to verify bot token on server.' });
      }
    } catch (err: any) {
      setTokenNotice({ type: 'error', message: err.message || 'Error updating token.' });
    } finally {
      setIsUpdatingToken(false);
    }
  };

  const handleTestConnection = async () => {
    setTestingConnection(true);
    setConnectionNotice(null);
    try {
      const res = await testTelegramConnection();
      if (res.success) {
        setConnectionNotice({
          type: 'success',
          message: res.message || `Connected successfully to @${res.botUsername}!`,
        });
        loadTelegramStatus();
      } else {
        setConnectionNotice({
          type: 'error',
          message: res.error || 'Connection failed. Please check that your Bot Token is valid.',
        });
      }
    } catch (err: any) {
      setConnectionNotice({ type: 'error', message: err.message || 'Connection test error.' });
    } finally {
      setTestingConnection(false);
    }
  };

  const handleSendTestMessage = async () => {
    setSendingTestMsg(true);
    setTestMsgNotice(null);
    try {
      const targetChannel = formData.telegramChannelId || '-1003328935535';
      const res = await sendTelegramTestMessage(targetChannel);
      if (res.success) {
        setTestMsgNotice({
          type: 'success',
          message: res.message || `Test message delivered to channel ${targetChannel}!`,
          messageId: res.messageId,
        });
        if (onAddTelegramLog) {
          onAddTelegramLog({
            id: Date.now(),
            contentType: 'test',
            title: `Admin Test Broadcast to ${targetChannel}`,
            status: 'success',
            messageId: res.messageId ? String(res.messageId) : undefined,
            timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
          });
        }
      } else {
        setTestMsgNotice({
          type: 'error',
          message: res.error || 'Failed to deliver test message to channel.',
        });
        if (onAddTelegramLog) {
          onAddTelegramLog({
            id: Date.now(),
            contentType: 'test',
            title: `Failed Test Broadcast to ${targetChannel}`,
            status: 'failed',
            errorMessage: res.error,
            timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
          });
        }
      }
    } catch (err: any) {
      setTestMsgNotice({ type: 'error', message: err.message || 'Network error sending test message.' });
    } finally {
      setSendingTestMsg(false);
    }
  };

  // Announcement modal state
  const [isAnnouncementModalOpen, setIsAnnouncementModalOpen] = useState(false);
  const [editingAnnouncement, setEditingAnnouncement] = useState<AnnouncementItem | null>(null);
  const [annTitle, setAnnTitle] = useState('');
  const [annMessage, setAnnMessage] = useState('');
  const [annImageUrl, setAnnImageUrl] = useState('');
  const [annButtonText, setAnnButtonText] = useState('');
  const [annButtonUrl, setAnnButtonUrl] = useState('');
  const [annLocation, setAnnLocation] = useState<'top_bar' | 'homepage_banner' | 'popup'>('top_bar');
  const [annHighlighted, setAnnHighlighted] = useState(false);
  const [annActive, setAnnActive] = useState(true);
  const [annStartDate, setAnnStartDate] = useState('');
  const [annExpiryDate, setAnnExpiryDate] = useState('');

  // Handle generic input change
  const handleChange = (field: keyof SiteSettings, value: any) => {
    setFormData((prev) => ({
      ...prev,
      [field]: value,
    }));
  };

  // Handle form submit
  const handleSaveAll = (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    onSaveSettings(formData);
    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 3000);
  };

  // Social link helpers
  const handleSocialChange = (id: string, field: keyof SocialLink, value: any) => {
    const updated = formData.socialLinks.map((s) => (s.id === id ? { ...s, [field]: value } : s));
    handleChange('socialLinks', updated);
  };

  // Announcement form helpers
  const openNewAnnouncementModal = () => {
    setEditingAnnouncement(null);
    setAnnTitle('');
    setAnnMessage('');
    setAnnImageUrl('');
    setAnnButtonText('');
    setAnnButtonUrl('');
    setAnnLocation('top_bar');
    setAnnHighlighted(false);
    setAnnActive(true);
    setAnnStartDate('');
    setAnnExpiryDate('');
    setIsAnnouncementModalOpen(true);
  };

  const openEditAnnouncementModal = (item: AnnouncementItem) => {
    setEditingAnnouncement(item);
    setAnnTitle(item.title);
    setAnnMessage(item.message);
    setAnnImageUrl(item.imageUrl || '');
    setAnnButtonText(item.buttonText || '');
    setAnnButtonUrl(item.buttonUrl || '');
    setAnnLocation(item.displayLocation);
    setAnnHighlighted(item.isHighlighted);
    setAnnActive(item.isActive);
    setAnnStartDate(item.startDate || '');
    setAnnExpiryDate(item.expiryDate || '');
    setIsAnnouncementModalOpen(true);
  };

  const handleSaveAnnouncement = (e: React.FormEvent) => {
    e.preventDefault();
    if (!annTitle.trim() || !annMessage.trim()) return;

    if (editingAnnouncement) {
      onUpdateAnnouncement(editingAnnouncement.id, {
        title: annTitle.trim(),
        message: annMessage.trim(),
        imageUrl: annImageUrl.trim() || undefined,
        buttonText: annButtonText.trim() || undefined,
        buttonUrl: annButtonUrl.trim() || undefined,
        displayLocation: annLocation,
        isHighlighted: annHighlighted,
        isActive: annActive,
        startDate: annStartDate || undefined,
        expiryDate: annExpiryDate || undefined,
      });
    } else {
      onAddAnnouncement({
        title: annTitle.trim(),
        message: annMessage.trim(),
        imageUrl: annImageUrl.trim() || undefined,
        buttonText: annButtonText.trim() || undefined,
        buttonUrl: annButtonUrl.trim() || undefined,
        displayLocation: annLocation,
        isHighlighted: annHighlighted,
        isActive: annActive,
        startDate: annStartDate || undefined,
        expiryDate: annExpiryDate || undefined,
      });
    }
    setIsAnnouncementModalOpen(false);
  };

  return (
    <div className="bg-white border border-cyan-200 rounded-3xl p-6 md:p-8 shadow-sm">
      {/* Top Header & Save Button */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 mb-6">
        <div>
          <div className="flex items-center gap-2">
            <h2 className="text-xl font-black text-slate-900">Central Website Settings System</h2>
            <span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-300">
              Live Configuration
            </span>
          </div>
          <p className="text-slate-500 text-xs mt-1 font-medium">
            Changes saved here automatically update all buttons, contacts, WhatsApp channels, fees, and rules across the entire website.
          </p>
        </div>

        <button
          onClick={() => handleSaveAll()}
          className="px-5 py-2.5 rounded-xl font-bold text-xs bg-gradient-to-r from-cyan-500 via-blue-600 to-fuchsia-600 hover:from-cyan-400 hover:via-blue-500 hover:to-fuchsia-500 text-white shadow-md shadow-cyan-500/25 flex items-center justify-center gap-2 transition-all hover:shadow-[0_0_15px_rgba(6,182,212,0.4)] shrink-0"
        >
          {savedSuccess ? <Check className="w-4 h-4 text-emerald-300" /> : <Save className="w-4 h-4" />}
          <span>{savedSuccess ? 'Settings Saved Live!' : 'Save All Settings'}</span>
        </button>
      </div>

      {/* Settings Navigation Tabs (Mobile Responsive & Clean) */}
      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 mb-8">
        <button
          type="button"
          onClick={() => setActiveSubTab('general')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'general'
              ? 'bg-cyan-50 text-cyan-800 border-cyan-300 shadow-[0_0_12px_rgba(6,182,212,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <Globe className="w-4 h-4 text-cyan-600 shrink-0" />
          <span className="truncate">General</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('whatsapp')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'whatsapp'
              ? 'bg-emerald-50 text-emerald-800 border-emerald-300 shadow-[0_0_12px_rgba(16,185,129,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <MessageCircle className="w-4 h-4 text-emerald-600 shrink-0" />
          <span className="truncate">WhatsApp & Contact</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('announcements')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'announcements'
              ? 'bg-amber-50 text-amber-800 border-amber-300 shadow-[0_0_12px_rgba(245,158,11,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <Megaphone className="w-4 h-4 text-amber-600 shrink-0" />
          <span className="truncate">Announcements ({announcements.length})</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('social')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'social'
              ? 'bg-blue-50 text-blue-800 border-blue-300 shadow-[0_0_12px_rgba(37,99,235,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <Share2 className="w-4 h-4 text-blue-600 shrink-0" />
          <span className="truncate">Social Media</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('seo')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'seo'
              ? 'bg-purple-50 text-purple-800 border-purple-300 shadow-[0_0_12px_rgba(168,85,247,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <Search className="w-4 h-4 text-purple-600 shrink-0" />
          <span className="truncate">SEO & Meta</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('homepage')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'homepage'
              ? 'bg-cyan-50 text-cyan-800 border-cyan-300 shadow-[0_0_12px_rgba(6,182,212,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <Home className="w-4 h-4 text-cyan-600 shrink-0" />
          <span className="truncate">Homepage</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('marketplace')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'marketplace'
              ? 'bg-indigo-50 text-indigo-800 border-indigo-300 shadow-[0_0_12px_rgba(99,102,241,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <ShoppingBag className="w-4 h-4 text-indigo-600 shrink-0" />
          <span className="truncate">Marketplace</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('jobs')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'jobs'
              ? 'bg-blue-50 text-blue-800 border-blue-300 shadow-[0_0_12px_rgba(37,99,235,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <Briefcase className="w-4 h-4 text-blue-600 shrink-0" />
          <span className="truncate">Jobs</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('activation')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'activation'
              ? 'bg-emerald-50 text-emerald-800 border-emerald-300 shadow-[0_0_12px_rgba(16,185,129,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <ShieldCheck className="w-4 h-4 text-emerald-600 shrink-0" />
          <span className="truncate">Seller Activation</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('notifications')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'notifications'
              ? 'bg-fuchsia-50 text-fuchsia-800 border-fuchsia-300 shadow-[0_0_12px_rgba(217,70,239,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <Bell className="w-4 h-4 text-fuchsia-600 shrink-0" />
          <span className="truncate">Notifications</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('maintenance')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'maintenance'
              ? 'bg-rose-50 text-rose-800 border-rose-300 shadow-[0_0_12px_rgba(244,63,94,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <Wrench className="w-4 h-4 text-rose-600 shrink-0" />
          <span className="truncate">Maintenance</span>
        </button>

        <button
          type="button"
          onClick={() => setActiveSubTab('telegram')}
          className={`p-3 rounded-2xl font-bold text-xs flex items-center gap-2 border transition-all text-left ${
            activeSubTab === 'telegram'
              ? 'bg-sky-50 text-sky-800 border-sky-300 shadow-[0_0_12px_rgba(14,165,233,0.18)]'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-100'
          }`}
        >
          <Send className="w-4 h-4 text-sky-500 shrink-0" />
          <span className="truncate">Telegram Bot</span>
        </button>
      </div>

      {/* SUB-TAB 1: GENERAL SETTINGS */}
      {activeSubTab === 'general' && (
        <div className="space-y-4 text-xs">
          <h3 className="font-bold text-sm text-slate-900 mb-2">1. General Website Settings</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">Website Name</label>
              <input
                type="text"
                value={formData.websiteName}
                onChange={(e) => handleChange('websiteName', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Brand Logo Text</label>
              <input
                type="text"
                value={formData.logoText}
                onChange={(e) => handleChange('logoText', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Tagline</label>
              <input
                type="text"
                value={formData.tagline}
                onChange={(e) => handleChange('tagline', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Contact Email</label>
              <input
                type="email"
                value={formData.contactEmail}
                onChange={(e) => handleChange('contactEmail', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Official Calling Phone</label>
              <input
                type="text"
                value={formData.officialPhone}
                onChange={(e) => handleChange('officialPhone', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Official WhatsApp Phone</label>
              <input
                type="text"
                value={formData.officialWhatsapp}
                onChange={(e) => handleChange('officialWhatsapp', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Official Office / Physical Address</label>
            <input
              type="text"
              value={formData.officialAddress}
              onChange={(e) => handleChange('officialAddress', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Website Description</label>
            <textarea
              rows={3}
              value={formData.websiteDescription}
              onChange={(e) => handleChange('websiteDescription', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Footer Copyright Text</label>
            <input
              type="text"
              value={formData.copyrightText}
              onChange={(e) => handleChange('copyrightText', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>
        </div>
      )}

      {/* SUB-TAB 2: WHATSAPP & CONTACT SETTINGS */}
      {activeSubTab === 'whatsapp' && (
        <div className="space-y-4 text-xs">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
              <h3 className="font-bold text-sm text-slate-900">2. WhatsApp & Contact Management</h3>
              <p className="text-slate-500 text-[11px]">Control the official channel, group URLs, numbers, button text, and ON/OFF switches</p>
            </div>
          </div>

          {/* Master ON/OFF Toggles */}
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-emerald-50/50 border border-emerald-200 p-4 rounded-2xl">
            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isWhatsappChannelEnabled}
                onChange={(e) => handleChange('isWhatsappChannelEnabled', e.target.checked)}
                className="w-4 h-4 text-emerald-600 rounded"
              />
              <span>WhatsApp Channel</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isWhatsappGroupEnabled}
                onChange={(e) => handleChange('isWhatsappGroupEnabled', e.target.checked)}
                className="w-4 h-4 text-emerald-600 rounded"
              />
              <span>WhatsApp Group</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isWhatsappContactEnabled}
                onChange={(e) => handleChange('isWhatsappContactEnabled', e.target.checked)}
                className="w-4 h-4 text-emerald-600 rounded"
              />
              <span>Direct WhatsApp Chat</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isCallButtonEnabled}
                onChange={(e) => handleChange('isCallButtonEnabled', e.target.checked)}
                className="w-4 h-4 text-emerald-600 rounded"
              />
              <span>Phone Call Button</span>
            </label>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">Official WhatsApp Channel URL *</label>
              <input
                type="url"
                value={formData.whatsappChannelUrl}
                onChange={(e) => handleChange('whatsappChannelUrl', e.target.value)}
                placeholder="https://whatsapp.com/channel/..."
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium font-mono text-[11px]"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Official WhatsApp Group URL</label>
              <input
                type="url"
                value={formData.whatsappGroupUrl}
                onChange={(e) => handleChange('whatsappGroupUrl', e.target.value)}
                placeholder="https://chat.whatsapp.com/..."
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium font-mono text-[11px]"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">WhatsApp Channel Name</label>
              <input
                type="text"
                value={formData.whatsappChannelName}
                onChange={(e) => handleChange('whatsappChannelName', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">WhatsApp Group Name</label>
              <input
                type="text"
                value={formData.whatsappGroupName}
                onChange={(e) => handleChange('whatsappGroupName', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Official WhatsApp Number (Helpline)</label>
              <input
                type="text"
                value={formData.officialWhatsappNumber}
                onChange={(e) => handleChange('officialWhatsappNumber', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Official Direct Calling Number</label>
              <input
                type="text"
                value={formData.officialCallNumber}
                onChange={(e) => handleChange('officialCallNumber', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Follow Channel Button Label</label>
              <input
                type="text"
                value={formData.followChannelBtnText}
                onChange={(e) => handleChange('followChannelBtnText', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Join Group Button Label</label>
              <input
                type="text"
                value={formData.joinGroupBtnText}
                onChange={(e) => handleChange('joinGroupBtnText', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>
          </div>
        </div>
      )}

      {/* SUB-TAB 3: ANNOUNCEMENT SYSTEM */}
      {activeSubTab === 'announcements' && (
        <div className="space-y-4 text-xs">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
              <h3 className="font-bold text-sm text-slate-900">3. Live Announcement System</h3>
              <p className="text-slate-500 text-[11px]">Display alerts on the top bar, homepage banner, or as high-priority notices.</p>
            </div>
            <button
              onClick={openNewAnnouncementModal}
              className="px-3.5 py-1.5 rounded-xl font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 flex items-center gap-1.5 shadow-sm"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>Add Announcement</span>
            </button>
          </div>

          <div className="space-y-2.5">
            {announcements.map((ann) => (
              <div
                key={ann.id}
                className={`p-4 rounded-2xl border flex flex-col md:flex-row md:items-center justify-between gap-3 ${
                  ann.isActive
                    ? 'bg-amber-50/40 border-amber-200 shadow-xs'
                    : 'bg-slate-50 border-slate-200 opacity-60'
                }`}
              >
                <div>
                  <div className="flex items-center gap-2 mb-1">
                    <span className="font-bold text-slate-900 text-sm">{ann.title}</span>
                    <span className="text-[10px] uppercase font-bold px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700">
                      📍 {ann.displayLocation.replace('_', ' ')}
                    </span>
                    {ann.isHighlighted && (
                      <span className="text-[10px] font-extrabold px-2 py-0.5 rounded bg-rose-100 text-rose-700 border border-rose-300">
                        ⭐ Highlighted
                      </span>
                    )}
                    <span
                      className={`text-[10px] font-bold px-2 py-0.5 rounded ${
                        ann.isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'
                      }`}
                    >
                      {ann.isActive ? 'Active' : 'Disabled'}
                    </span>
                  </div>
                  <p className="text-slate-600 font-medium">{ann.message}</p>
                  {ann.buttonText && ann.buttonUrl && (
                    <div className="text-[11px] text-cyan-700 font-bold mt-1 flex items-center gap-1">
                      <span>Button: {ann.buttonText} →</span>
                      <span className="font-mono text-slate-400">{ann.buttonUrl}</span>
                    </div>
                  )}
                </div>

                <div className="flex items-center gap-2 shrink-0">
                  <button
                    onClick={() => onUpdateAnnouncement(ann.id, { isActive: !ann.isActive })}
                    className={`px-3 py-1.5 rounded-xl font-bold text-xs border ${
                      ann.isActive ? 'bg-white text-slate-700 border-slate-300' : 'bg-emerald-600 text-white border-emerald-600'
                    }`}
                  >
                    {ann.isActive ? 'Disable' : 'Enable'}
                  </button>
                  <button
                    onClick={() => openEditAnnouncementModal(ann)}
                    className="p-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100"
                  >
                    <Edit className="w-3.5 h-3.5" />
                  </button>
                  <button
                    onClick={() => onDeleteAnnouncement(ann.id)}
                    className="p-1.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* SUB-TAB 4: SOCIAL MEDIA SETTINGS */}
      {activeSubTab === 'social' && (
        <div className="space-y-4 text-xs">
          <h3 className="font-bold text-sm text-slate-900 mb-2">4. Official Social Media Channels</h3>
          <p className="text-slate-500 text-[11px] mb-3">Links to official SargodhaMart Facebook, Instagram, YouTube, and TikTok accounts.</p>

          <div className="space-y-3">
            {formData.socialLinks.map((soc) => (
              <div
                key={soc.id}
                className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center gap-3"
              >
                <div className="w-36 font-bold text-slate-900 flex items-center gap-2">
                  <span>🌐</span>
                  <span>{soc.platform}</span>
                </div>

                <div className="flex-1">
                  <input
                    type="url"
                    value={soc.url}
                    onChange={(e) => handleSocialChange(soc.id, 'url', e.target.value)}
                    placeholder={`https://${soc.platform.toLowerCase()}.com/...`}
                    className="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-900 font-medium"
                  />
                </div>

                <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                  <input
                    type="checkbox"
                    checked={soc.isEnabled}
                    onChange={(e) => handleSocialChange(soc.id, 'isEnabled', e.target.checked)}
                    className="w-4 h-4 text-blue-600 rounded"
                  />
                  <span>Active</span>
                </label>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* SUB-TAB 5: SEO SETTINGS */}
      {activeSubTab === 'seo' && (
        <div className="space-y-4 text-xs">
          <h3 className="font-bold text-sm text-slate-900 mb-2">5. Search Engine Optimization & Social Sharing Cards</h3>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Default SEO Page Title *</label>
            <input
              type="text"
              value={formData.defaultSeoTitle}
              onChange={(e) => handleChange('defaultSeoTitle', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Default Meta Description *</label>
            <textarea
              rows={2}
              value={formData.defaultSeoDescription}
              onChange={(e) => handleChange('defaultSeoDescription', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Keywords (Comma Separated)</label>
            <input
              type="text"
              value={formData.defaultKeywords}
              onChange={(e) => handleChange('defaultKeywords', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">OpenGraph / Social Title</label>
              <input
                type="text"
                value={formData.ogTitle}
                onChange={(e) => handleChange('ogTitle', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">OpenGraph Share Image URL</label>
              <input
                type="url"
                value={formData.ogImageUrl}
                onChange={(e) => handleChange('ogImageUrl', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>
          </div>
        </div>
      )}

      {/* SUB-TAB 6: HOMEPAGE SETTINGS */}
      {activeSubTab === 'homepage' && (
        <div className="space-y-4 text-xs">
          <h3 className="font-bold text-sm text-slate-900 mb-2">6. Homepage Elements & Section Controls</h3>

          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-cyan-50/50 border border-cyan-200 p-4 rounded-2xl">
            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isFeaturedProductsEnabled}
                onChange={(e) => handleChange('isFeaturedProductsEnabled', e.target.checked)}
                className="w-4 h-4 text-cyan-600 rounded"
              />
              <span>Featured Products</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isLatestProductsEnabled}
                onChange={(e) => handleChange('isLatestProductsEnabled', e.target.checked)}
                className="w-4 h-4 text-cyan-600 rounded"
              />
              <span>Latest Products</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isJobsSectionEnabled}
                onChange={(e) => handleChange('isJobsSectionEnabled', e.target.checked)}
                className="w-4 h-4 text-cyan-600 rounded"
              />
              <span>Jobs & Work Hub</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isWorkflowGuideEnabled}
                onChange={(e) => handleChange('isWorkflowGuideEnabled', e.target.checked)}
                className="w-4 h-4 text-cyan-600 rounded"
              />
              <span>4-Step Workflow Guide</span>
            </label>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">Hero Heading Primary</label>
              <input
                type="text"
                value={formData.heroHeading}
                onChange={(e) => handleChange('heroHeading', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Hero Heading Gradient Highlight</label>
              <input
                type="text"
                value={formData.heroHeadingHighlight}
                onChange={(e) => handleChange('heroHeadingHighlight', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Hero Subtitle Description</label>
            <textarea
              rows={3}
              value={formData.heroDescription}
              onChange={(e) => handleChange('heroDescription', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">WhatsApp CTA Callout Text</label>
            <input
              type="text"
              value={formData.whatsappCtaText}
              onChange={(e) => handleChange('whatsappCtaText', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>
        </div>
      )}

      {/* SUB-TAB 7: MARKETPLACE SETTINGS */}
      {activeSubTab === 'marketplace' && (
        <div className="space-y-4 text-xs">
          <h3 className="font-bold text-sm text-slate-900 mb-2">7. Marketplace Listing Rules & Safeguards</h3>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">Maximum Photos per Product *</label>
              <input
                type="number"
                min={1}
                max={6}
                value={formData.maxImagesPerListing}
                onChange={(e) => handleChange('maxImagesPerListing', Number(e.target.value))}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
              <span className="text-[10px] text-slate-400">Default is 3 photos per listing to optimize mobile loading speed.</span>
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Default Listing Expiry Duration (Days)</label>
              <input
                type="number"
                value={formData.defaultListingDurationDays}
                onChange={(e) => handleChange('defaultListingDurationDays', Number(e.target.value))}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Marketplace Terms & Rules Notice</label>
            <textarea
              rows={3}
              value={formData.listingRulesText}
              onChange={(e) => handleChange('listingRulesText', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>
        </div>
      )}

      {/* SUB-TAB 8: JOBS & EMPLOYMENT SETTINGS */}
      {activeSubTab === 'jobs' && (
        <div className="space-y-4 text-xs">
          <h3 className="font-bold text-sm text-slate-900 mb-2">8. Jobs & Employment Hub Settings (Rozgar Portal)</h3>
          <p className="text-slate-500 text-[11px]">
            Configure the local recruitment & job opportunities portal for Sargodha, Shaheenabad, and Sillanwali.
          </p>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-blue-50/50 border border-blue-200 p-4 rounded-2xl">
            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isJobsSectionEnabled}
                onChange={(e) => handleChange('isJobsSectionEnabled', e.target.checked)}
                className="w-4 h-4 text-blue-600 rounded"
              />
              <span>Enable Jobs Module</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isNeedJobEnabled ?? true}
                onChange={(e) => handleChange('isNeedJobEnabled', e.target.checked)}
                className="w-4 h-4 text-blue-600 rounded"
              />
              <span>Allow "Seeking Work" Posts</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isNeedWorkerEnabled ?? true}
                onChange={(e) => handleChange('isNeedWorkerEnabled', e.target.checked)}
                className="w-4 h-4 text-blue-600 rounded"
              />
              <span>Allow "Employer Hiring" Posts</span>
            </label>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">Wage & Compensation Guidance Example</label>
              <input
                type="text"
                value={formData.jobSalaryGuidance ?? 'Daily: Rs. 1,000 - 3,500 | Monthly: Rs. 25,000 - 85,000'}
                onChange={(e) => handleChange('jobSalaryGuidance', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
                placeholder="e.g. Daily: Rs. 1,000 - 3,500 | Monthly: Rs. 25,000 - 85,000"
              />
              <span className="text-[10px] text-slate-400">Shown to workers and employers when posting jobs.</span>
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Dynamic Job SEO Title Pattern</label>
              <input
                type="text"
                value={formData.jobSeoPattern ?? '{title} – {city} | SargodhaMart Jobs'}
                onChange={(e) => handleChange('jobSeoPattern', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium font-mono"
                placeholder="{title} – {city} | SargodhaMart Jobs"
              />
              <span className="text-[10px] text-slate-400">Tokens: {'{title}'}, {'{city}'}, {'{area}'}</span>
            </div>
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Job Posting Rules & Safety Notice</label>
            <textarea
              rows={3}
              value={formData.jobRulesText ?? 'Only genuine local employment opportunities across Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp contact only. No illegal recruitment fees.'}
              onChange={(e) => handleChange('jobRulesText', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Job Published Success Message</label>
            <input
              type="text"
              value={formData.jobPublishedMsg}
              onChange={(e) => handleChange('jobPublishedMsg', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>
        </div>
      )}

      {/* SUB-TAB 9: SELLER ACTIVATION SETTINGS */}
      {activeSubTab === 'activation' && (
        <div className="space-y-4 text-xs">
          <h3 className="font-bold text-sm text-slate-900 mb-2">9. Lifetime Seller Activation & Payment Gateways</h3>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">Lifetime Activation Fee (PKR) *</label>
              <input
                type="number"
                value={formData.activationFee}
                onChange={(e) => handleChange('activationFee', Number(e.target.value))}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Account Title / Receiver Name *</label>
              <input
                type="text"
                value={formData.paymentAccountTitle}
                onChange={(e) => handleChange('paymentAccountTitle', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">EasyPaisa Account Number *</label>
              <input
                type="text"
                value={formData.easyPaisaNumber}
                onChange={(e) => handleChange('easyPaisaNumber', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-mono font-bold"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">JazzCash Account Number *</label>
              <input
                type="text"
                value={formData.jazzCashNumber}
                onChange={(e) => handleChange('jazzCashNumber', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-mono font-bold"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-emerald-50/50 border border-emerald-200 p-4 rounded-2xl">
            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isPaymentScreenshotRequired}
                onChange={(e) => handleChange('isPaymentScreenshotRequired', e.target.checked)}
                className="w-4 h-4 text-emerald-600 rounded"
              />
              <span>Require Rs. 1,000 Payment Screenshot</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isWhatsappFollowScreenshotRequired}
                onChange={(e) => handleChange('isWhatsappFollowScreenshotRequired', e.target.checked)}
                className="w-4 h-4 text-emerald-600 rounded"
              />
              <span>Require WhatsApp Channel Follow Screenshot</span>
            </label>
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Bank Transfer Details</label>
            <input
              type="text"
              value={formData.bankDetails}
              onChange={(e) => handleChange('bankDetails', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Payment Instructions to Sellers</label>
            <textarea
              rows={2}
              value={formData.paymentInstructions}
              onChange={(e) => handleChange('paymentInstructions', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>
        </div>
      )}

      {/* SUB-TAB 10: NOTIFICATIONS & MESSAGES */}
      {activeSubTab === 'notifications' && (
        <div className="space-y-4 text-xs">
          <h3 className="font-bold text-sm text-slate-900 mb-2">10. Toast Notifications & System Messages</h3>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Registration Success Message</label>
            <input
              type="text"
              value={formData.registrationSuccessMsg}
              onChange={(e) => handleChange('registrationSuccessMsg', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Activation Proofs Submitted Message</label>
            <input
              type="text"
              value={formData.activationSubmittedMsg}
              onChange={(e) => handleChange('activationSubmittedMsg', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Activation Approved Notification</label>
            <input
              type="text"
              value={formData.activationApprovedMsg}
              onChange={(e) => handleChange('activationApprovedMsg', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Product Listing Published Message</label>
            <input
              type="text"
              value={formData.listingPublishedMsg}
              onChange={(e) => handleChange('listingPublishedMsg', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>

          <div>
            <label className="block font-semibold text-slate-800 mb-1">Job Post Published Message</label>
            <input
              type="text"
              value={formData.jobPublishedMsg}
              onChange={(e) => handleChange('jobPublishedMsg', e.target.value)}
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
            />
          </div>
        </div>
      )}

      {/* SUB-TAB 11: MAINTENANCE SETTINGS */}
      {activeSubTab === 'maintenance' && (
        <div className="space-y-4 text-xs">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
              <h3 className="font-bold text-sm text-slate-900">11. Emergency Maintenance & Portal Access Controls</h3>
              <p className="text-slate-500 text-[11px]">Suspend user operations while preserving full administrative panel access.</p>
            </div>
          </div>

          <div className="p-4 rounded-2xl bg-rose-50 border border-rose-300 space-y-3">
            <label className="flex items-center gap-2 cursor-pointer font-black text-rose-800 text-sm">
              <input
                type="checkbox"
                checked={formData.isMaintenanceMode}
                onChange={(e) => handleChange('isMaintenanceMode', e.target.checked)}
                className="w-5 h-5 text-rose-600 rounded"
              />
              <span>Activate Marketplace Maintenance Mode</span>
            </label>
            <p className="text-[11px] text-rose-700 font-medium">
              When enabled, public visitors see the maintenance notice below. Administrators can always log in and manage the site.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">Maintenance Banner Title</label>
              <input
                type="text"
                value={formData.maintenanceTitle}
                onChange={(e) => handleChange('maintenanceTitle', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Maintenance Message Body</label>
              <textarea
                rows={2}
                value={formData.maintenanceMessage}
                onChange={(e) => handleChange('maintenanceMessage', e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 border border-slate-200 p-4 rounded-2xl">
            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isNewRegistrationEnabled}
                onChange={(e) => handleChange('isNewRegistrationEnabled', e.target.checked)}
                className="w-4 h-4 text-cyan-600 rounded"
              />
              <span>Allow New Registrations</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isNewListingEnabled}
                onChange={(e) => handleChange('isNewListingEnabled', e.target.checked)}
                className="w-4 h-4 text-cyan-600 rounded"
              />
              <span>Allow New Product Ads</span>
            </label>

            <label className="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
              <input
                type="checkbox"
                checked={formData.isJobPostingEnabled}
                onChange={(e) => handleChange('isJobPostingEnabled', e.target.checked)}
                className="w-4 h-4 text-cyan-600 rounded"
              />
              <span>Allow New Job Posts</span>
            </label>
          </div>
        </div>
      )}

      {/* SUB-TAB 12: TELEGRAM BOT AUTOMATION */}
      {activeSubTab === 'telegram' && (
        <div className="space-y-6 text-xs">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
              <h3 className="font-bold text-sm text-slate-900 flex items-center gap-2">
                <Send className="w-4 h-4 text-sky-500" />
                <span>12. Telegram Channel Automation & Bot Integration</span>
              </h3>
              <p className="text-slate-500 text-[11px]">
                Broadcast approved products, jobs, and urgent announcements directly to your verified Telegram Channel.
              </p>
            </div>
            <div className="flex items-center gap-2">
              <span
                className={`px-3 py-1 rounded-full text-[10px] font-extrabold uppercase flex items-center gap-1.5 ${
                  formData.telegramEnabled
                    ? 'bg-sky-100 text-sky-800 border border-sky-300'
                    : 'bg-slate-100 text-slate-600 border border-slate-200'
                }`}
              >
                <Radio className={`w-3 h-3 ${formData.telegramEnabled ? 'text-sky-600 animate-pulse' : 'text-slate-400'}`} />
                {formData.telegramEnabled ? 'Automation Active' : 'Automation Disabled'}
              </span>
            </div>
          </div>

          {/* Master Toggle */}
          <div className="p-4 rounded-2xl bg-sky-50/60 border border-sky-200 space-y-2">
            <div className="flex items-center justify-between">
              <label className="flex items-center gap-2.5 cursor-pointer font-bold text-slate-900 text-sm">
                <input
                  type="checkbox"
                  checked={formData.telegramEnabled}
                  onChange={(e) => handleChange('telegramEnabled', e.target.checked)}
                  className="w-5 h-5 text-sky-600 rounded"
                />
                <span>Enable Telegram Integration</span>
              </label>
              <span className="text-[11px] text-sky-800 font-semibold">
                Official Channel ID: <code className="bg-white px-2 py-0.5 rounded border border-sky-200 font-mono">{formData.telegramChannelId || '-1003328935535'}</code>
              </span>
            </div>
            <p className="text-[11px] text-slate-600 pl-7">
              When enabled, verified approved content is automatically formatted and published to your official Telegram Channel with interactive View, Call, and WhatsApp buttons.
            </p>
          </div>

          {/* Server-Side Secret Token Security Card */}
          <div className="bg-slate-900 text-white p-5 rounded-2xl border border-slate-800 space-y-4 shadow-sm">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div className="flex items-center gap-2.5">
                <div className="p-2 bg-sky-500/20 text-sky-400 rounded-xl border border-sky-500/30">
                  <KeyRound className="w-5 h-5" />
                </div>
                <div>
                  <div className="font-bold text-sm text-white flex items-center gap-2">
                    <span>Server-Side Bot Token Secret</span>
                    <span className="bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">
                      Zero Client Exposure
                    </span>
                  </div>
                  <p className="text-slate-400 text-[11px]">
                    Environment variable: <code className="text-sky-300 font-mono">TELEGRAM_BOT_TOKEN</code>. Never revealed in browser or source code.
                  </p>
                </div>
              </div>

              {/* Status Badge */}
              <div className="flex items-center gap-2">
                {checkingTelegram ? (
                  <span className="text-slate-400 flex items-center gap-1.5 text-[11px]">
                    <RefreshCw className="w-3.5 h-3.5 animate-spin" /> Verifying...
                  </span>
                ) : telegramStatus?.hasTokenConfigured ? (
                  <span className="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-3 py-1.5 rounded-xl font-bold text-xs flex items-center gap-1.5">
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    <span>Configured on Server {telegramStatus.botUsername ? `(@${telegramStatus.botUsername})` : ''}</span>
                  </span>
                ) : (
                  <span className="bg-amber-500/10 text-amber-400 border border-amber-500/30 px-3 py-1.5 rounded-xl font-bold text-xs flex items-center gap-1.5">
                    <AlertCircle className="w-3.5 h-3.5" />
                    <span>No Token Configured</span>
                  </span>
                )}
              </div>
            </div>

            {/* Token Update Input */}
            <div className="bg-slate-950 p-3.5 rounded-xl border border-slate-800 space-y-2">
              <label className="block text-slate-300 text-[11px] font-semibold">
                Update / Set Telegram Bot Token (from @BotFather)
              </label>
              <div className="flex flex-col sm:flex-row items-stretch gap-2">
                <input
                  type="password"
                  value={newTokenInput}
                  onChange={(e) => setNewTokenInput(e.target.value)}
                  placeholder={telegramStatus?.hasTokenConfigured ? '•••••••••••••••• (Token is active on server)' : 'Enter bot token e.g. 7123456789:ABCDef...'}
                  className="flex-grow px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white font-mono text-xs placeholder:text-slate-500 focus:outline-none focus:border-sky-500"
                />
                <button
                  type="button"
                  onClick={handleUpdateToken}
                  disabled={isUpdatingToken || !newTokenInput.trim()}
                  className="px-4 py-2 bg-sky-600 hover:bg-sky-500 disabled:opacity-50 text-white font-bold rounded-lg text-xs flex items-center justify-center gap-1.5 transition-all shrink-0"
                >
                  {isUpdatingToken ? <RefreshCw className="w-3.5 h-3.5 animate-spin" /> : <Save className="w-3.5 h-3.5" />}
                  <span>Save Server Secret</span>
                </button>
              </div>
              {tokenNotice && (
                <div
                  className={`p-2.5 rounded-lg text-xs font-semibold flex items-center gap-2 ${
                    tokenNotice.type === 'success'
                      ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-800'
                      : 'bg-rose-950/80 text-rose-300 border border-rose-800'
                  }`}
                >
                  {tokenNotice.type === 'success' ? <CheckCircle2 className="w-4 h-4 shrink-0" /> : <XCircle className="w-4 h-4 shrink-0" />}
                  <span>{tokenNotice.message}</span>
                </div>
              )}
            </div>
          </div>

          {/* Channel ID & Auto-Publishing Rules */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Channel ID */}
            <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
              <label className="block font-bold text-slate-900 text-xs">
                Telegram Channel ID
              </label>
              <input
                type="text"
                value={formData.telegramChannelId}
                onChange={(e) => handleChange('telegramChannelId', e.target.value)}
                placeholder="-1003328935535"
                className="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 font-mono font-bold text-xs"
              />
              <p className="text-[11px] text-slate-500 leading-relaxed">
                Default: <code className="font-mono text-cyan-800 font-bold">-1003328935535</code>. Make sure your bot is added to this channel as an Administrator with <strong>Post Messages</strong> permission.
              </p>
            </div>

            {/* Auto-Publish Rules */}
            <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
              <label className="block font-bold text-slate-900 text-xs">
                Automatic Publishing Triggers
              </label>
              <div className="space-y-2">
                <label className="flex items-center gap-2 cursor-pointer font-semibold text-slate-800 text-xs">
                  <input
                    type="checkbox"
                    checked={formData.telegramAutoPublishProducts}
                    onChange={(e) => handleChange('telegramAutoPublishProducts', e.target.checked)}
                    className="w-4 h-4 text-sky-600 rounded"
                  />
                  <span>Auto-publish when Product is approved</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer font-semibold text-slate-800 text-xs">
                  <input
                    type="checkbox"
                    checked={formData.telegramAutoPublishJobs}
                    onChange={(e) => handleChange('telegramAutoPublishJobs', e.target.checked)}
                    className="w-4 h-4 text-sky-600 rounded"
                  />
                  <span>Auto-publish when Job / Rozgar is approved</span>
                </label>
                <label className="flex items-center gap-2 cursor-pointer font-semibold text-slate-800 text-xs">
                  <input
                    type="checkbox"
                    checked={formData.telegramAutoPublishAnnouncements}
                    onChange={(e) => handleChange('telegramAutoPublishAnnouncements', e.target.checked)}
                    className="w-4 h-4 text-sky-600 rounded"
                  />
                  <span>Auto-publish active Announcements</span>
                </label>
              </div>
              <p className="text-[10px] text-amber-700 bg-amber-50 p-2 rounded-lg border border-amber-200 font-medium">
                🛡️ Security rule enforced: Pending, rejected, or unapproved content will NEVER be published to Telegram.
              </p>
            </div>
          </div>

          {/* Test Action Buttons Bar */}
          <div className="p-4 rounded-2xl bg-white border border-sky-200 shadow-sm space-y-3">
            <div className="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
              <div>
                <h4 className="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                  <Bot className="w-4 h-4 text-sky-600" />
                  <span>Interactive Connection & Channel Test</span>
                </h4>
                <p className="text-slate-500 text-[11px]">
                  Verify Bot credentials and dispatch a formatted test broadcast to channel {formData.telegramChannelId || '-1003328935535'}.
                </p>
              </div>

              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={handleTestConnection}
                  disabled={testingConnection}
                  className="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all border border-slate-200"
                >
                  <RefreshCw className={`w-3.5 h-3.5 ${testingConnection ? 'animate-spin' : ''}`} />
                  <span>Test Connection</span>
                </button>

                <button
                  type="button"
                  onClick={handleSendTestMessage}
                  disabled={sendingTestMsg}
                  className="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm shadow-sky-600/20"
                >
                  <Send className={`w-3.5 h-3.5 ${sendingTestMsg ? 'animate-spin' : ''}`} />
                  <span>Send Test Message</span>
                </button>
              </div>
            </div>

            {/* Test notices */}
            {connectionNotice && (
              <div
                className={`p-3 rounded-xl text-xs font-semibold flex items-center gap-2 ${
                  connectionNotice.type === 'success'
                    ? 'bg-emerald-50 text-emerald-800 border border-emerald-300'
                    : 'bg-rose-50 text-rose-800 border border-rose-300'
                }`}
              >
                {connectionNotice.type === 'success' ? <CheckCircle2 className="w-4 h-4 shrink-0" /> : <XCircle className="w-4 h-4 shrink-0" />}
                <span>{connectionNotice.message}</span>
              </div>
            )}

            {testMsgNotice && (
              <div
                className={`p-3 rounded-xl text-xs font-semibold flex items-center gap-2 ${
                  testMsgNotice.type === 'success'
                    ? 'bg-emerald-50 text-emerald-800 border border-emerald-300'
                    : 'bg-rose-50 text-rose-800 border border-rose-300'
                }`}
              >
                {testMsgNotice.type === 'success' ? <CheckCircle2 className="w-4 h-4 shrink-0" /> : <XCircle className="w-4 h-4 shrink-0" />}
                <div className="flex-grow">
                  <div>{testMsgNotice.message}</div>
                  {testMsgNotice.messageId && (
                    <div className="text-[10px] text-emerald-700 font-mono mt-0.5">
                      Message ID: #{testMsgNotice.messageId} (Channel: {formData.telegramChannelId || '-1003328935535'})
                    </div>
                  )}
                </div>
              </div>
            )}
          </div>

          {/* Telegram Publication Status & Logs History */}
          <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
            <div className="p-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
              <div>
                <h4 className="font-bold text-slate-900 text-xs flex items-center gap-2">
                  <span>📜</span> Telegram Publication Logs & Delivery Status
                </h4>
                <p className="text-slate-500 text-[10px]">
                  Automatic duplicate prevention: Published items record their Telegram Message ID to prevent re-posting.
                </p>
              </div>
              <span className="text-[10px] font-bold text-slate-600 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                Total Logs: {telegramLogs.length}
              </span>
            </div>

            {telegramLogs.length === 0 ? (
              <div className="p-8 text-center text-slate-400 text-xs">
                No Telegram publications recorded yet. Test broadcasts and approved listings will appear here.
              </div>
            ) : (
              <div className="divide-y divide-slate-100 max-h-72 overflow-y-auto">
                {telegramLogs.map((log) => (
                  <div key={log.id} className="p-3 flex items-center justify-between gap-3 text-xs hover:bg-slate-50">
                    <div className="flex items-center gap-3">
                      <span
                        className={`w-7 h-7 rounded-xl flex items-center justify-center shrink-0 font-bold text-xs ${
                          log.contentType === 'product'
                            ? 'bg-cyan-100 text-cyan-800'
                            : log.contentType === 'job'
                            ? 'bg-blue-100 text-blue-800'
                            : log.contentType === 'announcement'
                            ? 'bg-amber-100 text-amber-800'
                            : 'bg-purple-100 text-purple-800'
                        }`}
                      >
                        {log.contentType === 'product' ? '📦' : log.contentType === 'job' ? '💼' : log.contentType === 'announcement' ? '📢' : '🤖'}
                      </span>
                      <div>
                        <div className="font-bold text-slate-900 line-clamp-1">{log.title}</div>
                        <div className="text-[10px] text-slate-500 flex items-center gap-2 mt-0.5">
                          <span>{log.timestamp}</span>
                          <span>•</span>
                          <span className="font-mono">{formData.telegramChannelId || '-1003328935535'}</span>
                          {log.messageId && (
                            <>
                              <span>•</span>
                              <span className="text-emerald-700 font-mono font-bold">Msg #{log.messageId}</span>
                            </>
                          )}
                          {log.errorMessage && (
                            <>
                              <span>•</span>
                              <span className="text-rose-600 font-medium">{log.errorMessage}</span>
                            </>
                          )}
                        </div>
                      </div>
                    </div>

                    <div className="flex items-center gap-2">
                      <span
                        className={`px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase ${
                          log.status === 'success'
                            ? 'bg-emerald-100 text-emerald-800 border border-emerald-200'
                            : 'bg-rose-100 text-rose-800 border border-rose-200'
                        }`}
                      >
                        {log.status === 'success' ? 'Delivered' : 'Failed'}
                      </span>

                      {log.status === 'failed' && log.contentId && onPublishToTelegram && (
                        <button
                          type="button"
                          onClick={() => onPublishToTelegram(log.contentType as any, log.contentId!, true)}
                          className="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-[10px] rounded-lg border border-amber-300"
                        >
                          Retry
                        </button>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}

      {/* Floating Save Button Bar */}
      <div className="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between">
        <span className="text-slate-500 text-xs">
          Settings are saved instantly to local browser persistence and backend configuration.
        </span>
        <button
          onClick={() => handleSaveAll()}
          className="px-6 py-2.5 rounded-xl font-bold text-xs bg-gradient-to-r from-cyan-500 via-blue-600 to-fuchsia-600 hover:from-cyan-400 hover:via-blue-500 hover:to-fuchsia-500 text-white shadow-md shadow-cyan-500/25 flex items-center gap-2 hover:shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all"
        >
          {savedSuccess ? <Check className="w-4 h-4 text-emerald-300" /> : <Save className="w-4 h-4" />}
          <span>{savedSuccess ? 'Changes Applied Live!' : 'Save Settings'}</span>
        </button>
      </div>

      {/* Announcement Create / Edit Modal */}
      {isAnnouncementModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md overflow-y-auto">
          <div className="relative w-full max-w-lg bg-white border border-amber-200 rounded-3xl p-6 shadow-2xl my-8">
            <h3 className="text-base font-bold text-slate-900 mb-1 flex items-center gap-2">
              <Megaphone className="w-4 h-4 text-amber-600" />
              <span>{editingAnnouncement ? 'Edit Announcement' : 'Create New Announcement'}</span>
            </h3>
            <p className="text-slate-500 text-xs mb-4">
              Announcements appear automatically on the top bar or homepage based on display selection.
            </p>

            <form onSubmit={handleSaveAnnouncement} className="space-y-3 text-xs">
              <div>
                <label className="block font-semibold text-slate-800 mb-1">Announcement Title *</label>
                <input
                  type="text"
                  value={annTitle}
                  onChange={(e) => setAnnTitle(e.target.value)}
                  placeholder="e.g. 📢 Official WhatsApp Channel Now Live!"
                  className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
                  required
                />
              </div>

              <div>
                <label className="block font-semibold text-slate-800 mb-1">Announcement Message *</label>
                <textarea
                  rows={3}
                  value={annMessage}
                  onChange={(e) => setAnnMessage(e.target.value)}
                  placeholder="e.g. Join 5,000+ local citizens across Sargodha for verified notifications..."
                  className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-slate-800 mb-1">Button Label (Optional)</label>
                  <input
                    type="text"
                    value={annButtonText}
                    onChange={(e) => setAnnButtonText(e.target.value)}
                    placeholder="e.g. Join Channel"
                    className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium"
                  />
                </div>

                <div>
                  <label className="block font-semibold text-slate-800 mb-1">Button URL</label>
                  <input
                    type="url"
                    value={annButtonUrl}
                    onChange={(e) => setAnnButtonUrl(e.target.value)}
                    placeholder="https://whatsapp.com/channel/..."
                    className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium font-mono text-[11px]"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block font-semibold text-slate-800 mb-1">Display Location</label>
                  <select
                    value={annLocation}
                    onChange={(e) => setAnnLocation(e.target.value as any)}
                    className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 font-medium"
                  >
                    <option value="top_bar">Top Bar Strip</option>
                    <option value="homepage_banner">Homepage Banner</option>
                    <option value="popup">Notice Popup</option>
                  </select>
                </div>

                <div className="flex items-center gap-4 pt-5">
                  <label className="flex items-center gap-1.5 cursor-pointer font-bold text-slate-800">
                    <input
                      type="checkbox"
                      checked={annHighlighted}
                      onChange={(e) => setAnnHighlighted(e.target.checked)}
                      className="w-4 h-4 text-amber-600 rounded"
                    />
                    <span>Highlight</span>
                  </label>

                  <label className="flex items-center gap-1.5 cursor-pointer font-bold text-slate-800">
                    <input
                      type="checkbox"
                      checked={annActive}
                      onChange={(e) => setAnnActive(e.target.checked)}
                      className="w-4 h-4 text-emerald-600 rounded"
                    />
                    <span>Active</span>
                  </label>
                </div>
              </div>

              <div className="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setIsAnnouncementModalOpen(false)}
                  className="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 rounded-xl font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-sm"
                >
                  {editingAnnouncement ? 'Update Announcement' : 'Save Announcement'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
