import React, { useState } from 'react';
import { X, Phone, MessageCircle, Heart, MapPin, Sparkles, Eye, ShieldCheck, Share2, Flag, ExternalLink, Copy, Check } from 'lucide-react';
import { Listing } from '../types';

interface ProductDetailModalProps {
  listing: Listing | null;
  isOpen: boolean;
  onClose: () => void;
  isFavorite: boolean;
  onToggleFavorite: (id: number) => void;
  onReport: (listing: Listing) => void;
}

export const ProductDetailModal: React.FC<ProductDetailModalProps> = ({
  listing,
  isOpen,
  onClose,
  isFavorite,
  onToggleFavorite,
  onReport,
}) => {
  const [activeImageIdx, setActiveImageIdx] = useState(0);
  const [copied, setCopied] = useState(false);
  const [showShare, setShowShare] = useState(false);

  if (!isOpen || !listing) return null;

  const productUrl = typeof window !== 'undefined' ? `${window.location.origin}/?product=${listing.id}` : '';

  const handleCopyLink = () => {
    navigator.clipboard.writeText(productUrl || window.location.href);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const shareText = `Check out "${listing.title}" on SargodhaMart for Rs. ${listing.price.toLocaleString()} (${listing.city}): ${productUrl}`;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md overflow-y-auto">
      <div className="relative w-full max-w-3xl bg-white border border-cyan-200 rounded-3xl overflow-hidden shadow-[0_20px_60px_-15px_rgba(6,182,212,0.25)] my-8">
        <button
          onClick={onClose}
          className="absolute top-4 right-4 z-20 p-2 rounded-xl bg-white/90 text-slate-700 hover:text-slate-950 hover:bg-white border border-slate-200 shadow-md backdrop-blur-md transition-all"
        >
          <X className="w-5 h-5" />
        </button>

        <div className="grid grid-cols-1 md:grid-cols-2">
          {/* Images Gallery */}
          <div className="bg-slate-50 p-4 flex flex-col justify-between border-b md:border-b-0 md:border-r border-slate-100">
            <div className="relative aspect-4/3 rounded-2xl overflow-hidden mb-3 border border-slate-200 bg-white">
              <img
                src={listing.images[activeImageIdx] || listing.images[0]}
                alt={listing.title}
                className="w-full h-full object-cover"
              />
              {listing.isFeatured && (
                <div className="absolute top-3 left-3 px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-gradient-to-r from-fuchsia-600 to-purple-600 text-white shadow-lg shadow-fuchsia-600/30 flex items-center gap-1">
                  <Sparkles className="w-3 h-3" /> Featured
                </div>
              )}
            </div>

            {/* Thumbnails */}
            {listing.images.length > 1 && (
              <div className="flex gap-2">
                {listing.images.map((img, idx) => (
                  <button
                    key={idx}
                    onClick={() => setActiveImageIdx(idx)}
                    className={`w-16 h-16 rounded-xl overflow-hidden border-2 transition-all ${
                      activeImageIdx === idx ? 'border-cyan-500 scale-105 shadow-md shadow-cyan-500/20' : 'border-slate-200 opacity-70'
                    }`}
                  >
                    <img src={img} alt="Thumbnail" className="w-full h-full object-cover" />
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* Product & Seller Details */}
          <div className="p-6 flex flex-col justify-between max-h-[80vh] overflow-y-auto bg-white">
            <div>
              {/* Category & City */}
              <div className="flex items-center justify-between gap-2 text-xs text-slate-500 mb-2 font-medium">
                <span>{listing.categoryName} {listing.subcategory ? `• ${listing.subcategory}` : ''}</span>
                <span className="flex items-center gap-1 text-cyan-700 font-bold">
                  <MapPin className="w-3 h-3 text-cyan-600" /> {listing.city} ({listing.area})
                </span>
              </div>

              {/* Title */}
              <h2 className="text-xl font-black text-slate-900 mb-3">{listing.title}</h2>

              {/* Price & Condition */}
              <div className="flex items-baseline gap-3 mb-4 pb-4 border-b border-slate-100">
                <div className="text-2xl font-black text-cyan-700">
                  <span className="text-xs font-bold mr-1">Rs.</span>
                  {listing.price.toLocaleString()}
                </div>
                <div className="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-slate-100 text-slate-800 border border-slate-200">
                  {listing.condition}
                </div>
              </div>

              {/* Description */}
              <div className="mb-6">
                <h4 className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Description</h4>
                <p className="text-slate-700 text-xs leading-relaxed whitespace-pre-line font-medium">{listing.description}</p>
              </div>

              {/* Seller Information Card */}
              <div className="bg-slate-50 border border-slate-200 rounded-2xl p-4 mb-4 shadow-xs">
                <div className="flex items-center justify-between mb-2">
                  <div className="font-bold text-slate-900 text-sm">{listing.sellerName}</div>
                  <div className="flex items-center gap-1 text-[10px] text-emerald-800 font-bold bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-300">
                    <ShieldCheck className="w-3 h-3 text-emerald-600" /> Verified Seller
                  </div>
                </div>
                <div className="text-[11px] text-slate-500 mb-3 font-medium">
                  📍 {listing.sellerCity} · {listing.sellerArea}
                </div>

                {/* Seller WhatsApp Group / Channel Link if provided */}
                {listing.sellerWhatsappGroup && (
                  <a
                    href={listing.sellerWhatsappGroup}
                    target="_blank"
                    rel="noreferrer"
                    className="inline-flex items-center gap-1.5 text-xs text-emerald-700 font-bold hover:underline mb-2"
                  >
                    <span>💬</span> Join Seller's WhatsApp Group / Channel <ExternalLink className="w-3 h-3" />
                  </a>
                )}
              </div>
            </div>

            {/* Direct Contact Buttons (No internal chat) */}
            <div className="space-y-2 pt-2 border-t border-slate-100">
              <div className="grid grid-cols-2 gap-2">
                <a
                  href={`tel:${listing.sellerPhone}`}
                  className="py-2.5 px-4 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 flex items-center justify-center gap-2 transition-all shadow-xs"
                >
                  <Phone className="w-4 h-4 text-cyan-600" />
                  <span>Call Seller</span>
                </a>
                <a
                  href={`https://wa.me/92${listing.sellerPhone.replace(/^0/, '')}?text=${encodeURIComponent(`Salam! I saw your listing "${listing.title}" on SargodhaMart. Is it available?`)}`}
                  target="_blank"
                  rel="noreferrer"
                  className="py-2.5 px-4 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center gap-2 transition-all shadow-lg shadow-emerald-600/25"
                >
                  <MessageCircle className="w-4 h-4" />
                  <span>WhatsApp</span>
                </a>
              </div>

              {/* Utility actions: Favorite, Share, Report */}
              <div className="flex items-center justify-between text-xs pt-2">
                <button
                  onClick={() => onToggleFavorite(listing.id)}
                  className="text-slate-600 hover:text-rose-600 font-semibold flex items-center gap-1.5 transition-colors"
                >
                  <Heart className={`w-4 h-4 ${isFavorite ? 'fill-rose-500 text-rose-500' : ''}`} />
                  <span>{isFavorite ? 'Saved to Favorites' : 'Add to Favorites'}</span>
                </button>

                <div className="flex items-center gap-3">
                  <button
                    onClick={() => setShowShare(!showShare)}
                    className="text-slate-600 hover:text-cyan-700 font-semibold flex items-center gap-1.5 transition-colors"
                  >
                    <Share2 className="w-4 h-4" />
                    <span>Share</span>
                  </button>

                  <button
                    onClick={() => onReport(listing)}
                    className="text-slate-500 hover:text-rose-600 font-semibold flex items-center gap-1 transition-colors"
                  >
                    <Flag className="w-3.5 h-3.5" />
                    <span>Report</span>
                  </button>
                </div>
              </div>

              {/* Share Options Popup */}
              {showShare && (
                <div className="p-3 bg-slate-50 border border-slate-200 rounded-xl mt-2 flex items-center justify-between gap-2 shadow-sm">
                  <div className="flex items-center gap-2">
                    <a
                      href={`https://wa.me/?text=${encodeURIComponent(shareText)}`}
                      target="_blank"
                      rel="noreferrer"
                      className="px-2.5 py-1 rounded bg-emerald-600 text-white text-[11px] font-bold"
                    >
                      WhatsApp
                    </a>
                    <a
                      href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`}
                      target="_blank"
                      rel="noreferrer"
                      className="px-2.5 py-1 rounded bg-blue-600 text-white text-[11px] font-bold"
                    >
                      Facebook
                    </a>
                  </div>
                  <button
                    onClick={handleCopyLink}
                    className="px-2.5 py-1 rounded bg-white border border-slate-200 text-slate-700 text-[11px] font-bold flex items-center gap-1 shadow-xs"
                  >
                    {copied ? <Check className="w-3 h-3 text-emerald-600" /> : <Copy className="w-3 h-3" />}
                    {copied ? 'Copied' : 'Copy Link'}
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
