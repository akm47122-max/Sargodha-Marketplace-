import React from 'react';
import { Search, MapPin, Sparkles, ShieldCheck, CheckCircle2, Users, ArrowRight, UserPlus, CreditCard, Shield, Rocket, Megaphone, ExternalLink } from 'lucide-react';
import { Category, City, SiteSettings, AnnouncementItem } from '../types';
import { getActiveAnnouncements } from '../utils/settings';

interface HeroSectionProps {
  categories: Category[];
  searchQuery: string;
  setSearchQuery: (query: string) => void;
  selectedCategory: number | 'all';
  setSelectedCategory: (cat: number | 'all') => void;
  selectedCity: City | 'All';
  setSelectedCity: (city: City | 'All') => void;
  onPostClick: () => void;
  onViewJobsClick: () => void;
  settings?: SiteSettings;
  announcements?: AnnouncementItem[];
}

export const HeroSection: React.FC<HeroSectionProps> = ({
  categories,
  searchQuery,
  setSearchQuery,
  selectedCategory,
  setSelectedCategory,
  selectedCity,
  setSelectedCity,
  onPostClick,
  onViewJobsClick,
  settings,
  announcements = [],
}) => {
  const heading = settings?.heroHeading || 'Buy • Sell • Jobs • Grow';
  const headingHighlight = settings?.heroHeadingHighlight || 'Your Local Online Marketplace';
  const description =
    settings?.heroDescription ||
    'The premier trading & employment portal for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp dealing, verified Rs. 1,000 lifetime seller activation, zero middleman fees.';
  const feeFormatted = `Rs. ${(settings?.activationFee || 1000).toLocaleString()}`;

  const bannerAnnouncements = getActiveAnnouncements(announcements, 'homepage_banner');
  const activeBanner = bannerAnnouncements[0];

  const showCategories = settings ? settings.isCategoriesSectionEnabled : true;
  const showWorkflow = settings ? settings.isWorkflowGuideEnabled : true;

  return (
    <section className="relative overflow-hidden bg-gradient-to-b from-white via-slate-50 to-cyan-50/30 border-b border-cyan-100 py-12 md:py-16">
      {/* Background Neon Glow Mesh Halos */}
      <div className="absolute top-0 left-1/4 w-96 h-96 bg-cyan-400/15 rounded-full blur-3xl pointer-events-none"></div>
      <div className="absolute bottom-0 right-1/4 w-96 h-96 bg-fuchsia-400/15 rounded-full blur-3xl pointer-events-none"></div>

      <div className="relative max-w-7xl mx-auto px-4 text-center">
        {/* Optional Homepage Banner Announcement */}
        {activeBanner && (
          <div className="max-w-4xl mx-auto mb-6 p-4 rounded-3xl bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-amber-500/15 border border-amber-300 text-left flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
            <div className="flex items-start gap-3">
              <div className="w-10 h-10 rounded-2xl bg-amber-500 text-slate-950 flex items-center justify-center font-black shrink-0 shadow-sm">
                <Megaphone className="w-5 h-5 animate-pulse" />
              </div>
              <div>
                <div className="font-black text-slate-900 text-sm flex items-center gap-2">
                  <span>{activeBanner.title}</span>
                  {activeBanner.isHighlighted && (
                    <span className="px-2 py-0.2 rounded-full text-[10px] font-bold bg-rose-500 text-white">
                      Notice
                    </span>
                  )}
                </div>
                <div className="text-slate-600 text-xs font-medium mt-0.5">{activeBanner.message}</div>
              </div>
            </div>
            {activeBanner.buttonText && activeBanner.buttonUrl && (
              <a
                href={activeBanner.buttonUrl}
                target="_blank"
                rel="noreferrer"
                className="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shrink-0 flex items-center justify-center gap-1.5 transition-all shadow-sm"
              >
                <span>{activeBanner.buttonText}</span>
                <ExternalLink className="w-3.5 h-3.5" />
              </a>
            )}
          </div>
        )}

        {/* Hub Badge - White Neon Glass Pill */}
        <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/90 border border-cyan-300 text-xs text-slate-700 mb-6 shadow-[0_0_15px_rgba(6,182,212,0.15)]">
          <span className="flex h-2 w-2 relative">
            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-500 opacity-75"></span>
            <span className="relative inline-flex rounded-full h-2 w-2 bg-cyan-600"></span>
          </span>
          <span className="font-semibold text-slate-900">Local District Network:</span>
          <span className="text-cyan-700 font-extrabold">Sargodha • Shaheenabad • Sillanwali</span>
        </div>

        {/* Hero Title */}
        <h1 className="text-3xl md:text-5xl lg:text-6xl font-black text-slate-950 tracking-tight leading-tight max-w-4xl mx-auto mb-4">
          {heading} <br />
          <span className="bg-gradient-to-r from-cyan-600 via-blue-600 to-fuchsia-600 bg-clip-text text-transparent drop-shadow-xs">
            {headingHighlight}
          </span>
        </h1>

        <p className="text-slate-600 text-sm md:text-base max-w-2xl mx-auto mb-8 font-medium">
          {description}
        </p>

        {/* Search Bar Container */}
        <div className="max-w-4xl mx-auto bg-white/95 backdrop-blur-2xl border border-cyan-200/90 p-2.5 md:p-3 rounded-3xl shadow-[0_15px_40px_-10px_rgba(6,182,212,0.18)] mb-8">
          <div className="grid grid-cols-1 md:grid-cols-12 gap-2">
            {/* Search Input */}
            <div className="md:col-span-5 relative">
              <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-cyan-600" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search mobiles, cows, bikes, wanda, tractors..."
                className="w-full pl-10 pr-3 py-2.5 bg-slate-50/90 border border-slate-200 rounded-2xl text-slate-900 text-xs md:text-sm placeholder-slate-400 focus:outline-none focus:border-cyan-500 focus:bg-white focus:shadow-[0_0_12px_rgba(6,182,212,0.2)] transition-all font-medium"
              />
            </div>

            {/* Category Select */}
            <div className="md:col-span-3">
              <select
                value={selectedCategory}
                onChange={(e) => setSelectedCategory(e.target.value === 'all' ? 'all' : Number(e.target.value))}
                className="w-full px-3 py-2.5 bg-slate-50/90 border border-slate-200 rounded-2xl text-slate-800 text-xs md:text-sm focus:outline-none focus:border-cyan-500 focus:bg-white font-medium"
              >
                <option value="all">All Categories</option>
                {categories.map((c) => (
                  <option key={c.id} value={c.id}>
                    {c.icon} {c.name}
                  </option>
                ))}
              </select>
            </div>

            {/* City Select */}
            <div className="md:col-span-2">
              <select
                value={selectedCity}
                onChange={(e) => setSelectedCity(e.target.value as City | 'All')}
                className="w-full px-3 py-2.5 bg-slate-50/90 border border-slate-200 rounded-2xl text-slate-800 text-xs md:text-sm focus:outline-none focus:border-cyan-500 focus:bg-white font-medium"
              >
                <option value="All">All Cities</option>
                <option value="Sargodha">Sargodha</option>
                <option value="Shaheenabad">Shaheenabad</option>
                <option value="Sillanwali">Sillanwali</option>
              </select>
            </div>

            {/* Search Button */}
            <div className="md:col-span-2">
              <button
                type="button"
                className="w-full h-full py-2.5 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xs md:text-sm rounded-2xl shadow-lg shadow-cyan-500/25 transition-all flex items-center justify-center gap-1.5 hover:shadow-[0_0_15px_rgba(6,182,212,0.5)]"
              >
                <Search className="w-4 h-4" />
                Find
              </button>
            </div>
          </div>
        </div>

        {/* Quick Category Badges */}
        {showCategories && (
          <div className="flex items-center justify-center flex-wrap gap-2 max-w-4xl mx-auto mb-10">
            <button
              onClick={() => setSelectedCategory('all')}
              className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
                selectedCategory === 'all'
                  ? 'bg-cyan-600 text-white shadow-[0_0_15px_rgba(6,182,212,0.4)]'
                  : 'bg-white border border-slate-200 text-slate-700 hover:text-cyan-700 hover:border-cyan-300'
              }`}
            >
              All Products
            </button>
            {categories.slice(0, 8).map((cat) => (
              <button
                key={cat.id}
                onClick={() => setSelectedCategory(cat.id)}
                className={`px-3 py-1.5 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5 ${
                  selectedCategory === cat.id
                    ? 'bg-cyan-600 text-white shadow-[0_0_15px_rgba(6,182,212,0.4)]'
                    : 'bg-white border border-slate-200 text-slate-700 hover:text-cyan-700 hover:border-cyan-300 shadow-xs'
                }`}
              >
                <span>{cat.icon}</span>
                <span>{cat.name}</span>
              </button>
            ))}
            <button
              onClick={onViewJobsClick}
              className="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-fuchsia-50 border border-fuchsia-300 text-fuchsia-800 hover:bg-fuchsia-100 transition-all flex items-center gap-1 shadow-xs hover:shadow-[0_0_12px_rgba(217,70,239,0.25)]"
            >
              <span>💼</span> Jobs & Work Hub
            </button>
          </div>
        )}

        {/* Trust Badges - White Neon Cards */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3.5 max-w-4xl mx-auto text-left mb-8">
          <div className="p-3.5 rounded-2xl bg-white border border-cyan-200 shadow-[0_4px_16px_rgba(6,182,212,0.08)] flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-cyan-50 border border-cyan-300 flex items-center justify-center text-cyan-600 shadow-[0_0_10px_rgba(6,182,212,0.2)]">
              <ShieldCheck className="w-5 h-5" />
            </div>
            <div>
              <div className="text-slate-900 text-xs font-bold">{feeFormatted} Lifetime</div>
              <div className="text-slate-500 text-[10px]">One-time seller activation</div>
            </div>
          </div>

          <div className="p-3.5 rounded-2xl bg-white border border-emerald-200 shadow-[0_4px_16px_rgba(16,185,129,0.08)] flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-300 flex items-center justify-center text-emerald-600 shadow-[0_0_10px_rgba(16,185,129,0.2)]">
              <CheckCircle2 className="w-5 h-5" />
            </div>
            <div>
              <div className="text-slate-900 text-xs font-bold">Direct Public Listing</div>
              <div className="text-slate-500 text-[10px]">Instant live ads, no delay</div>
            </div>
          </div>

          <div className="p-3.5 rounded-2xl bg-white border border-fuchsia-200 shadow-[0_4px_16px_rgba(217,70,239,0.08)] flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-fuchsia-50 border border-fuchsia-300 flex items-center justify-center text-fuchsia-600 shadow-[0_0_10px_rgba(217,70,239,0.2)]">
              <Users className="w-5 h-5" />
            </div>
            <div>
              <div className="text-slate-900 text-xs font-bold">WhatsApp Channel</div>
              <div className="text-slate-500 text-[10px]">Verified follow verification</div>
            </div>
          </div>

          <div className="p-3.5 rounded-2xl bg-white border border-blue-200 shadow-[0_4px_16px_rgba(37,99,235,0.08)] flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-blue-50 border border-blue-300 flex items-center justify-center text-blue-600 shadow-[0_0_10px_rgba(37,99,235,0.2)]">
              <Sparkles className="w-5 h-5" />
            </div>
            <div>
              <div className="text-slate-900 text-xs font-bold">Jobs & Employment</div>
              <div className="text-slate-500 text-[10px]">Hire workers or seek jobs</div>
            </div>
          </div>
        </div>

        {/* 4-Step Verified Workflow Banner */}
        {showWorkflow && (
          <div className="max-w-4xl mx-auto bg-white/90 backdrop-blur-md border border-cyan-200/90 rounded-2xl p-4 shadow-sm">
            <div className="text-[11px] font-black uppercase tracking-wider text-cyan-800 mb-3 flex items-center justify-center gap-1.5">
              <Sparkles className="w-3.5 h-3.5 text-cyan-600" />
              <span>How SargodhaMart Works • اشتہار لگانے کا آسان فلو</span>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-2 text-left">
              <div className="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                <div className="text-[10px] font-bold text-slate-400 uppercase">مرحلہ 1 • Step 1</div>
                <div className="font-bold text-slate-900 text-xs mt-0.5">پبلک مارکیٹ پلیس</div>
                <div className="text-[10px] text-slate-500">Free open browsing without login</div>
              </div>

              <div className="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                <div className="text-[10px] font-bold text-slate-400 uppercase">مرحلہ 2 • Step 2</div>
                <div className="font-bold text-slate-900 text-xs mt-0.5">اکاؤنٹ رجسٹریشن</div>
                <div className="text-[10px] text-slate-500">Fast login or register to sell</div>
              </div>

              <div className="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                <div className="text-[10px] font-bold text-slate-400 uppercase">مرحلہ 3 • Step 3</div>
                <div className="font-bold text-cyan-800 text-xs mt-0.5">{feeFormatted} + WhatsApp</div>
                <div className="text-[10px] text-slate-500">Pay once & follow channel proof</div>
              </div>

              <div className="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
                <div className="text-[10px] font-bold text-emerald-700 uppercase">مرحلہ 4 • Step 4</div>
                <div className="font-bold text-emerald-800 text-xs mt-0.5">ڈائریکٹ پبلک پوسٹنگ</div>
                <div className="text-[10px] text-emerald-600 font-semibold">Unlimited free ads & jobs!</div>
              </div>
            </div>
          </div>
        )}
      </div>
    </section>
  );
};
