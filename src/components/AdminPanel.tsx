import React, { useState } from 'react';
import {
  Shield,
  Users,
  CheckCircle2,
  Clock,
  ShoppingBag,
  Briefcase,
  AlertTriangle,
  Sparkles,
  Eye,
  Check,
  X,
  Trash2,
  ExternalLink,
  Settings,
  Image as ImageIcon,
  LogOut,
  Send,
  RefreshCw,
  MapPin,
  Layers,
  MessageCircle,
} from 'lucide-react';
import {
  UserProfile,
  Listing,
  JobPost,
  ActivationPayment,
  ReportItem,
  SiteSettings,
  AnnouncementItem,
  TelegramLogItem,
  District,
  Tehsil,
  AreaLocation,
} from '../types';
import { AdminSettingsManager } from './AdminSettingsManager';
import { AdminLocationManager } from './AdminLocationManager';

interface AdminPanelProps {
  currentUser: UserProfile;
  users: UserProfile[];
  listings: Listing[];
  jobs: JobPost[];
  payments: ActivationPayment[];
  reports: ReportItem[];
  settings: SiteSettings;
  onSaveSettings: (updated: SiteSettings) => void;
  announcements: AnnouncementItem[];
  onAddAnnouncement: (item: Omit<AnnouncementItem, 'id' | 'createdAt'>) => void;
  onUpdateAnnouncement: (id: number, item: Partial<AnnouncementItem>) => void;
  onDeleteAnnouncement: (id: number) => void;
  onApprovePayment: (paymentId: number, userId: number) => void;
  onRejectPayment: (paymentId: number) => void;
  onToggleListingStatus: (listingId: number) => void;
  onToggleListingFeatured: (listingId: number) => void;
  onDeleteListing: (listingId: number) => void;
  onToggleJobStatus: (jobId: number) => void;
  onDeleteJob: (jobId: number) => void;
  onResolveReport: (reportId: number) => void;
  onForceLogoutUser: (userId: number) => void;
  telegramLogs?: TelegramLogItem[];
  onPublishToTelegram?: (type: 'product' | 'job' | 'announcement', id: number, force?: boolean) => Promise<boolean>;
  onAddTelegramLog?: (log: TelegramLogItem) => void;
  districts?: District[];
  tehsils?: Tehsil[];
  areas?: AreaLocation[];
  onAddDistrict?: (name: string) => void;
  onUpdateDistrict?: (id: number, updates: Partial<District>) => void;
  onDeleteDistrict?: (id: number) => void;
  onAddTehsil?: (name: string, districtId: number) => void;
  onUpdateTehsil?: (id: number, updates: Partial<Tehsil>) => void;
  onDeleteTehsil?: (id: number) => void;
  onAddArea?: (name: string, tehsilId: number) => void;
  onUpdateArea?: (id: number, updates: Partial<AreaLocation>) => void;
  onDeleteArea?: (id: number) => void;
}

export const AdminPanel: React.FC<AdminPanelProps> = ({
  currentUser,
  users,
  listings,
  jobs,
  payments,
  reports,
  settings,
  onSaveSettings,
  announcements,
  onAddAnnouncement,
  onUpdateAnnouncement,
  onDeleteAnnouncement,
  onApprovePayment,
  onRejectPayment,
  onToggleListingStatus,
  onToggleListingFeatured,
  onDeleteListing,
  onToggleJobStatus,
  onDeleteJob,
  onResolveReport,
  onForceLogoutUser,
  telegramLogs = [],
  onPublishToTelegram,
  onAddTelegramLog,
  districts = [],
  tehsils = [],
  areas = [],
  onAddDistrict = () => {},
  onUpdateDistrict = () => {},
  onDeleteDistrict = () => {},
  onAddTehsil = () => {},
  onUpdateTehsil = () => {},
  onDeleteTehsil = () => {},
  onAddArea = () => {},
  onUpdateArea = () => {},
  onDeleteArea = () => {},
}) => {
  const [activeTab, setActiveTab] = useState<'overview' | 'activations' | 'products' | 'jobs' | 'locations' | 'users' | 'reports' | 'settings'>('activations');
  const [selectedVerificationPayment, setSelectedVerificationPayment] = useState<ActivationPayment | null>(null);

  // 11 Key KPI Metrics
  const totalUsers = users.length;
  const activeUsers = users.filter((u) => u.activationStatus === 'active').length;
  const pendingActivations = payments.filter((p) => p.status === 'pending').length;
  const approvedActivations = payments.filter((p) => p.status === 'approved').length;
  const totalProducts = listings.length;
  const activeProducts = listings.filter((l) => l.status === 'published').length;
  const disabledProducts = listings.filter((l) => l.status === 'disabled').length;
  const featuredProducts = listings.filter((l) => l.isFeatured).length;
  const totalJobs = jobs.length;
  const activeJobs = jobs.filter((j) => j.status === 'published').length;
  const pendingReports = reports.filter((r) => r.status === 'pending').length;

  return (
    <div className="py-8 px-4 max-w-7xl mx-auto">
      {/* Header */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-200">
        <div className="flex items-center gap-3">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-500 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-rose-500/25">
            <Shield className="w-6 h-6" />
          </div>
          <div>
            <h1 className="text-2xl font-black text-slate-900">SargodhaMart Administrator Portal</h1>
            <p className="text-slate-500 text-xs font-medium">
              Direct verification of Rs. 1,000 payments, WhatsApp channel proof, and marketplace moderation
            </p>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <span className="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-white border border-slate-200 text-slate-700 shadow-xs">
            Admin: <strong className="text-rose-600 font-bold">{currentUser.name}</strong>
          </span>
        </div>
      </div>

      {/* 11 KPI Metrics Grid - White Neon Cards */}
      <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-8">
        <div className="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs">
          <div className="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Total Users</div>
          <div className="text-xl font-black text-slate-900 mt-1">{totalUsers}</div>
        </div>
        <div className="p-3.5 rounded-2xl bg-white border border-emerald-200 shadow-xs">
          <div className="text-emerald-700 text-[10px] font-bold uppercase tracking-wider">Active Sellers</div>
          <div className="text-xl font-black text-emerald-700 mt-1">{activeUsers}</div>
        </div>
        <div className="p-3.5 rounded-2xl bg-rose-50/60 border border-rose-300 shadow-[0_0_15px_rgba(244,63,94,0.1)]">
          <div className="text-rose-700 text-[10px] font-bold uppercase tracking-wider">Pending Activations</div>
          <div className="text-xl font-black text-rose-700 mt-1">{pendingActivations}</div>
        </div>
        <div className="p-3.5 rounded-2xl bg-white border border-cyan-200 shadow-xs">
          <div className="text-cyan-800 text-[10px] font-bold uppercase tracking-wider">Approved Fees</div>
          <div className="text-xl font-black text-cyan-800 mt-1">{approvedActivations}</div>
        </div>
        <div className="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs">
          <div className="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Total Products</div>
          <div className="text-xl font-black text-slate-900 mt-1">{totalProducts}</div>
        </div>
        <div className="p-3.5 rounded-2xl bg-white border border-blue-200 shadow-xs">
          <div className="text-blue-700 text-[10px] font-bold uppercase tracking-wider">Active Products</div>
          <div className="text-xl font-black text-blue-700 mt-1">{activeProducts}</div>
        </div>
        <div className="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs">
          <div className="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Disabled Products</div>
          <div className="text-xl font-black text-slate-500 mt-1">{disabledProducts}</div>
        </div>
        <div className="p-3.5 rounded-2xl bg-white border border-fuchsia-200 shadow-xs">
          <div className="text-fuchsia-700 text-[10px] font-bold uppercase tracking-wider">Featured Ads</div>
          <div className="text-xl font-black text-fuchsia-700 mt-1">{featuredProducts}</div>
        </div>
        <div className="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-xs">
          <div className="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Total Jobs</div>
          <div className="text-xl font-black text-slate-900 mt-1">{totalJobs}</div>
        </div>
        <div className="p-3.5 rounded-2xl bg-white border border-indigo-200 shadow-xs">
          <div className="text-indigo-700 text-[10px] font-bold uppercase tracking-wider">Active Jobs</div>
          <div className="text-xl font-black text-indigo-700 mt-1">{activeJobs}</div>
        </div>
        <div className="p-3.5 rounded-2xl bg-white border border-amber-200 shadow-xs">
          <div className="text-amber-700 text-[10px] font-bold uppercase tracking-wider">Reports Queue</div>
          <div className="text-xl font-black text-amber-700 mt-1">{pendingReports}</div>
        </div>
      </div>

      {/* Tabs Switcher - White Neon */}
      <div className="flex items-center gap-2 mb-6 border-b border-slate-200 pb-3 overflow-x-auto text-xs font-bold">
        <button
          onClick={() => setActiveTab('activations')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'activations'
              ? 'bg-white text-rose-800 border border-rose-300 shadow-[0_0_12px_rgba(244,63,94,0.18)]'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <span>💳</span> Activation Queue ({payments.filter((p) => p.status === 'pending').length})
        </button>
        <button
          onClick={() => setActiveTab('products')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'products'
              ? 'bg-white text-cyan-800 border border-cyan-300 shadow-[0_0_12px_rgba(6,182,212,0.18)]'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <span>📦</span> Products ({listings.length})
        </button>
        <button
          onClick={() => setActiveTab('jobs')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'jobs'
              ? 'bg-white text-blue-800 border border-blue-300 shadow-[0_0_12px_rgba(37,99,235,0.18)]'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <span>💼</span> Jobs & Work ({jobs.length})
        </button>

        <button
          onClick={() => setActiveTab('locations')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'locations'
              ? 'bg-white text-emerald-800 border border-emerald-300 shadow-[0_0_12px_rgba(16,185,129,0.18)] font-black'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <span>📍</span> Locations ({districts.length} Districts)
        </button>

        <button
          onClick={() => setActiveTab('users')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'users'
              ? 'bg-white text-purple-800 border border-purple-300 shadow-[0_0_12px_rgba(168,85,247,0.18)]'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <span>👥</span> Users ({users.length})
        </button>
        <button
          onClick={() => setActiveTab('reports')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'reports'
              ? 'bg-white text-amber-800 border border-amber-300 shadow-[0_0_12px_rgba(245,158,11,0.18)]'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <span>🚩</span> Reports ({reports.length})
        </button>

        <button
          onClick={() => setActiveTab('settings')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'settings'
              ? 'bg-white text-cyan-800 border border-cyan-300 shadow-[0_0_12px_rgba(6,182,212,0.18)] font-black'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <span>⚙️</span> Website Settings
        </button>
      </div>

      {/* TAB 1: Activation Verification Queue */}
      {activeTab === 'activations' && (
        <div className="bg-white border border-cyan-200 rounded-3xl p-6 shadow-[0_10px_35px_-5px_rgba(6,182,212,0.12)]">
          <div className="flex items-center justify-between mb-4">
            <h3 className="text-base font-bold text-slate-900 flex items-center gap-2">
              <span>🛡️</span> Dual-Verification Queue: Rs. 1,000 Payment + WhatsApp Channel Screenshot
            </h3>
            <span className="text-xs text-slate-500 font-medium">
              Each submission requires manual verification of both transfer and channel follow proof.
            </span>
          </div>

          <div className="space-y-3">
            {payments.map((p) => (
              <div
                key={p.id}
                className="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4"
              >
                <div className="space-y-1 text-xs">
                  <div className="flex items-center gap-2">
                    <span className="font-bold text-slate-900 text-sm">{p.userName}</span>
                    <span className="text-[11px] text-slate-500 font-medium">({p.userCity})</span>
                    <span
                      className={`px-2 py-0.5 rounded text-[10px] font-extrabold uppercase ${
                        p.status === 'approved'
                          ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                          : p.status === 'pending'
                          ? 'bg-rose-100 text-rose-800 border border-rose-300'
                          : 'bg-slate-200 text-slate-600'
                      }`}
                    >
                      {p.status}
                    </span>
                  </div>
                  <div className="text-slate-600 font-medium">
                    Phone: <span className="text-slate-900 font-bold">{p.userPhone}</span> • Method:{' '}
                    <span className="text-cyan-800 font-bold">{p.method}</span> • Sender:{' '}
                    <span className="text-slate-900 font-mono font-bold">{p.senderNumber}</span>
                  </div>
                  <div className="text-slate-600 font-medium">
                    TRX ID: <span className="font-mono text-cyan-800 font-bold">{p.transactionId}</span> • Amount:{' '}
                    <strong className="text-emerald-700 font-bold">Rs. {p.amount.toLocaleString()}</strong> • Date: {p.createdAt}
                  </div>
                </div>

                <div className="flex items-center gap-2">
                  <button
                    onClick={() => setSelectedVerificationPayment(p)}
                    className="px-3.5 py-2 rounded-xl text-xs font-bold bg-white hover:bg-slate-100 text-cyan-800 border border-cyan-300 flex items-center gap-1.5 transition-all shadow-xs"
                  >
                    <ImageIcon className="w-3.5 h-3.5" />
                    <span>View Dual Proofs</span>
                  </button>

                  {p.status === 'pending' && (
                    <>
                      <button
                        onClick={() => onApprovePayment(p.id, p.userId)}
                        className="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/25 flex items-center gap-1.5 transition-all"
                      >
                        <Check className="w-3.5 h-3.5" />
                        <span>Approve & Activate</span>
                      </button>
                      <button
                        onClick={() => onRejectPayment(p.id)}
                        className="px-3 py-2 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 transition-all"
                      >
                        Reject
                      </button>
                    </>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* TAB 2: Products Moderation */}
      {activeTab === 'products' && (
        <div className="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-3">
          {listings.map((l) => (
            <div
              key={l.id}
              className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4 text-xs"
            >
              <div className="flex items-center gap-3">
                <img src={l.images[0]} alt={l.title} className="w-14 h-14 rounded-xl object-cover border border-slate-200" />
                <div>
                  <div className="font-bold text-slate-900 text-sm line-clamp-1">{l.title}</div>
                  <div className="text-slate-600 font-medium">
                    Rs. {l.price.toLocaleString()} • {l.city} • Seller: {l.sellerName} ({l.sellerPhone})
                  </div>
                  <div className="text-slate-500 text-[10px]">
                    Status: <span className="text-cyan-800 font-bold">{l.status}</span> • Views: {l.views}
                  </div>
                </div>
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={() => onToggleListingFeatured(l.id)}
                  className={`px-3 py-1.5 rounded-xl font-bold text-xs flex items-center gap-1 ${
                    l.isFeatured
                      ? 'bg-fuchsia-600 text-white shadow-sm'
                      : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100'
                  }`}
                >
                  <Sparkles className="w-3 h-3" />
                  {l.isFeatured ? 'Featured' : 'Make Featured'}
                </button>

                <button
                  onClick={() => onToggleListingStatus(l.id)}
                  className={`px-3 py-1.5 rounded-xl font-bold text-xs ${
                    l.status === 'published' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                  }`}
                >
                  {l.status === 'published' ? 'Disable' : 'Publish'}
                </button>

                {/* Telegram Broadcast Button */}
                {l.status === 'published' ? (
                  l.telegramPublished ? (
                    <div className="flex items-center gap-1 bg-sky-50 border border-sky-200 px-2 py-1 rounded-xl text-[10px] font-bold text-sky-800">
                      <Send className="w-3 h-3 text-sky-500" />
                      <span>Sent {l.telegramMessageId ? `#${l.telegramMessageId}` : ''}</span>
                      {onPublishToTelegram && (
                        <button
                          type="button"
                          onClick={() => onPublishToTelegram('product', l.id, true)}
                          title="Resend to Telegram Channel"
                          className="p-0.5 rounded hover:bg-sky-200 text-sky-700 ml-1 transition-colors"
                        >
                          <RefreshCw className="w-2.5 h-2.5" />
                        </button>
                      )}
                    </div>
                  ) : (
                    onPublishToTelegram && (
                      <button
                        type="button"
                        onClick={() => onPublishToTelegram('product', l.id)}
                        className="px-2.5 py-1.5 rounded-xl font-bold text-xs bg-sky-500 hover:bg-sky-400 text-white flex items-center gap-1 shadow-sm transition-all"
                        title="Publish to Telegram Channel"
                      >
                        <Send className="w-3 h-3" />
                        <span>Telegram</span>
                      </button>
                    )
                  )
                ) : (
                  <span className="text-[10px] text-slate-400 font-medium px-1.5" title="Approve product to enable Telegram broadcast">
                    Pending
                  </span>
                )}

                <button
                  onClick={() => onDeleteListing(l.id)}
                  className="p-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* TAB 3: Jobs Moderation */}
      {activeTab === 'jobs' && (
        <div className="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-3">
          {jobs.map((j) => (
            <div
              key={j.id}
              className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4 text-xs"
            >
              <div>
                <div className="flex items-center gap-2 mb-1">
                  <span
                    className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase ${
                      j.postType === 'need_worker' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-cyan-100 text-cyan-800 border border-cyan-200'
                    }`}
                  >
                    {j.postType === 'need_worker' ? 'Hiring' : 'Seeking Work'}
                  </span>
                  <span className="font-bold text-slate-900 text-sm">{j.title}</span>
                </div>
                <div className="text-slate-600 font-medium">
                  {j.city} ({j.area}) • Contact: {j.phone} • Skills: {j.skills}
                </div>
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={() => onToggleJobStatus(j.id)}
                  className={`px-3 py-1.5 rounded-xl font-bold text-xs ${
                    j.status === 'published' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                  }`}
                >
                  {j.status === 'published' ? 'Disable' : 'Enable'}
                </button>

                {/* Telegram Broadcast Button */}
                {j.status === 'published' ? (
                  j.telegramPublished ? (
                    <div className="flex items-center gap-1 bg-sky-50 border border-sky-200 px-2 py-1 rounded-xl text-[10px] font-bold text-sky-800">
                      <Send className="w-3 h-3 text-sky-500" />
                      <span>Sent {j.telegramMessageId ? `#${j.telegramMessageId}` : ''}</span>
                      {onPublishToTelegram && (
                        <button
                          type="button"
                          onClick={() => onPublishToTelegram('job', j.id, true)}
                          title="Resend to Telegram Channel"
                          className="p-0.5 rounded hover:bg-sky-200 text-sky-700 ml-1 transition-colors"
                        >
                          <RefreshCw className="w-2.5 h-2.5" />
                        </button>
                      )}
                    </div>
                  ) : (
                    onPublishToTelegram && (
                      <button
                        type="button"
                        onClick={() => onPublishToTelegram('job', j.id)}
                        className="px-2.5 py-1.5 rounded-xl font-bold text-xs bg-sky-500 hover:bg-sky-400 text-white flex items-center gap-1 shadow-sm transition-all"
                        title="Publish to Telegram Channel"
                      >
                        <Send className="w-3 h-3" />
                        <span>Telegram</span>
                      </button>
                    )
                  )
                ) : (
                  <span className="text-[10px] text-slate-400 font-medium px-1.5" title="Approve job to enable Telegram broadcast">
                    Disabled
                  </span>
                )}

                <button
                  onClick={() => onDeleteJob(j.id)}
                  className="p-2 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* TAB 4: Location Hierarchy Management (Division -> District -> Tehsil -> Area) */}
      {activeTab === 'locations' && (
        <AdminLocationManager
          districts={districts}
          tehsils={tehsils}
          areas={areas}
          onAddDistrict={onAddDistrict}
          onUpdateDistrict={onUpdateDistrict}
          onDeleteDistrict={onDeleteDistrict}
          onAddTehsil={onAddTehsil}
          onUpdateTehsil={onUpdateTehsil}
          onDeleteTehsil={onDeleteTehsil}
          onAddArea={onAddArea}
          onUpdateArea={onUpdateArea}
          onDeleteArea={onDeleteArea}
        />
      )}

      {/* TAB 5: Users Management */}
      {activeTab === 'users' && (
        <div className="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-3">
          <div className="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
              <h3 className="font-bold text-slate-900 text-sm">Registered User Accounts & Session Control</h3>
              <p className="text-slate-500 text-xs">Manage active accounts, roles, and administrative force logout</p>
            </div>
            <span className="text-xs font-bold text-slate-500">Total Users: {users.length}</span>
          </div>

          {users.map((u) => (
            <div
              key={u.id}
              className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs"
            >
              <div>
                <div className="font-bold text-slate-900 text-sm flex items-center gap-2">
                  <span>{u.name}</span>
                  <span className={`text-[10px] px-2 py-0.5 rounded-full font-bold uppercase ${u.role === 'super_admin' ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-slate-200 text-slate-700'}`}>
                    {u.role}
                  </span>
                  {currentUser.id === u.id && (
                    <span className="text-[10px] text-cyan-800 font-extrabold bg-cyan-100 px-2 py-0.5 rounded-full border border-cyan-300">
                      You (Current Session)
                    </span>
                  )}
                </div>
                <div className="text-slate-600 font-medium mt-0.5">
                  {u.mobile} • {u.city} • {u.email}
                </div>
              </div>

              <div className="flex items-center gap-2">
                <span
                  className={`px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase ${
                    u.activationStatus === 'active'
                      ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                      : 'bg-amber-100 text-amber-800 border border-amber-300'
                  }`}
                >
                  {u.activationStatus === 'active' ? 'Active Seller' : 'Pending Activation'}
                </span>

                {/* Force Logout Button */}
                <button
                  onClick={() => onForceLogoutUser(u.id)}
                  className="px-3 py-1.5 rounded-xl font-bold text-xs bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1.5 transition-all shadow-xs"
                  title={`Force logout session for ${u.name}`}
                >
                  <LogOut className="w-3.5 h-3.5" />
                  <span>Force Logout</span>
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* TAB 5: Reports Moderation */}
      {activeTab === 'reports' && (
        <div className="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm space-y-3">
          {reports.map((r) => (
            <div
              key={r.id}
              className="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-4 text-xs"
            >
              <div>
                <div className="font-bold text-rose-700 text-sm mb-0.5">
                  Report: {r.reason} on {r.targetTitle}
                </div>
                <div className="text-slate-600 font-medium">
                  By {r.reporterName} • Details: {r.details}
                </div>
              </div>

              <div className="flex items-center gap-2">
                {r.status === 'pending' ? (
                  <button
                    onClick={() => onResolveReport(r.id)}
                    className="px-3.5 py-1.5 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 text-white"
                  >
                    Mark Resolved
                  </button>
                ) : (
                  <span className="text-emerald-700 font-bold text-xs">Resolved</span>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {/* TAB 6: Central Website Settings System */}
      {activeTab === 'settings' && (
        <AdminSettingsManager
          settings={settings}
          onSaveSettings={onSaveSettings}
          announcements={announcements}
          onAddAnnouncement={onAddAnnouncement}
          onUpdateAnnouncement={onUpdateAnnouncement}
          onDeleteAnnouncement={onDeleteAnnouncement}
          telegramLogs={telegramLogs}
          onAddTelegramLog={onAddTelegramLog}
          onPublishToTelegram={onPublishToTelegram}
        />
      )}

      {/* Dual Verification Modal (Payment Proof + WhatsApp Channel Screenshot) */}
      {selectedVerificationPayment && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md overflow-y-auto">
          <div className="relative w-full max-w-4xl bg-white border border-cyan-200 rounded-3xl p-6 md:p-8 shadow-[0_20px_60px_-15px_rgba(6,182,212,0.25)] my-8">
            <button
              onClick={() => setSelectedVerificationPayment(null)}
              className="absolute top-5 right-5 p-2 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-slate-100"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-lg font-bold text-slate-900 mb-2 flex items-center gap-2">
              <Shield className="w-5 h-5 text-rose-600" /> Dual-Verification Inspection
            </h3>
            <p className="text-slate-600 text-xs mb-6 font-medium">
              User: <strong className="text-slate-900">{selectedVerificationPayment.userName}</strong> ({selectedVerificationPayment.userCity}) • Phone:{' '}
              <strong className="text-slate-900">{selectedVerificationPayment.userPhone}</strong> • TRX ID:{' '}
              <strong className="text-cyan-800 font-mono font-bold">{selectedVerificationPayment.transactionId}</strong>
            </p>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              {/* Proof 1: Payment Screenshot */}
              <div className="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div className="text-xs font-bold text-cyan-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                  <span>1️⃣</span> Rs. 1,000 Payment Screenshot
                </div>
                <div className="aspect-4/3 rounded-xl overflow-hidden bg-white border border-slate-200">
                  <img
                    src={selectedVerificationPayment.paymentScreenshot}
                    alt="Payment Transfer Proof"
                    className="w-full h-full object-contain"
                  />
                </div>
                <div className="mt-2 text-[11px] text-slate-600 font-medium">
                  Method: {selectedVerificationPayment.method} • Sender: {selectedVerificationPayment.senderNumber}
                </div>
              </div>

              {/* Proof 2: WhatsApp Channel Screenshot (Optional) */}
              <div className="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div className="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-2 flex items-center justify-between">
                  <span className="flex items-center gap-1.5">
                    <span>📲</span> WhatsApp Follow
                  </span>
                  <span className="text-[10px] font-bold text-slate-500 bg-slate-200/70 px-2 py-0.5 rounded-full">
                    Optional
                  </span>
                </div>
                <div className="aspect-4/3 rounded-xl overflow-hidden bg-white border border-slate-200 flex items-center justify-center">
                  {selectedVerificationPayment.whatsappScreenshot && selectedVerificationPayment.whatsappScreenshot !== 'proof-placeholder' ? (
                    <img
                      src={selectedVerificationPayment.whatsappScreenshot}
                      alt="WhatsApp Channel Follow Proof"
                      className="w-full h-full object-contain"
                    />
                  ) : (
                    <div className="p-4 text-center text-slate-400">
                      <MessageCircle className="w-8 h-8 mx-auto mb-2 text-slate-300" />
                      <div className="text-xs font-bold text-slate-600">No Screenshot Provided</div>
                      <div className="text-[10px] text-slate-400 mt-1">WhatsApp follow is optional and does not block approval.</div>
                    </div>
                  )}
                </div>
                <div className="mt-2 text-[11px] text-slate-600 font-medium">
                  {selectedVerificationPayment.whatsappScreenshot && selectedVerificationPayment.whatsappScreenshot !== 'proof-placeholder'
                    ? 'Optional follow screenshot attached by seller'
                    : 'Optional community link (Membership is not required)'}
                </div>
              </div>
            </div>

            {/* Action Bar */}
            <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
              <button
                onClick={() => setSelectedVerificationPayment(null)}
                className="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-100"
              >
                Close
              </button>
              {selectedVerificationPayment.status === 'pending' && (
                <>
                  <button
                    onClick={() => {
                      onRejectPayment(selectedVerificationPayment.id);
                      setSelectedVerificationPayment(null);
                    }}
                    className="px-4 py-2 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-300"
                  >
                    Reject Verification
                  </button>
                  <button
                    onClick={() => {
                      onApprovePayment(selectedVerificationPayment.id, selectedVerificationPayment.userId);
                      setSelectedVerificationPayment(null);
                    }}
                    className="px-6 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/25 flex items-center gap-1.5"
                  >
                    <Check className="w-4 h-4" />
                    Approve & Activate Account
                  </button>
                </>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
