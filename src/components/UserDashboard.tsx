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
} from 'lucide-react';
import { UserProfile, Listing, JobPost } from '../types';

interface UserDashboardProps {
  currentUser: UserProfile;
  listings: Listing[];
  jobs: JobPost[];
  favorites: number[];
  onOpenPostAd: () => void;
  onOpenPostJob: () => void;
  onOpenActivation: () => void;
  onDeleteListing: (id: number) => void;
  onDeleteJob: (id: number) => void;
  onSelectListing: (listing: Listing) => void;
  onLogout: () => void;
}

export const UserDashboard: React.FC<UserDashboardProps> = ({
  currentUser,
  listings,
  jobs,
  favorites,
  onOpenPostAd,
  onOpenPostJob,
  onOpenActivation,
  onDeleteListing,
  onDeleteJob,
  onSelectListing,
  onLogout,
}) => {
  const [activeTab, setActiveTab] = useState<'products' | 'jobs' | 'favorites'>('products');

  const myListings = listings.filter((l) => l.userId === currentUser.id);
  const myJobs = jobs.filter((j) => j.userId === currentUser.id);
  const myFavoriteListings = listings.filter((l) => favorites.includes(l.id));

  return (
    <div className="py-8 px-4 max-w-7xl mx-auto">
      {/* User Header Profile Card - White Neon */}
      <div className="bg-white/95 backdrop-blur-xl border border-cyan-200 rounded-3xl p-6 md:p-8 mb-8 shadow-[0_10px_35px_-5px_rgba(6,182,212,0.12)]">
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
                className="px-4 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25 transition-all flex items-center gap-1.5 hover:shadow-[0_0_15px_rgba(6,182,212,0.4)]"
              >
                <ShieldCheck className="w-4 h-4" /> Activate Lifetime Account (Rs. 1,000)
              </button>
            ) : (
              <>
                <button
                  onClick={onOpenPostAd}
                  className="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/20 flex items-center gap-1.5 hover:shadow-[0_0_15px_rgba(6,182,212,0.4)] transition-all"
                >
                  <PlusCircle className="w-4 h-4" /> Post Free Ad
                </button>
                <button
                  onClick={onOpenPostJob}
                  className="px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white shadow-lg shadow-blue-600/20 flex items-center gap-1.5 hover:shadow-[0_0_15px_rgba(37,99,235,0.4)] transition-all"
                >
                  <Briefcase className="w-4 h-4" /> Post Job
                </button>
              </>
            )}

            {/* Logout button */}
            <button
              onClick={onLogout}
              className="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 flex items-center gap-1.5 transition-all shadow-xs"
              title="Log out of account"
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
              <ShieldCheck className="w-5 h-5 text-emerald-600" />
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

      {/* Tabs */}
      <div className="flex items-center gap-2 mb-6 border-b border-slate-200 pb-3 text-xs font-bold">
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
          onClick={() => setActiveTab('favorites')}
          className={`px-4 py-2 rounded-xl transition-all flex items-center gap-1.5 ${
            activeTab === 'favorites'
              ? 'bg-white text-rose-800 border border-rose-300 shadow-[0_0_12px_rgba(244,63,94,0.18)]'
              : 'text-slate-600 hover:text-slate-900'
          }`}
        >
          <Heart className="w-3.5 h-3.5 text-rose-600" />
          Saved Favorites ({myFavoriteListings.length})
        </button>
      </div>

      {/* Tab 1: My Products */}
      {activeTab === 'products' && (
        <div>
          {myListings.length === 0 ? (
            <div className="py-16 text-center bg-white border border-slate-200 rounded-2xl p-8 shadow-xs">
              <ShoppingBag className="w-12 h-12 text-slate-400 mx-auto mb-3" />
              <h3 className="text-base font-bold text-slate-900 mb-1">You haven't posted any products yet</h3>
              <p className="text-slate-500 text-xs mb-4">Post your first product to sell in Sargodha, Shaheenabad & Sillanwali.</p>
              <button
                onClick={onOpenPostAd}
                className="px-4 py-2 rounded-xl text-xs font-bold bg-cyan-600 text-white hover:bg-cyan-500 shadow-md shadow-cyan-600/20"
              >
                Post an Ad Now
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {myListings.map((listing) => (
                <div
                  key={listing.id}
                  className="bg-white border border-cyan-200 rounded-2xl p-4 flex flex-col justify-between shadow-xs hover:border-cyan-400 hover:shadow-md transition-all"
                >
                  <div className="flex gap-3 mb-3">
                    <img
                      src={listing.images[0]}
                      alt={listing.title}
                      className="w-20 h-20 rounded-xl object-cover border border-slate-200 shrink-0"
                    />
                    <div className="overflow-hidden">
                      <div className="text-xs text-slate-500 font-medium truncate">{listing.categoryName}</div>
                      <h4 className="font-bold text-slate-900 text-sm truncate mb-1">{listing.title}</h4>
                      <div className="text-cyan-800 font-bold text-sm">Rs. {listing.price.toLocaleString()}</div>
                      <div className="text-[10px] text-slate-500 mt-1 font-medium">Views: {listing.views} • Status: {listing.status}</div>
                    </div>
                  </div>

                  <div className="flex items-center justify-between pt-3 border-t border-slate-100 text-xs font-semibold">
                    <button
                      onClick={() => onSelectListing(listing)}
                      className="text-cyan-700 font-bold hover:underline"
                    >
                      View Live Product
                    </button>
                    <button
                      onClick={() => onDeleteListing(listing.id)}
                      className="text-rose-600 hover:text-rose-700 flex items-center gap-1 font-bold"
                    >
                      <Trash2 className="w-3.5 h-3.5" /> Delete
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {/* Tab 2: My Jobs */}
      {activeTab === 'jobs' && (
        <div>
          {myJobs.length === 0 ? (
            <div className="py-16 text-center bg-white border border-slate-200 rounded-2xl p-8 shadow-xs">
              <Briefcase className="w-12 h-12 text-slate-400 mx-auto mb-3" />
              <h3 className="text-base font-bold text-slate-900 mb-1">No job posts created yet</h3>
              <p className="text-slate-500 text-xs mb-4">Post a worker requirement or announce your availability.</p>
              <button
                onClick={onOpenPostJob}
                className="px-4 py-2 rounded-xl text-xs font-bold bg-blue-600 text-white hover:bg-blue-500 shadow-md shadow-blue-600/20"
              >
                Post a Job Post
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {myJobs.map((job) => (
                <div
                  key={job.id}
                  className="bg-white border border-blue-200 rounded-2xl p-4 flex flex-col justify-between shadow-xs hover:border-blue-400 hover:shadow-md transition-all"
                >
                  <div>
                    <span
                      className={`inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase mb-2 ${
                        job.postType === 'need_worker' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-cyan-50 text-cyan-700 border border-cyan-200'
                      }`}
                    >
                      {job.postType === 'need_worker' ? 'Hiring' : 'Seeking Work'}
                    </span>
                    <h4 className="font-bold text-slate-900 text-sm line-clamp-1 mb-1">{job.title}</h4>
                    <p className="text-slate-600 text-xs line-clamp-2 mb-2 font-medium">{job.description}</p>
                  </div>

                  <div className="flex items-center justify-between pt-3 border-t border-slate-100 text-xs font-semibold">
                    <span className="text-slate-500 text-[11px] font-medium">{job.city}</span>
                    <button
                      onClick={() => onDeleteJob(job.id)}
                      className="text-rose-600 hover:text-rose-700 flex items-center gap-1 font-bold"
                    >
                      <Trash2 className="w-3.5 h-3.5" /> Delete
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {/* Tab 3: Favorites */}
      {activeTab === 'favorites' && (
        <div>
          {myFavoriteListings.length === 0 ? (
            <div className="py-16 text-center bg-white border border-slate-200 rounded-2xl p-8 shadow-xs">
              <Heart className="w-12 h-12 text-slate-400 mx-auto mb-3" />
              <h3 className="text-base font-bold text-slate-900 mb-1">No saved favorites yet</h3>
              <p className="text-slate-500 text-xs">Click the heart icon on any product to save it here for quick access.</p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {myFavoriteListings.map((listing) => (
                <div
                  key={listing.id}
                  onClick={() => onSelectListing(listing)}
                  className="bg-white border border-cyan-200 rounded-2xl p-4 cursor-pointer hover:border-cyan-400 hover:shadow-md transition-all flex gap-3 shadow-xs"
                >
                  <img
                    src={listing.images[0]}
                    alt={listing.title}
                    className="w-20 h-20 rounded-xl object-cover border border-slate-200 shrink-0"
                  />
                  <div>
                    <h4 className="font-bold text-slate-900 text-sm line-clamp-1 mb-1">{listing.title}</h4>
                    <div className="text-cyan-800 font-bold text-sm mb-1">Rs. {listing.price.toLocaleString()}</div>
                    <div className="text-[11px] text-slate-500 font-medium">{listing.city} • {listing.sellerName}</div>
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
