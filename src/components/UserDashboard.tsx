import React, { useState } from 'react';
import {
  User,
  ShoppingBag,
  Briefcase,
  Heart,
  PlusCircle,
  ShieldCheck,
  Clock,
  Trash2,
  ExternalLink,
  MapPin,
  Phone,
  MessageCircle,
  LogOut,
  Sparkles,
  Megaphone,
  Star,
  FileText,
  AlertCircle,
  CheckCircle2,
  XCircle,
} from 'lucide-react';
import {
  UserProfile,
  Listing,
  JobPost,
  DigitalSkillProfile,
  PromotionPayment,
  Advertisement,
  AdvertisingPackage,
  AdvertisingPayment,
} from '../types';

interface UserDashboardProps {
  currentUser: UserProfile;
  listings: Listing[];
  jobs: JobPost[];
  skills?: DigitalSkillProfile[];
  promotions?: PromotionPayment[];
  advertisements?: Advertisement[];
  advertisingPackages?: AdvertisingPackage[];
  favorites: number[];
  onOpenPostAd: () => void;
  onOpenPostJob: () => void;
  onOpenPostSkill?: (skillToEdit?: DigitalSkillProfile | null) => void;
  onOpenActivation: () => void;
  onOpenPromoteModal?: (type?: 'PRODUCT' | 'SKILL', id?: number) => void;
  onOpenCreateAd?: (packageId?: number) => void;
  onDeleteListing: (id: number) => void;
  onDeleteJob: (id: number) => void;
  onDeleteSkill?: (id: number) => void;
  onSelectListing: (listing: Listing) => void;
  onSelectSkill?: (skill: DigitalSkillProfile) => void;
  onLogout: () => void;
}

export const UserDashboard: React.FC<UserDashboardProps> = ({
  currentUser,
  listings,
  jobs,
  skills = [],
  promotions = [],
  advertisements = [],
  advertisingPackages = [],
  favorites,
  onOpenPostAd,
  onOpenPostJob,
  onOpenPostSkill,
  onOpenActivation,
  onOpenPromoteModal,
  onOpenCreateAd,
  onDeleteListing,
  onDeleteJob,
  onDeleteSkill,
  onSelectListing,
  onSelectSkill,
  onLogout,
}) => {
  const [activeTab, setActiveTab] = useState<'products' | 'jobs' | 'skills' | 'promote' | 'advertising' | 'favorites'>('products');
  const [promoteSubTab, setPromoteSubTab] = useState<'products' | 'skills' | 'history'>('products');
  const [adSubTab, setAdSubTab] = useState<'my-ads' | 'create' | 'packages' | 'history'>('my-ads');

  const myListings = listings.filter((l) => l.userId === currentUser.id);
  const myJobs = jobs.filter((j) => j.userId === currentUser.id);
  const mySkills = skills.filter((s) => s.userId === currentUser.id);
  const myPromotions = promotions.filter((p) => p.userId === currentUser.id);
  const myAds = advertisements.filter((a) => a.userId === currentUser.id);
  const myFavoriteListings = listings.filter((l) => favorites.includes(l.id));

  return (
    <div className="py-8 px-4 max-w-7xl mx-auto space-y-8">
      {/* User Header Profile Card */}
      <div className="bg-white/95 backdrop-blur-xl border border-cyan-200 rounded-3xl p-6 md:p-8 shadow-[0_10px_35px_-5px_rgba(6,182,212,0.12)]">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="flex items-center gap-4">
            <div className="w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white text-2xl font-black shadow-lg shadow-cyan-500/25">
              {currentUser.name.charAt(0)}
            </div>
            <div>
              <div className="flex items-center gap-2 mb-1">
                <h1 className="text-xl md:text-2xl font-black text-slate-900">{currentUser.name}</h1>
                {currentUser.activationStatus === 'active' ? (
                  <span className="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1 shadow-xs">
                    <ShieldCheck className="w-3 h-3 text-emerald-600" /> Active Seller
                  </span>
                ) : (
                  <span className="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1 shadow-xs">
                    <Clock className="w-3 h-3 text-amber-600" /> Activation Pending
                  </span>
                )}
              </div>
              <div className="text-xs text-slate-600 font-medium">
                <span>{currentUser.mobile}</span> • <span>{currentUser.email}</span> •{' '}
                <span className="text-cyan-800 font-bold">{currentUser.city} ({currentUser.area})</span>
              </div>
            </div>
          </div>

          {/* Quick Action Buttons */}
          <div className="flex flex-wrap items-center gap-2">
            {currentUser.activationStatus !== 'active' ? (
              <button
                onClick={onOpenActivation}
                className="px-4 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25 transition-all flex items-center gap-1.5"
              >
                <ShieldCheck className="w-4 h-4" /> Activate Lifetime Account (Rs. 1,000)
              </button>
            ) : (
              <>
                <button
                  onClick={onOpenPostAd}
                  className="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-sm flex items-center gap-1.5 transition-all"
                >
                  <PlusCircle className="w-4 h-4" /> Post Free Ad
                </button>
                <button
                  onClick={onOpenPostJob}
                  className="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white shadow-sm flex items-center gap-1.5 transition-all"
                >
                  <Briefcase className="w-4 h-4" /> Post Job
                </button>
                {onOpenPostSkill && (
                  <button
                    onClick={() => onOpenPostSkill(null)}
                    className="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-fuchsia-600 to-pink-600 hover:from-fuchsia-500 hover:to-pink-500 text-white shadow-sm flex items-center gap-1.5 transition-all"
                  >
                    <FileText className="w-4 h-4" /> Add Digital CV
                  </button>
                )}
                {onOpenPromoteModal && (
                  <button
                    onClick={() => onOpenPromoteModal()}
                    className="px-4 py-2 rounded-xl text-xs font-black bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 shadow-md shadow-orange-500/20 flex items-center gap-1.5 transition-all"
                  >
                    <Megaphone className="w-4 h-4" /> 📣 Promote Item
                  </button>
                )}
              </>
            )}

            <button
              onClick={onLogout}
              className="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 flex items-center gap-1.5 transition-all shadow-xs"
              title="Log out"
            >
              <LogOut className="w-4 h-4" />
              <span>Log Out</span>
            </button>
          </div>
        </div>

        {/* Activation Status Banner */}
        {currentUser.activationStatus === 'active' ? (
          <div className="mt-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between shadow-xs">
            <div className="flex items-center gap-2">
              <ShieldCheck className="w-5 h-5 text-emerald-600 shrink-0" />
              <span>
                <strong>Lifetime Verified Seller Account:</strong> You can post unlimited free product ads and jobs directly without waiting for individual approval!
              </span>
            </div>
          </div>
        ) : (
          <div className="mt-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-xs">
            <div className="flex items-center gap-2">
              <Clock className="w-5 h-5 text-amber-600 shrink-0" />
              <span>
                <strong>Account Pending Activation:</strong> Pay Rs. 1,000 once & follow official WhatsApp Channel to unlock unlimited direct public postings.
              </span>
            </div>
            <button
              onClick={onOpenActivation}
              className="px-3.5 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs shrink-0 hover:bg-amber-400 transition-all shadow-xs"
            >
              Submit Proof Now
            </button>
          </div>
        )}
      </div>

      {/* Main Dashboard Navigation Tabs */}
      <div className="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3 text-xs font-bold">
        <button
          onClick={() => setActiveTab('products')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'products'
              ? 'bg-white text-cyan-800 border border-cyan-300 shadow-[0_0_12px_rgba(6,182,212,0.18)]'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <ShoppingBag className="w-3.5 h-3.5 text-cyan-600" />
          My Products ({myListings.length})
        </button>

        <button
          onClick={() => setActiveTab('jobs')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'jobs'
              ? 'bg-white text-blue-800 border border-blue-300 shadow-[0_0_12px_rgba(37,99,235,0.18)]'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <Briefcase className="w-3.5 h-3.5 text-blue-600" />
          My Jobs ({myJobs.length})
        </button>

        <button
          onClick={() => setActiveTab('skills')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'skills'
              ? 'bg-white text-fuchsia-800 border border-fuchsia-300 shadow-[0_0_12px_rgba(217,70,239,0.18)]'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <FileText className="w-3.5 h-3.5 text-fuchsia-600" />
          My Digital Skills & CV ({mySkills.length})
        </button>

        <button
          onClick={() => setActiveTab('promote')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'promote'
              ? 'bg-amber-400 text-slate-950 font-black shadow-md shadow-amber-400/20'
              : 'text-slate-600 hover:text-slate-900 bg-amber-50 border border-amber-200 text-amber-900'
          }`}
        >
          <Megaphone className="w-3.5 h-3.5 text-slate-950" />
          📣 Promote ({myPromotions.length})
        </button>

        <button
          onClick={() => setActiveTab('advertising')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'advertising'
              ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black shadow-md shadow-amber-500/25'
              : 'text-slate-600 hover:text-slate-900 bg-amber-50/60 border border-amber-300 text-amber-900'
          }`}
        >
          <Sparkles className="w-3.5 h-3.5 text-slate-950" />
          📢 Advertising ({myAds.length})
        </button>

        <button
          onClick={() => setActiveTab('favorites')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'favorites'
              ? 'bg-white text-rose-800 border border-rose-300 shadow-[0_0_12px_rgba(244,63,94,0.18)]'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <Heart className="w-3.5 h-3.5 text-rose-600" />
          Favorites ({myFavoriteListings.length})
        </button>
      </div>

      {/* 1. MY PRODUCTS TAB */}
      {activeTab === 'products' && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-base font-black text-slate-900">Your Product Listings</h2>
            {currentUser.activationStatus === 'active' && (
              <button
                onClick={onOpenPostAd}
                className="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold text-xs flex items-center gap-1 shadow-sm"
              >
                <PlusCircle className="w-3.5 h-3.5" /> Post New Ad
              </button>
            )}
          </div>

          {myListings.length === 0 ? (
            <div className="p-12 text-center rounded-3xl bg-white border border-slate-200">
              <ShoppingBag className="w-12 h-12 text-slate-300 mx-auto mb-3" />
              <p className="text-sm font-bold text-slate-700">You haven't posted any products yet.</p>
              <p className="text-xs text-slate-500 mt-1 mb-4">Post ads free across Sargodha, Shaheenabad and Sillanwali.</p>
              <button
                onClick={onOpenPostAd}
                className="px-5 py-2 rounded-xl bg-cyan-600 text-white font-bold text-xs hover:bg-cyan-500"
              >
                Post Your First Ad
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
              {myListings.map((listing) => (
                <div
                  key={listing.id}
                  className="rounded-3xl bg-white border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between"
                >
                  <div>
                    <div className="relative h-44 overflow-hidden">
                      <img
                        src={listing.images[0]}
                        alt={listing.title}
                        className="w-full h-full object-cover"
                      />
                      {listing.isFeatured && (
                        <span className="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase shadow-md flex items-center gap-1">
                          <Star className="w-3 h-3 fill-slate-950" /> FEATURED
                        </span>
                      )}
                      <span className="absolute bottom-3 right-3 px-2.5 py-0.5 rounded-full bg-slate-950/70 text-white text-[10px] font-extrabold backdrop-blur-xs">
                        Rs. {listing.price.toLocaleString()}
                      </span>
                    </div>

                    <div className="p-4">
                      <h3 className="text-sm font-black text-slate-900 line-clamp-1 mb-1">{listing.title}</h3>
                      <div className="text-[11px] text-slate-500 flex items-center gap-1 mb-3">
                        <MapPin className="w-3 h-3 text-cyan-600" />
                        <span>{listing.city} ({listing.area})</span>
                      </div>
                      <p className="text-xs text-slate-600 line-clamp-2">{listing.description}</p>
                    </div>
                  </div>

                  <div className="p-4 pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                    <button
                      onClick={() => onSelectListing(listing)}
                      className="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold"
                    >
                      View
                    </button>

                    {onOpenPromoteModal && !listing.isFeatured && (
                      <button
                        onClick={() => onOpenPromoteModal('PRODUCT', listing.id)}
                        className="px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black text-xs shadow-xs"
                      >
                        📣 Promote (Rs. 1,000)
                      </button>
                    )}

                    <button
                      onClick={() => onDeleteListing(listing.id)}
                      className="p-1.5 rounded-xl text-rose-600 hover:bg-rose-50"
                      title="Delete ad"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {/* 2. MY JOBS TAB */}
      {activeTab === 'jobs' && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-base font-black text-slate-900">Your Job Postings</h2>
            {currentUser.activationStatus === 'active' && (
              <button
                onClick={onOpenPostJob}
                className="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center gap-1 shadow-sm"
              >
                <PlusCircle className="w-3.5 h-3.5" /> Post New Job
              </button>
            )}
          </div>

          {myJobs.length === 0 ? (
            <div className="p-12 text-center rounded-3xl bg-white border border-slate-200">
              <Briefcase className="w-12 h-12 text-slate-300 mx-auto mb-3" />
              <p className="text-sm font-bold text-slate-700">No job postings created yet.</p>
              <p className="text-xs text-slate-500 mt-1 mb-4">Post recruitment or seeking work requirements easily.</p>
              <button
                onClick={onOpenPostJob}
                className="px-5 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-500"
              >
                Post a Job Post
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {myJobs.map((job) => (
                <div key={job.id} className="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                  <div>
                    <div className="flex items-start justify-between gap-2 mb-2">
                      <span className={`px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase ${
                        job.postType === 'need_job' ? 'bg-cyan-100 text-cyan-800' : 'bg-emerald-100 text-emerald-800'
                      }`}>
                        {job.postType === 'need_job' ? 'I Need a Job' : 'I Need a Worker'}
                      </span>
                      <button
                        onClick={() => onDeleteJob(job.id)}
                        className="text-rose-600 hover:text-rose-700 p-1"
                        title="Delete job post"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>

                    <h3 className="text-sm font-black text-slate-900 mb-1">{job.title}</h3>
                    <div className="text-xs text-slate-500 mb-2">
                      Category: <strong>{job.category}</strong> • Skills: {job.skills}
                    </div>
                    <p className="text-xs text-slate-600 line-clamp-2 leading-relaxed">{job.description}</p>
                  </div>

                  <div className="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 mt-3">
                    <span>{job.city} ({job.area})</span>
                    <span className="font-bold text-slate-900">{job.salaryOrPayment || 'Negotiable'}</span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {/* 3. MY DIGITAL SKILLS & CV TAB */}
      {activeTab === 'skills' && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-base font-black text-slate-900">My Digital Skills & Online CV</h2>
              <p className="text-xs text-slate-500">Showcase your freelance talent with a professional Digital CV</p>
            </div>
            {onOpenPostSkill && (
              <button
                onClick={() => onOpenPostSkill(null)}
                className="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-fuchsia-600 to-pink-600 text-white font-bold text-xs flex items-center gap-1 shadow-sm"
              >
                <PlusCircle className="w-3.5 h-3.5" /> Create Skill Profile
              </button>
            )}
          </div>

          {mySkills.length === 0 ? (
            <div className="p-12 text-center rounded-3xl bg-white border border-slate-200">
              <FileText className="w-12 h-12 text-slate-300 mx-auto mb-3" />
              <p className="text-sm font-bold text-slate-700">No Digital Skill Profile yet.</p>
              <p className="text-xs text-slate-500 mt-1 mb-4">
                Are you a Website Designer, Video Editor, Graphic Designer, or Developer? Create your profile now!
              </p>
              {onOpenPostSkill && (
                <button
                  onClick={() => onOpenPostSkill(null)}
                  className="px-5 py-2 rounded-xl bg-fuchsia-600 text-white font-bold text-xs hover:bg-fuchsia-500"
                >
                  Create Digital Profile
                </button>
              )}
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
              {mySkills.map((skill) => (
                <div key={skill.id} className="p-5 rounded-3xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between">
                  <div>
                    <div className="flex items-start justify-between gap-3 mb-3">
                      <div className="flex items-center gap-3">
                        <img
                          src={skill.profilePhoto}
                          alt={skill.fullName}
                          className="w-14 h-14 rounded-2xl object-cover border border-slate-200 shrink-0"
                        />
                        <div>
                          <div className="flex items-center gap-1.5">
                            <h3 className="text-sm font-black text-slate-900">{skill.fullName}</h3>
                            {skill.isFeatured && (
                              <span className="px-1.5 py-0.5 rounded-md bg-amber-400 text-slate-950 text-[9px] font-black uppercase">
                                ⭐ FEATURED
                              </span>
                            )}
                          </div>
                          <div className="text-xs font-bold text-fuchsia-700">{skill.professionalTitle}</div>
                          <div className="text-[11px] text-slate-500 font-medium">
                            {skill.mainSkill} ({skill.experience})
                          </div>
                        </div>
                      </div>

                      {onDeleteSkill && (
                        <button
                          onClick={() => onDeleteSkill(skill.id)}
                          className="text-rose-600 hover:text-rose-700 p-1"
                          title="Delete profile"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      )}
                    </div>

                    <p className="text-xs text-slate-600 line-clamp-2 leading-relaxed mb-3">{skill.about}</p>

                    <div className="flex flex-wrap gap-1 mb-3">
                      {skill.skills.map((s, idx) => (
                        <span key={idx} className="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-bold">
                          {s}
                        </span>
                      ))}
                    </div>
                  </div>

                  <div className="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    {onSelectSkill && (
                      <button
                        onClick={() => onSelectSkill(skill)}
                        className="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800"
                      >
                        View Digital CV
                      </button>
                    )}

                    {onOpenPostSkill && (
                      <button
                        onClick={() => onOpenPostSkill(skill)}
                        className="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200"
                      >
                        Edit Profile
                      </button>
                    )}

                    {onOpenPromoteModal && !skill.isFeatured && (
                      <button
                        onClick={() => onOpenPromoteModal('SKILL', skill.id)}
                        className="px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black text-xs shadow-xs"
                      >
                        ⭐ Promote (Rs. 1,000)
                      </button>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {/* 4. 📣 PROMOTE SECTION (Centralized Promote Hub) */}
      {activeTab === 'promote' && (
        <div className="space-y-6">
          {/* Promote Header Callout */}
          <div className="p-6 rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-amber-950 text-white border border-amber-400/40 shadow-xl relative overflow-hidden">
            <div className="relative z-10">
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400 text-slate-950 text-xs font-black uppercase mb-3">
                <Megaphone className="w-3.5 h-3.5" /> SargodhaMart Promote System
              </div>
              <h2 className="text-xl sm:text-2xl font-black text-white mb-2">
                Get Top Visibility Across Sargodha, Shaheenabad & Sillanwali
              </h2>
              <p className="text-xs sm:text-sm text-amber-100/90 leading-relaxed max-w-2xl mb-4">
                Promote your approved product or digital skill for <strong>Rs. 1,000 / 15 Days</strong>. Promoted items get the gold ⭐ FEATURED badge, homepage spotlight, and automatic broadcast to our official Telegram channel!
              </p>
              <div className="p-3 rounded-2xl bg-black/40 border border-amber-500/30 text-xs text-amber-200 max-w-xl">
                ⚖️ <strong>Fee Separation Guarantee:</strong> Promotion is an optional 15-day marketing add-on. It is completely separate from your one-time lifetime seller activation.
              </div>
            </div>
          </div>

          {/* Sub-tabs: Promote My Product | Promote My Skill | Promotion History */}
          <div className="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3 text-xs font-bold">
            <button
              onClick={() => setPromoteSubTab('products')}
              className={`px-3.5 py-1.5 rounded-xl transition-all ${
                promoteSubTab === 'products'
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'bg-slate-100 text-slate-600 hover:text-slate-900'
              }`}
            >
              Promote My Product ({myListings.length})
            </button>
            <button
              onClick={() => setPromoteSubTab('skills')}
              className={`px-3.5 py-1.5 rounded-xl transition-all ${
                promoteSubTab === 'skills'
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'bg-slate-100 text-slate-600 hover:text-slate-900'
              }`}
            >
              Promote My Skill ({mySkills.length})
            </button>
            <button
              onClick={() => setPromoteSubTab('history')}
              className={`px-3.5 py-1.5 rounded-xl transition-all ${
                promoteSubTab === 'history'
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'bg-slate-100 text-slate-600 hover:text-slate-900'
              }`}
            >
              Promotion History ({myPromotions.length})
            </button>
          </div>

          {/* Subtab 1: Promote My Product */}
          {promoteSubTab === 'products' && (
            <div className="space-y-4">
              <h3 className="text-sm font-black text-slate-900">Your Approved Products Eligible for Promotion:</h3>
              {myListings.length === 0 ? (
                <div className="p-8 text-center rounded-2xl bg-white border border-slate-200 text-xs text-slate-500">
                  You have no approved products yet. Post a product first to promote it.
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {myListings.map((listing) => (
                    <div key={listing.id} className="p-4 rounded-2xl bg-white border border-slate-200 flex items-center justify-between gap-4 shadow-xs">
                      <div className="flex items-center gap-3">
                        <img
                          src={listing.images[0]}
                          alt={listing.title}
                          className="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0"
                        />
                        <div>
                          <div className="text-xs font-black text-slate-900 line-clamp-1">{listing.title}</div>
                          <div className="text-[11px] font-bold text-emerald-700">
                            Rs. {listing.price.toLocaleString()} • {listing.city}
                          </div>
                          <div className="text-[10px] font-medium text-slate-500 mt-0.5">
                            Status:{' '}
                            {listing.isFeatured ? (
                              <span className="text-amber-600 font-bold">⭐ Active Featured</span>
                            ) : (
                              <span className="text-slate-600">Standard Listing</span>
                            )}
                          </div>
                        </div>
                      </div>

                      {onOpenPromoteModal && (
                        <button
                          onClick={() => onOpenPromoteModal('PRODUCT', listing.id)}
                          className="px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black text-xs shrink-0 shadow-sm"
                        >
                          {listing.isFeatured ? 'Extend Promotion' : '📣 Promote Product'}
                        </button>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}

          {/* Subtab 2: Promote My Skill */}
          {promoteSubTab === 'skills' && (
            <div className="space-y-4">
              <h3 className="text-sm font-black text-slate-900">Your Approved Digital Skills Eligible for Promotion:</h3>
              {mySkills.length === 0 ? (
                <div className="p-8 text-center rounded-2xl bg-white border border-slate-200 text-xs text-slate-500">
                  You have no Digital Skill profiles yet. Create a Digital Skill Profile first.
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {mySkills.map((skill) => (
                    <div key={skill.id} className="p-4 rounded-2xl bg-white border border-slate-200 flex items-center justify-between gap-4 shadow-xs">
                      <div className="flex items-center gap-3">
                        <img
                          src={skill.profilePhoto}
                          alt={skill.fullName}
                          className="w-14 h-14 rounded-xl object-cover border border-slate-200 shrink-0"
                        />
                        <div>
                          <div className="text-xs font-black text-slate-900 line-clamp-1">{skill.professionalTitle}</div>
                          <div className="text-[11px] font-bold text-fuchsia-700">
                            {skill.mainSkill} ({skill.experience})
                          </div>
                          <div className="text-[10px] font-medium text-slate-500 mt-0.5">
                            Status:{' '}
                            {skill.isFeatured ? (
                              <span className="text-amber-600 font-bold">⭐ Active Featured</span>
                            ) : (
                              <span className="text-slate-600">Standard Profile</span>
                            )}
                          </div>
                        </div>
                      </div>

                      {onOpenPromoteModal && (
                        <button
                          onClick={() => onOpenPromoteModal('SKILL', skill.id)}
                          className="px-3.5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black text-xs shrink-0 shadow-sm"
                        >
                          {skill.isFeatured ? 'Extend Promotion' : '⭐ Promote Skill'}
                        </button>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}

          {/* Subtab 3: Promotion History */}
          {promoteSubTab === 'history' && (
            <div className="space-y-4">
              <h3 className="text-sm font-black text-slate-900">Your Promotion Payments & History:</h3>
              {myPromotions.length === 0 ? (
                <div className="p-8 text-center rounded-2xl bg-white border border-slate-200 text-xs text-slate-500">
                  No promotion requests recorded yet. Click "Promote Item" above to get started.
                </div>
              ) : (
                <div className="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                  <table className="w-full text-left text-xs">
                    <thead className="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px]">
                      <tr>
                        <th className="p-3">ID / Type</th>
                        <th className="p-3">Item Promoted</th>
                        <th className="p-3">Amount</th>
                        <th className="p-3">Trx Reference</th>
                        <th className="p-3">Submitted</th>
                        <th className="p-3">Status</th>
                        <th className="p-3">Active Duration</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {myPromotions.map((p) => (
                        <tr key={p.id} className="hover:bg-slate-50/50">
                          <td className="p-3">
                            <span className="font-mono font-bold text-slate-800">#{p.id}</span>
                            <span className="block text-[10px] text-slate-500">{p.promotionType}</span>
                          </td>
                          <td className="p-3 font-bold text-slate-900">{p.entityTitle}</td>
                          <td className="p-3 font-extrabold text-emerald-700">Rs. {p.amount.toLocaleString()}</td>
                          <td className="p-3 font-mono text-[11px] text-slate-600">{p.transactionReference}</td>
                          <td className="p-3 text-slate-500">{p.submittedAt.slice(0, 10)}</td>
                          <td className="p-3">
                            {p.status === 'APPROVED' && (
                              <span className="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase">
                                APPROVED
                              </span>
                            )}
                            {p.status === 'PENDING' && (
                              <span className="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-black uppercase">
                                PENDING REVIEW
                              </span>
                            )}
                            {p.status === 'REJECTED' && (
                              <div>
                                <span className="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-black uppercase">
                                  REJECTED
                                </span>
                                {p.rejectionReason && (
                                  <div className="text-[10px] text-rose-600 mt-0.5">{p.rejectionReason}</div>
                                )}
                              </div>
                            )}
                            {p.status === 'EXPIRED' && (
                              <span className="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-black uppercase">
                                EXPIRED
                              </span>
                            )}
                          </td>
                          <td className="p-3 text-slate-500">
                            {p.startAt && p.endAt ? (
                              <span>
                                {p.startAt.slice(0, 10)} to {p.endAt.slice(0, 10)}
                              </span>
                            ) : (
                              <span>{p.durationDays} Days</span>
                            )}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          )}
        </div>
      )}

      {/* 5. ADVERTISING TAB */}
      {activeTab === 'advertising' && (
        <div className="space-y-6">
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 className="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
                <Megaphone className="w-5 h-5 text-amber-500" />
                Advertising Campaigns & Banners
              </h2>
              <p className="text-xs text-slate-500 font-medium mt-0.5">
                Multi-channel local marketing across SargodhaMart web directories and official Telegram Channel.
              </p>
            </div>

            {onOpenCreateAd && (
              <button
                onClick={() => onOpenCreateAd()}
                className="px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-md shadow-amber-500/20 transition-all hover:scale-102 shrink-0"
              >
                <PlusCircle className="w-4 h-4" /> Create Advertisement
              </button>
            )}
          </div>

          {/* Advertising Sub-tabs */}
          <div className="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-2 text-xs">
            <button
              onClick={() => setAdSubTab('my-ads')}
              className={`px-3 py-1.5 rounded-xl font-extrabold transition-all ${
                adSubTab === 'my-ads'
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
              }`}
            >
              My Advertisements ({myAds.length})
            </button>

            <button
              onClick={() => setAdSubTab('packages')}
              className={`px-3 py-1.5 rounded-xl font-extrabold transition-all ${
                adSubTab === 'packages'
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
              }`}
            >
              Advertising Packages ({advertisingPackages.filter((p) => p.isActive).length})
            </button>

            <button
              onClick={() => setAdSubTab('history')}
              className={`px-3 py-1.5 rounded-xl font-extrabold transition-all ${
                adSubTab === 'history'
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
              }`}
            >
              Payment History ({myAds.length})
            </button>
          </div>

          {/* Subtab 1: My Advertisements */}
          {adSubTab === 'my-ads' && (
            <div>
              {myAds.length === 0 ? (
                <div className="p-12 text-center rounded-3xl bg-white border border-slate-200">
                  <Megaphone className="w-12 h-12 text-slate-300 mx-auto mb-3" />
                  <h3 className="text-sm font-bold text-slate-700">No Advertisements Yet</h3>
                  <p className="text-xs text-slate-500 mt-1 max-w-sm mx-auto mb-4">
                    Boost your clinic, store, dairy farm, or services across Sargodha with targeted web banners and Telegram broadcasts.
                  </p>
                  {onOpenCreateAd && (
                    <button
                      onClick={() => onOpenCreateAd()}
                      className="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-sm"
                    >
                      Book Advertisement Campaign
                    </button>
                  )}
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {myAds.map((ad) => (
                    <div
                      key={ad.id}
                      className="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs flex flex-col justify-between"
                    >
                      <div>
                        <div className="flex items-center justify-between gap-2 mb-3">
                          <span className="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black uppercase">
                            {ad.packageName}
                          </span>
                          <span
                            className={`px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase ${
                              ad.status === 'ACTIVE'
                                ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                                : ad.status === 'PENDING'
                                ? 'bg-amber-100 text-amber-800 border border-amber-300 animate-pulse'
                                : ad.status === 'EXPIRED'
                                ? 'bg-slate-100 text-slate-600'
                                : 'bg-rose-100 text-rose-800 border border-rose-300'
                            }`}
                          >
                            {ad.status}
                          </span>
                        </div>

                        <div className="flex items-start gap-3 mb-3">
                          <img
                            src={ad.imageUrl}
                            alt={ad.title}
                            className="w-16 h-16 rounded-xl object-cover border border-slate-200 shrink-0"
                          />
                          <div>
                            <h4 className="text-sm font-black text-slate-900 leading-tight">{ad.title}</h4>
                            <p className="text-xs text-slate-500 line-clamp-2 mt-1">{ad.description}</p>
                          </div>
                        </div>

                        <div className="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 space-y-1 text-xs text-slate-600 mb-3">
                          <div className="flex justify-between">
                            <span className="text-slate-400">Price & Duration:</span>
                            <span className="font-bold text-slate-900">
                              Rs. {ad.amountPaid.toLocaleString()} / {ad.durationDays} Days
                            </span>
                          </div>
                          {ad.startAt && (
                            <div className="flex justify-between">
                              <span className="text-slate-400">Active Period:</span>
                              <span className="font-bold text-slate-900">{ad.startAt} to {ad.endAt}</span>
                            </div>
                          )}
                          <div className="flex justify-between">
                            <span className="text-slate-400">Transaction ID:</span>
                            <code className="font-mono text-[11px] font-bold text-cyan-800">{ad.transactionReference}</code>
                          </div>
                          {ad.telegramEnabled && (
                            <div className="flex justify-between">
                              <span className="text-slate-400">Telegram Status:</span>
                              <span className="font-bold text-blue-700">
                                {ad.telegramStatus === 'published' ? `Published (#${ad.telegramMessageId})` : ad.telegramStatus}
                              </span>
                            </div>
                          )}
                        </div>

                        {ad.rejectionReason && (
                          <div className="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs mb-3">
                            <strong>Admin Note: </strong> {ad.rejectionReason}
                          </div>
                        )}
                      </div>

                      <div className="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>Submitted {ad.createdAt}</span>
                        {ad.status === 'PENDING' && (
                          <span className="text-amber-700 font-bold">Waiting for verification</span>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}

          {/* Subtab 2: Packages Catalog */}
          {adSubTab === 'packages' && (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {advertisingPackages
                .filter((p) => p.isActive)
                .map((pkg) => (
                  <div
                    key={pkg.id}
                    className={`bg-white rounded-3xl p-5 border-2 transition-all flex flex-col justify-between relative ${
                      pkg.recommended
                        ? 'border-amber-400 shadow-md shadow-amber-500/10'
                        : 'border-slate-200 hover:border-slate-300'
                    }`}
                  >
                    {pkg.recommended && (
                      <span className="absolute -top-3 right-4 px-2.5 py-0.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 text-[10px] font-black uppercase shadow-xs flex items-center gap-1">
                        <Sparkles className="w-3 h-3 fill-slate-950" /> RECOMMENDED
                      </span>
                    )}

                    <div>
                      <span className="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-extrabold uppercase">
                        {pkg.adType}
                      </span>
                      <h4 className="text-sm font-black text-slate-900 mt-1 mb-1">{pkg.name}</h4>
                      <p className="text-xs text-slate-500 line-clamp-2 leading-relaxed mb-3">
                        {pkg.description}
                      </p>

                      <div className="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 mb-3 space-y-1 text-xs">
                        <div className="flex justify-between font-bold">
                          <span className="text-slate-500">Price:</span>
                          <span className="text-cyan-800 font-black">Rs. {pkg.price.toLocaleString()}</span>
                        </div>
                        <div className="flex justify-between font-bold">
                          <span className="text-slate-500">Duration:</span>
                          <span className="text-slate-800">{pkg.durationDays} Days</span>
                        </div>
                        {pkg.telegramEnabled && (
                          <div className="text-blue-700 font-bold text-[11px] pt-1">
                            📲 Includes official Telegram broadcast
                          </div>
                        )}
                      </div>

                      <ul className="space-y-1 mb-4 text-xs text-slate-600">
                        {pkg.features.map((feat, i) => (
                          <li key={i} className="flex items-center gap-1.5">
                            <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                            <span>{feat}</span>
                          </li>
                        ))}
                      </ul>
                    </div>

                    {onOpenCreateAd && (
                      <button
                        onClick={() => onOpenCreateAd(pkg.id)}
                        className={`w-full py-2.5 rounded-xl font-black text-xs transition-all shadow-xs ${
                          pkg.recommended
                            ? 'bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-amber-500/25'
                            : 'bg-slate-900 hover:bg-slate-800 text-white'
                        }`}
                      >
                        Choose this Package
                      </button>
                    )}
                  </div>
                ))}
            </div>
          )}

          {/* Subtab 3: Payment History */}
          {adSubTab === 'history' && (
            <div className="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs">
              <div className="overflow-x-auto">
                <table className="w-full text-left border-collapse text-xs">
                  <thead>
                    <tr className="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold">
                      <th className="py-3 px-4">Campaign Title</th>
                      <th className="py-3 px-4">Package</th>
                      <th className="py-3 px-4">Amount</th>
                      <th className="py-3 px-4">Transaction ID</th>
                      <th className="py-3 px-4">Date</th>
                      <th className="py-3 px-4">Status</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {myAds.map((ad) => (
                      <tr key={ad.id} className="hover:bg-slate-50/60">
                        <td className="py-3 px-4 font-bold text-slate-900 max-w-[180px] truncate">{ad.title}</td>
                        <td className="py-3 px-4 text-slate-600">{ad.packageName}</td>
                        <td className="py-3 px-4 font-black text-cyan-800">Rs. {ad.amountPaid.toLocaleString()}</td>
                        <td className="py-3 px-4 font-mono text-[11px] text-slate-500">{ad.transactionReference}</td>
                        <td className="py-3 px-4 text-slate-500">{ad.createdAt}</td>
                        <td className="py-3 px-4">
                          <span
                            className={`px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase ${
                              ad.status === 'ACTIVE'
                                ? 'bg-emerald-100 text-emerald-800'
                                : ad.status === 'PENDING'
                                ? 'bg-amber-100 text-amber-800'
                                : 'bg-slate-100 text-slate-600'
                            }`}
                          >
                            {ad.status}
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      )}

      {/* 5. FAVORITES TAB */}
      {activeTab === 'favorites' && (
        <div className="space-y-4">
          <h2 className="text-base font-black text-slate-900">Your Saved Favorites</h2>
          {myFavoriteListings.length === 0 ? (
            <div className="p-12 text-center rounded-3xl bg-white border border-slate-200">
              <Heart className="w-12 h-12 text-slate-300 mx-auto mb-3" />
              <p className="text-sm font-bold text-slate-700">No favorite items saved yet.</p>
              <p className="text-xs text-slate-500 mt-1">Click the heart icon on any ad to bookmark it here.</p>
            </div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
              {myFavoriteListings.map((listing) => (
                <div
                  key={listing.id}
                  onClick={() => onSelectListing(listing)}
                  className="rounded-3xl bg-white border border-slate-200 overflow-hidden shadow-sm hover:shadow-md cursor-pointer transition-shadow"
                >
                  <div className="h-44 overflow-hidden relative">
                    <img src={listing.images[0]} alt={listing.title} className="w-full h-full object-cover" />
                    <span className="absolute bottom-3 right-3 px-2 py-0.5 rounded-full bg-slate-950/70 text-white text-[10px] font-black">
                      Rs. {listing.price.toLocaleString()}
                    </span>
                  </div>
                  <div className="p-4">
                    <h3 className="text-xs font-black text-slate-900 line-clamp-1">{listing.title}</h3>
                    <p className="text-[11px] text-slate-500">{listing.city}</p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}
    </div>
  );
};
