import React, { useState } from 'react';
import {
  Megaphone,
  PlusCircle,
  Edit2,
  Trash2,
  CheckCircle2,
  XCircle,
  Clock,
  Sparkles,
  Send,
  Eye,
  RefreshCw,
  AlertCircle,
  Check,
  X,
  MapPin,
  ExternalLink,
  Phone,
  MessageCircle,
  DollarSign,
  Layers,
  PauseCircle,
  PlayCircle
} from 'lucide-react';
import {
  AdvertisingPackage,
  Advertisement,
  AdvertisingPayment,
  AdPlacement,
  AdStatus,
  SiteSettings,
} from '../types';
import { ADVERTISING_CATEGORIES } from '../data/advertisingData';

interface AdminAdvertisingManagerProps {
  packages: AdvertisingPackage[];
  advertisements: Advertisement[];
  payments?: AdvertisingPayment[];
  onSavePackage: (pkg: AdvertisingPackage) => void;
  onDeletePackage: (id: number) => void;
  onApproveAd: (adId: number) => void;
  onRejectAd: (adId: number, reason: string) => void;
  onToggleAdSuspension: (adId: number) => void;
  onRetryTelegram: (adId: number) => Promise<boolean>;
  settings?: SiteSettings;
}

const ALL_PLACEMENTS: AdPlacement[] = [
  'Homepage',
  'Category Page',
  'Product Page',
  'Jobs Page',
  'Digital Skills Page',
  'Search Results',
  'Location Page',
  'Telegram Channel',
  'Multiple Website Locations',
];

export const AdminAdvertisingManager: React.FC<AdminAdvertisingManagerProps> = ({
  packages,
  advertisements,
  onSavePackage,
  onDeletePackage,
  onApproveAd,
  onRejectAd,
  onToggleAdSuspension,
  onRetryTelegram,
  settings,
}) => {
  const [activeSubTab, setActiveSubTab] = useState<'packages' | 'ads'>('packages');
  const [adFilter, setAdFilter] = useState<'all' | 'pending' | 'active' | 'expired' | 'rejected'>('all');

  // Package Edit/Add Modal State
  const [editingPackage, setEditingPackage] = useState<AdvertisingPackage | null>(null);
  const [isPkgModalOpen, setIsPkgModalOpen] = useState(false);

  // Package Form Fields
  const [pkgName, setPkgName] = useState('');
  const [pkgAdType, setPkgAdType] = useState('Small Banner');
  const [pkgPrice, setPkgPrice] = useState<number>(1000);
  const [pkgDuration, setPkgDuration] = useState<number>(15);
  const [pkgDescription, setPkgDescription] = useState('');
  const [pkgPlacements, setPkgPlacements] = useState<AdPlacement[]>(['Homepage']);
  const [pkgTelegram, setPkgTelegram] = useState(false);
  const [pkgRecommended, setPkgRecommended] = useState(false);
  const [pkgActive, setPkgActive] = useState(true);
  const [pkgFeaturesStr, setPkgFeaturesStr] = useState('');

  // Ad Detail / Verification Modal
  const [selectedAd, setSelectedAd] = useState<Advertisement | null>(null);
  const [rejectionReason, setRejectionReason] = useState('');
  const [isRejecting, setIsRejecting] = useState(false);
  const [retryingTelegramId, setRetryingTelegramId] = useState<number | null>(null);

  // Statistics
  const pendingAdsCount = advertisements.filter((a) => a.status === 'PENDING').length;
  const activeAdsCount = advertisements.filter((a) => a.status === 'ACTIVE').length;
  const totalRevenue = advertisements
    .filter((a) => a.status === 'ACTIVE' || a.status === 'APPROVED' || a.status === 'EXPIRED')
    .reduce((sum, a) => sum + (a.amountPaid || 0), 0);

  const filteredAds = advertisements.filter((a) => {
    if (adFilter === 'all') return true;
    if (adFilter === 'pending') return a.status === 'PENDING';
    if (adFilter === 'active') return a.status === 'ACTIVE';
    if (adFilter === 'expired') return a.status === 'EXPIRED';
    if (adFilter === 'rejected') return a.status === 'REJECTED';
    return true;
  });

  const handleOpenAddPackage = () => {
    setEditingPackage(null);
    setPkgName('');
    setPkgAdType(ADVERTISING_CATEGORIES[0] || 'Small Banner');
    setPkgPrice(1000);
    setPkgDuration(15);
    setPkgDescription('');
    setPkgPlacements(['Homepage', 'Category Page']);
    setPkgTelegram(false);
    setPkgRecommended(false);
    setPkgActive(true);
    setPkgFeaturesStr('Featured Banner Placement, Direct Call & WhatsApp, High Visibility');
    setIsPkgModalOpen(true);
  };

  const handleOpenEditPackage = (pkg: AdvertisingPackage) => {
    setEditingPackage(pkg);
    setPkgName(pkg.name);
    setPkgAdType(pkg.adType);
    setPkgPrice(pkg.price);
    setPkgDuration(pkg.durationDays);
    setPkgDescription(pkg.description);
    setPkgPlacements(pkg.placements || []);
    setPkgTelegram(pkg.telegramEnabled);
    setPkgRecommended(pkg.recommended);
    setPkgActive(pkg.isActive);
    setPkgFeaturesStr(pkg.features?.join('\n') || '');
    setIsPkgModalOpen(true);
  };

  const handleSavePackageSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!pkgName.trim()) return;

    const featuresList = pkgFeaturesStr
      .split('\n')
      .map((f) => f.trim())
      .filter(Boolean);

    const savedPkg: AdvertisingPackage = {
      id: editingPackage ? editingPackage.id : Date.now(),
      name: pkgName.trim(),
      adType: pkgAdType,
      price: Number(pkgPrice) || 0,
      durationDays: Number(pkgDuration) || 1,
      description: pkgDescription.trim(),
      placements: pkgPlacements.length > 0 ? pkgPlacements : ['Homepage'],
      telegramEnabled: pkgTelegram,
      recommended: pkgRecommended,
      isActive: pkgActive,
      displayOrder: editingPackage ? editingPackage.displayOrder : packages.length + 1,
      features: featuresList.length > 0 ? featuresList : ['Standard Ad Placement'],
      updatedAt: new Date().toISOString(),
    };

    onSavePackage(savedPkg);
    setIsPkgModalOpen(false);
  };

  const handleTogglePlacement = (placement: AdPlacement) => {
    if (pkgPlacements.includes(placement)) {
      setPkgPlacements(pkgPlacements.filter((p) => p !== placement));
    } else {
      setPkgPlacements([...pkgPlacements, placement]);
    }
  };

  const handleApproveAdClick = (id: number) => {
    onApproveAd(id);
    setSelectedAd(null);
  };

  const handleConfirmReject = () => {
    if (!selectedAd || !rejectionReason.trim()) return;
    onRejectAd(selectedAd.id, rejectionReason.trim());
    setSelectedAd(null);
    setIsRejecting(false);
    setRejectionReason('');
  };

  const handleRetryTelegramClick = async (adId: number) => {
    setRetryingTelegramId(adId);
    await onRetryTelegram(adId);
    setRetryingTelegramId(null);
  };

  return (
    <div className="space-y-6">
      {/* Top Header & Metrics Banner */}
      <div className="bg-gradient-to-r from-slate-950 via-slate-900 to-amber-950 rounded-3xl p-6 text-white shadow-xl border border-amber-500/20 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div className="flex items-center gap-2 text-amber-400 font-extrabold text-xs uppercase tracking-wider mb-1">
            <Megaphone className="w-4 h-4" /> Advertising Pricing & Control System
          </div>
          <h2 className="text-xl sm:text-2xl font-black text-white">
            Manage Ad Packages & Campaigns
          </h2>
          <p className="text-xs text-amber-100/80 max-w-xl mt-1 leading-relaxed">
            Full admin pricing authority. Change package rates, duration, and multi-placement settings instantly without modifying codebase.
          </p>
        </div>

        {/* Quick Metrics */}
        <div className="flex flex-wrap items-center gap-3">
          <div className="px-4 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center min-w-[100px]">
            <div className="text-[10px] uppercase font-bold text-amber-300">Pending Review</div>
            <div className="text-xl font-black text-white">{pendingAdsCount}</div>
          </div>
          <div className="px-4 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center min-w-[100px]">
            <div className="text-[10px] uppercase font-bold text-emerald-300">Active Ads</div>
            <div className="text-xl font-black text-white">{activeAdsCount}</div>
          </div>
          <div className="px-4 py-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/10 text-center min-w-[120px]">
            <div className="text-[10px] uppercase font-bold text-cyan-300">Total Ad Revenue</div>
            <div className="text-xl font-black text-cyan-200">Rs. {totalRevenue.toLocaleString()}</div>
          </div>
        </div>
      </div>

      {/* Main Tabs: Packages vs Ads */}
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-3">
        <div className="flex items-center gap-2">
          <button
            onClick={() => setActiveSubTab('packages')}
            className={`px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 ${
              activeSubTab === 'packages'
                ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25'
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
            }`}
          >
            <Layers className="w-4 h-4" /> Advertising Packages ({packages.length})
          </button>

          <button
            onClick={() => setActiveSubTab('ads')}
            className={`px-4 py-2 rounded-xl text-xs font-black transition-all flex items-center gap-2 ${
              activeSubTab === 'ads'
                ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25'
                : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
            }`}
          >
            <Megaphone className="w-4 h-4" /> Advertisements ({advertisements.length})
            {pendingAdsCount > 0 && (
              <span className="px-1.5 py-0.2 rounded-full bg-rose-600 text-white text-[10px] font-extrabold animate-pulse">
                {pendingAdsCount}
              </span>
            )}
          </button>
        </div>

        {activeSubTab === 'packages' && (
          <button
            onClick={handleOpenAddPackage}
            className="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold flex items-center gap-1.5 shadow-md shadow-slate-900/20 transition-all hover:scale-102"
          >
            <PlusCircle className="w-4 h-4 text-amber-400" /> Add New Package
          </button>
        )}
      </div>

      {/* SUB-TAB 1: PACKAGES MANAGEMENT */}
      {activeSubTab === 'packages' && (
        <div className="space-y-4">
          <div className="text-xs text-slate-500 font-medium flex items-center justify-between">
            <span>Click <strong>"Edit"</strong> on any package to change its price, duration, placements, or status.</span>
            <span className="text-[11px] text-cyan-800 font-bold">⭐ Recommended package highlighted for users</span>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {packages.map((pkg) => (
              <div
                key={pkg.id}
                className={`bg-white rounded-3xl p-5 border-2 transition-all shadow-xs flex flex-col justify-between relative ${
                  pkg.recommended
                    ? 'border-amber-400 shadow-md shadow-amber-500/10'
                    : 'border-slate-200 hover:border-slate-300'
                } ${!pkg.isActive ? 'opacity-60 bg-slate-50' : ''}`}
              >
                {pkg.recommended && (
                  <span className="absolute -top-3 right-4 px-2.5 py-0.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 text-[10px] font-black uppercase shadow-xs flex items-center gap-1">
                    <Sparkles className="w-3 h-3 fill-slate-950" /> RECOMMENDED
                  </span>
                )}

                <div>
                  <div className="flex items-start justify-between gap-2 mb-2">
                    <div>
                      <span className="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-extrabold uppercase tracking-wider">
                        {pkg.adType}
                      </span>
                      <h3 className="text-sm font-black text-slate-950 mt-1">{pkg.name}</h3>
                    </div>

                    <span
                      className={`px-2 py-0.5 rounded-full text-[10px] font-extrabold ${
                        pkg.isActive
                          ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                          : 'bg-slate-200 text-slate-600'
                      }`}
                    >
                      {pkg.isActive ? 'Active' : 'Disabled'}
                    </span>
                  </div>

                  <p className="text-xs text-slate-600 line-clamp-2 mb-3 leading-relaxed">
                    {pkg.description}
                  </p>

                  <div className="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 space-y-1.5 mb-3 text-xs">
                    <div className="flex items-center justify-between font-bold">
                      <span className="text-slate-500">Current Price:</span>
                      <span className="text-cyan-800 font-black text-sm">Rs. {pkg.price.toLocaleString()}</span>
                    </div>
                    <div className="flex items-center justify-between font-bold">
                      <span className="text-slate-500">Duration:</span>
                      <span className="text-slate-800">{pkg.durationDays} Days</span>
                    </div>
                    <div className="flex items-center justify-between font-bold">
                      <span className="text-slate-500">Telegram Posting:</span>
                      <span className={pkg.telegramEnabled ? 'text-blue-700 font-extrabold' : 'text-slate-400'}>
                        {pkg.telegramEnabled ? '✅ Enabled' : '❌ No'}
                      </span>
                    </div>
                  </div>

                  <div className="mb-4">
                    <div className="text-[10px] uppercase font-bold text-slate-400 mb-1">Target Placements:</div>
                    <div className="flex flex-wrap gap-1">
                      {pkg.placements.map((plc, i) => (
                        <span
                          key={i}
                          className="px-2 py-0.5 rounded bg-white border border-slate-200 text-slate-700 text-[10px] font-semibold"
                        >
                          {plc}
                        </span>
                      ))}
                    </div>
                  </div>
                </div>

                <div className="flex items-center gap-2 pt-3 border-t border-slate-100">
                  <button
                    onClick={() => handleOpenEditPackage(pkg)}
                    className="flex-1 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center justify-center gap-1.5 shadow-xs transition-all"
                  >
                    <Edit2 className="w-3.5 h-3.5" /> Edit Settings
                  </button>

                  <button
                    onClick={() => onDeletePackage(pkg.id)}
                    className="p-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 transition-all border border-slate-200 hover:border-rose-200"
                    title="Delete Package"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* SUB-TAB 2: ADVERTISEMENTS APPROVAL & MANAGEMENT */}
      {activeSubTab === 'ads' && (
        <div className="space-y-4">
          {/* Sub-Filters */}
          <div className="flex flex-wrap items-center gap-2">
            {(['all', 'pending', 'active', 'expired', 'rejected'] as const).map((filterKey) => (
              <button
                key={filterKey}
                onClick={() => setAdFilter(filterKey)}
                className={`px-3 py-1.5 rounded-xl text-xs font-extrabold uppercase tracking-wider transition-all ${
                  adFilter === filterKey
                    ? 'bg-slate-900 text-white shadow-xs'
                    : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`}
              >
                {filterKey}
                {filterKey === 'pending' && pendingAdsCount > 0 && (
                  <span className="ml-1.5 px-1.5 py-0.2 rounded-full bg-rose-600 text-white text-[10px]">
                    {pendingAdsCount}
                  </span>
                )}
              </button>
            ))}
          </div>

          {filteredAds.length === 0 ? (
            <div className="bg-white rounded-3xl p-12 text-center border border-slate-200 text-slate-400">
              <Megaphone className="w-12 h-12 mx-auto mb-2 opacity-40" />
              <p className="text-sm font-bold text-slate-700">No advertisements in this filter.</p>
            </div>
          ) : (
            <div className="space-y-3">
              {filteredAds.map((ad) => {
                const isPending = ad.status === 'PENDING';
                const isActive = ad.status === 'ACTIVE';
                const isExpired = ad.status === 'EXPIRED';
                const isRejected = ad.status === 'REJECTED';

                return (
                  <div
                    key={ad.id}
                    className="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 hover:border-slate-300 shadow-xs transition-all flex flex-col md:flex-row md:items-center justify-between gap-4"
                  >
                    <div className="flex items-start sm:items-center gap-4 min-w-0">
                      <div className="relative shrink-0">
                        <img
                          src={ad.imageUrl}
                          alt={ad.title}
                          className="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border border-slate-200 shadow-xs"
                        />
                        <span
                          className={`absolute -top-2 -left-2 px-2 py-0.5 rounded-full text-[9px] font-black uppercase ${
                            isActive
                              ? 'bg-emerald-500 text-white'
                              : isPending
                              ? 'bg-amber-500 text-slate-950 animate-pulse'
                              : isExpired
                              ? 'bg-slate-400 text-white'
                              : 'bg-rose-500 text-white'
                          }`}
                        >
                          {ad.status}
                        </span>
                      </div>

                      <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2 mb-1">
                          <span className="px-2 py-0.2 rounded bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black">
                            {ad.packageName}
                          </span>
                          <span className="text-xs font-bold text-slate-500">
                            {ad.userName} ({ad.phone})
                          </span>
                          <span className="text-[11px] text-slate-400">• {ad.location}</span>
                        </div>

                        <h4 className="text-sm sm:text-base font-black text-slate-900 truncate">
                          {ad.title}
                        </h4>
                        <p className="text-xs text-slate-500 line-clamp-1 mt-0.5 mb-1.5">{ad.description}</p>

                        <div className="flex flex-wrap items-center gap-3 text-xs text-slate-600">
                          <span>
                            Amount: <strong className="text-cyan-800 font-black">Rs. {ad.amountPaid.toLocaleString()}</strong> ({ad.durationDays} Days)
                          </span>
                          <span>
                            Trx: <code className="bg-slate-100 px-1.5 py-0.5 rounded font-mono text-[11px]">{ad.transactionReference}</code>
                          </span>
                          {ad.startAt && (
                            <span>
                              Active: <strong>{ad.startAt}</strong> to <strong>{ad.endAt}</strong>
                            </span>
                          )}
                          {ad.telegramEnabled && (
                            <span className="flex items-center gap-1 font-bold text-blue-700">
                              📲 Telegram: {ad.telegramStatus === 'published' ? `Published (#${ad.telegramMessageId})` : ad.telegramStatus}
                            </span>
                          )}
                        </div>
                      </div>
                    </div>

                    {/* Actions */}
                    <div className="flex flex-wrap items-center gap-2 shrink-0 justify-end pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                      <button
                        onClick={() => setSelectedAd(ad)}
                        className="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold flex items-center gap-1 transition-all"
                      >
                        <Eye className="w-3.5 h-3.5" /> View Details
                      </button>

                      {isPending && (
                        <>
                          <button
                            onClick={() => handleApproveAdClick(ad.id)}
                            className="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black flex items-center gap-1 shadow-sm transition-all"
                          >
                            <Check className="w-3.5 h-3.5 stroke-[3]" /> Approve
                          </button>

                          <button
                            onClick={() => {
                              setSelectedAd(ad);
                              setIsRejecting(true);
                            }}
                            className="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold border border-rose-200 transition-all"
                          >
                            <X className="w-3.5 h-3.5" /> Reject
                          </button>
                        </>
                      )}

                      {isActive && (
                        <button
                          onClick={() => onToggleAdSuspension(ad.id)}
                          className="px-3 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold border border-amber-300 transition-all flex items-center gap-1"
                        >
                          <PauseCircle className="w-3.5 h-3.5" /> Suspend
                        </button>
                      )}

                      {ad.telegramEnabled && ad.telegramStatus === 'failed' && (
                        <button
                          onClick={() => handleRetryTelegramClick(ad.id)}
                          disabled={retryingTelegramId === ad.id}
                          className="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold flex items-center gap-1 shadow-xs transition-all"
                        >
                          <RefreshCw className={`w-3.5 h-3.5 ${retryingTelegramId === ad.id ? 'animate-spin' : ''}`} />
                          Retry Telegram
                        </button>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      )}

      {/* MODAL 1: ADD / EDIT ADVERTISING PACKAGE */}
      {isPkgModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
          <div className="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[90vh]">
            <div className="p-5 bg-gradient-to-r from-slate-950 to-amber-950 text-white flex items-center justify-between">
              <div>
                <h3 className="text-base font-black">
                  {editingPackage ? 'Edit Advertising Package' : 'Create Advertising Package'}
                </h3>
                <p className="text-xs text-amber-200/80">Configure pricing, duration, placements, and Telegram settings.</p>
              </div>
              <button
                onClick={() => setIsPkgModalOpen(false)}
                className="p-2 text-slate-400 hover:text-white rounded-xl"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <form onSubmit={handleSavePackageSubmit} className="p-6 overflow-y-auto space-y-4 text-xs">
              <div>
                <label className="block text-slate-800 font-bold mb-1">Package Name</label>
                <input
                  type="text"
                  required
                  value={pkgName}
                  onChange={(e) => setPkgName(e.target.value)}
                  placeholder="e.g. ⭐ SargodhaMart Business Promotion"
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-amber-500 font-medium"
                />
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                  <label className="block text-slate-800 font-bold mb-1">Ad Type</label>
                  <select
                    value={pkgAdType}
                    onChange={(e) => setPkgAdType(e.target.value)}
                    className="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-amber-500 font-medium"
                  >
                    {ADVERTISING_CATEGORIES.map((cat, i) => (
                      <option key={i} value={cat}>
                        {cat}
                      </option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-slate-800 font-bold mb-1">Price (PKR)</label>
                  <input
                    type="number"
                    required
                    min={0}
                    step={100}
                    value={pkgPrice}
                    onChange={(e) => setPkgPrice(Number(e.target.value))}
                    className="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-amber-500 font-bold text-cyan-800"
                  />
                </div>

                <div>
                  <label className="block text-slate-800 font-bold mb-1">Duration (Days)</label>
                  <input
                    type="number"
                    required
                    min={1}
                    value={pkgDuration}
                    onChange={(e) => setPkgDuration(Number(e.target.value))}
                    className="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-amber-500 font-bold"
                  />
                </div>
              </div>

              <div>
                <label className="block text-slate-800 font-bold mb-1">Description</label>
                <textarea
                  rows={2}
                  value={pkgDescription}
                  onChange={(e) => setPkgDescription(e.target.value)}
                  placeholder="Describe package visibility and value..."
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-hidden focus:border-amber-500 font-medium"
                />
              </div>

              <div>
                <label className="block text-slate-800 font-bold mb-1.5">Package Placements</label>
                <div className="grid grid-cols-2 sm:grid-cols-3 gap-2">
                  {ALL_PLACEMENTS.map((plc) => (
                    <label
                      key={plc}
                      className={`p-2 rounded-xl border flex items-center gap-2 cursor-pointer transition-colors text-[11px] ${
                        pkgPlacements.includes(plc)
                          ? 'border-amber-500 bg-amber-50/50 font-bold text-slate-900'
                          : 'border-slate-200 text-slate-600 hover:bg-slate-50'
                      }`}
                    >
                      <input
                        type="checkbox"
                        checked={pkgPlacements.includes(plc)}
                        onChange={() => handleTogglePlacement(plc)}
                        className="rounded text-amber-600 focus:ring-amber-500"
                      />
                      <span>{plc}</span>
                    </label>
                  ))}
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-100">
                <label className="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={pkgTelegram}
                    onChange={(e) => setPkgTelegram(e.target.checked)}
                    className="rounded text-blue-600"
                  />
                  <span className="font-bold text-slate-800">Telegram Posting</span>
                </label>

                <label className="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={pkgRecommended}
                    onChange={(e) => setPkgRecommended(e.target.checked)}
                    className="rounded text-amber-600"
                  />
                  <span className="font-bold text-slate-800">⭐ Recommended</span>
                </label>

                <label className="flex items-center gap-2 p-2.5 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                  <input
                    type="checkbox"
                    checked={pkgActive}
                    onChange={(e) => setPkgActive(e.target.checked)}
                    className="rounded text-emerald-600"
                  />
                  <span className="font-bold text-slate-800">Active Package</span>
                </label>
              </div>

              <div>
                <label className="block text-slate-800 font-bold mb-1">
                  Features List (One feature per line)
                </label>
                <textarea
                  rows={3}
                  value={pkgFeaturesStr}
                  onChange={(e) => setPkgFeaturesStr(e.target.value)}
                  placeholder="Website placement&#10;Direct Call & WhatsApp&#10;Telegram Broadcast"
                  className="w-full px-3.5 py-2 rounded-xl border border-slate-200 font-medium"
                />
              </div>

              <div className="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button
                  type="button"
                  onClick={() => setIsPkgModalOpen(false)}
                  className="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black shadow-md shadow-amber-500/25"
                >
                  Save Package Settings
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODAL 2: ADVERTISEMENT VERIFICATION & DETAIL VIEW */}
      {selectedAd && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
          <div className="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh]">
            <div className="p-5 bg-gradient-to-r from-slate-950 to-amber-950 text-white flex items-center justify-between">
              <div>
                <span className="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase">
                  Ad #{selectedAd.id} • {selectedAd.status}
                </span>
                <h3 className="text-base font-black mt-1">{selectedAd.title}</h3>
              </div>
              <button
                onClick={() => {
                  setSelectedAd(null);
                  setIsRejecting(false);
                }}
                className="p-2 text-slate-400 hover:text-white rounded-xl"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-6 overflow-y-auto space-y-4 text-xs">
              {/* Images Grid: Banner & Receipt */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <div className="font-bold text-slate-700 mb-1">Ad Banner Artwork:</div>
                  <div className="h-44 rounded-2xl overflow-hidden border border-slate-200 bg-slate-900">
                    <img src={selectedAd.imageUrl} alt="Banner" className="w-full h-full object-cover" />
                  </div>
                </div>

                <div>
                  <div className="font-bold text-slate-700 mb-1">Payment Screenshot Receipt:</div>
                  <div className="h-44 rounded-2xl overflow-hidden border border-emerald-300 bg-slate-900">
                    <img src={selectedAd.paymentScreenshot} alt="Receipt" className="w-full h-full object-cover" />
                  </div>
                </div>
              </div>

              {/* Campaign Info */}
              <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <div className="grid grid-cols-2 gap-2">
                  <div>
                    <span className="text-slate-400">User: </span>
                    <strong className="text-slate-900">{selectedAd.userName}</strong>
                  </div>
                  <div>
                    <span className="text-slate-400">Phone: </span>
                    <strong className="text-slate-900">{selectedAd.phone}</strong>
                  </div>
                  <div>
                    <span className="text-slate-400">Package: </span>
                    <strong className="text-cyan-800">{selectedAd.packageName}</strong>
                  </div>
                  <div>
                    <span className="text-slate-400">Amount Paid: </span>
                    <strong className="text-emerald-700">Rs. {selectedAd.amountPaid.toLocaleString()}</strong> ({selectedAd.durationDays} Days)
                  </div>
                  <div>
                    <span className="text-slate-400">Transaction ID: </span>
                    <code className="bg-white px-2 py-0.5 rounded border border-slate-200 font-mono">{selectedAd.transactionReference}</code>
                  </div>
                  <div>
                    <span className="text-slate-400">Location: </span>
                    <strong className="text-slate-900">{selectedAd.location}</strong>
                  </div>
                </div>

                <div className="pt-2 border-t border-slate-200">
                  <div className="text-slate-400 mb-0.5">Campaign Description:</div>
                  <p className="text-slate-700 leading-relaxed font-medium">{selectedAd.description}</p>
                </div>
              </div>

              {/* Rejection Input (if active) */}
              {isRejecting && (
                <div className="p-4 rounded-2xl bg-rose-50 border border-rose-200 space-y-2">
                  <label className="block text-rose-900 font-bold">
                    Rejection Reason (Will be sent to user):
                  </label>
                  <textarea
                    rows={2}
                    value={rejectionReason}
                    onChange={(e) => setRejectionReason(e.target.value)}
                    placeholder="e.g. Invalid payment transaction ID or incorrect amount transferred..."
                    className="w-full px-3 py-2 rounded-xl border border-rose-300 focus:outline-hidden text-slate-900 font-medium"
                  />
                  <div className="flex justify-end gap-2">
                    <button
                      type="button"
                      onClick={() => setIsRejecting(false)}
                      className="px-3 py-1.5 rounded-lg text-slate-600 hover:bg-slate-200"
                    >
                      Cancel
                    </button>
                    <button
                      type="button"
                      disabled={!rejectionReason.trim()}
                      onClick={handleConfirmReject}
                      className="px-4 py-1.5 rounded-lg bg-rose-600 text-white font-bold disabled:opacity-50"
                    >
                      Confirm Rejection
                    </button>
                  </div>
                </div>
              )}
            </div>

            {/* Modal Actions */}
            <div className="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
              <button
                onClick={() => {
                  setSelectedAd(null);
                  setIsRejecting(false);
                }}
                className="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-200 font-bold text-xs"
              >
                Close
              </button>

              {selectedAd.status === 'PENDING' && !isRejecting && (
                <div className="flex items-center gap-2">
                  <button
                    onClick={() => setIsRejecting(true)}
                    className="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 font-bold text-xs"
                  >
                    Reject Ad
                  </button>
                  <button
                    onClick={() => handleApproveAdClick(selectedAd.id)}
                    className="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-md shadow-emerald-600/25 flex items-center gap-1.5"
                  >
                    <Check className="w-4 h-4 stroke-[3]" /> Approve & Activate Ad
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
